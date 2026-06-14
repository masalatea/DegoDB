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
<title><?php print getres("TITLE_UPDATE_UNIT_TEST_SOURCE"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

include_once("test_pattern_common.php");

InitializeOutputShortenedStringWithExpansion();

$ProjectPID = trim(GetParam("ProjectPID"));
$TestGroupPID = trim(GetParam("TestGroupPID"));
$TestPID = trim(GetParam("TestPID"));

$UPDATE = trim(GetParam("UPDATE"));
$OUTPUT_TO_TEMP_FOLDER = (GetParam("OUTPUT_TO_TEMP_FOLDER") != "");

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

$DAProject = new ProjectDBAccess();
$project = NULL;

$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();

$DATestGroup = new TestGroupDBAccess();
$TestGroup = NULL;

$DATest = new TestDBAccess();
$Test = NULL;

$UnitTestWorkingDir = "";

if ($NoError) {
	
	printPathOnTopForTest("Create/Update Unit Test Source", $ProjectPID, $TestGroupPID, $TestPID, "");
	
	$project = $DAProject->GetProject($ProjectPID);
	
	if ($project == NULL) {
		?>
		<H3><font color="red">Project is not found. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
		<?php
		$NoError = false;
	}
}
if ($NoError) {
	
	$Test = NULL;
	$TestGroup = NULL;
	if ($project != NULL) {
		$Test = $DATest->GetTest($TestPID, $ProjectPID);
		if ($Test != NULL) {
			$TestGroup = $DATestGroup->GetTestGroup($Test->TestGroupPID, $ProjectPID);
		}
	}
	
	if ($project == NULL) {
		?>
		<H3><font color="red">Error. Project is not found. Please ask administrato if this continues.</font></H3>
		<?php
		$NoError = false;
	}
	if ($TestGroup == NULL) {
		?>
		<H3><font color="red">Error. Test Group is not found. Please ask administrato if this continues.</font></H3>
		<?php
		$NoError = false;
	}
	if ($Test == NULL) {
		?>
		<H3><font color="red">Error. Test is not found. Please ask administrato if this continues.</font></H3>
		<?php
		$NoError = false;
	}
}
if ($NoError) {
	
	if ($Test->UnitTestTargetClassName == "") {
		?>
		<H3><font color="red">Error. Unit Test Target Class Name is not set. Please check and update Test setting.</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {	

	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
	
	for($projectsourceoutputindex = 0 ; $projectsourceoutputindex < count($ProjectSourceOutputList); $projectsourceoutputindex++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$projectsourceoutputindex];
		
		$ext = "";
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				$ext = ".php";
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				$ext = ".cs";
				break;
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				$ext = ".java";
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				$ext = ".h";
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				$ext = ".m";
				break;
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				$ext = ".swift";
				break;
			default:
				die("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
		}
		$UnitTestWorkingDir = GetUnitTestWorkingDir($project, $ProjectSourceOutput, $TestGroup, $OUTPUT_TO_TEMP_FOLDER);
		if ($UnitTestWorkingDir == "") {
			?>
            <p>Warning: One setting was skipped because Unit Test Output Dir is not set.</p>
            <?php
			continue;
		}
		
		$classname = $Test->UnitTestTargetClassName . "Test";
		$filename = $classname . $ext;
		
		$DATestCondition = new TestConditionDBAccess();
		$TestConditionList = $DATestCondition->GetTestConditionList($ProjectPID, $TestGroupPID, $TestPID);
		
		$DATestConditionSelection = new TestConditionSelectionDBAccess();
		
		$DATestPattern = new TestPatternDBAccess();
		$TestPatternList = NULL;
		
		$DATestPatternSelection = new TestPatternSelectionDBAccess();
	
		$AllPatternCount = 0;
		$TestConditionSelectionContainerList = InitializeTestConditionSelectionContainerList($ProjectPID, $TestGroupPID, $TestPID, $TestConditionList, $AllPatternCount, false);
		
		// Initialize $AllSelectionListOfList
		$AllSelectionListOfList = InitializeAllSelectionListOfList($AllPatternCount, $TestConditionSelectionContainerList);
		
		// Initialize TestPatternList
		$TestPatternList = InitializeTestPatternList($ProjectPID, $TestGroupPID, $TestPID);
		
		print "<pre>";
		$showDetails = true;	
		
		$sourcecode = "";
		
		print "Test Count: " . count($AllSelectionListOfList) . "\n";
		
		for($RowIndex = 0 ; $RowIndex < count($AllSelectionListOfList); $RowIndex++) {
			$AllSelectionList = $AllSelectionListOfList[$RowIndex];
			
			$testNumber = $RowIndex + 1;
			$function_name = "test" . $testNumber;
			
			$this_function_comment = "Test Group: " . $TestGroup->name . " Test No: " . $testNumber . " Function Name: " . $function_name;
			$function_comment = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION-COMMENT", $TestGroup);
			$function_comment = preg_replace("/__COMMENT__/i", $this_function_comment, $function_comment);
			
			print $this_function_comment . "\n";
			
			$initialize_input_line_values = "";
			$initialize_input_line_comment = "";
			
			for($ColumnIndex = 0 ; $ColumnIndex < count($AllSelectionList) ; $ColumnIndex++) {
				$thisSelectionObj = $AllSelectionList[$ColumnIndex];
				$Selection = $thisSelectionObj->Selection;
				
				// Input Value
				if ($initialize_input_line_values != "") {
					$initialize_input_line_values .= ", ";
				}
				$initialize_input_line_values .= GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $Selection);
				
				// Get Title
				$Title = "";
				$Description = "";
				if ($ColumnIndex >= 0 && $ColumnIndex < count($TestConditionList)) {
					$Title = $TestConditionList[$ColumnIndex]->Title;
					$Description = $TestConditionList[$ColumnIndex]->Description;
				} else {
					die("Internal Error. Column size is not matched with Test Selection and Test Condition list.");
				}
				
				$thisComment = "Column Index: " . $ColumnIndex . " Name:" . $Title . " Value:" . $Selection;
				$this_initialize_input_line_comment = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION-INITIALIZE-INPUT-COMMENT", $TestGroup);
				$this_initialize_input_line_comment = preg_replace("/__COMMENT__/i", $thisComment, $this_initialize_input_line_comment);
				$initialize_input_line_comment .= $this_initialize_input_line_comment;
				
				$DescriptionLines = preg_split("/\r?\n/", $Description);
				if(count($DescriptionLines) > 0) {
					$this_initialize_input_line_comment = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION-INITIALIZE-INPUT-COMMENT", $TestGroup);
					$this_initialize_input_line_comment = preg_replace("/__COMMENT__/i", "Description:", $this_initialize_input_line_comment);
					$initialize_input_line_comment .= $this_initialize_input_line_comment;
					for($i = 0 ; $i < count($DescriptionLines) ; $i++) {
						$this_initialize_input_line_comment = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION-INITIALIZE-INPUT-COMMENT", $TestGroup);
						$this_initialize_input_line_comment = preg_replace("/__COMMENT__/i", $DescriptionLines[$i], $this_initialize_input_line_comment);
						$initialize_input_line_comment .= $this_initialize_input_line_comment;
					}
				}
				print "  -> Selection[" . $ColumnIndex . "]: " . $Selection . "  (Condition:" . $Title . ")\n";
			}
			
			$initialize_input_line = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION-INITIALIZE-INPUT", $TestGroup);
			$initialize_input_line = preg_replace("/__INPUT__/i", $initialize_input_line_values, $initialize_input_line);
			
			// Get Expected Result
			$ExpectedResult = "";
			$correspondingTestPattern = GetCorrespondingTestPattern($TestPatternList, $AllSelectionList);
			if ($correspondingTestPattern != NULL) {
				$ExpectedResult = $correspondingTestPattern->ExpectedResult;
			}
			$initialize_expected_line = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION-INITIALIZE-EXPECTED-RESULT", $TestGroup);
			$initialize_expected_line = preg_replace("/__EXPECTED_RESULT__/i", GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $ExpectedResult), $initialize_expected_line);
			
			$functioncode = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-FUNCTION", $TestGroup);
			$functioncode = preg_replace("/__FUNCTION_NAME__/i", $function_name, $functioncode);
			$functioncode = preg_replace("/__FUNCTION_COMMENT__[ \t]*(\r?\n)?/i", $function_comment, $functioncode);
			$functioncode = preg_replace("/__INITIALIZE_INPUT__[ \t]*(\r?\n)?/i", $initialize_input_line, $functioncode);
			$functioncode = preg_replace("/__INITIALIZE_INPUT_COMMENT__[ \t]*(\r?\n)?/i", $initialize_input_line_comment, $functioncode);
			$functioncode = preg_replace("/__INITIALIZE_EXPECTED_RESULT__[ \t]*(\r?\n)?/i", $initialize_expected_line, $functioncode);
			$functioncode = preg_replace("/__EDITABLE_AREA_NUMBER__/i", ($RowIndex + $UNIT_TEST_USER_AREA_START_NUM), $functioncode);
			for($editableAreaNumberIndex = 0 ; $editableAreaNumberIndex < 10 ; $editableAreaNumberIndex++) {
				$functioncode = preg_replace("/__EDITABLE_AREA_NUMBER" . $editableAreaNumberIndex . "__/i", ($RowIndex + $UNIT_TEST_USER_AREA_START_NUM + $editableAreaNumberIndex), $functioncode);
			}
			
			$sourcecode .= $functioncode;
		}
		
		$ReplaceParameterList = array(
				array("KEY"=>"__CLASS_NAME__", "VALUE"=>$classname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__AUTOMATED_CODE_COMES_HERE__", "VALUE"=>$sourcecode, "TRIMLASTRETURN"=>true)
			);
		$source_template = GetMtoolUnitTestTemplateFile(NULL, $project, $ProjectSourceOutput, "TEST-CLASS", $TestGroup);
		$result = UpdateAutomatedSource($project, $ProjectSourceOutput, $filename, $UnitTestWorkingDir, $ReplaceParameterList, $source_template, $OUTPUT_TO_TEMP_FOLDER, false);
		if ($result->Success) {
			// OK
		} else {
			print " -> Error! Failed to update\n";
		}
		$showDetails = false;
		print "</pre>";
	}
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
