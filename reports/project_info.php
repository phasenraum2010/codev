<?php if (!isset($_SESSION)) { session_start(); header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"'); } ?>
<?php /*
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
*/ ?>

<?php include_once '../path.inc.php'; ?>

<?php
include_once 'i18n.inc.php';
if (!isset($_SESSION['userid'])) {
  echo T_("Sorry, you need to <a href='../'>login</a> to access this page.");
  exit;
}
?>

<?php
   $_POST['page_name'] = T_("Project Info");
   include 'header.inc.php';
?>

<?php include 'login.inc.php'; ?>
<?php include 'menu.inc.php'; ?>


<script language="JavaScript">

  function submitForm() {
    document.forms["form1"].projectid.value = document.getElementById('projectidSelector').value;
    document.forms["form1"].action.value = "displayProject";
    document.forms["form1"].submit();
  }

   // ------ JQUERY ------
  $(function() {

     $( "#tabsVersions" ).tabs();

  });

</script>


<?php

include_once "issue.class.php";
include_once "project.class.php";
include_once "time_track.class.php";
include_once "user.class.php";
include_once "jobs.class.php";
include_once "holidays.class.php";

include_once "issue_fdj.class.php";

$logger = Logger::getLogger("project_info");

// ---------------------------------------------------------------

function displayProjectSelectionForm($originPage, $projList, $defaultProjectid = 0) {

   global $logger;

   // Display form
   echo "<div style='text-align: center;'>";
   echo "<form name='form1' method='post' Action='$originPage'>\n";

   // Project list
   echo "Project ";
   echo "<select id='projectidSelector' name='projectidSelector' title='".T_("Project")."'>\n";
   echo "<option value='0'></option>\n";
   foreach ($projList as $pid => $pname)
   {
      if ($pid == $defaultProjectid) {
         echo "<option selected value='".$pid."'>$pname</option>\n";
      } else {
         echo "<option value='".$pid."'>$pname</option>\n";
      }
   }
   echo "</select>\n";

   // --- Version list
/*
       $versionList = array();
       $formatedProjList = implode( ', ', array_keys($projList));

       $query  = "SELECT * ".
                 "FROM `mantis_project_version_table` ".
                 "WHERE project_id = $defaultProjectid ".
                 "ORDER BY version DESC";
        $result = mysql_query($query);
        if (!$result) {
            $logger->error("Query FAILED: $query");
            $logger->error(mysql_error());
            echo "<span style='color:red'>ERROR: Query FAILED</span>";
            exit;
        }
         if (0 != mysql_num_rows($result)) {
            while($row = mysql_fetch_object($result))
            {
               $versionList[] = $row->version;
            }
       }
*/

   echo "<input type=button value='".T_("Jump")."' onClick='javascript: submitForm()'>\n";

   echo "<input type=hidden name=projectid value=$defaultProjectid>\n";
   echo "<input type=hidden name=action       value=noAction>\n";
   echo "</form>\n";

   echo "</div>";
}


// ------------------------------------------------
function displayProjectVersions($project) {

   echo "<h3>".T_("Project Versions")."</h3>";
   echo "<div id='tabsVersions'>\n";
   echo "<ul>\n";
   echo "<li><a href='#tab1'>".T_("Overview")."</a></li>\n";
   echo "<li><a href='#tab2'>".T_("Detailed")."</a></li>\n";
   echo "</ul>\n";
   echo "<div id='tab1'>\n";
   echo "<p>";
   displayVersionsOverview($project);
   echo "</p>\n";
   echo "</div>\n";
   echo "<div id='tab2'>\n";
   echo "<p>";
   displayVersionsDetailed($project);
   echo "</p>\n";
   echo "</div>\n";
   echo "</div>\n";

}

// -----------------------------------------
function displayVersionsOverview($project) {
   $projectVersionList = $project->getVersionList();

   echo "<table>\n";

   echo "<tr>\n";
   echo "  <th>".T_("Target version")."</th>\n";
   echo "  <th>".T_("Date")."</th>\n";
   echo "  <th>".T_("Progress")."</th>\n";
   #echo "  <th>".T_("Remaining")."</th>\n";
   echo "  <th width='80'>".T_("Drift Mgr")."</th>\n";
   echo "  <th width='80'>".T_("Drift")."</th>\n";
   echo "</tr>\n";

   foreach ($projectVersionList as $version => $pv) {
	   echo "<tr>\n";
	   $totalElapsed += $pv->elapsed;
	   $totalRemaining += $pv->remaining;

       $valuesMgr = $pv->getDriftMgr();
       $formattedDriftMgr = "<span title='".T_("percent")."' class='float'>".round(100 * $valuesMgr['percent'])."%</span>";

	   $driftMgrColor = $pv->getDriftColor($valuesMgr['percent']);
       $formatteddriftMgrColor = (NULL == $driftMgrColor) ? "" : "style='background-color: #".$driftMgrColor.";' ";

       $values = $pv->getDrift();
       $formattedDrift    = "<span title='".T_("percent")."' class='float'>".round(100 * $values['percent'])."%</span>";
       $driftColor = $pv->getDriftColor($values['percent']);
       $formatteddriftColor = (NULL == $driftColor) ? "" : "style='background-color: #".$driftColor.";' ";

       echo "<td>".$pv->name."</td>\n";
       echo "<td>".date("Y-m-d", $pv->getVersionDate())."</td>\n";
       echo "<td>".round(100 * $pv->getProgress())."%</td>\n";
	   #echo "<td>".$pv->remaining."</td>\n";
       echo "<td $formatteddriftMgrColor >$formattedDriftMgr</td>\n";
       echo "<td $formatteddriftColor >$formattedDrift</td>\n";
	   echo "</tr>\n";
   }

   // compute total progress
   if (0 == $totalRemaining) {
   	  $totalProgress = 1;  // if no Remaining, then Project is 100% done.
   } elseif (0 == $totalElapsed) {
      $totalProgress = 0;  // if no time spent, then no work done.
   } else {
   	  $totalProgress = $totalElapsed / ($totalElapsed + $totalRemaining);
   }
   echo "<tr>\n";
   echo "<td>".T_("Total")."</td>\n";
   echo "<td></td>\n";
   echo "<td>".round(100 * $totalProgress)."%</td>\n";
   #echo "<td>".$totalRemaining."</td>\n";
   echo "<td></td>\n";
   echo "<td></td>\n";
   echo "</tr>\n";

   echo "</table>\n";
}

// -----------------------------------------
function displayVersionsDetailed($project) {

   $projectVersionList = $project->getVersionList();

   echo "<table>\n";

   echo "<tr>\n";
   echo "  <th>".T_("Target version")."</th>\n";
   #echo "  <th>".T_("Progress")."</th>\n";
   echo "  <th>".T_("MgrEffortEstim")."</th>\n";
   echo "  <th>".T_("EffortEstim")."</th>\n";
   echo "  <th>".T_("Elapsed")."</th>\n";
   echo "  <th>".T_("Remaining")."</th>\n";
   echo "  <th width='80'>".T_("Drift Mgr")."</th>\n";
   echo "  <th width='80'>".T_("Drift")."</th>\n";
   echo "  <th>".T_("Current Tasks")."</th>\n";
   echo "  <th>".T_("Resolved Tasks")."</th>\n";
   echo "</tr>\n";

   foreach ($projectVersionList as $version => $pv) {
	   echo "<tr>\n";
	   $totalElapsed += $pv->elapsed;
	   $totalRemaining += $pv->remaining;
	   $formatedList  = implode( ',', array_keys($pv->getIssueList()));

      // format Issues list
      $formatedResolvedList = "";
      $formatedOpenList = "";
      foreach ($pv->getIssueList() as $bugid => $issue) {

         if ($issue->currentStatus >= $issue->bug_resolved_status_threshold) {
         	if ("" != $formatedResolvedList) {
				   $formatedResolvedList .= ', ';
			   }
			   $formatedResolvedList .= issueInfoURL($bugid, $issue->summary);
         } else {
         	if ("" != $formatedOpenList) {
				   $formatedOpenList .= ', ';
			   }
			   $formatedOpenList .= issueInfoURL($bugid, $issue->summary);
         }
      }



       $valuesMgr = $pv->getDriftMgr();
       $formattedDriftMgr = "<span title='".T_("nb days")."'>".$valuesMgr['nbDays']."</span>";

	   $driftMgrColor = $pv->getDriftColor($valuesMgr['percent']);
       $formatteddriftMgrColor = (NULL == $driftMgrColor) ? "" : "style='background-color: #".$driftMgrColor.";' ";

       $values = $pv->getDrift();
       $formattedDrift    = "<span title='".T_("nb days")."'>".$values['nbDays']."</span>";
       $driftColor = $pv->getDriftColor($values['percent']);
       $formatteddriftColor = (NULL == $driftColor) ? "" : "style='background-color: #".$driftColor.";' ";

       echo "<td>".$pv->name."</td>\n";
	   #echo "<td>".round(100 * $pv->getProgress())."%</td>\n";
       echo "<td>".$pv->mgrEffortEstim."</td>\n";
       echo "<td title='$pv->effortEstim + $pv->effortAdd'>".($pv->effortEstim + $pv->effortAdd)."</td>\n";
	   echo "<td>".$pv->elapsed."</td>\n";
	   echo "<td>".$pv->remaining."</td>\n";
       echo "<td $formatteddriftMgrColor >$formattedDriftMgr</td>\n";
       echo "<td $formatteddriftColor >$formattedDrift</td>\n";
	   echo "<td>".$formatedOpenList."</td>\n";
	   echo "<td>".$formatedResolvedList."</td>\n";
	   echo "</tr>\n";
   }

   // compute total progress
   if (0 == $totalRemaining) {
   	  $totalProgress = 1;  // if no Remaining, then Project is 100% done.
   } elseif (0 == $totalElapsed) {
      $totalProgress = 0;  // if no time spent, then no work done.
   } else {
   	  $totalProgress = $totalElapsed / ($totalElapsed + $totalRemaining);
   }
   echo "<tr>\n";
   echo "<td>".T_("Total")."</td>\n";
   #echo "<td>".round(100 * $totalProgress)."%</td>\n";
   echo "<td></td>\n";
   echo "<td></td>\n";
   echo "<td>".$totalElapsed."</td>\n";
   echo "<td>".$totalRemaining."</td>\n";
   echo "<td></td>\n";
   echo "<td></td>\n";
   echo "<td></td>\n";
   echo "<td></td>\n";
   echo "</tr>\n";

   echo "</table>\n";
}


// ================ MAIN =================
$year = date('Y');

$originPage = "project_info.php";

$action           = isset($_POST['action']) ? $_POST['action'] : '';
$session_userid   = isset($_POST['userid']) ? $_POST['userid'] : $_SESSION['userid'];
$version          = isset($_POST['version']) ? $_POST['version'] : 0;

$defaultProject = isset($_SESSION['projectid']) ? $_SESSION['projectid'] : 0;
$projectid        = isset($_POST['projectid']) ? $_POST['projectid'] : $defaultProject;
$_SESSION['projectid'] = $projectid;

$user = UserCache::getInstance()->getUser($session_userid);


$dTeamList = $user->getDevTeamList();
$lTeamList = $user->getLeadedTeamList();
$managedTeamList = $user->getManagedTeamList();
$teamList = $dTeamList + $lTeamList + $managedTeamList;


// --- define the list of tasks the user can display
// All projects from teams where I'm a Developper or Manager (Observers not allowed)
$devProjList     = $user->getProjectList();
$managedProjList = (0 == count($managedTeamList)) ? array() : $user->getProjectList($managedTeamList);
$projList = $devProjList + $managedProjList;


// if bugid is set in the URL, display directly
 if (isset($_GET['projectid'])) {
    $projectid = $_GET['projectid'];

   // user may not have the rights to see this project (observers, ...)
    if (in_array($projectid, $projList)) {
      $action = "displayProject";
   } else {
     $action = "notAllowed";
   }
 }

if (0 == count($teamList)) {
   echo "<div id='content'' class='center'>";
    echo T_("Sorry, you need to be member of a Team to access this page.");
   echo "</div>";

} else {

    displayProjectSelectionForm($originPage, $projList, $projectid);

    if ("displayProject" == $action) {

       $project = ProjectCache::getInstance()->getProject($projectid);

      echo "<br/>";
      echo "<br/>";
      echo "<br/>";
      echo "<br/>";

      // show progress
      displayProjectVersions($project);

    } elseif ("setProjectid" == $action) {

       // pre-set form fields
       $projectid  = $_POST['projectid'];

    } elseif ("notAllowed" == $action) {
      echo "<br/>";
      echo "<br/>";
      echo "<br/>";
      echo "<br/>";
      echo T_("Sorry, you are not allowed to view the details of this project")."<br/>";
  }


}
echo "<br/>";
echo "<br/>";
echo "<br/>";
echo "<br/>";

?>

</div>

<?php include 'footer.inc.php'; ?>










