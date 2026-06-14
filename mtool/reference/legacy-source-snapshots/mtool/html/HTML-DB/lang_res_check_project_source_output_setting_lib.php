<?php

$IsIncludeXcode = false;
$IsDotNetUWP = false;

function CheckProjectSourceOutputSettingForLanguageResource($ProjectPID)
{
	global $IsIncludeXcode;
	global $IsDotNetUWP;
	
	$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
	
	for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$i];
		
		if ($ProjectSourceOutput->IsXCode()) {
			$IsIncludeXcode = true;
		}
		if ($ProjectSourceOutput->IsDotNetUWP()) {
			$IsDotNetUWP = true;
		}
	}
}
?>
