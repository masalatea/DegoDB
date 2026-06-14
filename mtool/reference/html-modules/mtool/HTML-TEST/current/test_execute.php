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
<title><?php print getres("TITLE_TEST_EXECUTE"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
	$DATestPatternExecuteResult = new TestPatternExecuteResultDBAccess();
	
	if (count($TestConditionList) > 0) {
		
		$AllPatternCount = 0;
		$TestConditionSelectionContainerList = InitializeTestConditionSelectionContainerList($ProjectPID, $TestGroupPID, $TestPID, $TestConditionList, $AllPatternCount, false);
		
		// Initialize $AllSelectionListOfList
		$AllSelectionListOfList = InitializeAllSelectionListOfList($AllPatternCount, $TestConditionSelectionContainerList);
		
		// Initialize TestPatternList
		$TestPatternList = InitializeTestPatternList($ProjectPID, $TestGroupPID, $TestPID);
		
		// Initialize Test Execute Result
		$TestPatternExecuteResultList = $DATestPatternExecuteResult->GetTestPatternExecuteResultList($ProjectPID, $TestGroupPID, $TestPID);
		
		if ($UPDATE != "") {
			$anyUpdated = false;
			if (count($AllSelectionListOfList) > 0) {
				for($RowIndex = 0 ; $RowIndex < count($AllSelectionListOfList); $RowIndex++) {
					$AllSelectionList = $AllSelectionListOfList[$RowIndex];
					
					$correspondingTestPattern = GetCorrespondingTestPattern($TestPatternList, $AllSelectionList);
					if ($correspondingTestPattern == NULL) {
						?>
						<h3><font color="red">Error! . Something Strange. Please ask administrato if this continues.</font></h3>
						<?php
					} else {
						$correspondingTestPatternExecuteResult = GetCorrespondingTestPatternExecuteResult($TestPatternExecuteResultList, $correspondingTestPattern);
						
						$TestPatternExecuteResultObj = new TestPatternExecuteResultData();
						$TestPatternExecuteResultObj->ProjectPID = $ProjectPID;
						$TestPatternExecuteResultObj->TestGroupPID = $TestGroupPID;
						$TestPatternExecuteResultObj->TestPID = $TestPID;
						$TestPatternExecuteResultObj->TestPatternPID = $correspondingTestPattern->PID;
						$TestPatternExecuteResultObj->ExecuteResult = trim(GetParam("ExecuteResult" . $RowIndex));
						$TestPatternExecuteResultObj->Comment = trim(GetParam("Comment" . $RowIndex));
						
						// print "Row Index: $RowIndex   Execute Result: " . $TestPatternExecuteResultObj->ExecuteResult . "<br>\n";
						
						if ($correspondingTestPatternExecuteResult == NULL) {
							// Need to Insert if not blank
							$TestPatternExecuteResultObj->PID = "";
							
							if ($TestPatternExecuteResultObj->ExecuteResult == "" &&
								$TestPatternExecuteResultObj->Comment == "") {
								// Blank. No need to insert.
							} else {
								if($DATestPatternExecuteResult->InsertTestPatternExecuteResult($TestPatternExecuteResultObj) === FALSE) {
									// Failed
									?>
									<h3><font color="red">Error! Failed to insert</font></h3>
									<?php
								} else {
									// Success
									$TestPatternExecuteResultObj->PID = $mtooldb->insert_id;
									$anyUpdated = true;
								}
							}
							
						} else {
							// Need to Update
							if ($correspondingTestPatternExecuteResult != NULL) {
								$TestPatternExecuteResultObj->PID = $correspondingTestPatternExecuteResult->PID;
							}
							if($DATestPatternExecuteResult->UpdateTestPatternExecuteResult($TestPatternExecuteResultObj) === FALSE) {
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
			}
			if ($anyUpdated) {
				?>
				<h3><font color="red">Updated Test Result</font></h3>
				<?php
			}
		}
		
		// Initialize Test Execute Result
		$TestPatternExecuteResultList = $DATestPatternExecuteResult->GetTestPatternExecuteResultList($ProjectPID, $TestGroupPID, $TestPID);
		
		?>
        <form action="test_execute.php" method="post" id="PatternAndResultSubmitForm">
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Pattern</th>
			  <th colspan="<?php print count($TestConditionList); ?>"></th>
			  <th>Expected Result</th>
			  <th>Execute Result</th>
			  <th>Comment</th>
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
                <td colspan="2"></td>
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
                <td></td>
                <td colspan="2"></td>
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
					$thisExecuteResult = "";
					$thisComment = "";
					$correspondingTestPattern = GetCorrespondingTestPattern($TestPatternList, $AllSelectionList);
					if ($correspondingTestPattern != NULL) {
						$thisExpectedResult = $correspondingTestPattern->ExpectedResult;
					}
					$correspondingTestPatternExecuteResult = GetCorrespondingTestPatternExecuteResult($TestPatternExecuteResultList, $correspondingTestPattern);
					if ($correspondingTestPatternExecuteResult != NULL) {
						$thisExecuteResult = $correspondingTestPatternExecuteResult->ExecuteResult;
						$thisComment = $correspondingTestPatternExecuteResult->Comment;
					}
                    ?>
                    <td><?php print htmlspecialchars($thisExpectedResult); ?></td>
                    <td>
                    <?php
					$radio_selction_list = array(
						array("VALUE"=>"", "CAPTION"=>"Not yet"),
						array("VALUE"=>"OK", "CAPTION"=>"OK"),
						array("VALUE"=>"NG", "CAPTION"=>"NG"),
						);
					for($i = 0 ; $i < count($radio_selction_list) ; $i++) {
						$radio_selction = $radio_selction_list[$i];
						$thisValue   = $radio_selction["VALUE"];
						$thisCaption = $radio_selction["CAPTION"];
						
						if ($i != 0) {
							print "<br>\n";
						}
						?>
                        <span class="radio-inline"><label><input name="ExecuteResult<?php print $RowIndex; ?>" type="radio" value="<?php print $thisValue; ?>"<?php if ($thisExecuteResult == $thisValue) { print " checked"; } ?>><?php print $thisCaption; ?></label></span>
						<?php
					}
					?>
                    </td>
                    <td>
                    <textarea name="Comment<?php print $RowIndex; ?>" id="Comment<?php print $RowIndex; ?>" rows="3" class="form-control"><?php print htmlspecialchars($thisComment); ?></textarea>
                    </td>
				</tr>
				<?php
			}
		}
		?>
			<tr>
            	<td></td>
                <td colspan="<?php print count($TestConditionList); ?>">
                </td>
            	<td></td>
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
	$("#UpdateButton").click(function () {
		$("#UpdateButton").hide();
		$("#UPDATE").val("t");
		$("#PatternAndResultSubmitForm").submit();
	});
});
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
