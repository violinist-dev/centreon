<?php
/**
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

//require_once "../../require.php";
require_once "/usr/share/centreon/www/widgets/require.php";
require_once "./DB-Func.php";

require_once $centreon_path . 'www/class/centreon.class.php';
require_once $centreon_path . 'www/class/centreonSession.class.php';
require_once $centreon_path . 'www/class/centreonDB.class.php';
require_once $centreon_path . 'www/class/centreonWidget.class.php';
require_once $centreon_path . 'www/class/centreonDuration.class.php';
require_once $centreon_path . 'www/class/centreonUtils.class.php';
require_once $centreon_path . 'www/class/centreonACL.class.php';
require_once $centreon_path . 'www/class/centreonHost.class.php';

 // Load specific Smarty class //
require_once $centreon_path ."GPL_LIB/Smarty/libs/Smarty.class.php";


// check if session is alive //
session_start();
if (!isset($_SESSION['centreon']) || !isset($_REQUEST['widgetId'])) {
    exit;
}


$db_centreon = new CentreonDB("centreon");
$pearDB = $db_centreon;
if (CentreonSession::checkSession(session_id(), $db_centreon) == 0) {
    exit;
}


// Configure new smarty object
$path = $centreon_path . "www/widgets/Top10_memory/src/";
$template = new Smarty();
$template = initSmartyTplForPopup($path, $template, "./", $centreon_path);

// Get widgets info & parameters
$centreon = $_SESSION['centreon'];
$widgetId = $_REQUEST['widgetId'];

$widgetObj = new CentreonWidget($centreon, $db_centreon);
$preferences = $widgetObj->getWidgetPreferences($widgetId);

if ($centreon->user->admin == 0) {
  $access = new CentreonACL($centreon->user->get_id());
  $grouplist = $access->getAccessGroups();
  $grouplistStr = $access->getAccessGroupsString();
}

$data = array();
$db = new CentreonDB("centstorage");

if ($preferences['host_group'] == ''){
$query = "select DISTINCT T1.service_id, T1.host_id, T1.host_name, T1.service_description, current_value/max as ratio, max-current_value as remaining_space, state as status 
from index_data T1, metrics T2, services T3 " .($centreon->user->admin == 0 ? ", centreon_acl acl" : ""). " 
where T1.service_description like '%".$preferences['service_description']."%'
AND metric_name like '%".$preferences['metric_name']."%'
and T2.index_id = id
and T1.service_id = T3.service_id
and T1.host_id = T3.host_id
AND Max is not null 
and T2.index_id = id
" .($centreon->user->admin == 0 ? " AND T1.host_id = acl.host_id AND T1.service_id = acl.service_id AND acl.group_id IN (" .($grouplistStr != "" ? $grouplistStr : 0). ")" : ""). " 
order by ratio desc limit ".$preferences['nb_lin'].";";

} else {

$query = "select T2.service_id, T2.host_id, T2.host_name, T2.service_description, current_value/Max as ratio, Max-current_value as remaining_space, state as status 
from services T1, index_data T2, metrics T3, hosts_hostgroups T5" .($centreon->user->admin == 0 ? ", centreon_acl acl" : ""). "
where T2.service_description like '%".$preferences['service_description']."%' 
AND Max is not null 
and T3.index_id = id 
and T2.service_id = T1.service_id 
and T2.host_id = T1.host_id 
and metric_name like '%".$preferences['metric_name']."%' 
and T5.hostgroup_id = ".$preferences['host_group']."
and T1.host_id = T5.host_id
" .($centreon->user->admin == 0 ? " AND T1.host_id = acl.host_id AND T1.service_id = acl.service_id AND acl.group_id AND T4.hg_id AND T5.hostgroup_id IN (" .($grouplistStr != "" ? $grouplistStr : 0). ")" : ""). " 
group by T2.host_id order by ratio desc limit ".$preferences['nb_lin'].";";
}

$title ="Default Title";
$numLine = 1;
$in = 0;

function getUnit($in)
{
  if ($in == 0) {
    $return = "B";
    return $return;
  }
  else if ($in == 1) {
    $return = "KB";
    return $return;
  }
  else if ($in == 2) {
    $return = "MB";
    return $return;
  }
  else if ($in == 3) {
    $return = "GB";
    return $return;
  }
  else if ($in == 4) {
    $return = "TB";
    return $return;
  }
}

$res = $db->query($query);
while ($row = $res->fetchRow()) {
$row['numLin'] = $numLine;
while ($row['remaining_space'] >= 1024) {
  $row['remaining_space'] = $row['remaining_space'] / 1024;
    $in = $in + 1;
  }
$row['unit'] = getUnit($in);
$in = 0;
$row['remaining_space'] = round($row['remaining_space']);
$row['ratio'] = ceil($row['ratio'] * 100); 
$data[] = $row;
$numLine++;
}

$template->assign('title', $title);
$template->assign('data', $data);
$template->display('table.ihtml');
?>
