<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DafuncselectwhereBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dafuncselectwhere.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dafuncselectwhere.php` and extend `DafuncselectwhereDataBase` for project-specific customizations.

    class DafuncselectwhereData extends DafuncselectwhereDataBase
    {
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == "fixed") {
			return $this->FixedParameter;
		}
		return "";
	}
	public function GetParameterDataTypeCaptionIfParameterTypeIsNotAnotherField()
	{
		if ($this->ParameterType != "anotherfield") {
			return GetParameterDataTypeCaptionCommon($this->ParameterDataType);
		}
		return "";
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
function GetDafuncselectwhereJoinTypeCaption($jointype)
{
	switch($jointype) {
		case "":
			return "Where";
		case DafuncselectwhereJoinTypeEnum::$INNER:
			return "Inner Join";
		case DafuncselectwhereJoinTypeEnum::$LEFT:
			return "Left Outer Join";
		case DafuncselectwhereJoinTypeEnum::$RIGHT:
			return "Right Outer Join";
		case DafuncselectwhereJoinTypeEnum::$HAVING:
			return "Having";
	}
	return $jointype;
}


?>
