<?PHP

function PrepareBuildProjectSource(
	$project,
	$ProjectSourceOutputPIDListForUpdateClass,
	$ProjectSourceOutputPIDListForUpdateFunc,
	$ProjectSourceOutputPIDListForUpdateProxyServer,
	$ProjectSourceOutputPIDListForUpdateProxyClient,
	$ProjectSourceOutputPIDListForUpdateHtml,
	$ProjectSourceOutputPIDListForUpdateLanguageResource,
	$output_to_temp_folder,
	$output_after_copy_to_temp_folder,
	$quick_build,
	$output_debug_message
	)
{
	global $mtooldb;
	global $matsuesoft_login_token_id;
	
	InitializeMtoolBuildResultMessage();
	
	// Update Proxy automatically
	$DAda = new daDBAccess();
	$dalist = $DAda->GetdaList($project->PID); 
	for($i = 0 ; $i < count($dalist); $i++) {
		$da = $dalist[$i];
		
		synchronize_mtool_proxy_if_automatic($project->PID, $da->PID);
	}
	
	$thisNewTokenObj = new BuildTokenData();
	$thisNewTokenObj->Token = time() . "-ByUser:" . $matsuesoft_login_token_id . "-ProjectPID:" . $project->PID . "-" . makeRandStr(30);
	$thisNewTokenObj->ProjectPID = $project->PID;
	if ($output_to_temp_folder) {
		$thisNewTokenObj->IsOutputToTempFolder = "1";
	} else {
		$thisNewTokenObj->IsOutputToTempFolder = "0";
	}
	if ($output_after_copy_to_temp_folder) {
		$thisNewTokenObj->IsOutputAfterCopyToTempFolder = "1";
	} else {
		$thisNewTokenObj->IsOutputAfterCopyToTempFolder = "0";
	}
	if ($quick_build) {
		$thisNewTokenObj->IsQuickBuild = "1";
	} else {
		$thisNewTokenObj->IsQuickBuild = "0";
	}
	if ($output_debug_message) {
		$thisNewTokenObj->IsOutputDebugMessage = "1";
	} else {
		$thisNewTokenObj->IsOutputDebugMessage = "0";
	}
	
	$DABuildToken = new BuildTokenDBAccess();
	if ($DABuildToken->InsertBuildToken($thisNewTokenObj)) {
		$thisNewTokenObj->PID = $mtooldb->insert_id;
		
		StoreBuildTokenProjectSourceOutput($project, $thisNewTokenObj->PID, BuildTokenProjectSourceOutputBuildTargetTypeEnum::$DATACLASS, $ProjectSourceOutputPIDListForUpdateClass);
		StoreBuildTokenProjectSourceOutput($project, $thisNewTokenObj->PID, BuildTokenProjectSourceOutputBuildTargetTypeEnum::$DA, $ProjectSourceOutputPIDListForUpdateFunc);
		StoreBuildTokenProjectSourceOutput($project, $thisNewTokenObj->PID, BuildTokenProjectSourceOutputBuildTargetTypeEnum::$PROXYSERVER, $ProjectSourceOutputPIDListForUpdateProxyServer);
		StoreBuildTokenProjectSourceOutput($project, $thisNewTokenObj->PID, BuildTokenProjectSourceOutputBuildTargetTypeEnum::$PROXYCLIENT, $ProjectSourceOutputPIDListForUpdateProxyClient);
		StoreBuildTokenProjectSourceOutput($project, $thisNewTokenObj->PID, BuildTokenProjectSourceOutputBuildTargetTypeEnum::$HTML, $ProjectSourceOutputPIDListForUpdateHtml);
		StoreBuildTokenProjectSourceOutput($project, $thisNewTokenObj->PID, BuildTokenProjectSourceOutputBuildTargetTypeEnum::$LANGUAGERESOURCE, $ProjectSourceOutputPIDListForUpdateLanguageResource);
		
		PrintOutMtoolBuildResultMessage();
		
		return $thisNewTokenObj->Token;
	}
	PrintOutMtoolBuildResultMessage();
	
	return NULL;
}
function StoreBuildTokenProjectSourceOutput($project, $BuildTokenPID, $BuildTargetType, $ProjectSourceOutputPIDList)
{
	if ($ProjectSourceOutputPIDList && is_array($ProjectSourceOutputPIDList)) {
		$DABuildTokenProjectSourceOutput = new BuildTokenProjectSourceOutputDBAccess();
		for($i = 0 ; $i < count($ProjectSourceOutputPIDList); $i++) {
			$ProjectSourceOutputPID = $ProjectSourceOutputPIDList[$i];
			
			$thisTargetObj = new BuildTokenProjectSourceOutputData();
			$thisTargetObj->ProjectPID = $project->PID;
			$thisTargetObj->BuildTokenPID = $BuildTokenPID;
			$thisTargetObj->ProjectSourceOutputPID = $ProjectSourceOutputPID;
			$thisTargetObj->BuildTargetType = $BuildTargetType;
			
			if (!$DABuildTokenProjectSourceOutput->InsertBuildTokenProjectSourceOutput($thisTargetObj)) {
				AddMtoolErrorBuildMessage("Error while storing Build Target Token's Project Source Output");
			}
		}
	}
}

?>
