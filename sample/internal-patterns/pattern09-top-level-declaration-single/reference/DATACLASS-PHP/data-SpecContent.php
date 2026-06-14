<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

$CONTENT_DEPTH_MAX = 7;
$OUTPUT_SECTION_NUMBER_START = 2;

require_once __DIR__ . '/base/data-SpecContentBase.php';

class SpecContentData extends SpecContentDataBase
{
	public function GetDepthCaption()
	{
		return GetDepthCaptionCommon($this->Depth);
	}
}
function GetDepthCaptionCommon($DepthValue)
{
	switch($DepthValue)
	{
		case "0":
			return "Undefined";
		case "1":
			return "Part";
		case "2":
			return "Chapter";
		case "3":
			return "Section";
		case "4":
			return "Sub Section";
		case "5":
			return "Sub Sub Section";
		case "6":
			return "Paragraph";
		case "7":
			return "Sub Paragraph";
	}
	return "Depth: " . $DepthValue;
}

?>