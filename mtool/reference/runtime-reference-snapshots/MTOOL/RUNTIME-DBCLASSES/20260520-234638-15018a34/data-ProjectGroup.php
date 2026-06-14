<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ProjectGroupBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ProjectGroup.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ProjectGroup.php` and extend `ProjectGroupDataBase` for project-specific customizations.

    class ProjectGroupData extends ProjectGroupDataBase
    {
    }
}
function GetProjectGroupProjectGroupTypeCaption($value)
{
	switch($value)
	{
		case ProjectGroupProjectGroupTypeEnum::$SANDBOX:
			return "Sandbox";
		case ProjectGroupProjectGroupTypeEnum::$SHAREDSERVER:
			return "Shared Server";
		case ProjectGroupProjectGroupTypeEnum::$VPS:
			return "VPS";
		default:
			die("Unknown Value: " . $value);
	}
	return $value;
}

function GetProjectGroupProjectGroupTypeFromTemplate($group_type_value_for_template)
{
	$group_type_value = NULL;
	switch($group_type_value_for_template)
	{
		case ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX:
			$group_type_value = ProjectGroupProjectGroupTypeEnum::$SANDBOX;
			break;
		case ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER:
			$group_type_value = ProjectGroupProjectGroupTypeEnum::$SHAREDSERVER;
			break;
		case ProjectGroupTemplateProjectGroupTypeEnum::$VPS:
			$group_type_value = ProjectGroupProjectGroupTypeEnum::$VPS;
			break;
		default:
			die("Unknown Value: " . $group_type_value_for_template);
	}
	return $group_type_value;
}


?>
