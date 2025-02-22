<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
 */

/**
 * Class
 *
 * @class MysqlTable
 * @descrfiption Class that handles properties to create partitions for a table
 * @category Database
 * @package  Centreon
 * @author   qgarnier <qgarnier@centreon.com>
 * @license  GPL http://www.gnu.org/licenses
 * @link     http://www.centreon.com
 */
class MysqlTable
{
    /** @var string|null */
    public $type = null;
    /** @var CentreonDB */
    private $db;
    /** @var string|null */
    private $name = null;
    /** @var string|null */
    private $schema = null;
    /** @var int */
    private $activate = 1;
    /** @var string|null */
    private $column = null;
    /** @var string|null */
    private $duration = null;
    /** @var string|null */
    private $timezone = null;
    /** @var int|null */
    private $retention = null;
    /** @var int|null */
    private $retentionforward = null;
    /** @var string|null */
    private $createstmt = null;
    /** @var string|null */
    private $backupFolder = null;
    /** @var string|null */
    private $backupFormat = null;

    /**
     * Class constructor
     *
     * @param CentreonDB $DBobj     the centreon database
     * @param string     $tableName the database table name
     * @param string     $schema    the schema
     */
    public function __construct($DBobj, $tableName, $schema)
    {
        $this->db = $DBobj;
        $this->setName($tableName);
        $this->setSchema($schema);
    }
    
    /**
     * Set table name
     *
     * @param string $name the name
     *
     * @return null
     */
    private function setName($name): void
    {
        $this->name = isset($name) && $name != "" ? $name : null;
    }
    
    /**
     * Get table name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Set table schema
     *
     * @param string $schema the schema
     *
     * @return null
     */
    private function setSchema($schema): void
    {
        $this->schema = isset($schema) && $schema != "" ? $schema : null;
    }
    
    /**
     * Get table schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }
    
    /**
     * Set partitioning activation flag
     *
     * @param int $activate the activate integer
     *
     * @return null
     */
    public function setActivate($activate): void
    {
        if (isset($activate) && is_numeric($activate)) {
            $this->activate = $activate;
        }
    }
    
    /**
     * Get activate value
     *
     * @return int
     */
    public function getActivate()
    {
        return $this->activate;
    }
    
    /**
     * Set partitioning column name
     *
     * @param string $column the column name
     *
     * @return null
     */
    public function setColumn($column): void
    {
        if (isset($column) && $column != "") {
            $this->column = $column;
        }
    }
    
    /**
     * Get column value
     *
     * @return string|null
     */
    public function getColumn()
    {
        return $this->column;
    }
    
    /**
     * Set partitioning timezone
     *
     * @param string $timezone the timezone
     *
     * @return null
     */
    public function setTimezone($timezone): void
    {
        $this->timezone = isset($timezone) && $timezone != "" ? $timezone : date_default_timezone_get();
    }
    
    /**
     * Get timezone value
     *
     * @return string|null
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set partitioning column type
     *
     * @param string $type the type
     *
     * @return void
     * @throws Exception
     */
    public function setType($type): void
    {
        if (isset($type) && ($type == "date")) {
            $this->type = $type;
        } else {
            throw new Exception(
                "Config Error: Wrong type format for table "
                . $this->schema . "." . $this->name . "\n"
            );
        }
    }
    
    /**
     * Get partitioning column type
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set partition range
     *
     * @param string $duration the duration
     *
     * @return null
     * @throws Exception
     */
    public function setDuration($duration): void
    {
        if (isset($duration) && ($duration != 'daily')) {
            throw new Exception(
                "Config Error: Wrong duration format for table "
                . $this->schema . "." . $this->name . "\n"
            );
        } else {
            $this->duration = $duration;
        }
    }

    /**
     * Get partition range
     *
     * @return string|null
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set partitioning create table
     *
     * @param string $createstmt the statement
     *
     * @return null
     */
    public function setCreateStmt($createstmt): void
    {
        if (isset($createstmt) && $createstmt != "") {
            $this->createstmt = str_replace(";", "", $createstmt);
        }
    }

