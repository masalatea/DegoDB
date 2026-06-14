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
<title><?php print getres("TITLE_DA_FUNC_SELECT_TARGET_FIELDS_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("da_func_select_target_fields_update_list_order_lib.php");

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
	adjust_list_order_of_select_target_fields_and_show_message($ProjectPID, $DAPID, $DAFuncPID);
	
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForDBAccessClass("Select Target Field(s)", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
	$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID);
	
	$storeClassFieldNameHT = array();
	
	if (count($dafuncselecttargetfieldlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Target Table Name</th>
			  <th>Alias Table Name</th>
			  <th>Prefix for Column Name</th>
			  <th>Column Name on Target Table</th>
			  <th>Suffix for Column Name</th>
			  <th>Field Name of Store Class</th>
			  <th>Group-By Target</th>
              <?php if ($DBWritePermission) { ?>
			  <th></th>
              <?php } // if DBWritePermission ?>
			</tr>
          </thead>
            <tbody>
		<?php

		for($i = 0 ; $i < count($dafuncselecttargetfieldlist); $i++) {
			$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$i];
			
			?>
			<tr>
			  <td><?php print htmlspecialchars($dafuncselecttargetfield->targetTableName); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecttargetfield->targetTableAliasName); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecttargetfield->targetTableColumnPrefix); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecttargetfield->targetTableColumnName); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecttargetfield->targetTableColumnSuffix); ?></td>
			  <td><?php print htmlspecialchars($dafuncselecttargetfield->storeClassFieldName); 
			  
			  if (array_key_exists(trim($dafuncselecttargetfield->storeClassFieldName), $storeClassFieldNameHT)) {
				  ?>
                  <br>
                  <font color="red">ERROR! This will override another value. Please check.</font>
                  <?php
			  }
			  $storeClassFieldNameHT[trim($dafuncselecttargetfield->storeClassFieldName)] = true;
			  
			  ?></td>
              <td>
              <?php
			  if ($dafuncselecttargetfield->GroupByTarget == "1") {
				  print "Yes";
			  } else {
				  print "No";
			  }
			  ?>
              </td>
              <?php if ($DBWritePermission) { ?>
			  <td><a href="da_func_select_target_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($dafuncselecttargetfield->daPID); ?>&DAFuncPID=<?php print urlencode($dafuncselecttargetfield->dafuncPID); ?>&PID=<?php print urlencode($dafuncselecttargetfield->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
              <?php } // if DBWritePermission ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
        
		<?php
	} else {
		?>
<p>none</p>
		<?php
	}
	?>
	<?php if ($DBWritePermission) { ?>
    <p align="right"><a href="da_func_select_target_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($DAFuncPID); ?>&<?php print makeRandStr(8); ?>">Add New Select Target Field</a></p>
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
