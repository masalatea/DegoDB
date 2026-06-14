<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-ProjectGroupTemplateBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-ProjectGroupTemplate.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-ProjectGroupTemplate.php` and extend `ProjectGroupTemplateDataBase` for project-specific customizations.

    class ProjectGroupTemplateData extends ProjectGroupTemplateDataBase
    {
    }
}
function GetProjectGroupTemplateProjectGroupTypeCaption($value)
{
	switch($value)
	{
		case ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX:
			return "Sandbox";
		case ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER:
			return "Shared Server";
		case ProjectGroupTemplateProjectGroupTypeEnum::$VPS:
			return "VPS";
		default:
			die("Unknown Value: " . $value);
	}
	return $value;
}


?>
