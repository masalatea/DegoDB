<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build_template.php");

function GetMtoolSettingDirForView($TemplateType, $TemplateTargetType, $ProgramLanguage, $DoPrint, $ClassType)
{
	global $DEFAULT_MTOOL_SETTING_DIR;
	
	$target_dir = $DEFAULT_MTOOL_SETTING_DIR;
	switch($TemplateType) {
		case "common":
			$target_dir = pathCombine($target_dir, GetCommonTemplateDir());
			
			if ($DoPrint) {
				?>
				<h4>Common Setting</h4>
				<?php
			}
			break;
			
		case "source":
			$target_dir = pathCombine($target_dir, GetDefaultTemplateDir($TemplateTargetType, $ClassType));
			
			if ($DoPrint) {
				?>
				<h4>Template: Source for <?php print GethtmlTemplateTargetTypeCaption($TemplateTargetType); ?>
				<?php
			}
			if (trim($ProgramLanguage) != "") {
				$target_dir = pathCombine($target_dir, $ProgramLanguage);
				
				if ($DoPrint) {
					print " (" . GethtmlTemplateDataLanguageCaption($ProgramLanguage) . ")";
				}
			}
			if ($DoPrint) {
				?>
				</h4>
				<?php
			}
			break;
	}
	return $target_dir;
}

?>