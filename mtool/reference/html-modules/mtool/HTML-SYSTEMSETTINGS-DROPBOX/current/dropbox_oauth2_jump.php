<?php
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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_uploader.php");

$DropboxSettingPID = trim(GetParam("DropboxSettingPID"));

if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
}

$DADropboxSetting = new DropboxSettingDBAccess();
$DropboxSetting = $DADropboxSetting->GetDropboxSetting($DropboxSettingPID);
if ($DropboxSetting) {
	// Setting Exist
	
	$newHash = new DropboxOauth2StatusHashData();
	$newHash->HashKey = time() . makeRandStr(30);
	$newHash->TargetDropboxSettingPID = $DropboxSettingPID;
	
	$DADropboxOauth2StatusHash = new DropboxOauth2StatusHashDBAccess();
	if ($DADropboxOauth2StatusHash->InsertDropboxOauth2StatusHash($newHash)) {
		
		$jumpUrl = "https://www.dropbox.com/1/oauth2/authorize?client_id=" . urlencode($DropboxSetting->DropboxAppKey) . "&response_type=code&redirect_uri=" . urlencode($DropboxSetting->Oauth2RedirectUrl) . "&state=" . urlencode($newHash->HashKey);
		header("Location: " . $jumpUrl);
		
	} else {
		die("Internal Error while storing Hash.");
	}
	
} else {
	die("Setting is not exist. Aborted.");
}

?>