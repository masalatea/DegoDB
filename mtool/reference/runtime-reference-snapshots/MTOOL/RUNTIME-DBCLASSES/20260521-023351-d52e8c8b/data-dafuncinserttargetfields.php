<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dafuncinserttargetfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dafuncinserttargetfields.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dafuncinserttargetfields.php` and extend `dafuncinserttargetfieldsDataBase` for project-specific customizations.

    class dafuncinserttargetfieldsData extends dafuncinserttargetfieldsDataBase
    {
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == dafuncinserttargetfieldsParameterTypeEnum::$FIXED) {
			return $this->FixedParameter;
		}
		return "";
	}
	public function GetParameterDataTypeCaption()
	{
		return GetParameterDataTypeCaptionCommon($this->ParameterDataType);
	}
	public function IsFileDataType()
	{
		return ($this->ParameterDataType == dafuncinserttargetfieldsParameterDataTypeEnum::$FILE);
	}
    }
}

?>
