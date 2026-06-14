<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-daCustomProxyFuncBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-daCustomProxyFunc.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-daCustomProxyFunc.php` and extend `daCustomProxyFuncDataBase` for project-specific customizations.

    class daCustomProxyFuncData extends daCustomProxyFuncDataBase
    {
	public function ForList()
	{
		return ($this->IsList == 1);
	}
    }
}
function GetCustomProxyFuncAddIndentTypeEnumCaption($value)
{
	switch($value)
	{
		case daCustomProxyFuncAddIndentTypeEnum::$DEFAULT:
			return "Start Indent and End Indent";
		case daCustomProxyFuncAddIndentTypeEnum::$START:
			return "Start Indent";
		case daCustomProxyFuncAddIndentTypeEnum::$END:
			return "End Indent";
		case daCustomProxyFuncAddIndentTypeEnum::$CONTINUE:
			return "Continue Indent";
	}
	return $value;
}


?>
