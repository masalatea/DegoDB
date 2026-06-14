<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-dafuncupdatetargetfieldsBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-dafuncupdatetargetfields.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-dafuncupdatetargetfields.php` and extend `dafuncupdatetargetfieldsDataBase` for project-specific customizations.

    class dafuncupdatetargetfieldsData extends dafuncupdatetargetfieldsDataBase
    {
	public function GetFixedParameterCaptionIfParameterTypeIsFixed()
	{
		if ($this->ParameterType == dafuncupdatetargetfieldsParameterTypeEnum::$FIXED) {
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
		return ($this->ParameterDataType == dafuncupdatetargetfieldsParameterDataTypeEnum::$FILE);
	}
    }
}

?>
