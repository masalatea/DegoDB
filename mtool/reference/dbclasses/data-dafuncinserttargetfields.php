<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DafuncinserttargetfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dafuncinserttargetfields.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dafuncinserttargetfields.php` and extend `DafuncinserttargetfieldsDataBase` for project-specific customizations.

    class DafuncinserttargetfieldsData extends DafuncinserttargetfieldsDataBase
    {
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == DafuncinserttargetfieldsParameterTypeEnum::$FIXED) {
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
		return ($this->ParameterDataType == DafuncinserttargetfieldsParameterDataTypeEnum::$FILE);
	}
    }
}

?>
