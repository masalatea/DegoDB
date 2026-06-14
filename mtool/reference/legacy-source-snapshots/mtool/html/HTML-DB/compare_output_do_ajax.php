<?php
require '/srv/legacy/composer/vendor/autoload.php';

$base_dir = __DIR__ . "/";
$MAX_INCLUDE_CONFIG_TRY = 10;
for($try_index = 0 ; $try_index <= $MAX_INCLUDE_CONFIG_TRY ; $try_index++) {
	$site_configfile = $base_dir . "config.php";
	if (is_file($site_configfile)) {
		include_once($site_configfile);
		break;
	}
	$base_dir .= "../";
}
include_once("/srv/legacy/www/mtool_lib/lib_commonheader.php");
include_once("/srv/legacy/www/mtool_lib/dbclasses/autoload_mtool.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_db.php");
include_once("/srv/legacy/www/mtool_lib/lib_path_on_top.php");

if ($matsuesoft_login_token_id == "") {
	?>
	<h3><?php print getres("NOTICE_PLEAE_LOGIN_BEFORE_USE"); ?></h3>
	<?php
} else {
	if ($matsuesoft_login_user_email_verified == false) {
		?>
	    <h3><?php print getres("NOTICE_PLEAE_AUTHENTICATE_EMAIL_BEFORE_USE"); ?></h3>
		<?php
		
		include_once("/srv/legacy/www/mtool_lib/email_verification_include.php");
		
		show_message_about_email_verification(true);
		
	} else {
		$ProjectPID = trim(GetParam("ProjectPID"));
		if (CheckIfLoginUserIsUserAndOutputMessage($ProjectPID, $matsuesoft_login_token_id)) {
			
include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_compare_output.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_ignore.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_compare_output.php");

$SHOW_DEBUG = false;

$ProjectPID = trim(GetParam("ProjectPID"));
$QUICK_CHECK = trim(GetParam("QUICK_CHECK"));
$QUICK_CHECK_THRESHOLD_DAY = trim(GetParam("QUICK_CHECK_THRESHOLD_DAY"));

$CheckTargetProjectPIDList = array();
$CheckTargetProjectPIDListValue = GetParam("CheckTargetProjectPIDList");
if ($CheckTargetProjectPIDListValue) {
	$CheckTargetProjectPIDList = preg_split("/,/", $CheckTargetProjectPIDListValue);
}

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
	<H3><font color="red">ERROR! Unknown Project PID</font></H3>
	<?php
	$NoError = false;
}
if ($QUICK_CHECK != "") {
	if (!is_numeric($QUICK_CHECK_THRESHOLD_DAY)) {
		?>
		<H3><font color="red">ERROR! Unknown Threshold Day</font></H3>
		<?php
		$NoError = false;
	}
}

$COMPARE_OUTPUT_TEMPLATE_FOR_TEXT                = "compare_output_template_for_text.txt";
$COMPARE_OUTPUT_TEMPLATE_FOR_TEXT_LINE           = "compare_output_template_for_text_line.txt";
$COMPARE_OUTPUT_TEMPLATE_FOR_WINDOWS_BATCH       = "compare_output_template_for_windows_batch.txt";
$COMPARE_OUTPUT_TEMPLATE_FOR_WINDOWS_BATCH_LINE  = "compare_output_template_for_windows_batch_line.txt";
$COMPARE_OUTPUT_TEMPLATE_FOR_MAC_COMMAND         = "compare_output_template_for_mac_command.txt";
$COMPARE_OUTPUT_TEMPLATE_FOR_MAC_COMMAND_LINE    = "compare_output_template_for_mac_command_line.txt";

$IgnoreDirSettingFile  = "compare_ignore_dir_setting_regex.txt";
$IgnoreDirSettingFileSettingList = InitializeIgnoreSettingFileSettingListSub($IgnoreDirSettingFile);

function check_if_time_is_within_threshold_of_quick_check($t_server_modified)
{
	global $QUICK_CHECK_THRESHOLD_DAY;
	
	if ($t_server_modified > 0) {
		if (time() - $t_server_modified < (60 * 60 * 24 * $QUICK_CHECK_THRESHOLD_DAY)) {
			return true;
		}
	}
	return false;
}
function check_all_dropbox_folder_continuously_if_not_yet($CompareOutput, $DropboxSettingPID, $DropboxAccessToken, $DropboxBaseFolderPID, $DropboxBaseFolderPath, $dropboxPath, &$ContentList)
{
	// global $matsuesoft_login_token_id;
	// global $AlreadyLoadedDropboxBaseFolderPathHT;
	// $key = "DropboxSettingPID:" . $DropboxSettingPID . " DropboxAccessToken:" . $DropboxAccessToken . "DropboxBaseFolderPID:" . $DropboxBaseFolderPID . " DropboxBaseFolderPath:" . $DropboxBaseFolderPath . " dropboxPath:" . $dropboxPath;
	// if (array_key_exists($key, $AlreadyLoadedDropboxBaseFolderPathHT)) {
	// 	return;
	// }
	// $AlreadyLoadedDropboxBaseFolderPathHT[$key] = true;
	
	$correspondingContent = GetCorrespondingContentForMtool($ContentList, $dropboxPath);
	if ($correspondingContent) {
		// print "already exists. Skip\n";
		return;
	}
	
	print "Checking File List...\n";
	
	$thisContent = new DropboxAPIMetaData($DropboxBaseFolderPath, json_decode(
		json_encode(array(
			'.tag'  => "folder",
			'path_lower' => $dropboxPath,
			'path_display' => $dropboxPath,
			'rev' => "-1"
		)), true));
	array_push($ContentList, $thisContent);
	
	check_all_dropbox_folder_continuously_sub($CompareOutput, $DropboxSettingPID, $DropboxAccessToken, $DropboxBaseFolderPID, $DropboxBaseFolderPath, $dropboxPath, $ContentList);
	
	print "Check File List Done\n";
	print "\n";
}
// $AlreadyLoadedDropboxBaseFolderPathHT = array();

function check_all_dropbox_folder_continuously_sub($CompareOutput, $DropboxSettingPID, $DropboxAccessToken, $DropboxBaseFolderPID, $DropboxBaseFolderPath, $dropboxPath, &$ContentList)
{
	global $IgnoreDirSettingFileSettingList;
	global $QUICK_CHECK;
	
	// $DropboxFullPath = pathCombine($DropboxBaseFolderPath, $dropboxPath);
	$DropboxFullPath = $dropboxPath;
	print "$DropboxFullPath";
	
	if (CheckIgnoreDirForUploadFileSettingListForOne($IgnoreDirSettingFileSettingList, $dropboxPath)) {
		// Skip
		print " -&gt; Skip\n";
		return;
	}
	
	$DACompareOutputSearchCache = new CompareOutputSearchCacheDBAccess();
	$meta_data_list = array();
	
	$load_from_dropbox_server = false;
	if ($QUICK_CHECK != "") {
		$CompareOutputSearchCacheList = $DACompareOutputSearchCache->GetCompareOutputSearchCacheList($CompareOutput->PID, $CompareOutput->ProjectPID, $DropboxBaseFolderPID, $DropboxAccessToken, $DropboxBaseFolderPath, $DropboxFullPath);
		for($i = 0 ; $i < count($CompareOutputSearchCacheList); $i++) {
			$CompareOutputSearchCache = $CompareOutputSearchCacheList[$i];
			$thisContent = new DropboxAPIMetaData($CompareOutputSearchCache->Result_DropboxBaseFolder, json_decode($CompareOutputSearchCache->Result_MetaData, true));
			
			array_push($meta_data_list, $thisContent);
			
			if ($thisContent->IsFile) {
				// print_r($CompareOutputSearchCache);
				// print_r($thisContent);
				
				$this_server_modified = GetHashValue($thisContent->OriginalMetaData, "server_modified");
				$t_server_modified = strtotime($this_server_modified);
				
				if ($t_server_modified > 0) {
					$date_server_modified = date("Y/n/j G:i:s", $t_server_modified);
					
					if (check_if_time_is_within_threshold_of_quick_check($t_server_modified)) {
						// recently updated
						// print $this_server_modified . " ------- " . $date_server_modified . "\n";
						// print_r($CompareOutputSearchCache);
						// print_r($thisContent);
						
						$load_from_dropbox_server = true;
					} else {
						// Check the Hint
						$DACompareOutputSearchCacheHint = new CompareOutputSearchCacheHintDBAccess();
						$CompareOutputSearchCacheHint = $DACompareOutputSearchCacheHint->GetCompareOutputSearchCacheHint($DropboxSettingPID, $thisContent->PathDisplay);
						if ($CompareOutputSearchCacheHint) {
							// print_r($CompareOutputSearchCacheHint);
							$t_server_modified = strtotime($CompareOutputSearchCacheHint->UpdatedDT);
							
							if (check_if_time_is_within_threshold_of_quick_check($t_server_modified)) {
								$load_from_dropbox_server = true;
							}
						}
						
					}
					
				} else {
					print "Internal Error! Failed to parse Date Time Text: " . $this_server_modified . "\n";
				}
			}
		}
	} else {
		$load_from_dropbox_server = true;
	}
	if ($load_from_dropbox_server) {
		print " <font color=green>Loading...</font>";
		// print " load from dropbox server\n";
		$meta_data_list = GetAllDropboxListFolderContinuously($DropboxAccessToken, $DropboxBaseFolderPath, $DropboxFullPath);
		
		$CompareOutputSearchCacheObj = new CompareOutputSearchCacheData();
		$CompareOutputSearchCacheObj->CompareOutputPID = $CompareOutput->PID;
		$CompareOutputSearchCacheObj->ProjectPID = $CompareOutput->ProjectPID;
		$CompareOutputSearchCacheObj->DropboxBaseFolderPID = $DropboxBaseFolderPID;
		$CompareOutputSearchCacheObj->SearchKey_DropboxAccessToken = $DropboxAccessToken;
		$CompareOutputSearchCacheObj->SearchKey_DropboxBaseFolderPath = $DropboxBaseFolderPath;
		$CompareOutputSearchCacheObj->SearchKey_DropboxFullPath = $DropboxFullPath;
		$DACompareOutputSearchCache->DeleteCompareOutputSearchCache($CompareOutputSearchCacheObj);
		
		for($k = 0 ; $k < count($meta_data_list); $k++) {
			$thisContent = $meta_data_list[$k];
			// print_r($thisContent);
			
			$CompareOutputSearchCacheObj->Result_MetaData = json_encode($thisContent->OriginalMetaData);
			$CompareOutputSearchCacheObj->Result_DropboxBaseFolder = $thisContent->OriginalDropboxBaseFolder;
			$DACompareOutputSearchCache->InsertCompareOutputSearchCache($CompareOutputSearchCacheObj);
			
			DeleteCompareOutputSearchCasheHint($DropboxSettingPID, $thisContent->PathDisplay);
		}
	} else {
		print " (use Cache)";
	}
	print "\n";
	
	for($k = 0 ; $k < count($meta_data_list); $k++) {
		$thisContent = $meta_data_list[$k];
		// print_r($thisContent);
		
		array_push($ContentList, $thisContent);
		
		if ($thisContent->IsDir) {
			// Dir
			// print_r($thisContent);
			// print "\n";
			
			// $NextDropboxFullPath = pathCombine($DropboxFullPath, $thisContent->PathDisplay);
			
			check_all_dropbox_folder_continuously_sub($CompareOutput, $DropboxSettingPID, $DropboxAccessToken, $DropboxBaseFolderPID, $DropboxBaseFolderPath, $thisContent->PathDisplay, $ContentList);
		} else {
			// print_r($thisContent);
			// print "\n";
		}
	}
}
function GetCorrespondingContentForMtool($ContentList, $searchPath)
{
	for($k = 0 ; $k < count($ContentList); $k++) {
		$thisContent = $ContentList[$k];
		
		if (strtoupper($thisContent->PathDisplay) == strtoupper($searchPath)) {
			return $thisContent;
		}
	}
	return NULL;
}

function compare_folder_on_dropbox($ContentList, $relative_dropbox_data_for_Base, $baseFolderContent, $relative_dropbox_data_for_Corresponding, $baseCorrespondingFolderContent, $is_same_filename_only)
{
	global $SHOW_DEBUG;
	
	$ThereIsDeviation = false;
	
	if ($baseFolderContent->IsDir && $baseCorrespondingFolderContent->IsDir) {
		
		print "Comparing Folder: $baseFolderContent->PathDisplay and $baseCorrespondingFolderContent->PathDisplay\n";
		
		for($k = 0 ; $k < count($ContentList); $k++) {
			$thisContent = $ContentList[$k];
			
			if ($SHOW_DEBUG) {
				print_r($thisContent);
			}
			
			if (startsWith(strtoupper($thisContent->PathDisplay), strtoupper($baseFolderContent->PathDisplay)) &&
			    $thisContent->PathDisplay != $baseFolderContent->PathDisplay)
			{
				$correspondingFilePath = substr_replace($thisContent->PathDisplay, $baseCorrespondingFolderContent->PathDisplay, 0, strlen($baseFolderContent->PathDisplay));
				
				if ($thisContent->PathDisplay == $correspondingFilePath) {
					$error_message = "Internal Error! Something Wrong... Failed to make Corresponding File Path.";
					print "$error_message\n";
					die($error_message);
				}

				$correspondingContent = GetCorrespondingContentForMtool($ContentList, $correspondingFilePath);
				if ($correspondingContent) {
					
					if ($SHOW_DEBUG) {
						print_r($correspondingContent);
					}
					
					if ($thisContent->IsFile)
					{
						$this_content_hash = GetHashValue($thisContent->OriginalMetaData, "content_hash");
						$corresponding_content_hash = GetHashValue($correspondingContent->OriginalMetaData, "content_hash");
						if ($this_content_hash && $this_content_hash != "" && $this_content_hash == $corresponding_content_hash) {
							// Same
						} else {
							// Different
							print "<font color=red>Not Match: $thisContent->PathDisplay and $correspondingFilePath</font>\n";
							$ThereIsDeviation = true;
							
							AddCompareOutputSearchCasheHint($relative_dropbox_data_for_Base->DropboxSetting->PID, $thisContent->PathDisplay);
							AddCompareOutputSearchCasheHint($relative_dropbox_data_for_Corresponding->DropboxSetting->PID, $correspondingFilePath);
						}
					}
					else if ($thisContent->IsDir)
					{
						// print_r($thisContent);
						$thisThereIsDeviation = compare_folder_on_dropbox($ContentList, $relative_dropbox_data_for_Base, $thisContent, $relative_dropbox_data_for_Corresponding, $correspondingContent, $is_same_filename_only);
						$ThereIsDeviation = $ThereIsDeviation | $thisThereIsDeviation;
					}
					
				} else {
					if ($thisContent->IsFile) {
						if ($is_same_filename_only) {
							// No Problem
						} else {
							print "<font color=red>Corresponding file is not exist: $thisContent->PathDisplay -&gt; $correspondingFilePath not found</font>\n";
							$ThereIsDeviation = true;
							
							AddCompareOutputSearchCasheHint($relative_dropbox_data_for_Base->DropboxSetting->PID, $thisContent->PathDisplay);
						}
					}
					else if ($thisContent->IsDir)
					{
						// no warning
					}
				}
			}
		}
	}
	return $ThereIsDeviation;
}

$TMP_OUTPUT_REGEX_PATTERN = "/\s*-\s*tmp\s*output\s*$/i";
function CheckIfTmpOutputDirForMtool($path)
{
	global $TMP_OUTPUT_REGEX_PATTERN;
	return preg_match($TMP_OUTPUT_REGEX_PATTERN, $path);
}
function RemoveTmpOutputDirSuffixForMtool($path)
{
	global $TMP_OUTPUT_REGEX_PATTERN;
	return preg_replace($TMP_OUTPUT_REGEX_PATTERN, "", $path);
}

class DeviationFolderInfo
{
	public $PathA;
	public $PathB;
    function DeviationFolderInfo($path_a, $path_b)
    {
		$this->PathA = $path_a;
		$this->PathB = $path_b;
    }
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	$IsMtoolProjectOwner = CheckIfMtoolProjectOwner($ProjectPID, $matsuesoft_login_token_id);
	
	$DAProject = new ProjectDBAccess();
	$projectlist = $DAProject->GetProjectbyOwnerOrUserSecurityList($matsuesoft_login_token_id); 
	
	for($i = 0 ; $i < count($projectlist); $i++) {
		$project = $projectlist[$i];

		$is_check_target = false;
		for($j = 0 ; $j < count($CheckTargetProjectPIDList) ; $j++) {
			$CheckTargetProjectPID = $CheckTargetProjectPIDList[$j];
			if ($project->PID == $CheckTargetProjectPID) {
				$is_check_target = true;
				break;
			}
		}
		if ($is_check_target) {
			?>
			<h3>Checking for Project: <?php print htmlspecialchars($project->name); ?></h3>
			<?php
			$DACompareOutput = new CompareOutputDBAccess();
			$CompareOutputList = $DACompareOutput->GetCompareOutputList($project->PID);

			for($j = 0 ; $j < count($CompareOutputList) ; $j++) {
				$CompareOutput = $CompareOutputList[$j];

				$DropboxBaseFolder = initialize_compare_output($project->PID, $CompareOutput->DropboxBaseFolderPID, $matsuesoft_login_token_id);
				if ($DropboxBaseFolder) {
					?>
					<h4>Dropbox Base Folder: <?php print htmlspecialchars($DropboxBaseFolder->Name); ?></h4>
					<?php
					$OutputFileFullPath = get_dropbox_folder_path_for_compare_output($DropboxBaseFolder, $CompareOutput->OutputFilePath);
					$CompareFullPath = get_dropbox_folder_path_for_compare_output($DropboxBaseFolder, $CompareOutput->ComparePath);

					print "<pre>";

					$relative_dropbox_data = GetRelativeDropboxPathBasedOnUserAndDropboxBaseFolder($DropboxBaseFolder->PID, $matsuesoft_login_token_id, $CompareOutput->ComparePath);
					if ($relative_dropbox_data) {

						$DropboxBaseFolderPath = MakeDropboxBaseFolder($DropboxBaseFolder->Name);
						// $DropboxFullPath = pathCombine($DropboxBaseFolderPath, $CompareOutput->ComparePath);

						// print "full path: " . $relative_dropbox_data->DropboxFullPath . "\n";

						$ContentList = array();
						check_all_dropbox_folder_continuously_if_not_yet($CompareOutput, $relative_dropbox_data->DropboxSetting->PID, $relative_dropbox_data->DropboxSetting->AccessToken, $DropboxBaseFolder->PID, $DropboxBaseFolderPath, $relative_dropbox_data->DropboxFullPath, $ContentList);

						print "Comparing...\n";

						$DeviationFolderInfoList = array();

						for($k = 0 ; $k < count($ContentList); $k++) {
							$thisContent = $ContentList[$k];

							if ($thisContent->IsDir) {
								if (CheckIfTmpOutputDirForMtool($thisContent->PathDisplay)) {
									$correspondingPath = RemoveTmpOutputDirSuffixForMtool($thisContent->PathDisplay);
									$correspondingContent = GetCorrespondingContentForMtool($ContentList, $correspondingPath);
									if ($correspondingContent) {
										$ThereIsDeviation = compare_folder_on_dropbox($ContentList, $relative_dropbox_data, $thisContent, $relative_dropbox_data, $correspondingContent, false);
										if($ThereIsDeviation) {
											array_push($DeviationFolderInfoList,
														new DeviationFolderInfo("." . $thisContent->PathRelativeDisplay, "." . $correspondingContent->PathRelativeDisplay)
											);
										}
									}
								}
							}
						}

						// $SHOW_DEBUG = true;

						$DACompareOutputAdditionalPath = new CompareOutputAdditionalPathDBAccess();
						$CompareOutputAdditionalPathList = $DACompareOutputAdditionalPath->GetCompareOutputAdditionalPathList($CompareOutput->PID, $project->PID);
						for($k = 0 ; $k < count($CompareOutputAdditionalPathList); $k++) {
							$CompareOutputAdditionalPath = $CompareOutputAdditionalPathList[$k];
							
							$is_same_filename_only = ($CompareOutputAdditionalPath->IsSameFilenameOnly == 1);

							$DropboxBaseFoldePIDForPathA = $DropboxBaseFolder->PID;
							$DropboxBaseFolderForPathA = $DropboxBaseFolder;
							$CompareBasePathForPathA = ".";
							if ($CompareOutputAdditionalPath->PathA_DropboxBaseFolderPID > 0) {
								$DropboxBaseFoldePIDForPathA = $CompareOutputAdditionalPath->PathA_DropboxBaseFolderPID;
								
								$DADropboxBaseFolder = new DropboxBaseFolderDBAccess();
								$result = $DADropboxBaseFolder->GetDropboxBaseFolderByUserForAllSettingGroup($DropboxBaseFoldePIDForPathA, $matsuesoft_login_token_id);
								if ($result) {
									$DropboxBaseFolderForPathA = $result;
									$CompareBasePathForPathA = ".." . MakeDropboxBaseFolder($DropboxBaseFolderForPathA->Name);
								}
							}
							$DropboxBaseFoldePIDForPathB = $DropboxBaseFolder->PID;
							$DropboxBaseFolderForPathB = $DropboxBaseFolder;
							$CompareBasePathForPathB = ".";
							if ($CompareOutputAdditionalPath->PathB_DropboxBaseFolderPID > 0) {
								$DropboxBaseFoldePIDForPathB = $CompareOutputAdditionalPath->PathB_DropboxBaseFolderPID;
								
								$DADropboxBaseFolder = new DropboxBaseFolderDBAccess();
								$result = $DADropboxBaseFolder->GetDropboxBaseFolderByUserForAllSettingGroup($DropboxBaseFoldePIDForPathB, $matsuesoft_login_token_id);
								if ($result) {
									$DropboxBaseFolderForPathB = $result;
									$CompareBasePathForPathB = ".." . MakeDropboxBaseFolder($DropboxBaseFolderForPathB->Name);
								}
							}
							$DropboxBaseFolderPathForPathA = MakeDropboxBaseFolder($DropboxBaseFolderForPathA->Name);
							$DropboxBaseFolderPathForPathB = MakeDropboxBaseFolder($DropboxBaseFolderForPathB->Name);

							$relative_dropbox_data_for_PathA = GetRelativeDropboxPathBasedOnUserAndDropboxBaseFolder($DropboxBaseFoldePIDForPathA, $matsuesoft_login_token_id, $CompareOutputAdditionalPath->GetPathAWithoutLastSlush());
							$relative_dropbox_data_for_PathB = GetRelativeDropboxPathBasedOnUserAndDropboxBaseFolder($DropboxBaseFoldePIDForPathB, $matsuesoft_login_token_id, $CompareOutputAdditionalPath->GetPathBWithoutLastSlush());
							if ($relative_dropbox_data_for_PathA && $relative_dropbox_data_for_PathB) {
								
								check_all_dropbox_folder_continuously_if_not_yet($CompareOutput, $relative_dropbox_data_for_PathA->DropboxSetting->PID, $relative_dropbox_data_for_PathA->DropboxSetting->AccessToken, $DropboxBaseFoldePIDForPathA, $DropboxBaseFolderPathForPathA, $relative_dropbox_data_for_PathA->DropboxFullPath, $ContentList);
								check_all_dropbox_folder_continuously_if_not_yet($CompareOutput, $relative_dropbox_data_for_PathB->DropboxSetting->PID, $relative_dropbox_data_for_PathB->DropboxSetting->AccessToken, $DropboxBaseFoldePIDForPathB, $DropboxBaseFolderPathForPathB, $relative_dropbox_data_for_PathB->DropboxFullPath, $ContentList);
								
								$ContentA = GetCorrespondingContentForMtool($ContentList, $relative_dropbox_data_for_PathA->DropboxFullPath);
								$ContentB = GetCorrespondingContentForMtool($ContentList, $relative_dropbox_data_for_PathB->DropboxFullPath);
								
								if ($ContentA && $ContentB) {
									$ThereIsDeviation = compare_folder_on_dropbox($ContentList, $relative_dropbox_data_for_PathA, $ContentA, $relative_dropbox_data_for_PathB, $ContentB, $is_same_filename_only);
									if($ThereIsDeviation) {
										// print "There is a deviation\n";
										array_push($DeviationFolderInfoList,
													new DeviationFolderInfo($CompareBasePathForPathA . $ContentA->PathRelativeDisplay,
																			$CompareBasePathForPathB . $ContentB->PathRelativeDisplay)
										);
									}
									
								} else {
									if (!$ContentA) {
										print "Warning. Path Not Found: " . $relative_dropbox_data_for_PathA->DropboxFullPath . "\n";
										// print_r($ContentList);
									}
									if (!$ContentB) {
										print "Warning. Path Not Found: " . $relative_dropbox_data_for_PathB->DropboxFullPath . "\n";
										// print_r($ContentList);
									}
								}
							}
						}

						$template_file      = "";
						$template_line_file = "";

						switch($CompareOutput->OutputFileType) {
							case CompareOutputOutputFileTypeEnum::$TEXT:
								$template_file      = $COMPARE_OUTPUT_TEMPLATE_FOR_TEXT;
								$template_line_file = $COMPARE_OUTPUT_TEMPLATE_FOR_TEXT_LINE;
								break;
							case CompareOutputOutputFileTypeEnum::$WINDOWSBATCH:
								$template_file      = $COMPARE_OUTPUT_TEMPLATE_FOR_WINDOWS_BATCH;
								$template_line_file = $COMPARE_OUTPUT_TEMPLATE_FOR_WINDOWS_BATCH_LINE;
								break;
							case CompareOutputOutputFileTypeEnum::$MACCOMMAND:
								$template_file      = $COMPARE_OUTPUT_TEMPLATE_FOR_MAC_COMMAND;
								$template_line_file = $COMPARE_OUTPUT_TEMPLATE_FOR_MAC_COMMAND_LINE;
								break;
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Outputt File Type: " . $CompareOutput->OutputFileType);
								continue;
						}
						$template      = file_get_contents($template_file);
						$template_line = file_get_contents($template_line_file);

						$lines = "";
						for($k = 0 ; $k < count($DeviationFolderInfoList); $k++) {
							$DeviationFolderInfo = $DeviationFolderInfoList[$k];
							
							$lines .= ReplaceOutputSourceInfoByKeyValue($template_line, array(
									array("KEY"=>"__COMPARE_COMMAND__", "VALUE"=>$CompareOutput->CompareToolFilePath, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
									array("KEY"=>"__PATH_A__", "VALUE"=>$DeviationFolderInfo->PathA, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
									array("KEY"=>"__PATH_B__", "VALUE"=>$DeviationFolderInfo->PathB, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
								));
						}
						$template = ReplaceOutputSourceInfoByKeyValue($template, array(
								array("KEY"=>"__LINES__", "VALUE"=>$lines, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));


						$fullpath_filename = pathCombine($relative_dropbox_data->DropboxFullPath, $CompareOutput->OutputFilePath);

						$writemode = DropboxUploadWriteMode::$add;
						$rev = "";

						// Check Existin File
						$needToAddOrUpdate = true;
						$tmp_file_name = tempnam(sys_get_temp_dir(), "existingfile");
						$result = GetFileFromDropBoxByAccessTokenWithFullDropboxPath($matsuesoft_login_token_id, $relative_dropbox_data->DropboxSetting->AccessToken, $fullpath_filename, $tmp_file_name, -1, 5);
						if ($result->Success && $result->FileExist) {
							$rev = $result->Rev;
							$originalLinesOneText = file_get_contents($tmp_file_name);
							$needToAddOrUpdate = CheckIfNeedToUpdateForMultiText($originalLinesOneText, $template);

							if (!$needToAddOrUpdate) {
								print "File is Same. No need to update. <br>\n";
							}
							$writemode = DropboxUploadWriteMode::$update;
						}
						if ($needToAddOrUpdate) {
							// $result = $dbxClient->uploadFile($fullpath_filename, $writemode, $f);
							$result = StartDropboxUpload($relative_dropbox_data->DropboxSetting->AccessToken, $template, $fullpath_filename, $writemode, $rev);
							if ($result->Success) {
								AddCompareOutputSearchCasheHint($relative_dropbox_data->DropboxSetting->PID, $fullpath_filename);
								?><font color="red">Created: <?php print htmlspecialchars($fullpath_filename); ?></font><?php

								if (is_file($tmp_file_name)) {
									unlink($tmp_file_name);
								}
							} else {
								?><font color="red">Failed to create: <?php print htmlspecialchars($fullpath_filename); ?></font><?php
							}
						}
					} else {
						// No Setting
					}
					print "</pre>";
				}
			}
		}
	}
}
?>
            <?php
			}
        	?>
		
		<?php
	}
	?>
	
	<?php
} // ここより上の処理にはLoginが必要
?>