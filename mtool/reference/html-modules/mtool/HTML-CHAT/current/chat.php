<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NEED_LOGIN = true;
$MTOOL_NEED_EMAIL_VERIFY_AFTER_LOGIN = true;
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_NO_MENU = true;
$MTOOL_SHOW_TAWKTO = false;
$MTOOL_NO_BODY_SCRIPT = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<section>
  <div class="container-fluid">
	<div class="row">
	  <div class="col-lg-12 mx-auto">
<?php
$WHITEBOARD_SITE = "mtool";
include_once("/srv/legacy/www/nodejs_lib/lib_create_whiteboard_token.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$chattopicPID = trim(GetParam("chattopicPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	exit();
}
if (!is_numeric($chattopicPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Topic</font></H3>
    <?php
	exit();
}
$topicCaption = "";
if ($NoError) {
	$DAchattopic = new chattopicDBAccess();
	$chattopic = $DAchattopic->Getchattopic($chattopicPID, $ProjectPID);
	if ($chattopic && $chattopic != NULL) {
		$topicCaption = $chattopic->name;
	}
}
?>
<title><?php print htmlspecialchars($topicCaption); ?> - <?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/css/whiteboard_radiogroup.css" />
<link rel="stylesheet" href="/css/whiteboard_chat_for_dev.css" />
<link rel="stylesheet" href="/css/whiteboard_penpicker.css" />
<link rel="stylesheet" href="/css/whiteboard_book.css" />
<link rel="stylesheet" href="/css/whiteboard_print.css" rel="stylesheet" media="print"/ >
<link rel="stylesheet" href="/js/jquery.simple-color-picker.css" />
<script src="/js/jquery.simple-color-picker.js"></script>
<script src="/js/jquery.upload-1.0.2.js"></script>
<script src="/js/jquery.autosize.1.17.2.js"></script>
<script src="/js/jquery.titlealert.js"></script>
<script src="/js/lightbox/js/lightbox-2.8.2.js"></script>
<script src="/js/socket.io.js"></script>
<script src="/js/whiteboard_frame_for_chat.js"></script>
<script src="/js/whiteboard_network-2018-0707.js"></script>
<script src="/js/rgbcolor.js"></script>
<script src="/js/scanlineseedfill.js"></script>
<script src="/js/whiteboard_background_simple.js"></script>
<script src="/js/whiteboard_initializedialog-2017-1014.js"></script>
<script src="/js/whiteboard_paint.js"></script>
<script src="/js/whiteboard_pointer.js"></script>
<script src="/js/whiteboard_penpicker.js"></script>
<script src="/js/whiteboard_colorpicker.js"></script>
<script src="/js/whiteboard_setup.js"></script>
<script src="/js/whiteboard_chat_simple-2016-0406.js"></script>
<script src="/js/whiteboard_savetolocalfile.js"></script>
<script src="/js/whiteboard_text.js"></script>
<script src="/js/whiteboard_print.js"></script>
<script src="/js/title.js"></script>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
<?php
$ProjectPID = trim(GetParam("ProjectPID"));
if (!CheckIfMtoolProjectOwnerOrUser($ProjectPID, $matsuesoft_login_token_id)) {
	?>
	<H3><font color="red">Security Error</font></H3>
	<p>This page is Project User Only. Please ask administrator to get access.</p>
	<?php
} else if (!CheckSecurityForEachPageForCurrentPage($ProjectPID, $matsuesoft_login_token_id)) {
	?>
	<H3><font color="red">Security Error</font></H3>
	<p>You don't have permission to access to this page. Plase ask Project Owner or Administrator to have access permission.</p>
	<?php
} else {
	
	$room = "";
	$userid = "";
	$username = "";
	$useridtype = "";
	$student_token_for_teacher = "";
	$student_id = "";
	$student_name = "";
	$is_student = false;
	$is_teacher = false;
	$token_create_time = 0;
	
	if ($NoError) {
		$room = CreateRoomNameFromChatTopicPID($ProjectPID, $chattopicPID);
		if ($room != "") {
			
			$token = create_whiteboard_token_for_chat($room, $matsuesoft_login_token_id);
			if ($token != ""){ 
				$token_json = get_token_json($token);
				if ($token_json != NULL) {
					$userid   = $token_json->userid;
					$username = $token_json->username;
					$useridtype = $token_json->useridtype;
					$student_token_for_teacher = $token_json->student_token_for_teacher;
					$room = $token_json->room;
					$token_create_time = $token_json->create_time;
					
					include_once("include_mainpage.php");
					
				} else {
					?>
			  <h3><font color="red">Error! Failed to get access token. Please ask administrator if this continues.</font></h3>
			  <?php
				}
				
			} else {
				?>
		  <h3><font color="red">Error! Failed to create access token. Please ask administrator if this continues.</font></h3>
		  <?php
			}
		} else {
			?>
		  <h3><font color="red">Error! Corresponding topic is not found.</font></h3>
		  <?php
		}
	}
}
?>
	  </div>
	</div>
  </div>
</section>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
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
