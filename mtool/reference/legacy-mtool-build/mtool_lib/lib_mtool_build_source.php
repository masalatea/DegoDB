<?PHP

$UNIT_TEST_USER_AREA_START_NUM = 100;
$MAX_USER_CODE_NUM = $UNIT_TEST_USER_AREA_START_NUM + 1000;

include_once($MTOOL_LIB . "/lib_mtool_compare_output.php");

function GetAutomatedSourceFilename($ProjectSourceOutput)
{
	$autoloadfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$autoloadfilename = "";
			if (trim($ProjectSourceOutput->AutoloadFilenameSuffix) != "") {
				$autoloadfilename = "autoload_" . trim($ProjectSourceOutput->AutoloadFilenameSuffix) . ".php";
			} else {
				$autoloadfilename = "autoload.php";
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
	}
	return $autoloadfilename;
}

class UpdateAutomatedSourceResult
{
	public $Success = false;
	public $Source = "";
}

function UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, $storebasepath, $outputSourceInfoList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	global $MAX_USER_CODE_NUM;
	
	$result = new UpdateAutomatedSourceResult();
	$result->Success = false;
	
	$copyfrombasepath = "";
	if ($storebasepath != "") {
		$storebasepath = GetTempFolderForSourceOutputIfNeeded($project, $ProjectSourceOutput, $output_to_temp_folder, $storebasepath);
		if ($output_after_copy_to_temp_folder) {
			$copyfrombasepath = $storebasepath;
		}
	} else {
		$storebasepath = GetTempFolderForSourceOutputIfNeeded($project, $ProjectSourceOutput, $output_to_temp_folder, $ProjectSourceOutput->SourceOutputDir);
		if ($output_after_copy_to_temp_folder) {
			$copyfrombasepath = $ProjectSourceOutput->SourceOutputDir;
		}
	}
	if (!$output_after_copy_to_temp_folder) {
		$copyfrombasepath = $storebasepath;
	}
	
	$targetFilePathOnTarget = NULL;
	$tempOriginalFilePathForLoad = NULL;
	$tempOriginalFilePathForSave = NULL;
	$tempOutputFilePath = NULL;
	$originalFileExistsForLoad = false;
	$originalFileExistsForSave = false;
	$originalFileRev = "";
	PrepareUpdateSourceFromServer($project, $ProjectSourceOutput, $localfilename, $copyfrombasepath, $storebasepath, $targetFilePathOnTarget, $tempOriginalFilePathForLoad, $tempOriginalFilePathForSave, $tempOutputFilePath, $originalFileExistsForLoad, $originalFileExistsForSave, $originalFileRev);
	
	$originalEditableCode = "";
	$originalEditableCodeWithNoList = array();
	$originalEditableCodeWithNameList = array();
	$editableAreaNameList = array();
	if ($originalFileExistsForLoad) {
		if (is_file($tempOriginalFilePathForLoad)) {
			$all_original_file_contents = file_get_contents($tempOriginalFilePathForLoad);
			
			if (trim($ProjectSourceOutput->SourceTextCharCode) != "") {
				$all_original_file_contents = mb_convert_encoding($all_original_file_contents, "UTF-8", $ProjectSourceOutput->SourceTextCharCode);
			}
			
			$SourceLines = preg_split("/\r?\n/", $all_original_file_contents);
			$prescan = DoPrescanForUserCustomTemplate($SourceLines);
			
			$originalEditableCode = GetUserCustomCode($SourceLines, $prescan);
			
			for($codeindex = 0 ; $codeindex < $MAX_USER_CODE_NUM ; $codeindex++) {
				$thisoriginalEditableCode = GetUserCustomCodeWithNo($SourceLines, $prescan, $codeindex);
				if ($thisoriginalEditableCode != NULL) {
					$originalEditableCodeWithNoList[$codeindex] = $thisoriginalEditableCode;
				}
			}
			$editableAreaNameList = GetAllEditableAreaName($SourceLines);
			for($i = 0 ; $i < count($editableAreaNameList) ; $i++) {
				$editableAreaName = $editableAreaNameList[$i];
				
				$thisoriginalEditableCode = GetUserCustomCodeWithName($SourceLines, $prescan, $editableAreaName);
				if ($thisoriginalEditableCode != NULL) {
					$originalEditableCodeWithNameList[$editableAreaName] = $thisoriginalEditableCode;
				}
			}
		}
	}
	
	$outputsourcetext = $source_template;
	// $outputsourcetext = preg_replace("/__CLASS_NAME__/i", $className, $outputsourcetext);
	if ($outputSourceInfoList) {
		$outputsourcetext = ReplaceOutputSourceInfoByKeyValue($outputsourcetext, $outputSourceInfoList);
	}
	
	if ($originalFileExistsForLoad) {
		$SourceLines = preg_split("/\r?\n/", $outputsourcetext);
		$prescan = DoPrescanForUserCustomTemplate($SourceLines);
		
		$is_changed = false;
		
		if ($originalEditableCode != NULL) {
			$SourceLines = replace_user_custom_code_with_no("", $originalEditableCode, $SourceLines, $prescan, $is_changed);
			// $outputsourcetext = preg_replace("/__USER_CUSTOM__[ \t]*\r?\n?/i",  $originalEditableCode,  $outputsourcetext);
		}
		
		for($codeindex = 0 ; $codeindex < $MAX_USER_CODE_NUM ; $codeindex++) {
			if (isset($originalEditableCodeWithNoList[$codeindex])) {
				$thisCode = GetHashValue($originalEditableCodeWithNoList, $codeindex);
				if ($is_changed) {
					$prescan = DoPrescanForUserCustomTemplate($SourceLines);
					$is_changed = true;
				}
				$SourceLines = replace_user_custom_code_with_no($codeindex, $thisCode, $SourceLines, $prescan, $is_changed);
			}
			// $outputsourcetext = preg_replace("/__USER_CUSTOM" . $codeindex . "__[ \t]*\r?\n?/i", $thisCode, $outputsourcetext);
		}
		for($i = 0 ; $i < count($editableAreaNameList) ; $i++) {
			$editableAreaName = $editableAreaNameList[$i];
			$thisCode = GetHashValue($originalEditableCodeWithNameList, $editableAreaName);
			if ($is_changed) {
				$prescan = DoPrescanForUserCustomTemplate($SourceLines);
				$is_changed = true;
			}
			$SourceLines = replace_user_custom_code_with_name($editableAreaName, $thisCode, $SourceLines, $prescan, $is_changed);
		}
		$outputsourcetext = implode("\n", $SourceLines);
	}
	$fp = fopen($tempOutputFilePath, 'w');
	if (trim($ProjectSourceOutput->SourceTextCharCode) != "") {
		$outputsourcetext = mb_convert_encoding($outputsourcetext, $ProjectSourceOutput->SourceTextCharCode, "UTF-8,EUC-JP,SJIS,JIS");
	}
	fwrite($fp, $outputsourcetext);
	fclose($fp);
	
	$result->Success = StoreSourceToServer($BuildToken, $project, $ProjectSourceOutput, $targetFilePathOnTarget, $tempOriginalFilePathForSave, $tempOutputFilePath, $originalFileExistsForSave, $originalFileRev);
	if ($result->Success) {
		$result->Source = file_get_contents($tempOutputFilePath);
	}
	
	if (is_file($tempOriginalFilePathForLoad)) {
		unlink($tempOriginalFilePathForLoad);
	}
	if (is_file($tempOriginalFilePathForSave)) {
		unlink($tempOriginalFilePathForSave);
	}
	if (is_file($tempOutputFilePath)) {
		unlink($tempOutputFilePath);
	}
	return $result;
}

