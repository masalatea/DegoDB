<?php
require '/srv/legacy/composer/vendor/autoload.php';

$base_dir = __DIR__ . "/";
$MAX_INCLUDE_CONFIG_TRY = 10;
for($try_index = 0 ; $try_index <= $MAX_INCLUDE_CONFIG_TRY ; $try_index++) {
	$site_configfile = $base_dir . "config.php";
	if (is_file($site_configfile)) {
		include($site_configfile);
		break;
	}
	$base_dir .= "../";
}
include_once("/srv/legacy/www/mtool_lib/lib_commonheader.php");
include_once("/srv/legacy/www/mtool_lib/dbclasses/autoload_mtool.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_db.php");
include_once("/srv/legacy/www/mtool_lib/lib_path_on_top.php");
?>
<!DOCTYPE HTML>
<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
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
<link rel="stylesheet" href="/js/themes/smoothness/jquery-ui.min.css" />
<link rel="stylesheet" href="/css/radiogroup.css" />
<link rel="stylesheet" href="/css/chat.css" />
<link rel="stylesheet" href="/css/penpicker.css" />
<link rel="stylesheet" href="/css/book.css" />
<link rel="stylesheet" href="/css/print.css" rel="stylesheet" media="print"/ >
<link rel="stylesheet" href="/js/jquery.simple-color-picker.css" />
<link rel="stylesheet" href="/js/sidr/stylesheets/jquery.sidr.dark.css" />
<script src="/js/jquery-2.2.4.min.js"></script>
<script src="/js/jquery-ui.min.js"></script>
<script src="/js/jquery.activity-indicator-2013-1127.js"></script>
<script src="/js/jquery.bottom-1.0.js"></script>
<script src="/js/jquery.touchwipe.1.1.1.js"></script>
<script src="/js/jquery.autosize.2.0.0.js"></script>
<script src="/js/jquery.simple-color-picker.js"></script>
<script src="/js/jquery.upload-1.0.2.js"></script>
<script src="/js/jquery.autosize.1.17.2.js"></script>
<script src="/js/jquery.titlealert.js"></script>
<script src="/js/sidr/jquery.sidr.min.js"></script>
<script src="/js/lightbox/js/lightbox-2.8.2.js"></script>
<script src="/js/socket.io.js"></script>
<script src="/js/frame_for_chat.js"></script>
<script src="/js/network-2016-0416.js"></script>
<script src="/js/rgbcolor.js"></script>
<script src="/js/scanlineseedfill.js"></script>
<script src="/js/background.js"></script>
<script src="/js/initializedialog-2016-0406.js"></script>
<script src="/js/paint.js"></script>
<script src="/js/pointer.js"></script>
<script src="/js/penpicker.js"></script>
<script src="/js/colorpicker.js"></script>
<script src="/js/setup.js"></script>
<script src="/js/chat-2016-0406.js"></script>
<script src="/js/savetolocalfile.js"></script>
<script src="/js/text.js"></script>
<script src="/js/print.js"></script>
<script src="/js/util.js"></script>
<script src="/js/title.js"></script>
</head>

<body>

<?php
if ($matsuesoft_login_token_id == "") {
	?>
	<h3><?php print getres("NOTICE_PLEAE_LOGIN_BEFORE_USE"); ?></h3>
	<?php
} else {
	?>
	<?php
	if ($matsuesoft_login_user_email_verified == false) {
		?>
		<h3><?php print getres("NOTICE_PLEAE_AUTHENTICATE_EMAIL_BEFORE_USE"); ?></h3>
		<?php
		
		include_once("/srv/legacy/www/mtool_lib/email_verification_include.php");
		
		show_message_about_email_verification(true);
		
	} else {
		
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
?>
	<?php
			}
        	?>
		
		<?php
	}
	?>
	
	<?php
} // ここより上の処理にはLoginが必要
?>

</body>
</html>
