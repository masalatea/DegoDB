<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-BuildSourceFuncCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-BuildSourceFuncCache.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-BuildSourceFuncCache.php` and extend `BuildSourceFuncCacheDataBase` for project-specific customizations.

    class BuildSourceFuncCacheData extends BuildSourceFuncCacheDataBase
    {
    }
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
