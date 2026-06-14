<?php

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

include_once("default_setting_lib.php");

$TemplateType = GetParam("TemplateType");
$TemplateTargetType = GetParam("TemplateTargetType");
$ProgramLanguage = trim(GetParam("ProgramLanguage"));
$filenameonly = trim(GetParam("File"));
$ClassType = GetParam("ClassType");

$target_dir = GetMtoolSettingDirForView($TemplateType, $TemplateTargetType, $ProgramLanguage, false, $ClassType);

if (is_dir($target_dir)) {
	if (preg_match("/\//", $File)) {
		// include "/" in Filename. Something Strange. Security Error.
		?>
        <p>Something Strange. Aborted</p>
        <?php
	} else {
		$fullfilepath = pathCombine($target_dir, $filenameonly);
		
		if ( is_file($fullfilepath) ) {
			$file_length = filesize($fullfilepath);
			header("Content-Disposition: attachment; filename=$filenameonly");
			header("Content-Length:$file_length");
			header("Content-Type: application/octet-stream");
			header("Content-Transfer-Encoding: binary");
			readfile ($fullfilepath);
			
			exit();
		}
	}
}

?>