<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dafuncselectwhereBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dafuncselectwhere.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dafuncselectwhere.php` and extend `dafuncselectwhereDataBase` for project-specific customizations.

    class dafuncselectwhereData extends dafuncselectwhereDataBase
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
function GetdafuncselectwhereJoinTypeCaption($jointype)
{
	switch($jointype) {
		case "":
			return "Where";
		case dafuncselectwhereJoinTypeEnum::$INNER:
			return "Inner Join";
		case dafuncselectwhereJoinTypeEnum::$LEFT:
			return "Left Outer Join";
		case dafuncselectwhereJoinTypeEnum::$RIGHT:
			return "Right Outer Join";
		case dafuncselectwhereJoinTypeEnum::$HAVING:
			return "Having";
	}
	return $jointype;
}


?>
