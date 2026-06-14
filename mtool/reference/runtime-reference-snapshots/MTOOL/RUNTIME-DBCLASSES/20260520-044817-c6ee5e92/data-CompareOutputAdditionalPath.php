<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-CompareOutputAdditionalPathBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-CompareOutputAdditionalPath.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-CompareOutputAdditionalPath.php` and extend `CompareOutputAdditionalPathDataBase` for project-specific customizations.

    class CompareOutputAdditionalPathData extends CompareOutputAdditionalPathDataBase
    {
	public function GetPathAWithoutLastSlush()
	{
		return preg_replace("/\/$/", "", $this->PathA);
	}
	public function GetPathBWithoutLastSlush()
	{
		return preg_replace("/\/$/", "", $this->PathB);
	}
    }
}

?>
