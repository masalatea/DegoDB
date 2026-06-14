<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-minutes_and_RelatedTablesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-minutes_and_RelatedTables.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-minutes_and_RelatedTables.php` and extend `minutes_and_RelatedTablesDataBase` for project-specific customizations.

    class minutes_and_RelatedTablesData extends minutes_and_RelatedTablesDataBase
    {
	function GetRequirementTitle()
	{
		if (trim($this->ReqSummary) != "") {
			return trim($this->ReqSummary);
		}
		return trim($this->ReqUserRequest);
	}
	
	function GetdafuncFunctionName()
	{
		return GetFunctionNameFromFunctionActionType($this->dafuncname, $this->dafuncActionType);
	}
    }
}

?>
