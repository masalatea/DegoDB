<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DaBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-Da.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-Da.php` and extend `DaDataBase` for project-specific customizations.

    class DaData extends DaDataBase
    {
	public function NormalizeIsAutoloadProperty()
	{
		if ($this->IsAutoload == "0" ||
		    $this->IsAutoload == "1") {
			// OK
		} else if ($this->IsAutoload == "") {
			$this->IsAutoload = "0";
		} else {
			$this->IsAutoload = "1";		// Default
		}
	}
	public function GetIsAutoloadBoolean()
	{
		if ($this->IsAutoload == "1") {
			return true;
		}
		return false;
	}
	public function GetIsAutoloadCaption()
	{
		if ($this->GetIsAutoloadBoolean()) {
			return "Yes";
		}
		return "No";
	}
    }
}

?>