function ReplaceOutputSourceInfoByKeyValue($outputsourcetext, $outputSourceInfoList)
{
	return ReplaceTemplateByKeyValue($outputsourcetext, $outputSourceInfoList);
}


function PrepareUpdateSourceFromServer($project, $ProjectSourceOutput, $localfilename, $copyfrombasepath, $storebasepath, &$targetFilePathOnTarget, &$tempOriginalFilePathForLoad, &$tempOriginalFilePathForSave, &$tempOutputFilePath, &$originalFileExistsForLoad, &$originalFileExistsForSave, &$originalFileRev)
{
	global $matsuesoft_login_token_id;
	
	$targetFilePathOnCopyFrom = pathCombine($copyfrombasepath, $localfilename);
	$targetFilePathOnTarget = pathCombine($storebasepath, $localfilename);
	$tempOriginalFilePathForLoad = tempnam(sys_get_temp_dir(), $localfilename);
	if ($targetFilePathOnCopyFrom != $targetFilePathOnTarget) {
		$tempOriginalFilePathForSave = tempnam(sys_get_temp_dir(), $localfilename);
	} else {
		$tempOriginalFilePathForSave = $tempOriginalFilePathForLoad;
	}
	
	$tempOutputFilePath = tempnam(sys_get_temp_dir(), $localfilename);
	// AddMtoolGeneralBuildMessage("    Path on Target: " . $targetFilePathOnTarget);
	// AddMtoolGeneralBuildMessage("    Original File(tmp): " . $tempOriginalFilePath);
	// AddMtoolGeneralBuildMessage("    Output File(tmp): " . $tempOutputFilePath);
	
	$originalFileExistsForLoad = false;
	$originalFileExistsForSave = false;
	$originalFileRev = "";
	
	if ($project->StorageType == ProjectStorageTypeEnum::$DROPBOX) {
		AddMtoolDebugBuildMessage("     => Loading DropBox file into local");
		
		if ($targetFilePathOnCopyFrom != $targetFilePathOnTarget) {
			$result = GetFileFromDropBoxByDropboxBaseFolderPID("", GetTargetDropboxBaseFolderPIDBasedOnProjectOrProjectSourceOutput($project, $ProjectSourceOutput), $targetFilePathOnCopyFrom, $tempOriginalFilePathForLoad, -1, 100);
			if ($result && $result->Success) {
				if ($result->FileExist) {
					$originalFileExistsForLoad = true;
					AddMtoolDebugBuildMessage("     => Loaded DropBox file into local");
				}
				
			} else {
				// Failed to connect to DropBox
				PrintOutMtoolBuildResultMessage();
				die("Failed to connect DropBox. Fatal Error. Aborted");
			}
			
			$result = GetFileFromDropBoxByDropboxBaseFolderPID("", GetTargetDropboxBaseFolderPIDBasedOnProjectOrProjectSourceOutput($project, $ProjectSourceOutput), $targetFilePathOnTarget, $tempOriginalFilePathForSave, -1, 100);
			if ($result && $result->Success) {
				if ($result->FileExist) {
					$originalFileExistsForSave = true;
					$originalFileRev = $result->Rev;
				}
			}
			if (!$originalFileExistsForSave) {
				AddMtoolDebugBuildMessage("     => File is not exist in DropBox Server. It will be created.");
			}
			
		} else {
			$result = GetFileFromDropBoxByDropboxBaseFolderPID("", GetTargetDropboxBaseFolderPIDBasedOnProjectOrProjectSourceOutput($project, $ProjectSourceOutput), $targetFilePathOnTarget, $tempOriginalFilePathForLoad, -1, 100);
			if ($result && $result->Success) {
				if ($result->FileExist) {
					AddMtoolDebugBuildMessage("     => Loaded DropBox file into local");
					$originalFileExistsForLoad = true;
					$originalFileExistsForSave = true;
					$originalFileRev = $result->Rev;
					// print_r($rev);
				} else {
					AddMtoolDebugBuildMessage("     => File is not exist in DropBox Server. It will be created.");
				}
				
			} else {
				// Failed to connect to DropBox
				PrintOutMtoolBuildResultMessage();
				die("Failed to connect DropBox. Fatal Error. Aborted.");
			}
		}

	} else {
		AddMtoolErrorBuildMessage("     => Nothing to be done because Storage Type is unknown.");
	}
}
function StoreSourceToServer($BuildToken, $project, $ProjectSourceOutput, $targetFilePathOnTarget, $tempOriginalFilePathForSave, $tempOutputFilePath, $originalFileExistsForSave, $originalFileRev)
{
	$result = false;
	
	$needToUpdate = CheckIfNeedToUpdate($tempOriginalFilePathForSave, $tempOutputFilePath);
	
	if ($needToUpdate) {
		if ($project->StorageType == ProjectStorageTypeEnum::$DROPBOX) {
			AddMtoolDebugBuildMessage("     => Saving file into Dropbox");
			
			// $f = fopen($tempOutputFilePath, "rb");
			// 
			// $writemode = NULL;
			// if ($originalFileExists) {
			// 	$writemode = dbx\WriteMode::update($originalFileRev);
			// } else {
			// 	$writemode = dbx\WriteMode::add();
			// }
			// $result = $project->dbxClient->uploadFile($targetFilePathOnTarget, $writemode, $f);
			// fclose($f);
			
			$TargetDropboxBaseFolderPID = GetTargetDropboxBaseFolderPIDBasedOnProjectOrProjectSourceOutput($project, $ProjectSourceOutput);
			
			$writemode = NULL;
			if ($originalFileExistsForSave) {
				$writemode = DropboxUploadWriteMode::$update;
			} else {
				$writemode = DropboxUploadWriteMode::$add;
				$originalFileRev = "";
			}
			$contents = file_get_contents($tempOutputFilePath);
			$result = StartDropboxUploadByDropboxBaseFolderPID("", $TargetDropboxBaseFolderPID, $contents, $targetFilePathOnTarget, $writemode, $originalFileRev);
			
			// print_r($result);
			if ($result->Success) {
				AddMtoolDebugBuildMessage("     => Done Saving file into Dropbox");
				$result = true;
				
				// Save into DB
				$DAProjectSourceOutputSavedFiles = new ProjectSourceOutputSavedFilesDBAccess();
				$ProjectSourceOutputSavedFilesObj = new ProjectSourceOutputSavedFilesData();
				$ProjectSourceOutputSavedFilesObj->BuildTokenPID = $BuildToken->PID;
				$ProjectSourceOutputSavedFilesObj->ProjectPID = $project->PID;
				$ProjectSourceOutputSavedFilesObj->ProjectSourceOutputPID = $ProjectSourceOutput->PID;
				$ProjectSourceOutputSavedFilesObj->TargetDropboxBaseFolderPID = $TargetDropboxBaseFolderPID;
				$ProjectSourceOutputSavedFilesObj->FilePathOnTarget = $targetFilePathOnTarget;
				$ProjectSourceOutputSavedFilesObj->SourceText = $contents;
				if (!$DAProjectSourceOutputSavedFiles->DeleteProjectSourceOutputSavedFiles($ProjectSourceOutputSavedFilesObj)) {
					AddMtoolErrorBuildMessage("     => Failed to Delete Last Saved Data from DB to clean up last data");
				}
				if (!$DAProjectSourceOutputSavedFiles->InsertProjectSourceOutputSavedFiles($ProjectSourceOutputSavedFilesObj)) {
					AddMtoolErrorBuildMessage("     => Failed to Saving Last Saved Data into DB");
				}
				
			} else {
				AddMtoolDebugBuildMessage("     => Failed to Saving file into Dropbox: " . $targetFilePathOnTarget);
			}
		}
	} else {
		AddMtoolDebugBuildMessage("     => No need to update");
		$result = true;
	}
	return $result;
}

function CheckIfListAndAddListClassSuffixIfList($ProjectSourceOutput, $dafunc, $dataclassname)
{
	switch($dafunc->ActionType) {
		case dafuncActionTypeEnum::$SELECTSINGLE:
			break;
		case dafuncActionTypeEnum::$SELECTLIST:
			$dataclassname = GetMtoolDataListClassName($ProjectSourceOutput, $dataclassname);
			break;
		case dafuncActionTypeEnum::$INSERT:
		case dafuncActionTypeEnum::$UPDATE:
		case dafuncActionTypeEnum::$DELETE:
			break;
		default:
			AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
			break;
	}
	return $dataclassname;
}

function GetReturnCodeForEachLang($ProjectSourceOutput)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "\n";
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return "\r\n";
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}

?>
