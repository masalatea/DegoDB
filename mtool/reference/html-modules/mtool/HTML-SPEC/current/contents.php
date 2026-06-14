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
<title><?php print getres("TITLE_CONTENT_LIST"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
$SpecPID = trim(GetParam("SpecPID"));

$filterSpecContentPID = trim(GetParam("filterSpecContentPID"));

if (is_numeric($filterSpecContentPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific Spec Content</i></font></h3>
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
	printPathOnTopForSpec("Content List", $ProjectPID, $SpecPID, "");
	
	$DASpecContent = new SpecContentDBAccess();
	$SpecContentList = $DASpecContent->GetSpecContentList($ProjectPID, $SpecPID);
	
	if (count($SpecContentList) > 0) {
		
		$forSort = false;
		include_once("contents_table_include.php");
		
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
    <p align="right"><a href="content_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&SpecPID=<?php print urlencode($SpecPID); ?>&<?php print makeRandStr(8); ?>">Add New Content</a></p>
    <p align="right"><a href="contents_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&SpecPID=<?php print urlencode($SpecPID); ?>&<?php print makeRandStr(8); ?>">Change Order of Content(s)</a></p>
    
	<p align="right"><?php PrintAddMinutesLinkForSpec($ProjectPID, $SpecPID); ?></p>
	<p align="right"><?php PrintSearchMinutesLinkForSpec($SpecProjectPID, $SpecPID); ?></p>

    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/spec/footer_back_link_include.php");
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
