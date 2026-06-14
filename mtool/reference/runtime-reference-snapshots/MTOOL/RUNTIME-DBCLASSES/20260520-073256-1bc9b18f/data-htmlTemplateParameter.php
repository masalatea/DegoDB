<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-htmlTemplateParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-htmlTemplateParameter.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-htmlTemplateParameter.php` and extend `htmlTemplateParameterDataBase` for project-specific customizations.

    class htmlTemplateParameterData extends htmlTemplateParameterDataBase
    {
	public function GetTargetVariable()
	{
		$variable = '$' . trim($this->TargetVariableOrClassObject);
		if (trim($this->TargetPropertyOfClassObject) != "") {
			$variable .= "->" . trim($this->TargetPropertyOfClassObject);
		}
		return $variable;
	}
    }
}
function GethtmlTemplateParameterTargetValueTypeCaption($targetvalue)
{
	switch($targetvalue)
	{
		case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
			return "Each HTML (for each Project)";
		case htmlTemplateParameterTargetValueTypeEnum::$CODE:
			return "Code (common for all Project)";
		case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
			return "Another Template";
	}
	return $targetvalue;
}

function GethtmlTemplateParameterDataTypeCaption($datatype)
{
	switch($datatype)
	{
		case htmlTemplateParameterDataTypeEnum::$DEFAULT:
			return "String";
		case htmlTemplateParameterDataTypeEnum::$DATACLASSNAME:
			return "Data Class";
		case htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME:
			return "DB Access Class";
	}
	return $datatype;
}


?>
