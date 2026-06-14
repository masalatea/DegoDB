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
<title><?php print getres("TITLE_OPENING_WHITEBOARD_FOR_ATTACHMENT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
<style type="text/css">
.test_red {
	color: #F00;
}
</style>
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

$WHITEBOARD_SITE = "mtool";

$ProjectPID = trim(GetParam("ProjectPID"));
$chattopicPID = trim(GetParam("chattopicPID"));
$chattopicAttachmentPID = trim(GetParam("chattopicAttachmentPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($chattopicPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Topic</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($chattopicAttachmentPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Attachment</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	printPathOnTopForChat("Opening Whiteboard...", $ProjectPID);
	
	$room = CreateRoomNameFromChatAttachmentPID($ProjectPID, $chattopicPID, $chattopicAttachmentPID);
	if ($room != "") {
		
		$jump_success = false;
		
		$access_token = create_whiteboard_token_for_chat($room, $matsuesoft_login_token_id);
		if ($access_token != ""){ 
			?>
			<SCRIPT type="text/JavaScript">
			location.href = 'http://whiteboard.dokotera.com/open_room.php?token=<?php print $access_token; ?>';
			</SCRIPT>
			<?php
			$jump_success = true;
		}
		if (!$jump_success) {
			print "Internal Error. You may reload this page and retry.";
		}
	} else {
		?>
        <h3><font color="red">Error! Corresponding topic is not found.</font></h3>
        <?php
	}
	?>
    <br>
    <br>
    <br>
    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/chat/footer_back_link_include.php");
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
