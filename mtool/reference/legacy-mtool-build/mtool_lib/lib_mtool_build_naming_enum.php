<?PHP

function GetEnumName($classname, $fieldname)
{
	return $classname . $fieldname . "Enum";
}

function GetStringFromEnumFunctionNameIncludeClassName($classname, $fieldname)
{
	return CreateDataClassName($classname) . "." . GetStringFromEnumFunctionName($classname, $fieldname);
}
function GetStringFromEnumFunctionName($classname, $fieldname)
{
	return "GetStringFromEnumFor" . GetEnumName($classname, $fieldname);
}
function GetEnumFromStringFunctionNameIncludeClassName($classname, $fieldname)
{
	return CreateDataClassName($classname) . "." . GetEnumFromStringFunctionName($classname, $fieldname);
}
function GetEnumFromStringFunctionName($classname, $fieldname)
{
	return "GetEnumFromStringFor" . GetEnumName($classname, $fieldname);
}

function SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $template)
{
	return SetEnumTranslateFrunctionSub($BuildToken, $project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $template, TRANSLATE_ENUM_FUNC_TYPE::$ENUM_TO_STRING);
}
function SetEnumTranslateFrunctionFromStringToEnum($BuildToken, $project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $template)
{
	return SetEnumTranslateFrunctionSub($BuildToken, $project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $template, TRANSLATE_ENUM_FUNC_TYPE::$STRING_TO_ENUM);
}
function SetEnumTranslateFrunctionSub($BuildToken, $project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $template, $translateType)
{
	$func_prefix = "";
	$func_suffix = "";
	if ($targetTableName != "" && $targetTableColumnName != "") {
		$dbdatabype = "";
		$nullable = false;
		$datatypeInProgLang = GetDataTypeInProgLangBasedOnSQLSelect($project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $dbdatabype, $nullable);
		if (CheckIfEnumDataType($datatypeInProgLang)) {
			$datatypeInProgLang = GetEnumName($targetTableName, $targetTableColumnName);
			
			$funcname = "";
			switch($translateType)
			{
				case TRANSLATE_ENUM_FUNC_TYPE::$ENUM_TO_STRING:
					$funcname = GetStringFromEnumFunctionNameIncludeClassName($targetTableName, $targetTableColumnName);
					break;
				case TRANSLATE_ENUM_FUNC_TYPE::$STRING_TO_ENUM:
					$funcname = GetEnumFromStringFunctionNameIncludeClassName($targetTableName, $targetTableColumnName);
					break;
			}
			$func_prefix = $funcname . "(";
			$func_suffix = ")";
			
			$template = preg_replace("/__DATA_TYPE__/i", "string", $template);
		} else {
			
			$check_default_value_begin = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE-BY-CONSIDERING-DEFAULT-BEGIN");
			$check_default_value_end = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE-BY-CONSIDERING-DEFAULT-END");
			$nullable_source = "false";
			if ($nullable) {
				$nullable_source = "true";
			}
			$return_default_value_based_on_data_type_source = "false";
			if (CheckIfDefaultValueForThisLangAndDataType($project, $ProjectSourceOutput->ProgramLanguage, $dbdatabype)) {
				$return_default_value_based_on_data_type_source = "true";
			}
			$template = preg_replace("/__CHECK_DEFAULT_VALUE_BEGIN__/i", $check_default_value_begin, $template);
			$template = preg_replace("/__CHECK_DEFAULT_VALUE_END__/i", $check_default_value_end, $template);
			$template = preg_replace("/__NULLABLE__/i", $nullable_source, $template);
			$template = preg_replace("/__RETURN_DEFAULT_VALUE_BASED_ON_DATA_TYPE__/i", $return_default_value_based_on_data_type_source, $template);
			
			$template = preg_replace("/__DATA_TYPE__/i", $datatypeInProgLang, $template);
		}
	}
	$template = preg_replace("/__ENUM_TRANSLATION_FUNCTION_PREFIX__/i", $func_prefix, $template);
	$template = preg_replace("/__ENUM_TRANSLATION_FUNCTION_SUFFIX__/i", $func_suffix, $template);
	
	return $template;
}
class TRANSLATE_ENUM_FUNC_TYPE
{
	static $ENUM_TO_STRING = "EnumToString";
	static $STRING_TO_ENUM = "StringToEnum";
}
function CheckIfEnumDataType($datatype)
{
	return (strtoupper($datatype) == strtoupper("enum"));
}

function GetProgramLangBasedOnEnum($ProjectSourceOutput, $datatype, $classname, $fieldname)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			if (CheckIfEnumDataType($datatype)) {
				return GetEnumName($classname, $fieldname);
			}
			break;
	}
	return $datatype;
}

?>
