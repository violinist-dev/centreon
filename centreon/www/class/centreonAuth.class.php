<?php

/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

use Pimple\Container;

require_once __DIR__ . '/centreonContact.class.php';
require_once __DIR__ . '/centreonAuth.LDAP.class.php';

/**
 * Class
 *
 * @class CentreonAuth
 */
class CentreonAuth
{
    /**
     * The default page has to be Resources status
     */
    public const DEFAULT_PAGE = 200;
    public const PWS_OCCULTATION = '******';

    public const AUTOLOGIN_ENABLE = 1;
    public const AUTOLOGIN_DISABLE = 0;

    public const PASSWORD_HASH_ALGORITHM = PASSWORD_BCRYPT;

    public const PASSWORD_VALID = 1;
    public const PASSWORD_INVALID = 0;
    public const PASSWORD_CANNOT_BE_VERIFIED = -1;

    public const ENCRYPT_MD5 = 1;
    public const ENCRYPT_SHA1 = 2;

    public const AUTH_TYPE_LOCAL = 'local';
    public const AUTH_TYPE_LDAP = 'ldap';

    // Declare Values
    /** @var array */
    public $userInfos;
    /** @var string */
    protected $login;
    /** @var string */
    protected $password;
    /** @var */
    protected $enable;
    /** @var */
    protected $userExists;
    /** @var int */
    protected $cryptEngine;
    /** @var int */
    protected $autologin;
    /** @var string[] */
    protected $cryptPossibilities = ['MD5', 'SHA1'];

    /** @var CentreonDB */
    protected $pearDB;

    /** @var int */
    protected $debug;
    /** @var Container */
    protected $dependencyInjector;

    // Flags
    /** @var */
    public $passwdOk;
    /** @var */
    protected $authType;
    /** @var array */
    protected $ldap_auto_import = [];
    /** @var array */
    protected $ldap_store_password = [];
    /** @var int */
    protected $default_page = self::DEFAULT_PAGE;

    // keep log class
    /** @var CentreonUserLog */
    protected $CentreonLog;

    // Error Message
    /** @var */
    protected $error;

    /**
     * CentreonAuth constructor
     *
     * @param Container $dependencyInjector
     * @param string $username
     * @param string $password
     * @param int $autologin
     * @param CentreonDB $pearDB
     * @param CentreonUserLog $CentreonLog
     * @param int $encryptType
     * @param string $token | for autologin
     *
     * @throws PDOException
     */
    public function __construct(
        $dependencyInjector,
        $username,
        $password,
        $autologin,
        $pearDB,
        $CentreonLog,
        $encryptType = self::ENCRYPT_MD5,
        $token = ""
    ) {
        $this->dependencyInjector = $dependencyInjector;
        $this->CentreonLog = $CentreonLog;
        $this->login = $username;
        $this->password = $password;
        $this->pearDB = $pearDB;
        $this->autologin = $autologin;
        $this->cryptEngine = $encryptType;
        $this->debug = $this->getLogFlag();

        $res = $pearDB->query(
            "SELECT ar.ar_id, ari.ari_value, ari.ari_name " .
            "FROM auth_ressource_info ari, auth_ressource ar " .
            "WHERE ari_name IN ('ldap_auto_import', 'ldap_store_password') " .
            "AND ari.ar_id = ar.ar_id " .
            "AND ar.ar_enable = '1'"
        );
        while ($row = $res->fetch()) {
            if ($row['ari_name'] == 'ldap_auto_import' && $row['ari_value']) {
                $this->ldap_auto_import[$row['ar_id']] = $row['ari_value'];
            } elseif ($row['ari_name'] == 'ldap_store_password') {
                $this->ldap_store_password[$row['ar_id']] = $row['ari_value'];
            }
        }
        $this->checkUser($username, $password, $token);
    }

    /**
     * Log enabled
     *
     * @return int
     * @throws PDOException
     */
    protected function getLogFlag()
    {
        $res = $this->pearDB->query("SELECT value FROM options WHERE `key` = 'debug_auth'");
        $data = $res->fetch();
        return $data["value"] ?? 0;
    }

