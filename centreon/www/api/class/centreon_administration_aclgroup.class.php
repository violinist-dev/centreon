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
 * this program; if not, see <htcontact://www.gnu.org/licenses>.
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

require_once dirname(__FILE__) . "/centreon_configuration_objects.class.php";

class CentreonAdministrationAclgroup extends CentreonConfigurationObjects
{
    /**
     * @return array
     * @throws Exception
     */
    public function getList()
    {
        $queryValues = array();

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $limit = $this->arguments['page_limit'];
            $range = 'LIMIT :offset,:limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$limit;
        } else {
            $range = '';
        }

        $filterAclgroup = '';
        if (isset($this->arguments['q'])) {
            $filterAclgroup = "WHERE acl_group_name LIKE :aclGroup OR acl_group_alias LIKE :aclGroup ";
            $queryValues['aclGroup'] = '%' .(string)$this->arguments['q'] . '%';
        }

        $query = "SELECT SQL_CALC_FOUND_ROWS DISTINCT acl_group_id, acl_group_name " .
            "FROM acl_groups " .
            $filterAclgroup .
            "ORDER BY acl_group_name " .
            $range;
        $stmt = $this->pearDB->prepare($query);
        $stmt->bindParam(':aclGroup', $queryValues["aclGroup"], PDO::PARAM_STR);
        if (isset($queryValues["offset"])) {
            $stmt->bindParam(':offset', $queryValues["offset"], PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }
        $aclGroupList = array();
        while ($data = $stmt->fetch()) {
            $aclGroupList[] = array(
                'id' => $data['acl_group_id'],
                'text' => $data['acl_group_name']
            );
        }

        return array(
            'items' => $aclGroupList,
            'total' => $stmt->rowCount()
        );
    }
}
