<?php
include_once('../include/session.inc.php');

/*
    This file is part of CoDev-Timetracking.

    CoDev-Timetracking is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    CoDev-Timetracking is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with CoDev-Timetracking.  If not, see <http://www.gnu.org/licenses/>.
*/

require('../path.inc.php');

require('super_header.inc.php');

include_once "issue.class.php";
include_once "user.class.php";
include_once "team.class.php";
include_once "engagement.class.php";
#include_once "time_tracking.class.php";

include_once "smarty_tools.php";

$logger = Logger::getLogger("engagement_info");


function getEngagements($teamid, $selectedEngId) {

   $team = TeamCache::getInstance()->getTeam($teamid);
   $engList = $team->getEngagements();

   foreach ($engList as $id => $eng) {
      $engagements[] = array(
         'id' => $id,
         'name' => $eng->getName(),
         'selected' => ($id == $selectedEngId)
      );
   }
   return $engagements;


}



/**
 *
 */
function getEngagementIssues($engagement) {

   $issueArray = array();

   $issues = $engagement->getIssueSelection()->getIssueList();
   foreach ($issues as $id => $issue) {

      $issueInfo = array();
      $issueInfo["bugid"] = $issue->bugId;
      $issueInfo["project"] = $issue->getProjectName();
      $issueInfo["target"] = $issue->getTargetVersion();
      $issueInfo["status"] = $issue->getCurrentStatusName();
      $issueInfo["progress"] = round(100 * $issue->getProgress());
      $issueInfo["elapsed"] = $issue->elapsed;
      $issueInfo["driftMgr"] = $issue->getDriftMgrEE();
      $issueInfo["durationMgr"] = $issue->getDurationMgr();
      $issueInfo["summary"] = $issue->summary;

      $issueArray[$id] = $issueInfo;
   }
   return $issueArray;
}




// =========== MAIN ==========

require('display.inc.php');


$smartyHelper = new SmartyHelper();
$smartyHelper->assign('pageName', T_('Engagement'));
$smartyHelper->assign('menu2', "menu/management_menu.html");

if (isset($_SESSION['userid'])) {

   $userid = $_SESSION['userid'];
   $session_user = UserCache::getInstance()->getUser($userid);

   $teamid = isset($_SESSION['teamid']) ? $_SESSION['teamid'] : 0;

   // use the engid set in the form, if not defined (first page call) use session engid
    if (isset($_GET['engid'])) {
      $engagementid = $_GET['engid'];
      $_SESSION['engid'] = $engagementid;

    } else if (isset($_POST['engid'])) {
      $engagementid = $_POST['engid'];
      $_SESSION['engid'] = $engagementid;

   } else {
      $engagementid = isset($_SESSION['engid']) ? $_SESSION['engid'] : 0;
   }


   // set TeamList (including observed teams)
   $teamList = $session_user->getTeamList();
   $smartyHelper->assign('teams', getTeams($teamList, $teamid));

   $smartyHelper->assign('engagements', getEngagements($teamid, $engagementid));


   if (0 != $engagementid) {

      $eng = new Engagement($engagementid);

      $smartyHelper->assign('engName', $eng->getName());
      $smartyHelper->assign('engDesc', $eng->getDesc());

      // set Eng Details
      $engDetailedMgr = getIssueSelectionDetailedMgr($eng->getIssueSelection());
      $smartyHelper->assign('engDetailedMgr', $engDetailedMgr);


      // set EngagementList (for selected the team)
      $issueList = getEngagementIssues($eng);
      $smartyHelper->assign('engIssues', $issueList);

   }



}

$smartyHelper->displayTemplate($codevVersion, $_SESSION['username'], $_SESSION['realname'],$mantisURL);


?>
