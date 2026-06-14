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
<title><?php print getres("TITLE_DA_FUNC_UPDATE_TARGET_FIELDS_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("da_func_insert_or_update_target_fields_update_list_order_lib.php");
include_once("da_func_common_for_blob.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Class PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAFuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	adjust_list_order_of_insert_or_update_target_fields_and_show_message($ProjectPID, $DAPID, $DAFuncPID);
	
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForDBAccessClass("Update/Delete Target Field(s)", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
	$dafuncupdatetargetfieldlist = $DAdafuncupdatetargetfields->GetdafuncupdatetargetfieldsList($ProjectPID, $DAPID, $DAFuncPID); 
	
	if (count($dafuncupdatetargetfieldlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Column Name on Target Table</th>
			  <th>Parameter Type</th>
			  <th>Parameter's Data Type</th>
			  <th>Fixed Parameter</th>
              <?php if ($DBWritePermission) { ?>
			  <th></th>
              <?php } // if DBWritePermission ?>
			</tr>
          </thead>
            <tbody>
		<?php

		for($i = 0 ; $i < count($dafuncupdatetargetfieldlist); $i++) {
			$dafuncupdatetargetfield = $dafuncupdatetargetfieldlist[$i];
			
			if ($dafuncupdatetargetfield->ParameterDataType == dafuncupdatetargetfieldsParameterDataTypeEnum::$FILE) {
				IncrementFileDataTypeCount();
			}
			?>
			<tr>
			  <td><?php print htmlspecialchars($dafuncupdatetargetfield->targetTableColumnName); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatetargetfield->ParameterType); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatetargetfield->GetParameterDataTypeCaption()); ?></td>
			  <td><?php print htmlspecialchars($dafuncupdatetargetfield->GetFixedParameterCaptionIfParameterTypeIsFixed()); ?></td>
              <?php if ($DBWritePermission) { ?>
			  <td><a href="da_func_update_target_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($dafuncupdatetargetfield->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncupdatetargetfield->dafuncPID); ?>&PID=<?php print urlencode($dafuncupdatetargetfield->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
              <?php } // if DBWritePermission ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
        
		<?php
		
		CheckAndDisplayFileDataTypeCountWarning();
		
	} else {
		?>
<p>none</p>
		<?php
	}
	?>
	<?php if ($DBWritePermission) { ?>
    <p align="right"><a href="da_func_update_target_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($DAFuncPID); ?>&<?php print makeRandStr(8); ?>">Add New Update/Delete Target Field</a></p>
    <?php } // if DBWritePermission ?>
	<br>
	<br>
	<br>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
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
