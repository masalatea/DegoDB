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
<title><?php print getres("TITLE_TEST_CONDITION_LIST"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

include_once("test_pattern_common.php");

InitializeOutputShortenedStringWithExpansion();

$ProjectPID = trim(GetParam("ProjectPID"));
$TestGroupPID = trim(GetParam("TestGroupPID"));
$TestPID = trim(GetParam("TestPID"));

$UPDATE = trim(GetParam("UPDATE"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($TestGroupPID)) {
	?>
    <H3><font color="red">Test Group is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($TestPID)) {
	?>
    <H3><font color="red">Test is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	
	printPathOnTopForTest("Test Pattern List", $ProjectPID, $TestGroupPID, $TestPID, "");
	
	$DATestCondition = new TestConditionDBAccess();
	$TestConditionList = $DATestCondition->GetTestConditionList($ProjectPID, $TestGroupPID, $TestPID);
	
	$DATestConditionSelection = new TestConditionSelectionDBAccess();
	
	$DATestPattern = new TestPatternDBAccess();
	$TestPatternList = NULL;
	
	$DATestPatternSelection = new TestPatternSelectionDBAccess();
	
	if (count($TestConditionList) > 0) {
		
		$AllPatternCount = 0;
		$TestConditionSelectionContainerList = InitializeTestConditionSelectionContainerList($ProjectPID, $TestGroupPID, $TestPID, $TestConditionList, $AllPatternCount, false);
		
		// Initialize $AllSelectionListOfList
		$AllSelectionListOfList = InitializeAllSelectionListOfList($AllPatternCount, $TestConditionSelectionContainerList);
		
		// Initialize TestPatternList
		$TestPatternList = InitializeTestPatternList($ProjectPID, $TestGroupPID, $TestPID);
		
		if ($UPDATE != "") {
			$anyUpdated = false;
			if (count($AllSelectionListOfList) > 0) {
				for($RowIndex = 0 ; $RowIndex < count($AllSelectionListOfList); $RowIndex++) {
					$AllSelectionList = $AllSelectionListOfList[$RowIndex];
					
					$correspondingTestPattern = GetCorrespondingTestPattern($TestPatternList, $AllSelectionList);
					
					$TestPatternObj = new TestPatternData();
					$TestPatternObj->ProjectPID = $ProjectPID;
					$TestPatternObj->TestGroupPID = $TestGroupPID;
					$TestPatternObj->TestPID = $TestPID;
					$TestPatternObj->ExpectedResult = trim(GetParam("ExpectedResult" . $RowIndex));
					
					if ($correspondingTestPattern == NULL) {
						// Need to Insert if not blank
						$TestPatternObj->PID = "";
						
						if($DATestPattern->InsertTestPattern($TestPatternObj) === FALSE) {
							// Failed
							?>
							<h3><font color="red">Error! Failed to insert</font></h3>
							<?php
						} else {
							// Success
							$TestPatternObj->PID = $mtooldb->insert_id;
							
							for($ColumnIndex = 0 ; $ColumnIndex < count($AllSelectionList) ; $ColumnIndex++) {
								$thisSelectionObj = $AllSelectionList[$ColumnIndex];
								
								$thisResultSelection = trim(GetParam("ExpectedResultPID" . $RowIndex . "_" . $ColumnIndex . "_" . $thisSelectionObj->PID));
								
								if ($thisResultSelection == $thisSelectionObj->Selection) {	// check for the safety
									$TestPatternSelectionObj = new TestPatternSelectionData();
									$TestPatternSelectionObj->ProjectPID = $ProjectPID;
									$TestPatternSelectionObj->TestGroupPID = $TestGroupPID;
									$TestPatternSelectionObj->TestPID = $TestPID;
									$TestPatternSelectionObj->TestPatternPID = $TestPatternObj->PID;
									$TestPatternSelectionObj->PID = "";
									$TestPatternSelectionObj->Selection = $thisSelectionObj->Selection;
									
									if($DATestPatternSelection->InsertTestPatternSelection($TestPatternSelectionObj) === FALSE) {
										// Failed
										?>
										<h3><font color="red">Error! Failed to insert</font></h3>
										<?php
									} else {
										// Success
										$anyUpdated = true;
									}
								}
							}
						}
						
					} else {
						// Need to Update
						$TestPatternObj->PID = $correspondingTestPattern->PID;
						if($DATestPattern->UpdateTestPattern($TestPatternObj) === FALSE) {
							// Failed
							?>
							<h3><font color="red">Error! Failed to update</font></h3>
							<?php
						} else {
							// Success
							$anyUpdated = true;
						}
					}
				}
			}
			if ($anyUpdated) {
				?>
				<h3><font color="red">Updated Test Pattern</font></h3>
				<?php
			}
		}
		
		// Initialize TestPatternList
		$TestPatternList = InitializeTestPatternList($ProjectPID, $TestGroupPID, $TestPID);
		
		?>
        <form action="test_pattern_and_results.php" method="post" id="PatternAndResultSubmitForm">
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Pattern</th>
			  <th colspan="<?php print count($TestConditionList); ?>"></th>
			  <th><span class="eacheditarea">Test Result</span></th>
			  <th><span class="bulkeditarea" style="display:none">Test Result</span></th>
			</tr>
            </thead>
            <tbody>
            <tr>
                <td rowspan="2"></td>
                <?php
                for($ColumnIndex = 0 ; $ColumnIndex < count($TestConditionList); $ColumnIndex++) {
                    ?>
                    <td><?php print ConvertNumToExcelColumnAlphabet($ColumnIndex); ?></td>
                    <?php
                }
                ?>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <?php
                for($ColumnIndex = 0 ; $ColumnIndex < count($TestConditionList); $ColumnIndex++) {
                    $TestCondition = $TestConditionList[$ColumnIndex];
                    ?>
                    <td><?php print htmlspecialchars(trim($TestCondition->Title)); ?></td>
                    <?php
                }
                ?>
                <td>
                <span id="EditInBulkLink">[Edit in Bulk]</span>
                </td>
                <td>
                <span id="EditEach" style="display:none">[Edit Each]</span>
                </td>
            </tr>
		<?php
		if (count($AllSelectionListOfList) > 0) {
			for($RowIndex = 0 ; $RowIndex < count($AllSelectionListOfList); $RowIndex++) {
				$AllSelectionList = $AllSelectionListOfList[$RowIndex];
				?>
				<tr>
                    <td><?php print ($RowIndex + 1); ?></td>
                    <?php
					
					for($ColumnIndex = 0 ; $ColumnIndex < count($AllSelectionList) ; $ColumnIndex++) {
						$thisSelectionObj = $AllSelectionList[$ColumnIndex];
						
						$thisSelectionCaption = $thisSelectionObj->Selection;
						if ($thisSelectionCaption == "") {
							$thisSelectionCaption = "-";
						}
						?>
						<td><?php print htmlspecialchars($thisSelectionCaption); ?></td>
						<?php
                    }
					
					// Get Current Value
					$thisExpectedResult = "";
					$correspondingTestPattern = GetCorrespondingTestPattern($TestPatternList, $AllSelectionList);
					if ($correspondingTestPattern != NULL) {
						$thisExpectedResult = $correspondingTestPattern->ExpectedResult;
					}
                    ?>
                    <td><div class="eacheditarea"><input name="ExpectedResult<?php print $RowIndex; ?>" id="ExpectedResult<?php print $RowIndex; ?>" type="text" class="form-control" value="<?php print htmlspecialchars($thisExpectedResult); ?>">
                    </div>
                    <?php
					for($ColumnIndex = 0 ; $ColumnIndex < count($AllSelectionList) ; $ColumnIndex++) {
						$thisSelectionObj = $AllSelectionList[$ColumnIndex];
						?>
						<input name="ExpectedResultPID<?php print $RowIndex . "_" . $ColumnIndex . "_" . $thisSelectionObj->PID; ?>" type="hidden" value="<?php print htmlspecialchars($thisSelectionObj->Selection); ?>">
						
						<?php
					}
					?>
                    </td>
                    <?php
					if ($RowIndex == 0) {
					?>
                    <td rowspan="<?php print count($AllSelectionListOfList); ?>">
                    <div id="BuilEditArea" style="display:none">
                    <textarea name="ExpectedResultBulkTextArea" id="ExpectedResultBulkTextArea" rows="<?php print count($AllSelectionListOfList) + 1; ?>" class="form-control"></textarea>
                    </div>
                    </td>
                    <?php
					}
					?>
				</tr>
				<?php
			}
		}
		?>
			<tr>
            	<td></td>
                <td colspan="<?php print count($TestConditionList); ?>">
                </td>
                <td colspan="2">
                <input name="UpdateButton" id="UpdateButton" type="button" value="UPDATE">
                </td>
			</tr>
        	</tbody>
		</table>
        <input name="ProjectPID" type="hidden" value="<?php print $ProjectPID; ?>">
        <input name="TestGroupPID" type="hidden" value="<?php print $TestGroupPID; ?>">
        <input name="TestPID" type="hidden" value="<?php print $TestPID; ?>">
        <input name="UPDATE" id="UPDATE" type="hidden" value="">
        </form>
<script>
$(document).ready(function() {
	$("#EditInBulkLink").click(function () {
		ChangeModeToBulkEdit();
	});
	$("#EditEach").click(function () {
		ChangeModeToEachEdit();
	});
	$("#UpdateButton").click(function () {
		$("#UpdateButton").hide();
		ChangeModeToEachEdit();
		$("#UPDATE").val("t");
		$("#PatternAndResultSubmitForm").submit();
	});
});

var EDIT_MODE_EACH ="EACH";
var EDIT_MODE_BULK ="BULK";
var NowMode =EDIT_MODE_EACH;
function ChangeModeToBulkEdit()
{
	if (NowMode == EDIT_MODE_BULK) {
		return;
	}
	NowMode = EDIT_MODE_BULK;
	
	$("#BuilEditArea").show();
	var bulkText = "";
	for(var i = 0 ; i < <?php print count($AllSelectionListOfList); ?>; i++) {
		bulkText += i + "=" + $("#ExpectedResult" + i).val() + "\r\n";
	}
	$("#ExpectedResultBulkTextArea").val(bulkText);
	
	$(".eacheditarea").hide();
	$(".bulkeditarea").show();
	$("#EditInBulkLink").hide();
	$("#EditEach").show();
}
	
function ChangeModeToEachEdit()
{
	if (NowMode == EDIT_MODE_EACH) {
		return;
	}
	NowMode = EDIT_MODE_EACH;
	
	for(var i = 0 ; i < <?php print count($AllSelectionListOfList); ?>; i++) {
		$("#ExpectedResult" + i).val("");
	}
	var bulkText = $("#ExpectedResultBulkTextArea").val();
	var bulkTextList = bulkText.split(/\r\n|\r|\n/);
	for (i = 0; i < bulkTextList.length; i++) {
		var bulkTextOneLineSplittedData = bulkTextList[i].match(/^\s*(\d+)\s*=\s*(.*)$/);
		if (bulkTextOneLineSplittedData != null) {
			var thisIndex = bulkTextOneLineSplittedData[1];
			var thisValue = bulkTextOneLineSplittedData[2];
			
			$("#ExpectedResult" + thisIndex).val(thisValue);
		}
	}
	$("#BuilEditArea").hide();
	$(".eacheditarea").show();
	$(".bulkeditarea").hide();
	$("#EditInBulkLink").show();
	$("#EditEach").hide();
}
</script>


		<?php
		
		if (count($TestConditionList) > 0) {
			?>
            <H3>Test Condition Details</H3>
			<table class="table">
				<thead>
				<tr bgcolor="#ECECEC">
				  <th>Column</th>
				  <th>Title / Description</th>
				</tr>
				</thead>
				<tbody>
			<?php
			for($i = 0 ; $i < count($TestConditionList); $i++) {
				$TestCondition = $TestConditionList[$i];
				?>
				<tr>
				  <td rowspan="2"><?php print ConvertNumToExcelColumnAlphabet($i); ?></td>
				  <td><?php print htmlspecialchars($TestCondition->Title); ?></td>
				</tr>
				<tr>
				  <td><?php print nl2br(htmlspecialchars(trim($TestCondition->Description))); ?></td>
				</tr>
				<?php
			}
			?>
				</tbody>
			</table>
			
			<?php
		}
		
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>

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

