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
<title><?php print getres("TITLE_DBTABLE_COLUMN_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
$DBTablePID = trim(GetParam("DBTablePID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DBTablePID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Table PID</font></H3>
    <?php
	$NoError = false;
}

InitializeOutputShortenedStringWithExpansion();

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForDBTable("Column List", $ProjectPID, $DBTablePID, "");
	
	$DAdbtablecolumns = new dbtablecolumnsDBAccess();
	$dbtablecolumnslist = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $DBTablePID); 
	
	if (count($dbtablecolumnslist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Name</th>
			  <th>Data Type</th>
			  <th>Null</th>
			  <th>Key</th>
			  <th>Default</th>
			  <th>Extra</th>
			  <th>Memo</th>
              <?php if ($DBWritePermission) { ?>
			  <th></th>
              <?php } // if DBWritePermission ?>
			</tr>
          </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($dbtablecolumnslist); $i++) {
			$dbtablecolumns = $dbtablecolumnslist[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($dbtablecolumns->name); ?></td>
			  <td><?php OutputShortenedStringWithExpansion($dbtablecolumns->datatype, 20); ?></td>
			  <td><?php print htmlspecialchars($dbtablecolumns->IsNull); ?></td>
			  <td><?php print htmlspecialchars($dbtablecolumns->IsKey); ?></td>
			  <td><?php print htmlspecialchars($dbtablecolumns->IsDefault); ?></td>
			  <td><?php print htmlspecialchars($dbtablecolumns->Extra); ?></td>
			  <td><?php print htmlspecialchars($dbtablecolumns->memo); ?></td>
              <?php if ($DBWritePermission) { ?>
			  <td><a href="dbtable_column_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DBTablePID=<?php print urlencode($dbtablecolumns->dbtablePID); ?>&DBTableColumnPID=<?php print urlencode($dbtablecolumns->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a></td>
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
    <p align="right"><a href="dbtable_column_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DBTablePID=<?php print urlencode($DBTablePID); ?>&<?php print makeRandStr(8); ?>">Add New Column</a></p>
    <?php } // if DBWritePermission ?>
	<br>
	<br>
	<br>
    <p><a href="./dbtables.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Table List</a></p>
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
