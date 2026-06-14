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
<title><?php print getres("TITLE_TEST_LIST"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

InitializeOutputShortenedStringWithExpansion();

$ProjectPID = trim(GetParam("ProjectPID"));
$TestGroupPID = trim(GetParam("TestGroupPID"));

$filterTestPID = trim(GetParam("filterTestPID"));

if (is_numeric($filterTestPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific Test</i></font></h3>
    <?php
}

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	
	printPathOnTopForTest("Test List", $ProjectPID, $TestGroupPID, "", "");
	
	$DATest_leftouterjoin_ProjectDBAccess = new Test_leftouterjoin_ProjectDBAccess();
	$TestList = $DATest_leftouterjoin_ProjectDBAccess->GetTestList($ProjectPID, $TestGroupPID);
	
	if (count($TestList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Test Name</th>
			  <th>Unit Test Target Class Name</th>
			  <th></th>
			  <th></th>
			  <th></th>
			  <th></th>
			  <th></th>
			  <th></th>
              <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($TestList); $i++) {
			$Test = $TestList[$i];
			
			// filter
			if (is_numeric($filterTestPID)) {
				if ($filterTestPID != $Test->PID) {
					continue;
				}
			}
			?>
			<tr>
			  <td><?php print htmlspecialchars($Test->name); ?></td>
			  <td><?php print htmlspecialchars($Test->UnitTestTargetClassName); ?></td>
			  <td><a href="test_conditions.php?ProjectPID=<?php print urlencode($Test->ProjectPID); ?>&TestGroupPID=<?php print urlencode($Test->TestGroupPID); ?>&TestPID=<?php print urlencode($Test->PID); ?>&<?php print makeRandStr(8); ?>">Edit Test Condition(s)</a></td>
			  <td><a href="test_pattern_and_results.php?ProjectPID=<?php print urlencode($Test->ProjectPID); ?>&TestGroupPID=<?php print urlencode($Test->TestGroupPID); ?>&TestPID=<?php print urlencode($Test->PID); ?>&<?php print makeRandStr(8); ?>">Edit Test Pattern(s) and Result(s)</a></td>
			  <td><a href="test_update_unit_test_source.php?ProjectPID=<?php print urlencode($Test->ProjectPID); ?>&TestGroupPID=<?php print urlencode($Test->TestGroupPID); ?>&TestPID=<?php print urlencode($Test->PID); ?>&<?php print makeRandStr(8); ?>">Create/Update Unit Test Source</a> / <a href="test_update_unit_test_source.php?ProjectPID=<?php print urlencode($Test->ProjectPID); ?>&TestGroupPID=<?php print urlencode($Test->TestGroupPID); ?>&TestPID=<?php print urlencode($Test->PID); ?>&OUTPUT_TO_TEMP_FOLDER=t&<?php print makeRandStr(8); ?>">Create/Update Unit Test Source to Temp Folder</a></td>
			  <td><a href="test_execute.php?ProjectPID=<?php print urlencode($Test->ProjectPID); ?>&TestGroupPID=<?php print urlencode($Test->TestGroupPID); ?>&TestPID=<?php print urlencode($Test->PID); ?>&<?php print makeRandStr(8); ?>">Execute Manual Test</a></td>
			  <td><a href="test_edit.php?ProjectPID=<?php print urlencode($Test->ProjectPID); ?>&TestGroupPID=<?php print urlencode($Test->TestGroupPID); ?>&TestPID=<?php print urlencode($Test->PID); ?>&<?php print makeRandStr(8); ?>">Edit Test Info</a></td>
              <td><?php PrintAddMinutesLinkForTest($ProjectPID, $TestGroupPID, $Test->PID); ?></td>
              <td><?php PrintSearchMinutesLinkForTest($ProjectPID, $TestGroupPID, $Test->PID); ?></td>
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
    <p align="right"><a href="test_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&TestGroupPID=<?php print urlencode($TestGroupPID); ?>&<?php print makeRandStr(8); ?>">Add New Test</a></p>
    
    <p align="right"><?php PrintAddMinutesLinkForTestGroup($ProjectPID, $TestGroupPID); ?></p>
    <p align="right"><?php PrintSearchMinutesLinkForTestGroup($ProjectPID, $TestGroupPID); ?></p>
    
    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/test/footer_back_link_include.php");
	print_footer_back_link($Test->ProjectPID);
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
