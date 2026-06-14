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
<title><?php print getres("TITLE_DATA_CLASS_FIELD_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = trim(GetParam("ProjectPID"));
$DataClassPID = trim(GetParam("DataClassPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DataClassPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Data Class PID</font></H3>
    <?php
	$NoError = false;
}

InitializeOutputShortenedStringWithExpansion();

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForDataClasses("Field List", $ProjectPID, $DataClassPID, "");
	
	$DAdataclassfields = new dataclassfieldsDBAccess();
	$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $DataClassPID); 
	
	if (count($dataclassfieldlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Name</th>
			  <th>Data Type<br>
<font size="-2">(For C#, not for PHP)</font></th>
              <?php if ($DBWritePermission) { ?>
			  <th>Referencing Table Name</th>
			  <th>Referencing Field Name of Table</th>
			  <th></th>
			  <th></th>
              <?php } // if DBWritePermission ?>
			</tr>
          </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($dataclassfieldlist); $i++) {
			$dataclassfield = $dataclassfieldlist[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($dataclassfield->name); ?></td>
			  <td><?php OutputShortenedStringWithExpansion($dataclassfield->datatype, 20); ?></td>
              <?php if ($DBWritePermission) { ?>
			  <td><?php print htmlspecialchars($dataclassfield->RefDataClassName); ?></td>
			  <td><?php print htmlspecialchars($dataclassfield->RefDataClassFieldName); ?></td>
			  <td><a href="dataclass_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&DataClassFieldPID=<?php print urlencode($dataclassfield->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
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
    <p align="right"><a href="dataclass_field_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&<?php print makeRandStr(8); ?>">Add New Field</a></p>
    <p align="right"><a href="dataclasses_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&<?php print makeRandStr(8); ?>">Change Field's Order</a></p>
    <?php } // if DBWritePermission ?>
    <br>
    <br>
    <br>
    <p><a href="./dataclasses.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Data Class List</a></p>
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
