<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-LanguageResourceBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-LanguageResource.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-LanguageResource.php` and extend `LanguageResourceDataBase` for project-specific customizations.

    class LanguageResourceData extends LanguageResourceDataBase
    {
	public function GetUWPTargetPropertyWithDot()
	{
		if (trim($this->UWPTargetProperty) != "") {
			if (!preg_match("/^\./", $this->UWPTargetProperty)) {
				// Add Dot for Property
				return "." . $this->UWPTargetProperty;
			}
		}
		return $this->UWPTargetProperty;
	}
    }
}

?>
