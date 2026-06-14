<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-CompareOutputBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-CompareOutput.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-CompareOutput.php` and extend `CompareOutputDataBase` for project-specific customizations.

    class CompareOutputData extends CompareOutputDataBase
    {
    }
}
function GetCompareOutputOutputFileTypeCaption($value)
{
	switch($value)
	{
		case CompareOutputOutputFileTypeEnum::$TEXT:
			return "Text";
		case CompareOutputOutputFileTypeEnum::$WINDOWSBATCH:
			return "Windows Batch";
		case CompareOutputOutputFileTypeEnum::$MACCOMMAND:
			return "Mac Command";
	}
	return $value;
}


?>
