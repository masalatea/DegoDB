<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$TestGroup = new TestGroupData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$TestGroup->ProjectPID = trim(GetParam("ProjectPID"));
$TestGroup->PID = trim(GetParam("TestGroupPID"));
$TestGroup->name = trim(GetParam("name"));
$TestGroup->UnitTestTemplateBaseDir = trim(GetParam("UnitTestTemplateBaseDir"));
$TestGroup->UnitTestWorkingDir = trim(GetParam("UnitTestWorkingDir"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($TestGroup->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$TestGroup->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($TestGroup->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DATestGroup = new TestGroupDBAccess();
			$insertResult = $DATestGroup->InsertTestGroup($TestGroup);
			// == END OF EDITABLE AREA FOR "Insert Data" ==
			if($insertResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Insert Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to insert</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Failed" ==
			} else {
				// Success
				$TestGroup->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_TEST_GROUP"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $TestGroup->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($TestGroup->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $TestGroup->PID)) {
					// Success
					$insertToken = "";
				} else {
					// Failed
					?>
					<h3><font color="red">Internal Error! Failed to complete Insert</font></h3>
					<?php
				}
			}
		}
		
	} else if (is_numeric($TestGroup->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DATestGroup = new TestGroupDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DATestGroup->UpdateTestGroup($TestGroup);
			// == END OF EDITABLE AREA FOR "Update Data" ==
			if($updateResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Update Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Failed" ==
				$needToLoad = false;
				
			} else {
				// Success
				// == START OF EDITABLE AREA FOR "Update Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_UPDATED_TEST_GROUP"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DATestGroup->DeleteTestGroup($TestGroup);
			// == END OF EDITABLE AREA FOR "Delete Data" ==
			if($deleteResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Delete Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to delete</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Failed" ==
				$needToLoad = false;
				
			} else {
				// Success
				// == START OF EDITABLE AREA FOR "Delete Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_DELETED_TEST_GROUP"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$TestGroup = $DATestGroup->GetTestGroup($TestGroup->PID, $TestGroup->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! Test Group PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($TestGroup->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_TEST_GROUP");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_TEST_GROUP");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $TestGroup != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		// printPathOnTopForDataClasses($HeaderCaption, $TestGroup->ProjectPID, $TestGroup->PID, "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="testgroup_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $TestGroup->name,
			array($LANG_ENGLISH=>"Test Group Name", $LANG_JAPANESE=>"テストグループ名"),
			array($LANG_ENGLISH=>"Please input Test Group Name", $LANG_JAPANESE=>"テストグループ名を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("UnitTestTemplateBaseDir", $TestGroup->UnitTestTemplateBaseDir,
			array($LANG_ENGLISH=>"Unit Test's Template Base Dir", $LANG_JAPANESE=>"Unit Testテンプレートのパス"),
			array($LANG_ENGLISH=>"Please input Unit Test's Template Base Dir (on Storege such as DropBox)", $LANG_JAPANESE=>"Unit Testテンプレートのパスを入力して下さい。格納先(DropBox等)のパス。空の場合はプロジェクト規定あるいはシステム規定が使用されます"),
			"text", "");
		mtoolCommonFormInput("UnitTestWorkingDir", $TestGroup->UnitTestWorkingDir,
			array($LANG_ENGLISH=>"Unit Test's Work Dir", $LANG_JAPANESE=>"Unit Test作業用パス"),
			array($LANG_ENGLISH=>"Please input Unit Test's Work Dir (on Storege such as DropBox)", $LANG_JAPANESE=>"Unit Test作業用パスを入力して下さい。格納先(DropBox等)のパス。空の場合はプロジェクト規定あるいはシステム規定が使用されます"),
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($TestGroup->PID != "") {
				?>
				<p align="right">
				<input name="DELETE" type="submit" value="<?php print htmlspecialchars(getres("ACTION_DELETE")); ?>" onClick="return confirm('<?php print htmlspecialchars(getres("ACTION_DELETE_CONFIRM")); ?>');">
				</p>
				<?php
			}
			?>
			</div>
		</div>
		<?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($TestGroup->ProjectPID); ?>">
		<input name="TestGroupPID" type="hidden" value="<?php print htmlspecialchars($TestGroup->PID); ?>">
		<?php
		// == END OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="insertToken" type="hidden" value="<?php print htmlspecialchars($insertToken); ?>">
		</form>
		<?php
	}
	?>
	<br>
	<br>
	<br>
	<?php
	// == START OF EDITABLE AREA FOR "Bottom Links" ==
	include_once("/srv/legacy/www/$WWWDOMAINNAME/test/footer_back_link_include.php");
	print_footer_back_link($TestGroup->ProjectPID);
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