    /**
     * Check if password is ok
     *
     * @param string $password
     * @param string $token
     * @param bool $autoImport
     *
     * @return void
     * @throws PDOException
     */
    protected function checkPassword($password, $token = "", $autoImport = false)
    {
        if (empty($password) && empty($token)) {
            $this->passwdOk = self::PASSWORD_INVALID;
            return;
        }

        if ($this->autologin) {
            $this->checkAutologinKey($password, $token);
            return;
        }

        if ($this->userInfos["contact_auth_type"] === self::AUTH_TYPE_LDAP) {
            $this->checkLdapPassword($password, $autoImport);
            return;
        }

        if (
            empty($this->userInfos["contact_auth_type"])
            || $this->userInfos["contact_auth_type"] === self::AUTH_TYPE_LOCAL
        ) {
            $this->checkLocalPassword($password);
            return;
        }

        $this->passwdOk = self::PASSWORD_INVALID;
    }

    /**
     * Check autologin key
     *
     * @param string $password
     * @param string $token
     */
    private function checkAutologinKey($password, $token): void
    {
        if (
            array_key_exists('contact_oreon', $this->userInfos)
            && $this->userInfos['contact_oreon'] !== '1'
        ) {
            $this->passwdOk = self::PASSWORD_INVALID;
            return;
        }

        if (
            !empty($this->userInfos["contact_autologin_key"])
            && $this->userInfos["contact_autologin_key"] === $token
        ) {
            $this->passwdOk = self::PASSWORD_VALID;
        } elseif (
            !empty($password)
            && $this->userInfos["contact_passwd"] === $password
        ) {
            $this->passwdOk = self::PASSWORD_VALID;
        } else {
            $this->passwdOk = self::PASSWORD_INVALID;
        }
    }

    /**
     * Check ldap user password
     *
     * @param string $password
     * @param bool $autoImport
     *
     * @throws PDOException
     */
    private function checkLdapPassword($password, $autoImport): void
    {
        $res = $this->pearDB->query("SELECT ar_id FROM auth_ressource WHERE ar_enable = '1'");
        $authResources = [];
        while ($row = $res->fetch()) {
            $index = $row['ar_id'];
            if (isset($this->userInfos['ar_id']) && $this->userInfos['ar_id'] == $row['ar_id']) {
                $index = 0;
            }
            $authResources[$index] = $row['ar_id'];
        }

        foreach ($authResources as $arId) {
            if ($autoImport && !isset($this->ldap_auto_import[$arId])) {
                break;
            }
            if ($this->passwdOk == self::PASSWORD_VALID) {
                break;
            }
            $authLDAP = new CentreonAuthLDAP(
                $this->pearDB,
                $this->CentreonLog,
                $this->login,
                $this->password,
                $this->userInfos,
                $arId
            );
            $this->passwdOk = $authLDAP->checkPassword();

            if ($this->passwdOk == self::PASSWORD_VALID) {
                if (isset($this->ldap_store_password[$arId]) && $this->ldap_store_password[$arId]) {
                    if (!isset($this->userInfos["contact_passwd"])) {
                        $hashedPassword = password_hash($this->password, self::PASSWORD_HASH_ALGORITHM);
                        $contact = new CentreonContact($this->pearDB);
                        $contactId = $contact->findContactIdByAlias($this->login);
                        if ($contactId !== null) {
                            $contact->addPasswordByContactId($contactId, $hashedPassword);
                        }
                    // Update password if LDAP authentication is valid but password not up to date in Centreon.
                    } elseif (!password_verify($this->password, $this->userInfos["contact_passwd"])) {
                        $hashedPassword = password_hash($this->password, self::PASSWORD_HASH_ALGORITHM);
                        $contact = new CentreonContact($this->pearDB);
                        $contactId = $contact->findContactIdByAlias($this->login);
                        if ($contactId !== null) {
                            $contact->replacePasswordByContactId(
                                $contactId,
                                $this->userInfos["contact_passwd"],
                                $hashedPassword
                            );
                        }
                    }
                }
                break;
            }
        }

        if ($this->passwdOk == self::PASSWORD_CANNOT_BE_VERIFIED) {
            if (
                !empty($password)
                && !empty($this->userInfos["contact_passwd"])
                && password_verify($password, $this->userInfos["contact_passwd"])
            ) {
                $this->passwdOk = self::PASSWORD_VALID;
            } else {
                $this->passwdOk = self::PASSWORD_INVALID;
            }
        }
    }