    /**
     * Get create table value
     *
     * @return string|null
     */
    public function getCreateStmt()
    {
        return $this->createstmt;
    }

    /**
     * Set partition backup folder
     *
     * @param string $backupFolder the backup folder
     *
     * @return null
     */
    public function setBackupFolder($backupFolder): void
    {
        if (isset($backupFolder) || $backupFolder != "") {
            $this->backupFolder = $backupFolder;
        }
    }

    /**
     * Get partition backup folder
     *
     * @return string|null
     */
    public function getBackupFolder()
    {
        return $this->backupFolder;
    }

    /**
     * Set partition backup file name format
     *
     * @param string $backupFormat the backup format
     *
     * @return null
     */
    public function setBackupFormat($backupFormat): void
    {
        if (isset($backupFormat) || $backupFormat != "") {
            $this->backupFormat = $backupFormat;
        }
    }

    /**
     * Get partition backup file name format
     *
     * @return string|null
     */
    public function getBackupFormat()
    {
        return $this->backupFormat;
    }

    /**
     * Set partitions retention value
     *
     * @param int $retention the retention
     *
     * @return null
     * @throws Exception
     */
    public function setRetention($retention): void
    {
        if (isset($retention) && is_numeric($retention)) {
            $this->retention = $retention;
        } else {
            throw new Exception(
                "Config Error: Wrong format of retention value for table "
                . $this->schema . "." . $this->name . "\n"
            );
        }
    }

    /**
     * Get retention value
     *
     * @return int|null
     */
    public function getRetention()
    {
        return $this->retention;
    }

    /**
     * Set partitions retention forward value
     *
     * @param int $retentionforward the retention forward
     *
     * @return void
     * @throws Exception
     */
    public function setRetentionForward($retentionforward): void
    {
        if (isset($retentionforward) && is_numeric($retentionforward)) {
            $this->retentionforward = $retentionforward;
        } else {
            throw new Exception(
                "Config Error: Wrong format of retention forward value for table "
                . $this->schema . "." . $this->name . "\n"
            );
        }
    }

    /**
     * Get retention forward value
     *
     * @return int|null
     */
    public function getRetentionForward()
    {
        return $this->retentionforward;
    }

    /**
     * Check if table properties are all set
     *
     * @return bool
     */
    public function isValid()
    {
        // Condition to mod with new version
        if (is_null($this->name) || is_null($this->column)
            || is_null($this->activate) || is_null($this->duration)
            || is_null($this->schema) || is_null($this->retention)
            || is_null($this->type) || is_null($this->createstmt)
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if table exists in database
     *
     * @return bool
     * @throws Exception
     */
    public function exists()
    {
        try {
            $DBRESULT = $this->db->query("use `" . $this->schema . "`");
        } catch (PDOException $e) {
            throw new Exception(
                "SQL Error: Cannot use database "
                . $this->schema . "," . $e->getMessage() . "\n"
            );
            return false;
        }

        try {
            $DBRESULT = $this->db->query("show tables like '" . $this->name . "'");
        } catch (PDOException $e) {
            throw new Exception(
                "SQL Error: Cannot execute query,"
                . $e->getMessage() . "\n"
            );
            return false;
        }

        if (!$DBRESULT->rowCount()) {
            return false;
        }

        return true;
    }

    /**
     * Check of column exists in table
     *
     * @return bool
     * @throws Exception
     */
    public function columnExists()
    {
        try {
            $DBRESULT = $this->db->query(
                "describe " . $this->schema . "." . $this->name
            );
        } catch (PDOException $e) {
            throw new Exception(
                "SQL query error : " . $e->getMessage() . "\n"
            );
        }

        $found = false;
        while ($row = $DBRESULT->fetchRow()) {
            if ($row["Field"] == $this->column) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return (false);
        }

        return (true);
    }
}
