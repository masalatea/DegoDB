<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DafuncupdatetargetfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Dafuncupdatetargetfields.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Dafuncupdatetargetfields.php` and extend `DafuncupdatetargetfieldsDataBase` for project-specific customizations.

    class DafuncupdatetargetfieldsData extends DafuncupdatetargetfieldsDataBase
    {
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == DafuncupdatetargetfieldsParameterTypeEnum::$FIXED) {
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
		return ($this->ParameterDataType == DafuncupdatetargetfieldsParameterDataTypeEnum::$FILE);
	}
    }
}

?>
