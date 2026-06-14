<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-CompareOutputBase.php';

class CompareOutputData extends CompareOutputDataBase
{
}
function GetCompareOutputOutputFileTypeCaption($value)
{
	switch($value)
	{
		case CompareOutputOutputFileTypeEnum::$TEXT:
			return "Text";
		case CompareOutputOutputFileTypeEnum::$WINDOWSBATCH:
			return "Windows Batch";
		case CompareOutputOutputFileTypeEnum::$MACCOMMAND:
			return "Mac Command";
	}
	return $value;
}

?>