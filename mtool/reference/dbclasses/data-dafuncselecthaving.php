<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DafuncselecthavingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dafuncselecthaving.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dafuncselecthaving.php` and extend `DafuncselecthavingDataBase` for project-specific customizations.

    class DafuncselecthavingData extends DafuncselecthavingDataBase
    {
	public function GetRelationalOperatorSQL()
	{
		return GetRelationalOperatorSQLCommon($this->RelationalOperator);
	}
    }
}
function GetDafuncselecthavingRightParameterTypeCaption($parametertype)
{
	switch($parametertype)
	{
		case DafuncselecthavingRightParameterTypeEnum::$ARGUMENT:
			return "Argument";
		case DafuncselecthavingRightParameterTypeEnum::$FIXED:
			return "Fixed";
		case DafuncselecthavingRightParameterTypeEnum::$FIELD:
			return "Field";
	}
	return $parametertype;
}
function GetDafuncselecthavingRightParameterDataTypeCaption($datatype)
{
	switch($datatype)
	{
		case DafuncselecthavingRightParameterDataTypeEnum::$DEFAULT:
			return "String";
		case DafuncselecthavingRightParameterDataTypeEnum::$RAW;
			return "Raw";
	}
	return $datatype;
}


?>
