<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-daBase.php';

class daData extends daDataBase
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
?>