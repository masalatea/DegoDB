<?PHP

function GetProjectSourceOutputDefaultAutoloadFilename($ProjectPID, $ProjectSourceOutputSourceOutputDir)
{
	$autoloadfilename = "";
	
	$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID);
	if ($ProjectSourceOutputList) {
		for($i = 0 ; $i < count($ProjectSourceOutputList) ; $i++) {
			$thisProjectSourceOutput = $ProjectSourceOutputList[$i];
			
			if (strtoupper($thisProjectSourceOutput->SourceOutputDir) == strtoupper($ProjectSourceOutputSourceOutputDir)) {
				// Matched
				if ($thisProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBACCESS) {
					$autoloadfilename = GetAutomatedSourceFilename($thisProjectSourceOutput);
					break;
				}
			}
		}
	}
	return $autoloadfilename;
}

?>
