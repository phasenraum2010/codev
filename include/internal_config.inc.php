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
<?php

   /*
    * The Variables in here are not expected to be changed in any way.
    *
    * most of them are initialyzed from the 'codev_config_table'.
    *
    */

   include_once "config.class.php";
   include_once "config_mantis.class.php";

   // CoDev project started on: 17 May 2010

   $codevVersion = "v0.99.11 (16 Jun 2011)";

   $codevVersionHistory = array("v0.99.0" => "(09 Sept 2010) - team management complete",
                                "v0.99.1" => "(28 Sept 2010) - jobs management",
                                "v0.99.2" => "(08 Dec  2010) - Project Management",
                                "v0.99.3" => "(03 Jan  2011) - fix new year problems",
                                "v0.99.4" => "(13 Jan  2011) - ConsistencyCheck",
                                "v0.99.5" => "(21 Jan  2011) - Update directory structure & Apache config",
                                "v0.99.6" => "(16 Feb  2011) - i18n (internationalization)",
                                "v0.99.7" => "(25 Feb  2011) - Graph & Statistics",
                                "v0.99.8" => "(25 Mar  2011) - Add Job and specificities for 'support' + createTeam enhancements",
                                "v0.99.9" => "(11 Apr  2011) - Planning + enhance global performances",
                                "v0.99.10" => "(28 May  2011) - Install Procedure (unpolished)",
                                "v0.99.11" => "(16 Jun  2011) - Replace ETA with Preliminary Est. Effort"
                                );


  // ---
  // il peut y avoir plusieurs observer
  // il n'y a qu'un seul teamLeader
  // il peut y avoir plusieurs managers, mais ils ne peuvent imputer que sur des SideTasks
  // un observer ne fait jamais partie de l'equipe, il n'a acces qu'a des donnees impersonnelles

  // ==================

  $admin_teamid = Config::getInstance()->getValue(Config::id_adminTeamId); // users allowed to do CoDev administration

  $job_support = Config::getInstance()->getValue(Config::id_jobSupport); // jobid in codev_job_table corresponding to the 'Support' job (used to compute drifts)

  // this is the custom field added to mantis issues for TimeTracking
  $tcCustomField           = Config::getInstance()->getValue(Config::id_customField_TC);
  $estimEffortCustomField  = Config::getInstance()->getValue(Config::id_customField_effortEstim); //  BI
  $remainingCustomField    = Config::getInstance()->getValue(Config::id_customField_remaining); //  RAE
  $deadLineCustomField     = Config::getInstance()->getValue(Config::id_customField_deadLine);
  $addEffortCustomField    = Config::getInstance()->getValue(Config::id_customField_addEffort); // BS
  $deliveryIdCustomField   = Config::getInstance()->getValue(Config::id_customField_deliveryId); // FDL (id of the associated Delivery Issue)
  $deliveryDateCustomField = Config::getInstance()->getValue(Config::id_customField_deliveryDate);


  // ---
  // TODO translate astreinte = "on duty"
  $astreintesTaskList = Config::getInstance()->getValue(Config::id_astreintesTaskList); // fiches de SuiviOp:Inactivite qui sont des astreintes


  // --- Mantis Values ---
  $priorityNames   = Config::getInstance()->getValue(Config::id_priorityNames);
  $resolutionNames = Config::getInstance()->getValue(Config::id_resolutionNames);

  // ---
  $defaultSideTaskProject = Config::getInstance()->getValue(Config::id_defaultSideTaskProject); // "SuiviOp" in table mantis_project_table

  $codevReportsDir = Config::getInstance()->getValue(Config::id_codevReportsDir);



?>
