<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DaCustomProxyFuncBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DaCustomProxyFunc.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DaCustomProxyFunc.php` and extend `DaCustomProxyFuncDataBase` for project-specific customizations.

    class DaCustomProxyFuncData extends DaCustomProxyFuncDataBase
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
		case DaCustomProxyFuncAddIndentTypeEnum::$DEFAULT:
			return "Start Indent and End Indent";
		case DaCustomProxyFuncAddIndentTypeEnum::$START:
			return "Start Indent";
		case DaCustomProxyFuncAddIndentTypeEnum::$END:
			return "End Indent";
		case DaCustomProxyFuncAddIndentTypeEnum::$CONTINUE:
			return "Continue Indent";
	}
	return $value;
}


?>
