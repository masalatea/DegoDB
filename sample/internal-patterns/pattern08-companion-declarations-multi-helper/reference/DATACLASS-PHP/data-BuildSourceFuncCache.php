<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-BuildSourceFuncCacheBase.php';

class BuildSourceFuncCacheData extends BuildSourceFuncCacheDataBase
{
}
function GetAllBuildSourceFuncCacheReleaseTargetTypeEnumList()
{
	return array(
		BuildSourceFuncCacheReleaseTargetTypeEnum::$RELEASE,
		BuildSourceFuncCacheReleaseTargetTypeEnum::$BETA
		);
}
function GetAllBuildSourceFuncCacheReleaseTargetTypeEnumCaption($value)
{
	switch($value)
	{
		case BuildSourceFuncCacheReleaseTargetTypeEnum::$RELEASE:
			return "Release";
		case BuildSourceFuncCacheReleaseTargetTypeEnum::$BETA:
			return "Beta";
		default:
			die("Unknown BuildSourceFuncCacheReleaseTargetTypeEnum: " . $value);
	}
	return $value;
}

function GetBuildSourceFuncCacheReleaseTargetTypeFromProjectSourceOutputReleaseTargetType($value)
{
	switch($value)
	{
		case ProjectSourceOutputReleaseTargetTypeEnum::$RELEASE:
			return BuildSourceFuncCacheReleaseTargetTypeEnum::$RELEASE;
		case ProjectSourceOutputReleaseTargetTypeEnum::$BETA:
			return BuildSourceFuncCacheReleaseTargetTypeEnum::$BETA;
		// default:
		// 	die("Unknown Release Target Type: " . $value);
	}
	return $value;
}

?>