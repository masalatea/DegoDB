<?php
namespace Google\Cloud\Samples\Auth;
use Google\Cloud\Translate\TranslateClient;

require '/srv/legacy/composer/vendor/autoload.php';

$base_dir = __DIR__ . "/";
$MAX_INCLUDE_CONFIG_TRY = 10;
for($try_index = 0 ; $try_index <= $MAX_INCLUDE_CONFIG_TRY ; $try_index++) {
	$site_configfile = $base_dir . "config.php";
	if (is_file($site_configfile)) {
		include_once($site_configfile);
		break;
	}
	$base_dir .= "../";
}
include_once("/srv/legacy/www/mtool_lib/lib_commonheader.php");
include_once("/srv/legacy/www/mtool_lib/dbclasses/autoload_mtool.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_db.php");
include_once("/srv/legacy/www/mtool_lib/lib_path_on_top.php");

if ($matsuesoft_login_token_id == "") {
	?>
	<h3><?php print getres("NOTICE_PLEAE_LOGIN_BEFORE_USE"); ?></h3>
	<?php
} else {
	if ($matsuesoft_login_user_email_verified == false) {
		?>
		<h3><?php print getres("NOTICE_PLEAE_AUTHENTICATE_EMAIL_BEFORE_USE"); ?></h3>
		<?php
		
		include_once("/srv/legacy/www/mtool_lib/email_verification_include.php");
		
		show_message_about_email_verification(true);
		
	} else {
		$ProjectPID = trim(GetParam("ProjectPID"));
		if (CheckIfLoginUserIsUserAndOutputMessage($ProjectPID, $matsuesoft_login_token_id)) {
			// ここから上はTemplateと共通
			
			$SourceText = trim(GetParam("SourceText"));
			$SourceLang = trim(GetParam("SourceLang"));
			$TargetLang = trim(GetParam("TargetLang"));
			
			// error_log("SourceText: $SourceText");
			// error_log("SourceLang: $SourceLang");
			// error_log("TargetLang: $TargetLang");
			
			$translate = new TranslateClient([
				'keyFilePath' => "/srv/legacy/www/mtool_lib/google_api/Matsuesoft-5e75424573cb.json",
				'projectId' => "matsuesoft",
			]);
			
			# Translates some text into Russian
			$translation = $translate->translate($SourceText, [
				'source' => $SourceLang,
				'target' => $TargetLang
			]);
			
			// error_log($translation['text']);
			
			print json_encode(array(
				"TranslatedText"=>htmlspecialchars_decode($translation['text'], ENT_QUOTES),
				"_status"=>"OK",
				"Message"=>"Successfully called"
			));
			exit();
			
			// ここから下はTemplateと共通
		}
	}
}
print json_encode(array(
	"IsCompleted"=>false,
	"_status"=>"NG",
	"Message"=>"Something Wrong"
));

?>
