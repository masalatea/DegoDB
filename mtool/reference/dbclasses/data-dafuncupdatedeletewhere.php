<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dafuncupdatedeletewhereBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dafuncupdatedeletewhere.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dafuncupdatedeletewhere.php` and extend `dafuncupdatedeletewhereDataBase` for project-specific customizations.

    class dafuncupdatedeletewhereData extends dafuncupdatedeletewhereDataBase
    {
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == "fixed") {
			return $this->FixedParameter;
		}
		return "";
	}
	public function GetParameterDataTypeCaption()
	{
		return GetParameterDataTypeCaptionCommon($this->ParameterDataType);
	}
	public function GetRelationalOperatorCaption()
	{
		return GetRelationalOperatorCaptionCommon($this->RelationalOperator);
	}
	public function GetRelationalOperatorSQL()
	{
		return GetRelationalOperatorSQLCommon($this->RelationalOperator);
	}
    }
}

?>
