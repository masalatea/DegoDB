<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_USER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DATA_CLASS_SYNC_WITH_DBTABLE"); ?> - <?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dataclasses_sync.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dataclasse_fields_sync.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$ClassName = trim(GetParam("ClassName"));
$ColumnName = trim(GetParam("ColumnName"));

$DoSync = trim(GetParam("DoSync"));
$DoSyncAll = trim(GetParam("DoSyncAll"));

$IncludeOrder = trim(GetParam("IncludeOrder"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAdbtable = new dbtableDBAccess();
$dbtable = NULL;

$DAdbtablecolumns = new dbtablecolumnsDBAccess();
$dbtablecolumnlist = NULL;

$DAdataclass = new dataclassDBAccess();
$dataclass = NULL;

$DAdataclassfields = new dataclassfieldsDBAccess();
$dataclassfieldlist = NULL;

$DAProject = new ProjectDBAccess();
$project = NULL;
if ($NoError) {
	$project = $DAProject->GetProject($ProjectPID);
	if ($project == NULL) {
		?>
		<H3><font color="red">Unknown Project. Please check Project Setting.</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {

	printPathOnTopForSyncDataClassWithDBTable($ClassName . " Class", $ProjectPID, $ClassName);
	
	$dbtable = $DAdbtable->GetdbtableByName($ProjectPID, $ClassName);
	if ($dbtable == NULL) {
		?>
		<H3><font color="red">Error! There is no DB Table by name: <?php print $ClassName; ?></font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	
	mtool_dataclasses_sync_for_each($project, $dbtable, $ClassName, $ColumnName, $DoSync, $DoSyncAll, $IncludeOrder);
	
	$dbtablecolumnlist = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $dbtable->PID);
	
	$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $ClassName);
	if ($dataclass != NULL) {
		$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $dataclass->PID);
	} else {
		?>
		<H3><font color="red">INFO: Corresponding Data Class is not exist.</font></H3>
        <?php
	}
	
	if (count($dbtablecolumnlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Column Name in DB Table Design</th>
			  <th>Corresponding Column exists?</th>
			  <th>Matched? (without order)</th>
			  <th>Exactly Matched? (include order)</th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		
		for($i = 0 ; $i < count($dbtablecolumnlist); $i++) {
			$dbtablecolumn = $dbtablecolumnlist[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($dbtablecolumn->name); ?></td>
              <td><?php
              	
				$correspondingDataClassField = NULL;
				$isMatched = false;
				$MatchOrder = false;
				check_mtool_dataclasses_field_match_order($project, $correspondingDataClassField, $isMatched, $MatchOrder, $dataclassfieldlist, $dbtablecolumn, $i);
				
				// if ($dataclassfieldlist) {
				// 	for($j = 0 ; $j < count($dataclassfieldlist); $j++) {
				// 		$dataclassfield = $dataclassfieldlist[$j];
				// 		
				// 		if ($dbtablecolumn->name == $dataclassfield->name) {
				// 			$correspondingDataClassField = $dataclassfield;
				// 			if (GetSourceDataTypeFromDatabaseDataTypeForGeneral($project, $dbtablecolumn->datatype) == $dataclassfield->datatype) {
				// 				$isMatched = true;
				// 				$MatchOrder = ($i == $j);
				// 				
				// 				if (!$MatchOrder) {
				// 					break;
				// 				}
				// 			}
				// 		}
				// 	}
				// }
				
				if ($correspondingDataClassField != "") {
					?>
					Exist
					<?php
				} else {
					?>
					<font color="red">Not Exist</font>
					<?php
				}
			  ?></td>
			  <td><?php
				if ($isMatched) {
					?>
					Matched
					<?php
				} else {
					?>
					<font color="red">Not Matched</font>
					<?php
				}
				?></td>
			  <td><?php
				if ($isMatched && $MatchOrder) {
					?>
					Matched
					<?php
				} else {
					?>
					Not Matched (you can ignore)
					<?php
				}
				?></td>
               <td>
               <?php
				if (!$isMatched) {
					?>
					<a href="dataclasses_sync_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ClassName=<?php print urlencode($ClassName); ?>&ColumnName=<?php print urlencode($dbtablecolumn->name); ?>&DoSync=y&IncludeOrder=&<?php print makeRandStr(8); ?>">Sync This Column (Exclude Order)</a>
					<br>
					<?Php
			   }
			   ?>
               </td>
			</tr>
			<?php
		}
		?>
			<tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td><a href="dataclasses_sync_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ClassName=<?php print urlencode($ClassName); ?>&DoSyncAll=y&IncludeOrder=&<?php print makeRandStr(8); ?>">Sync All Column (Exclude Order)</a><br>
			  <a href="dataclasses_sync_for_each.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&ClassName=<?php print urlencode($ClassName); ?>&DoSyncAll=y&IncludeOrder=y&<?php print makeRandStr(8); ?>">Sync All Column (Include Order)</a>
              </td>
            </tr>
        	</tbody>
		</table>
        
		<?php
	} else {
		?>
    <p>none</p>
		<?php
	}
	
	$NoCorrespondingDataClassFieldList = array();
	if ($dataclass != NULL && $dataclassfieldlist != NULL) {
		for($j = 0 ; $j < count($dataclassfieldlist); $j++) {
			$dataclassfield = $dataclassfieldlist[$j];
			
			if ($dbtablecolumnlist != NULL && count($dbtablecolumnlist) > 0) {
				$definitionExists = false;
				for($i = 0 ; $i < count($dbtablecolumnlist); $i++) {
					$dbtablecolumn = $dbtablecolumnlist[$i];
					
					if ($dbtablecolumn->name == $dataclassfield->name) {
						$definitionExists = true;
						break;
					}
				}
				if (!$definitionExists) {
					array_push($NoCorrespondingDataClassFieldList, $dataclassfield);
				}
			}
		}
		
		if (count($NoCorrespondingDataClassFieldList) > 0) {
			?>
            <h4>[FYI] Following definition exists in Data Class Field but not exists in DB Table.</h4>
            <?php
			for($i = 0 ; $i < count($NoCorrespondingDataClassFieldList) ; $i++) {
				$dataclassfield = $NoCorrespondingDataClassFieldList[$i];
				
				?>
                <p>Field: <?php print $dataclassfield->name; ?> [<a href="dataclass_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclass->PID); ?>&DataClassFieldPID=<?php print urlencode($dataclassfield->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>]</p>
                <?php
			}
			?>
            <H5>Those definition is NOT deleted automatically because this may be a intentional.</H5>
            <?php
		}
	}
	?>
    <br>
    <br>
    <br>
    <p><a href="./dataclasses_sync.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Target List of Sync Data Class with DB Table </a></p>
    <?php
}
?>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content