    /**
     * Check local user password
     *
     * @param string $password
     *
     * @throws PDOException
     */
    private function checkLocalPassword($password): void
    {
        if (empty($password)) {
            $this->passwdOk = self::PASSWORD_INVALID;
            return;
        }

        if (password_verify($password, $this->userInfos["contact_passwd"])) {
            $this->passwdOk = self::PASSWORD_VALID;
            return;
        }

        if (
            (
                str_starts_with($this->userInfos["contact_passwd"], 'md5__')
                && $this->userInfos["contact_passwd"] === $this->myCrypt($password)
            )
            || 'md5__' . $this->userInfos["contact_passwd"] === $this->myCrypt($password)
        ) {
            $newPassword = password_hash($password, self::PASSWORD_HASH_ALGORITHM);
            $statement = $this->pearDB->prepare(
                "UPDATE `contact_password` SET password = :newPassword
                WHERE password = :oldPassword AND contact_id = :contactId"
            );
            $statement->bindValue(':newPassword', $newPassword, PDO::PARAM_STR);
            $statement->bindValue(':oldPassword', $this->userInfos["contact_passwd"], PDO::PARAM_STR);
            $statement->bindValue(':contactId', $this->userInfos["contact_id"], PDO::PARAM_INT);
            $statement->execute();
            $this->passwdOk = self::PASSWORD_VALID;
            return;
        }

        $this->passwdOk = self::PASSWORD_INVALID;
    }

