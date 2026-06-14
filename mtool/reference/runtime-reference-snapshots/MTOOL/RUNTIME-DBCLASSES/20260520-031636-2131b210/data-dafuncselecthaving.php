<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dafuncselecthavingBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dafuncselecthaving.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dafuncselecthaving.php` and extend `dafuncselecthavingDataBase` for project-specific customizations.

    class dafuncselecthavingData extends dafuncselecthavingDataBase
    {
	public function GetRelationalOperatorSQL()
	{
		return GetRelationalOperatorSQLCommon($this->RelationalOperator);
	}
    }
}
function GetdafuncselecthavingRightParameterTypeCaption($parametertype)
{
	switch($parametertype)
	{
		case dafuncselecthavingRightParameterTypeEnum::$ARGUMENT:
			return "Argument";
		case dafuncselecthavingRightParameterTypeEnum::$FIXED:
			return "Fixed";
		case dafuncselecthavingRightParameterTypeEnum::$FIELD:
			return "Field";
	}
	return $parametertype;
}
function GetdafuncselecthavingRightParameterDataTypeCaption($datatype)
{
	switch($datatype)
	{
		case dafuncselecthavingRightParameterDataTypeEnum::$DEFAULT:
			return "String";
		case dafuncselecthavingRightParameterDataTypeEnum::$RAW;
			return "Raw";
	}
	return $datatype;
}


?>
