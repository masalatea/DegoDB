<?PHP

function DeleteBuildSourceCache(
	$ProjectPID,
	$build_source_type,
	$dataclass_PID,				// Data Class PID
	$da_PID						// DA Class PID
)
{
	$thisCache = new BuildSourceCacheData();
	$thisCache->ProjectPID = $ProjectPID;
	$thisCache->SourceType = $build_source_type;
	
	$DABuildSourceCache = new BuildSourceCacheDBAccess();
	
	switch($build_source_type) {
		case BuildSourceCacheSourceTypeEnum::$DATACLASS:
			$thisCache->dataclassPID = $dataclass_PID;
			if ($DABuildSourceCache->DeleteBuildSourceCacheByDataClass($thisCache)) {
				// Success
			} else {
				AddMtoolErrorBuildMessage(" -> Failed to delete Build Source Cache for this Data Class");
			}
			break;
		case BuildSourceCacheSourceTypeEnum::$DA:
			$thisCache->daPID = $da_PID;
			if ($DABuildSourceCache->DeleteBuildSourceCacheByDA($thisCache)) {
				// Success
			} else {
				AddMtoolErrorBuildMessage(" -> Failed to delete Build Source Cache for this Database Access Class");
			}
			break;
		default:
			break;
	}
}

function SaveIntoBuildSourceCache(
	$ProjectPID,
	$dataclassfilename,
	$build_source_type,
	$dataclass_PID,				// Data Class PID
	$da_PID,					// DA Class PID
	$source
)
{
	$thisCache = new BuildSourceCacheData();
	$thisCache->ProjectPID = $ProjectPID;
	$thisCache->SourceType = $build_source_type;
	switch($build_source_type) {
		case BuildSourceCacheSourceTypeEnum::$DATACLASS:
			$thisCache->dataclassPID = $dataclass_PID;
			$thisCache->daPID = -1;
			break;
		case BuildSourceCacheSourceTypeEnum::$DA:
			$thisCache->dataclassPID = -1;
			$thisCache->daPID = $da_PID;
			break;
		default:
			break;
	}
	$thisCache->Filename = $dataclassfilename;
	$thisCache->SourceCode = $source;
	
	$DABuildSourceCache = new BuildSourceCacheDBAccess();
	if ($DABuildSourceCache->InsertBuildSourceCache($thisCache)) {
		
	}
}

?>