    /**
     * Check user password
     *
     * @param string $username
     * @param string $password
     * @param string $token
     *
     * @return void
     * @throws PDOException
     */
    protected function checkUser($username, $password, $token)
    {
        if ($this->autologin == 0 || ($this->autologin && $token != "")) {
            $dbResult = $this->pearDB->prepare(
                "SELECT `contact`.*, `contact_password`.`password` AS `contact_passwd` FROM `contact`
                LEFT JOIN `contact_password` ON `contact_password`.`contact_id` = `contact`.`contact_id`
                WHERE `contact_alias` = :contactAlias
                AND `contact_activate` = '1' AND `contact_register` = '1'
                ORDER BY contact_password.creation_date DESC LIMIT 1"
            );
            $dbResult->bindValue(':contactAlias', $username, PDO::PARAM_STR);
            $dbResult->execute();
        } else {
            $dbResult = $this->pearDB->query(
                "SELECT * FROM `contact` " .
                "WHERE MD5(contact_alias) = '" . $this->pearDB->escape($username, true) . "'" .
                "AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1"
            );
        }
        if ($dbResult->rowCount()) {
            $this->userInfos = $dbResult->fetch();
            if ($this->userInfos["default_page"]) {
                $statement = $this->pearDB->prepare(
                    "SELECT topology_url_opt FROM topology WHERE topology_page = :topology_page"
                );
                $statement->bindValue(':topology_page', (int) $this->userInfos["default_page"], PDO::PARAM_INT);
                $statement->execute();
                if ($statement->rowCount()) {
                    $data = $statement->fetch(PDO::FETCH_ASSOC);
                    $this->userInfos["default_page"] .= $data["topology_url_opt"];
                }
            }

            /*
             * Check password matching
             */
            $this->checkPassword($password, $token);
            if ($this->passwdOk == self::PASSWORD_VALID) {
                $this->CentreonLog->setUID($this->userInfos["contact_id"]);
                $this->CentreonLog->insertLog(
                    CentreonUserLog::TYPE_LOGIN,
                    "[" . self::AUTH_TYPE_LOCAL . "] [" . $_SERVER["REMOTE_ADDR"] . "] "
                        . "Authentication succeeded for '" . $username . "'"
                );
            } else {
                $this->setAuthenticationError(
                    $this->userInfos['contact_auth_type'],
                    $username,
                    'invalid credentials'
                );
            }
        } elseif (count($this->ldap_auto_import)) {
            /*
             * Add temporary userinfo auth_type
             */
            $this->userInfos['contact_alias'] = $username;
            $this->userInfos['contact_auth_type'] = self::AUTH_TYPE_LDAP;
            $this->userInfos['contact_email'] = '';
            $this->userInfos['contact_pager'] = '';
            $this->checkPassword($password, "", true);
            /*
             * Reset userInfos with imported information
             */
            $statement = $this->pearDB->prepare(
                "SELECT * FROM `contact` " .
                "WHERE `contact_alias` = :contact_alias " .
                "AND `contact_activate` = '1' AND `contact_register` = '1' LIMIT 1"
            );
            $statement->bindValue(':contact_alias', $this->pearDB->escape($username, true), PDO::PARAM_STR);
            $statement->execute();
            if ($statement->rowCount()) {
                $this->userInfos = $statement->fetch(PDO::FETCH_ASSOC);
                if ($this->userInfos["default_page"]) {
                    $statement = $this->pearDB->prepare(
                        "SELECT topology_url_opt FROM topology WHERE topology_page = :topology_page"
                    );
                    $statement->bindValue(':topology_page', (int) $this->userInfos["default_page"], PDO::PARAM_INT);
                    $statement->execute();
                    if ($statement->rowCount()) {
                        $data = $statement->fetch(PDO::FETCH_ASSOC);
                        $this->userInfos["default_page"] .= $data["topology_url_opt"];
                    }
                }
            } else {
                $this->setAuthenticationError(self::AUTH_TYPE_LDAP, $username, 'not found');
            }
        } else {
            $this->setAuthenticationError(self::AUTH_TYPE_LOCAL, $username, 'not found');
        }
    }

    /**
     * Crypt String
     * @param $str
     *
     * @return mixed
     */
    protected function myCrypt($str)
    {
        $algo = $this->dependencyInjector['utils']->detectPassPattern($str);
        if (!$algo) {
            switch ($this->cryptEngine) {
                case 1:
                    return $this->dependencyInjector['utils']->encodePass($str, 'md5');
                case 2:
                    return $this->dependencyInjector['utils']->encodePass($str, 'sha1');
                default:
                    return $this->dependencyInjector['utils']->encodePass($str, 'md5');
            }
        } else {
            return $str;
        }
    }

    /**
     * @return int
     */
    protected function getCryptEngine()
    {
        return $this->cryptEngine;
    }

    /**
     * @return mixed
     */
    protected function userExists()
    {
        return $this->userExists;
    }

    /**
     * @return mixed
     */
    protected function userIsEnable()
    {
        return $this->enable;
    }

    /**
     * @return mixed
     */
    protected function passwordIsOk()
    {
        return $this->passwdOk;
    }

    /**
     * @return mixed
     */
    protected function getAuthType()
    {
        return $this->authType;
    }

    /**
     * Set authentication error and log it
     *
     * @param string $authenticationType
     * @param string|bool $username
     * @param string $reason
     *
     * @return void
     */
    private function setAuthenticationError(string $authenticationType, $username, string $reason): void
    {
        if (is_string($username) && strlen($username) > 0) {
            //  Take care before modifying this message pattern as it may break tools such as fail2ban
            $this->CentreonLog->insertLog(
                CentreonUserLog::TYPE_LOGIN,
                "[" . $authenticationType . "] [" . $_SERVER["REMOTE_ADDR"] . "] "
                    . "Authentication failed for '" . $username . "' : " . $reason
            );
        }

        $this->error = _('Your credentials are incorrect.');
    }
}
