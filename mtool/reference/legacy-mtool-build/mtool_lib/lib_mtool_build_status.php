<?PHP

function check_if_this_item_is_already_build_by_mtool($BuildTokenCompletedItemList, $TargetPID)
{
	$dummy = false;
	return check_if_this_item_is_already_build_by_mtool_with_anyOutputOption($BuildTokenCompletedItemList, $TargetPID, $dummy);
}

function check_if_this_item_is_already_build_by_mtool_with_anyOutputOption($BuildTokenCompletedItemList, $TargetPID, &$option_anyOutput)
{
	global $createdBaseClasses;
	
	if ($BuildTokenCompletedItemList && is_array($BuildTokenCompletedItemList)) {
		for($i = 0 ; $i < count($BuildTokenCompletedItemList) ; $i++) {
			$BuildTokenCompletedItem = $BuildTokenCompletedItemList[$i];
			
			if ($BuildTokenCompletedItem->EachTargetPID == $TargetPID) {
				$option_anyOutput   = ($BuildTokenCompletedItem->option_anyOutput == 1);
				$createdBaseClasses = ($BuildTokenCompletedItem->option_createdBaseClasses == 1);
				return true;
			}
		}
	}
	return false;
}
function save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, $BuildTargetType, $TargetPID, $option_anyOutput)
{
	global $createdBaseClasses;
	
	$DABuildTokenCompletedItem = new BuildTokenCompletedItemDBAccess();
	
	$thisObj = new BuildTokenCompletedItemData();
	$thisObj->ProjectPID = $project->PID;
	$thisObj->BuildTokenPID = $BuildToken->PID;
	$thisObj->ProjectSourceOutputPID = $ProjectSourceOutput->PID;
	$thisObj->BuildTargetType = $BuildTargetType;
	$thisObj->EachTargetPID = $TargetPID;
	if ($option_anyOutput) {
		$thisObj->option_anyOutput = "1";
	} else {
		$thisObj->option_anyOutput = "0";
	}
	if ($createdBaseClasses) {
		$thisObj->option_createdBaseClasses = "1";
	} else {
		$thisObj->option_createdBaseClasses = "0";
	}
	
	return $DABuildTokenCompletedItem->InsertBuildTokenCompletedItem($thisObj);
}

// function checkIfThisIsTargetOfUpdate($thisPID, $ProjectSourceOutputPIDList)
// {
// 	$updateThis = NULL;
// 	if (is_array($ProjectSourceOutputPIDList)) {
// 		$updateThis = in_array($thisPID, $ProjectSourceOutputPIDList);
// 	} else {
// 		$updateThis = $ProjectSourceOutputPIDList;
// 	}
// 	return $updateThis;
// }

function check_if_skip_for_quick_build($quick_build, $LastBuild, $LastModifiedDT)
{
	$skip_for_quick_build = false;
	if ($quick_build) {
		$DALastBuild = new LastBuildDBAccess();
		$thisLastBuild = $DALastBuild->GetLastBuild($LastBuild->ProjectPID, $LastBuild->ProjectSourceOutputPID, $LastBuild->BuildClassType, $LastBuild->EachTargetPID, $LastBuild->ToTempFolder, $LastBuild->OutputAfterCopyToTempFolder);
		if ($thisLastBuild) {
			$thisLastBuildTime = strtotime($thisLastBuild->LastBuildDT);
			$LastModifiedTime  = strtotime($LastModifiedDT);
			
			if ($thisLastBuildTime > $LastModifiedTime) {
				$skip_for_quick_build = true;
			}
		}
	}
	return $skip_for_quick_build;
}
function set_last_build_time($LastBuild)
{
	$DALastBuild = new LastBuildDBAccess();
	$DALastBuild->DeleteLastBuild($LastBuild);
	$DALastBuild->InsertLastBuild($LastBuild);
}

function check_build_timeout_for_mtool()
{
	global $time_for_check_build_timeout_for_mtool;
	global $MTOOL_BUILD_TIMEOUT_SEC;
	
	if (abs(time() - $time_for_check_build_timeout_for_mtool) > $MTOOL_BUILD_TIMEOUT_SEC) {
		return true;
	}
	return false;
}
$time_for_check_build_timeout_for_mtool = time();

?>
