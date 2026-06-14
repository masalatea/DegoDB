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

InitializeOutputShortenedStringWithExpansion();

$ProjectPID = trim(GetParam("ProjectPID"));
$TestGroupPID = trim(GetParam("TestGroupPID"));
$TestPID = trim(GetParam("TestPID"));

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
	
	printPathOnTopForTest("Test Condition List", $ProjectPID, $TestGroupPID, $TestPID, "");
	
	$DATestCondition = new TestConditionDBAccess();
	$TestConditionList = $DATestCondition->GetTestConditionList($ProjectPID, $TestGroupPID, $TestPID);
	
	if (count($TestConditionList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th></th>
			  <th>Title</th>
			  <th>Description</th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		for($i = 0 ; $i < count($TestConditionList); $i++) {
			$TestCondition = $TestConditionList[$i];
			?>
			<tr>
			  <td><?php print ($i + 1); ?></td>
			  <td><?php print htmlspecialchars($TestCondition->Title); ?></td>
			  <td><?php OutputShortenedStringWithExpansionWithBR(trim($TestCondition->Description), 300); ?></td>
			  <td><a href="test_condition_edit.php?ProjectPID=<?php print urlencode($TestCondition->ProjectPID); ?>&TestGroupPID=<?php print urlencode($TestCondition->TestGroupPID); ?>&TestPID=<?php print urlencode($TestCondition->TestPID); ?>&TestConditionPID=<?php print urlencode($TestCondition->PID); ?>&<?php print makeRandStr(8); ?>">Edit Test Condition</a></td>
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
    <p align="right"><a href="test_condition_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&TestGroupPID=<?php print urlencode($TestGroupPID); ?>&TestPID=<?php print urlencode($TestPID); ?>&<?php print makeRandStr(8); ?>">Add New Test Condition</a></p>
    
    
    <br>
    <br>
    <br>
    <p><a href="tests.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to View Test(s)</a></p>
    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/test/footer_back_link_include.php");
	print_footer_back_link($ProjectPID);
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
