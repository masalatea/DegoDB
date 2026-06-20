<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-HtmlTemplateParameterBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-HtmlTemplateParameter.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-HtmlTemplateParameter.php` and extend `HtmlTemplateParameterDataBase` for project-specific customizations.

    class HtmlTemplateParameterData extends HtmlTemplateParameterDataBase
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
		case HtmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
			return "Each HTML (for each Project)";
		case HtmlTemplateParameterTargetValueTypeEnum::$CODE:
			return "Code (common for all Project)";
		case HtmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
			return "Another Template";
	}
	return $targetvalue;
}

function GethtmlTemplateParameterDataTypeCaption($datatype)
{
	switch($datatype)
	{
		case HtmlTemplateParameterDataTypeEnum::$DEFAULT:
			return "String";
		case HtmlTemplateParameterDataTypeEnum::$DATACLASSNAME:
			return "Data Class";
		case HtmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME:
			return "DB Access Class";
	}
	return $datatype;
}


?>
