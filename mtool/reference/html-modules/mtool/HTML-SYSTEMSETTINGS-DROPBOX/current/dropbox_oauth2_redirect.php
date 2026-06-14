<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_EMAIL_VERIFY_AFTER_LOGIN = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title>Uploader from DropBox to Server</title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_uploader.php");

$authorizationCode = trim(GetParam("code"));
$CSRFtoken = trim(GetParam("state"));

// print "<p>Code: " . $authorizationCode . "</p>";

$DADropboxOauth2StatusHash = new DropboxOauth2StatusHashDBAccess();
$newHash = $DADropboxOauth2StatusHash->GetByHashKey($CSRFtoken);
if ($newHash) {
	$DropboxSettingPID = $newHash->TargetDropboxSettingPID;
	
	$DADropboxSetting = new DropboxSettingDBAccess();
	$DropboxSetting = $DADropboxSetting->GetDropboxSetting($DropboxSettingPID, $SettingGroupPID);
	if ($DropboxSetting) {
		// Setting Exist
		$bearerToken = GetBearerTokenByAuthorizationCode($authorizationCode, $DropboxSetting->DropboxAppKey, $DropboxSetting->DropboxAppSecret, $DropboxSetting->Oauth2RedirectUrl);
		if ($bearerToken) {
			// print "<p>Bearer Token: " . $bearerToken . "</p>";
			
			$DropboxSetting->AccessToken = $bearerToken;
			if ($DADropboxSetting->UpdateAccessToken($DropboxSetting)) {
				?>
                <p>Successfully updated Token to Dropbox Setting</p>
                <?php
			} else {
				die("Failed to update Access Token.");
			}
		}
	}
	
} else {
	?>
    <p>Error! CSRF Token is not Matched</p>
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
