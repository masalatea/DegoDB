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
<title><?php print getres("TITLE_DBTABLE_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbtable_import.php");
include_once("dbtables_import_common.php");

$filterdbtablePID = trim(GetParam("filterdbtablePID"));

if (is_numeric($filterdbtablePID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific DB Table</i></font></h3>
    <?php
}

if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	
	printPathOnTopForDBTable("DB Table List", $ProjectPID, "", "");
	
	$DAdbtable = new dbtableDBAccess();
	$DAdbtablecolumns = new dbtablecolumnsDBAccess();
	$tablelist = $DAdbtable->GetdbtableList($ProjectPID); 
	
	if (count($tablelist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th></th>
			  <th>DB Table Name</th>
              <?php if ($show_recommended_column_warning) { ?>
			  <th>Required Column Exist?</th>
              <?php } ?>
              <th>Column Count(*1)</th>
			  <th></th>
              <?php if ($DBWritePermission) { ?>
              <th></th>
              <?php } // if DBWritePermission ?>
			  <th></th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		
		$TotalColumnCount = 0;
		
		for($i = 0 ; $i < count($tablelist); $i++) {
			$table = $tablelist[$i];
			
			// filter
			if (is_numeric($filterdbtablePID)) {
				if ($filterdbtablePID != $table->PID) {
					continue;
				}
			}
			$correspondingTableColumnList = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $table->PID);
			
			?>
			<tr>
              <td><?php print ($i+1); ?></td>
			  <td><?php print htmlspecialchars($table->name); ?></td>
              <?php if ($show_recommended_column_warning) { ?>
              <td><?php
			    $message = "";
				if (CheckIfRequiredColumnExistInTable($correspondingTableColumnList, $message)) {
					?>
                    Exists
					<?php
				} else {
					?>
                    <font color="red"><?php print $message; ?></font>
                    <?php
				}
			  ?>
              </td>
              <?php } ?>
              <td>
              <?php
			  	$thisColumnCount = 0;
				if ($correspondingTableColumnList != NULL) {
					if (is_array($correspondingTableColumnList)) {
						
						for ($j = 0 ; $j < count($correspondingTableColumnList); $j++) {
							$DBTableColumn = $correspondingTableColumnList[$j];
							
							if ($DBTableColumn->name == "PID")
							{
								// No Count
							} else {
								$thisColumnCount++;
							}
						}
					}
				}
				print $thisColumnCount;
				
				$TotalColumnCount += $thisColumnCount;
			  ?>
              
              </td>
			  <td><a href="dbtable_columns.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DBTablePID=<?php print urlencode($table->PID); ?>&<?php print makeRandStr(8); ?>">View Column(s)</a></td>
              <?php if ($DBWritePermission) { ?>
			  <td><a href="dbtable_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DBTablePID=<?php print urlencode($table->PID); ?>&<?php print makeRandStr(8); ?>">Edit DB Table Info</a></td>
              <?php } // if DBWritePermission ?>
              <td><?php PrintAddMinutesLinkFordbtable($ProjectPID, $table->PID); ?></td>
              <td><?php PrintSearchMinutesLinkFordbtable($ProjectPID, $table->PID); ?></td>
			</tr>
			<?php
		}
		?>
			<tr>
			  <td></td>
              <td></td>
              <td>Total <?php print $TotalColumnCount; ?></td>
			  <td></td>
              <?php if ($DBWritePermission) { ?>
			  <td></td>
              <?php } // if DBWritePermission ?>
              <td></td>
			</tr>
        	</tbody>
		</table>
        
        <p>(*1) Excluding internal Required Column such as PID column</p>
        
		<?php
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
    <p align="right"><a href="dbtable_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New Table</a></p>
	<br>
	<br>
	<br>
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
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
