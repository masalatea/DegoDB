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
	
function SaveConditionSelections($TestCondition, $ConditionSelections)
{
	$DATestConditionSelection = new TestConditionSelectionDBAccess();
	$TestConditionSelectionList = $DATestConditionSelection->GetTestConditionSelectionList(
																$TestCondition->ProjectPID,
																$TestCondition->TestGroupPID,
																$TestCondition->TestPID,
																$TestCondition->PID);
	$ConditionSelectionList = preg_split("/\r?\n/", $ConditionSelections);
	
	$thisOrderNum = 1;
	for($i = 0 ; $i < count($ConditionSelectionList); $i++) {
		$thisSelection = trim($ConditionSelectionList[$i]);
		if ($thisSelection != "") {
			
			$correspondingExistingSelection = NULL;
			for($j = 0 ; $j < count($TestConditionSelectionList); $j++) {
				$TestConditionSelection = $TestConditionSelectionList[$j];
				if ($thisSelection == $TestConditionSelection->Selection) {
					// Already exist.
					$correspondingExistingSelection = $TestConditionSelection;
					break;
				}
			}
			if ($correspondingExistingSelection != NULL) {
				if ($correspondingExistingSelection->SelectionOrder == $thisOrderNum &&
				    $correspondingExistingSelection->IsNewest == '1') {
					// No need to update
				} else {
					// Update Order and/or Newest Flag
					$correspondingExistingSelection->IsNewest = '1';
					$DATestConditionSelection->UpdateTestConditionSelection($correspondingExistingSelection);
				}
				$correspondingExistingSelection->AlreadyCheckedWhenEdit = true;
				
			} else {
				// Need to Insert
				$NewTestConditionSelection = new TestConditionSelectionData();
				$NewTestConditionSelection->ProjectPID = $TestCondition->ProjectPID;
				$NewTestConditionSelection->TestGroupPID = $TestCondition->TestGroupPID;
				$NewTestConditionSelection->TestPID = $TestCondition->TestPID;
				$NewTestConditionSelection->TestConditionPID = $TestCondition->PID;
				$NewTestConditionSelection->PID = "";
				$NewTestConditionSelection->Selection = $thisSelection;
				$NewTestConditionSelection->SelectionOrder = $thisOrderNum;
				$NewTestConditionSelection->IsNewest = '1';
				$NewTestConditionSelection->ResultExists = '0';
				$DATestConditionSelection->InsertTestConditionSelection($NewTestConditionSelection);
			}
			$thisOrderNum++;
		}
	}
	for($j = 0 ; $j < count($TestConditionSelectionList); $j++) {
		$TestConditionSelection = $TestConditionSelectionList[$j];
		if ($TestConditionSelection->AlreadyCheckedWhenEdit == false) {
			// Need to Delete
			$DATestConditionSelection->UpdateTestConditionSelectionSetToOld($TestConditionSelection);
		}
	}
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_TEST_CONDITION_EDIT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$TestCondition = new TestConditionData();
$TestCondition->ProjectPID = trim(GetParam("ProjectPID"));
$TestCondition->TestGroupPID = trim(GetParam("TestGroupPID"));
$TestCondition->TestPID = trim(GetParam("TestPID"));
$TestCondition->PID = trim(GetParam("TestConditionPID"));
$TestCondition->Title = trim(GetParam("Title"));
$TestCondition->Description = trim(GetParam("Description"));

$ConditionSelections = trim(GetParam("ConditionSelections"));

$NoError = true;
if (!is_numeric($TestCondition->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($TestCondition->TestGroupPID)) {
	?>
    <H3><font color="red">Test Group is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($TestCondition->TestPID)) {
	?>
    <H3><font color="red">Test is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

$showForm = true;

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($TestCondition->PID == "") {
		// Add
		if ($UPDATE != "") {
			
			// print_r($TestCondition);
			
			$DATestCondition = new TestConditionDBAccess();
			if($DATestCondition->InsertTestCondition($TestCondition) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to insert</font></h3>
                <?php
			} else {
				// Success
				$TestCondition->PID = $mtooldb->insert_id;
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_TEST_CONDITION"); ?></font></h3>
                <?php
				
				SaveConditionSelections($TestCondition, $ConditionSelections);
			}
		}
		
	} else if (is_numeric($TestCondition->PID)) {
		// Select/Update
		$needToLoad = true;
		$DATestCondition = new TestConditionDBAccess();
		if ($UPDATE != "") {
			
			if($DATestCondition->UpdateTestCondition($TestCondition) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red"><?php print getres("ACTION_UPDATED_TEST_CONDITION"); ?></font></h3>
                <?php
				
				SaveConditionSelections($TestCondition, $ConditionSelections);
			}
		}
		if ($DELETE != "") {
			if($DATestCondition->DeleteTestCondition($TestCondition) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to delete</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red"><?php print getres("ACTION_DELETED_TEST_CONDITION"); ?></font></h3>
                <?php
				$needToLoad = false;
				$showForm = false;
			}
			$TestCondition->PID = "";
		}

		if ($needToLoad) {
			$TestCondition = $DATestCondition->GetTestCondition($TestCondition->PID, $TestCondition->ProjectPID);
			
			$ConditionSelections = "";
			if ($TestCondition != NULL) {
				
				$DATestConditionSelection = new TestConditionSelectionDBAccess();
				$TestConditionSelectionList = $DATestConditionSelection->GetNewestTestConditionSelectionList(
																			$TestCondition->ProjectPID,
																			$TestCondition->TestGroupPID,
																			$TestCondition->TestPID,
																			$TestCondition->PID);
				for($i = 0 ; $i < count($TestConditionSelectionList); $i++) {
					$TestConditionSelection = $TestConditionSelectionList[$i];
					
					$ConditionSelections .= $TestConditionSelection->Selection . "\n";
				}
				$ConditionSelections = trim($ConditionSelections);
			}
		}
		
	} else {
		?>
		<h4>FATAL ERROR! Test Condition PID is something strange.</h4>
		<?php
		die();
	}
	if ($TestCondition->PID == "") {
		// Add
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_TEST_CONDITION");
		
	} else {
		// Select/Update
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_TEST_CONDITION");
	}
	
	if ($showForm && $TestCondition != NULL) {
		
		printPathOnTopForTest($HeaderCaption, $TestCondition->ProjectPID, $TestCondition->TestGroupPID, $TestCondition->TestPID, $TestCondition->PID);
		
		?>
		
		<form action="test_condition_edit.php" method="post">
		
        <?php
		$TEXTAREA_MARGIN_ROW_COUNT = 2;
		
		mtoolCommonFormInput("Title", $TestCondition->Title,
			array($LANG_ENGLISH=>"Title", $LANG_JAPANESE=>"タイトル"),
			array($LANG_ENGLISH=>"Please input Title", $LANG_JAPANESE=>"タイトルを入力して下さい"),
			"text", "");
		mtoolCommonFormTextAreaWithRowCount("Description", $TestCondition->Description,
			array($LANG_ENGLISH=>"Description", $LANG_JAPANESE=>"説明文"),
			array($LANG_ENGLISH=>"Please input Description", $LANG_JAPANESE=>"説明文を入力して下さい"),
			"", CountLineCount($TestCondition->Description) + $TEXTAREA_MARGIN_ROW_COUNT);
		mtoolCommonFormTextAreaWithRowCount("ConditionSelections", $ConditionSelections,
			array($LANG_ENGLISH=>"Selection(s)<br>(Input one selection for one line)", $LANG_JAPANESE=>"選択肢<br>(1行に1選択肢を入力)"),
			array($LANG_ENGLISH=>"Please input Selection(s)", $LANG_JAPANESE=>"選択肢を入力して下さい"),
			"", CountLineCount($ConditionSelections) + $TEXTAREA_MARGIN_ROW_COUNT);
		
		?>
		
		<div class="row">
		  <label class="col-md-3 control-label" for="inputtext"></label>
		  <div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
          
			<?php
            if ($TestCondition->PID != "") {
				?>
				<p align="right">
				<input name="DELETE" type="submit" value="<?php print htmlspecialchars(getres("ACTION_DELETE")); ?>" onClick="return confirm('<?php print htmlspecialchars(getres("ACTION_DELETE_CONFIRM")); ?>');">
				</p>
				<?php
            }
            ?>
          </div>
		</div>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($TestCondition->ProjectPID); ?>">
		<input name="TestGroupPID" type="hidden" value="<?php print htmlspecialchars($TestCondition->TestGroupPID); ?>">
		<input name="TestPID" type="hidden" value="<?php print htmlspecialchars($TestCondition->TestPID); ?>">
		<input name="TestConditionPID" type="hidden" value="<?php print htmlspecialchars($TestCondition->PID); ?>">
		</form>
        
		<?php
	}
	?>
	<br>
	<br>
	<br>
    <p><a href="test_conditions.php?ProjectPID=<?php print urlencode($TestCondition->ProjectPID); ?>&TestGroupPID=<?php print urlencode($TestCondition->TestGroupPID); ?>&TestPID=<?php print urlencode($TestCondition->TestPID); ?>&<?php print makeRandStr(8); ?>">Back to Test Condition List</a></p>
    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/test/footer_back_link_include.php");
	print_footer_back_link($TestCondition->ProjectPID);
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
