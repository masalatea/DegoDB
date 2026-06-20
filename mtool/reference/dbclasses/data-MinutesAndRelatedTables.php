<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-MinutesAndRelatedTablesBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-MinutesAndRelatedTables.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-MinutesAndRelatedTables.php` and extend `MinutesAndRelatedTablesDataBase` for project-specific customizations.

    class MinutesAndRelatedTablesData extends MinutesAndRelatedTablesDataBase
    {
	function GetRequirementTitle()
	{
		if (trim($this->ReqSummary) != "") {
			return trim($this->ReqSummary);
		}
		return trim($this->ReqUserRequest);
	}
	
	function GetDafuncFunctionName()
	{
		return GetFunctionNameFromFunctionActionType($this->Dafuncname, $this->DafuncActionType);
	}
    }
}

?>
