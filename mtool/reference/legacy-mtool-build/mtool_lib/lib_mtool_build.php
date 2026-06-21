<?PHP

$MTOOL_BUILD_TIMEOUT_SEC = 10;

include_once($MTOOL_LIB . "/lib_mtool_build_message.php");
include_once($MTOOL_LIB . "/lib_mtool_build_prepare.php");
include_once($MTOOL_LIB . "/lib_mtool_build_status.php");
include_once($MTOOL_LIB . "/lib_mtool_build_source.php");
include_once($MTOOL_LIB . "/lib_mtool_build_source_parameter.php");
include_once($MTOOL_LIB . "/lib_mtool_build_source_sql_where.php");
include_once($MTOOL_LIB . "/lib_mtool_build_cache.php");
include_once($MTOOL_LIB . "/lib_mtool_build_naming.php");
include_once($MTOOL_LIB . "/lib_mtool_build_naming_enum.php");
include_once($MTOOL_LIB . "/lib_mtool_build_path.php");
include_once($MTOOL_LIB . "/lib_mtool_build_dataclass.php");
include_once($MTOOL_LIB . "/lib_mtool_build_dafunc.php");
include_once($MTOOL_LIB . "/lib_mtool_build_proxyserver.php");
include_once($MTOOL_LIB . "/lib_mtool_build_proxyclient.php");
include_once($MTOOL_LIB . "/lib_mtool_build_html.php");
include_once($MTOOL_LIB . "/lib_mtool_build_template.php");
include_once($MTOOL_LIB . "/lib_mtool_build_language_resource.php");
include_once($MTOOL_LIB . "/lib_mtool_build_related_table.php");
include_once($MTOOL_LIB . "/lib_mtool_build_setting.php");
include_once($MTOOL_LIB . "/lib_mtool_build_project.php");
include_once($MTOOL_LIB . "/lib_mtool_build_insert.php");
include_once($MTOOL_LIB . "/lib_mtool_build_project_source_output.php");
include_once($MTOOL_LIB . "/lib_mtool_build_include.php");
include_once($MTOOL_LIB . "/lib_mtool_build_indent.php");
include_once($MTOOL_LIB . "/lib_mtool_build_const.php");

include_once($MTOOL_LIB . "/lib_mtool_dropbox_core.php");
include_once($MTOOL_LIB . "/lib_mtool_dropbox.php");
include_once($MTOOL_LIB . "/lib_mtool_uploader.php");
include_once($MTOOL_LIB . "/lib_mtool_update_last_update_timestamp.php");
include_once($MTOOL_LIB . "/lib_mtool_proxy.php");

function UpdateProjectSource($projectPID, $BuildTokenString)
{
	InitializeMtoolBuildResultMessage();
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($projectPID);
	if ($project != NULL) {
		$DABuildToken = new BuildTokenDBAccess();
		$BuildToken = $DABuildToken->GetBuildToken($BuildTokenString, $project->PID);
		
		$output_to_temp_folder            = ($BuildToken->IsOutputToTempFolder          == 1);
		$output_after_copy_to_temp_folder = ($BuildToken->IsOutputAfterCopyToTempFolder == 1);
		$quick_build                      = ($BuildToken->IsQuickBuild                  == 1);
		$mtool_output_debug_message       = ($BuildToken->IsOutputDebugMessage          == 1);
		
		SetMtoolBuildOutputMessageMode($mtool_output_debug_message);
		SetMtoolBuildOutputMessageTokenPID($BuildToken->PID);
		
		if ($BuildToken) {
			
			$DABuildTokenProjectSourceOutput = new BuildTokenProjectSourceOutputDBAccess();
			$BuildTokenProjectSourceOutputList = $DABuildTokenProjectSourceOutput->GetBuildTokenProjectSourceOutputList($BuildToken->PID, $project->PID);
			
			$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
			$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($project->PID); 
			
			for($i = 0 ; $i < count($ProjectSourceOutputList) ; $i++) {
				$ProjectSourceOutput = $ProjectSourceOutputList[$i];
				
				$TargetBuildTokenProjectSourceOutputList = array();
				
				$is_partly_completed = false;
				
				$updateClass_BuildTokenProjectSourceOutput            = NULL;
				$updateFunc_BuildTokenProjectSourceOutput             = NULL;
				$updateProxyServer_BuildTokenProjectSourceOutput      = NULL;
				$updateProxyClient_BuildTokenProjectSourceOutput      = NULL;
				$updateHtml_BuildTokenProjectSourceOutput             = NULL;
				$updateLanguageResource_BuildTokenProjectSourceOutput = NULL;
				if ($BuildTokenProjectSourceOutputList) {
					for($j = 0 ; $j < count($BuildTokenProjectSourceOutputList) ; $j++) {
						$BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutputList[$j];
						
						if ($BuildTokenProjectSourceOutput->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
							switch($BuildTokenProjectSourceOutput->BuildTargetType) {
								case BuildTokenProjectSourceOutputBuildTargetTypeEnum::$DATACLASS:
									$updateClass_BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutput;
									break;
								case BuildTokenProjectSourceOutputBuildTargetTypeEnum::$DA:
									$updateFunc_BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutput;
									break;
								case BuildTokenProjectSourceOutputBuildTargetTypeEnum::$PROXYSERVER:
									$updateProxyServer_BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutput;
									break;
								case BuildTokenProjectSourceOutputBuildTargetTypeEnum::$PROXYCLIENT:
									$updateProxyClient_BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutput;
									break;
								case BuildTokenProjectSourceOutputBuildTargetTypeEnum::$HTML:
									$updateHtml_BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutput;
									break;
								case BuildTokenProjectSourceOutputBuildTargetTypeEnum::$LANGUAGERESOURCE:
									$updateLanguageResource_BuildTokenProjectSourceOutput = $BuildTokenProjectSourceOutput;
									break;
							}
							array_push($TargetBuildTokenProjectSourceOutputList, $BuildTokenProjectSourceOutput);
							
							$is_partly_completed |= ($BuildTokenProjectSourceOutput->IsPartlyCompleted == 1);
						}
					}
				}
				// $updateClass       = checkIfThisIsTargetOfUpdate($ProjectSourceOutput->PID, $ProjectSourceOutputPIDListForUpdateClass);
				// $updateFunc        = checkIfThisIsTargetOfUpdate($ProjectSourceOutput->PID, $ProjectSourceOutputPIDListForUpdateFunc);
				// $updateProxyServer = checkIfThisIsTargetOfUpdate($ProjectSourceOutput->PID, $ProjectSourceOutputPIDListForUpdateProxyServer);
				// $updateProxyClient = checkIfThisIsTargetOfUpdate($ProjectSourceOutput->PID, $ProjectSourceOutputPIDListForUpdateProxyClient);
				// $updateHtml        = checkIfThisIsTargetOfUpdate($ProjectSourceOutput->PID, $ProjectSourceOutputPIDListForUpdateHtml);
				
				if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER) {
					if ($updateClass_BuildTokenProjectSourceOutput) {
						$updateClass_BuildTokenProjectSourceOutput = NULL;
						AddMtoolErrorBuildMessage("=> Proxy Server doesn't include Data Class. Force disabled. Please make data class by DBAccess type and use by combination.");
						AddMtoolErrorBuildMessage("");
					}
				}
				
				if ($updateClass_BuildTokenProjectSourceOutput || $updateFunc_BuildTokenProjectSourceOutput || $updateProxyServer_BuildTokenProjectSourceOutput || $updateProxyClient_BuildTokenProjectSourceOutput || $updateHtml_BuildTokenProjectSourceOutput || $updateLanguageResource_BuildTokenProjectSourceOutput) {
					
					if (check_build_timeout_for_mtool()) {
						// Timeout. Do the rest in next try.
						PrintOutMtoolBuildResultMessage();
						return;
					}
					
					$already_build_count = 0;
					$DABuildTokenCompletedItem = new BuildTokenCompletedItemDBAccess();
					$thisObj = $DABuildTokenCompletedItem->GetCountForTokenAndProjectSourceOutput($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID);
					if ($thisObj) {
						$already_build_count = $thisObj->PID;
					}
					
					if ($already_build_count == 0 && !$is_partly_completed) {
						AddMtoolGeneralBuildMessage("Start Build for Setting " . ($i + 1) . "/" . count($ProjectSourceOutputList));
						if ($quick_build) {
							AddMtoolDebugBuildMessage(" -> Quick Build");
						}
						AddMtoolGeneralBuildMessage("Language: " . GetProjectSourceOutputProgramLanguageCaption($ProjectSourceOutput->ProgramLanguage));
						AddMtoolGeneralBuildMessage("Class Type: " . GetProjectSourceOutputClassTypeCaption($ProjectSourceOutput->ClassType));
						AddMtoolGeneralBuildMessage("Source Output Dir: " . $ProjectSourceOutput->SourceOutputDir);
						if ($output_to_temp_folder) {
							AddMtoolGeneralBuildMessage(" -> Now Output To Temp Folder: " . GetTempFolderForSourceOutput($ProjectSourceOutput->SourceOutputDir));
							
							if ($output_after_copy_to_temp_folder) {
								AddMtoolGeneralBuildMessage(" -> Output after copy to Temp Folder.");
							}
						}
						AddMtoolDebugBuildMessage("Source Template Dir: " . MakeDropboxFolderByNameIfNotBlank($project->DropboxBaseFolderName, $ProjectSourceOutput->SourceTemplateDir));
						if ($ProjectSourceOutput->ProgramLanguage == ProjectSourceOutputProgramLanguageEnum::$PHP) {
							AddMtoolDebugBuildMessage("DB Object Name: " . $project->DBConnectionObjectNameForPHP);
							AddMtoolDebugBuildMessage("Autoload Filename Suffix: " . $ProjectSourceOutput->AutoloadFilenameSuffix);
						}
						AddMtoolGeneralBuildMessage("");
					}
					
					if (UpdateProjectSourceForOneSetting($BuildToken, $project, $ProjectSourceOutput, $updateClass_BuildTokenProjectSourceOutput, $updateFunc_BuildTokenProjectSourceOutput, $updateProxyServer_BuildTokenProjectSourceOutput, $updateProxyClient_BuildTokenProjectSourceOutput, $updateHtml_BuildTokenProjectSourceOutput, $updateLanguageResource_BuildTokenProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, $quick_build)) {
						// Completed
						for ($j = 0 ; $j < count($TargetBuildTokenProjectSourceOutputList) ; $j++) {
							$TargetBuildTokenProjectSourceOutput = $TargetBuildTokenProjectSourceOutputList[$j];
							
							if (!$DABuildTokenProjectSourceOutput->UpdateCompletedFlag($TargetBuildTokenProjectSourceOutput)) {
								AddMtoolErrorBuildMessage("Internal Error! Something Strange. Failed to set Completed Flag");
							}
						}
					}
					AddMtoolGeneralBuildMessage("");
				}
			}
			
		} else {
			AddMtoolErrorBuildMessage("No Build Info... Something Strange. Please ask administrator if this continues.");
		}
	} else {
		AddMtoolErrorBuildMessage("No Project Info... Something Strange. Please ask administrator if this continues.");
	}
	PrintOutMtoolBuildResultMessage();
}

function UpdateProjectSourceForOneSetting($BuildToken, $project, $ProjectSourceOutput, $updateClass_BuildTokenProjectSourceOutput, $updateFunc_BuildTokenProjectSourceOutput, $updateProxyServer_BuildTokenProjectSourceOutput, $updateProxyClient_BuildTokenProjectSourceOutput, $updateHtml_BuildTokenProjectSourceOutput, $updateLanguageResource_BuildTokenProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, $quick_build)
{
	global $createdBaseClasses;
	global $MTOOL_INSERT_ID_PROPERTY_NAME;
	global $ENUM_UNKNOWN_VALUE_NAME;
	global $CONFIG_TOKEN_VALUE_NAME;
	
	// Check Server Setting Selection for Client
	if ($ProjectSourceOutput->IsProxyClient()) {
		// Proxy Client
		if ($ProjectSourceOutput->TargtServerPSOProgramLanguage == "") {
			AddMtoolErrorBuildMessage("Corresponding Server Project is not selected on Project Source Output Setting. Please check and update setting.");
			return true;
		}
	}
	
	$DABuildTokenProjectSourceOutput = new BuildTokenProjectSourceOutputDBAccess();
	$DABuildTokenCompletedItem = new BuildTokenCompletedItemDBAccess();
	
	$DALastBuild = new LastBuildDBAccess();
	$LastBuild = new LastBuildData();
	$LastBuild->ProjectPID = $project->PID;
	$LastBuild->ProjectSourceOutputPID = $ProjectSourceOutput->PID;
	if ($quick_build) {
		$LastBuild->ToTempFolder = "1";
	} else {
		$LastBuild->ToTempFolder = "0";
	}
	if ($output_after_copy_to_temp_folder) {
		$LastBuild->OutputAfterCopyToTempFolder = "1";
	} else {
		$LastBuild->OutputAfterCopyToTempFolder = "0";
	}
	
	// $autoloadCode = "";
	
	if ($updateClass_BuildTokenProjectSourceOutput && $updateClass_BuildTokenProjectSourceOutput->IsPartlyCompleted == 0) {
		if (check_build_timeout_for_mtool()) {
			return false;				// Timeout. Do the rest in next try.
		}
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$DATACLASS);
		
		InitializeToSkipUnusedDataClassForProxy($project, $ProjectSourceOutput);
		
		$DAdataclass = new dataclassDBAccess();
		$DAdataclassfields = new dataclassfieldsDBAccess();
		$dataclasslist = $DAdataclass->GetdataclassList($project->PID);
		for($k = 0 ; $k < count($dataclasslist); $k++) {
			$dataclass = $dataclasslist[$k];
			
			if (check_build_timeout_for_mtool()) {
				return false;				// Timeout. Do the rest in next try.
			}
			if (check_if_this_item_is_already_build_by_mtool($BuildTokenCompletedItemList, $dataclass->PID)) {
				continue;
			}
			
			AddMtoolGeneralBuildMessage(" Data Class");
			AddMtoolGeneralBuildMessage(" => Name:" . $dataclass->name);
			AddMtoolDebugBuildMessage(" => Project PID:" . $dataclass->ProjectPID . "  PID:" . $dataclass->PID);
			
			$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$DATACLASS;
			$LastBuild->EachTargetPID = $dataclass->PID;
			if (check_if_skip_for_quick_build($quick_build, $LastBuild, $dataclass->LastModifiedDT)) {
				AddMtoolGeneralBuildMessage(" => Skip");
				AddMtoolGeneralBuildMessage("");
				continue;
			}
			
			if (CheckIfSkipUnusedDataClassForProxy($project, $ProjectSourceOutput, $dataclass, $dataclasslist)) {
				AddMtoolGeneralBuildMessage(" => Skip. This Data Class Source is not created because Proxy Client is not exist");
				continue;
			}
			
			// $need_to_skip_for_any_language = false;
			// switch ($ProjectSourceOutput->ProgramLanguage) {
			// 	case ProjectSourceOutputProgramLanguageEnum::$PHP:
			// 		AddMtoolGeneralBuildMessage(" => Skip. This Data Class Source is not created because PHP is not a target");
			// 		$need_to_skip_for_any_language = true;
			// 		break;
			// 	case ProjectSourceOutputProgramLanguageEnum::$CS:
			// 	case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			// 	case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			// 	case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			// 	case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			// 		break;
			// 	default:
			// 		AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			// 		return;
			// }
			// if ($need_to_skip_for_any_language) {
			// 	continue;
			// }
			
			$CHeaderIncludeForClassOrEnumList = array();
			$HeaderSourceForSerializeAndDeserializeFromListForDataClassList = array();
			
			$dataclassName = CreateDataClassName($dataclass->name);
			$dataclassListName = GetMtoolDataListClassName($ProjectSourceOutput, $dataclassName);
			
			$inheritClassCode = "";
			if (trim($dataclass->InheritParentDataClassName) != "") {
				$inheritClassName = CreateDataClassName(trim($dataclass->InheritParentDataClassName));
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						$inheritClassCode = " extends " . $inheritClassName;
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
						$inheritClassCode = " : " . $inheritClassName . ", ";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						$inheritClassCode = " extends " . $inheritClassName;
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						$inheritClassCode = " : " . $inheritClassName;
						AddMtoolCHeaderIncludeForMiscClass($ProjectSourceOutput, $CHeaderIncludeForClassOrEnumList, $inheritClassName);
						break;
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						$inheritClassCode = " : " . $inheritClassName;
						break;
					default:
						AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
						return;
				}
			}
			if ($inheritClassCode == "") {
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
						$inheritClassCode = " : ";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
						$inheritClassCode = " : NSObject";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						$inheritClassCode = " : ProxyClientDataClassBase";
						break;
					default:
						AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
						return;
				}
			}
			
			$dataclassfilename = GetMtoolDataClassFileName($ProjectSourceOutput, $dataclass);
			$dataclasslistfilename = GetMtoolDataClassListFileName($ProjectSourceOutput, $dataclass);
			
			$automaticallyOutputSource = "";
			$property_copy_from_value_source = "";
			$property_copy_from_raise_event_source = "";
			GetclassfieldsSource($BuildToken, $project, $ProjectSourceOutput, $dataclass, $automaticallyOutputSource, $property_copy_from_value_source, $property_copy_from_raise_event_source, $CHeaderIncludeForClassOrEnumList, $HeaderSourceForSerializeAndDeserializeFromListForDataClassList);
			
			$DAdbtable = new dbtableDBAccess();
			$DAdbtablecolumns = new dbtablecolumnsDBAccess();
			
			$constSource = "";
			$getConstStringFunctionSource = "";
			
			$table = $DAdbtable->GetdbtableByName($project->PID, $dataclass->name);
			if ($table != NULL) {
				$dbtablecolumnlist = $DAdbtablecolumns->GetdbtablecolumnsList($project->PID, $table->PID);
				if ($dbtablecolumnlist != NULL) {
					for($j = 0 ; $j < count($dbtablecolumnlist); $j++) {
						$dbtablecolumn = $dbtablecolumnlist[$j];
						
						if (preg_match("/enum\s*\((.+)\)/i", $dbtablecolumn->datatype, $matches)) {
							$thisConstSource = "";
							$thisConstStringSource = "";
							$thisConstClassStringSource = "";
							$enumValuesString = $matches[1];
							$enumValueList = preg_split("/[\"']\s*,\s*[\"']/", $enumValuesString);
							if (count($enumValueList) > 0) {
								$enumValueList[0] = preg_replace("/^\s*[\"']/", "", $enumValueList[0]);
								$enumValueList[count($enumValueList) - 1] = preg_replace("/[\"']\s*$/", "", $enumValueList[count($enumValueList) - 1]);
							}
							$is_matched = false;
							for($l = 0 ; $l < count($enumValueList) ; $l++) {
								$enumValue = $enumValueList[$l];
								
								if ($enumValue == $ENUM_UNKNOWN_VALUE_NAME) {
									$is_matched = true;
									break;
								}
							}
							if (!$is_matched) {
								array_unshift($enumValueList, $ENUM_UNKNOWN_VALUE_NAME);
							}
							
							$num_append_num = 0;
							$enum_name_output_log_list = array();
							for($l = 0 ; $l < count($enumValueList) ; $l++) {
								$enumValue = $enumValueList[$l];
								
								$enumName = "";
								$enumValueChars = preg_split("//", preg_replace("/ /", "_", $enumValue));
								for($m = 0 ; $m < count($enumValueChars); $m++) {
									$thisChar = $enumValueChars[$m];
									if (preg_match("/^[a-zA-Z0-9_\-]$/", $thisChar)) {
										$enumName .= $thisChar;
									}
								}
								if ($enumName == "") {
									$enumName = "DEFAULT";
								}
								$comma = "";
								if ($l != (count($enumValueList) - 1)) {
									$comma = ",";
								}
								$enumName = strtoupper($enumName);
								
								$BaseEnumName = $enumName;
								while (in_array($enumName, $enum_name_output_log_list)) {
									$num_append_num++;
									$enumName = $BaseEnumName . $num_append_num;
								}
								array_push($enum_name_output_log_list, $enumName);
								
								$thisConstSource .= GetMtoolOneConstDefinition($BuildToken, $project, $ProjectSourceOutput, "CONST-ONE-DEFINITION", $enumName, $enumValue, $l, $comma);
								$thisConstStringSource .= GetMtoolOneConstDefinition($BuildToken, $project, $ProjectSourceOutput, "CONST-ONE-STRING-DEFINITION", $enumName, $enumValue, $l, $comma);
								
								switch ($ProjectSourceOutput->ProgramLanguage) {
									case ProjectSourceOutputProgramLanguageEnum::$PHP:
										break;
									case ProjectSourceOutputProgramLanguageEnum::$CS:
									case ProjectSourceOutputProgramLanguageEnum::$JAVA:
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
									case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
										if ($thisConstClassStringSource !== NULL) {
											if ($thisConstClassStringSource != "") {
												$thisConstClassStringSource .= ", ";
											}
											$thisConstClassStringSource .= GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $enumValue);
										}
										break;
									default:
										AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
										return;
								}
								
							}
							if ($thisConstSource != "" || $thisConstStringSource != "") {
								$thisEnumName = GetEnumName($dataclass->name, $dbtablecolumn->name);
								
								$enum_count = -1;
								switch ($ProjectSourceOutput->ProgramLanguage) {
									case ProjectSourceOutputProgramLanguageEnum::$PHP:
									case ProjectSourceOutputProgramLanguageEnum::$CS:
									case ProjectSourceOutputProgramLanguageEnum::$JAVA:
										$enum_count = count($enumValueList);
										break;
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
									case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
										$enum_count = count($enumValueList) + 1;
										break;
									default:
										AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
										return;
								}
								
								$ReplaceParameterList = array(
										array("KEY"=>"__CLASS_NAME__", "VALUE"=>$thisEnumName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
										array("KEY"=>"__CONST_DEFINITIONS__", "VALUE"=>$thisConstSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
										array("KEY"=>"__CONST_STRING_DEFINITIONS__", "VALUE"=>$thisConstStringSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
										array("KEY"=>"__DEFINE_COUNT__", "VALUE"=>$enum_count, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
										array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
									);
								$template_const = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "CONST");
								
								switch ($ProjectSourceOutput->ProgramLanguage) {
									case ProjectSourceOutputProgramLanguageEnum::$PHP:
									case ProjectSourceOutputProgramLanguageEnum::$CS:
										$template_const = ReplaceOutputSourceInfoByKeyValue($template_const, $ReplaceParameterList);
										
										$constSource .= $template_const;
										
										break;
									case ProjectSourceOutputProgramLanguageEnum::$JAVA:
										$enumclass_filename = $thisEnumName . ".java";
										$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $enumclass_filename, $dataclass->StoreBasePath, $ReplaceParameterList, $template_const, $output_to_temp_folder, $output_after_copy_to_temp_folder);
										break;
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
										$enumclass_filename = $thisEnumName . ".h";
										$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $enumclass_filename, $dataclass->StoreBasePath, $ReplaceParameterList, $template_const, $output_to_temp_folder, $output_after_copy_to_temp_folder);
										break;
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
										$enumclass_filename = $thisEnumName . ".m";
										$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $enumclass_filename, $dataclass->StoreBasePath, $ReplaceParameterList, $template_const, $output_to_temp_folder, $output_after_copy_to_temp_folder);
										break;
									case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
										$enumclass_filename = $thisEnumName . ".swift";
										$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $enumclass_filename, $dataclass->StoreBasePath, $ReplaceParameterList, $template_const, $output_to_temp_folder, $output_after_copy_to_temp_folder);
										break;
									default:
										AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
										return;
								}
								switch ($ProjectSourceOutput->ProgramLanguage) {
									case ProjectSourceOutputProgramLanguageEnum::$PHP:
										break;
									case ProjectSourceOutputProgramLanguageEnum::$CS:
										$template_get_string = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "CONST-GET-STRING");
										$template_get_string = ReplaceOutputSourceInfoByKeyValue($template_get_string, array(
												array("KEY"=>"__CLASS_NAME__", "VALUE"=>$thisEnumName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
												array("KEY"=>"__FUNC_TO_GET_STRING_FROM_ENUM__", "VALUE"=>GetStringFromEnumFunctionName($dataclass->name, $dbtablecolumn->name), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
												array("KEY"=>"__FUNC_TO_GET_ENUM_FROM_STRING__", "VALUE"=>GetEnumFromStringFunctionName($dataclass->name, $dbtablecolumn->name), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
												array("KEY"=>"__CLASS_STRINGS__", "VALUE"=>$thisConstClassStringSource, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
											));
										
										if ($getConstStringFunctionSource == "") {
											$getConstStringFunctionSource .= "\n";
										}
										$getConstStringFunctionSource .= $template_get_string;
										break;
									case ProjectSourceOutputProgramLanguageEnum::$JAVA:
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
									case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
									case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
										break;
									default:
										AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
										return;
								}
							}
						}
					}
				}
			}
			DeleteBuildSourceCache(
				$project->PID,
				BuildSourceCacheSourceTypeEnum::$DATACLASS,
				$dataclass->PID,				// Data Class PID
				-1								// DA Class PID
			);
			
			$ReplaceParameterList = array(
			  		array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			  		array("KEY"=>"__CLASS_NAME__", "VALUE"=>$dataclassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			  		array("KEY"=>"__INHERIT_CLASS__", "VALUE"=>$inheritClassCode, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			  		array("KEY"=>"__AUTOMATED_CODE_COMES_HERE__", "VALUE"=>$automaticallyOutputSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			  		array("KEY"=>"__CONST_DEFINITION__", "VALUE"=>$constSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__GET_CONST_STRING__", "VALUE"=>$getConstStringFunctionSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__COPY_FROM_VALUE__", "VALUE"=>$property_copy_from_value_source, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__COPY_FROM_RAISE_EVENT__", "VALUE"=>$property_copy_from_raise_event_source, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__SERIALIZE__", "VALUE"=>GetHeaderSourceForSerializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeFromListForDataClassList), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__SERIALIZE_DEFINE__", "VALUE"=>GetHeaderSourceForSerializetDefineForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeFromListForDataClassList), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__INITIALIZE_PROPERTY__", "VALUE"=>GetHeaderSourceForInitializePropertyForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeFromListForDataClassList), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__DESERIALIZE__", "VALUE"=>GetHeaderSourceForDeserializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeFromListForDataClassList), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__INCLUDE_CLASS_OR_ENUM_HEADER__", "VALUE"=>GetHeaderSourceForMiscClassFromList(HeaderSourceForMiscClassTypeEnum::$DATACLASS, "DATACLASS-INCLUDE-PROPERTY-CLASS-HEADER", $BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForClassOrEnumList), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
				);
			$source_template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS");
			$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $dataclassfilename, $dataclass->StoreBasePath, $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			if ($result->Success) {
				// OK
				SaveIntoBuildSourceCache(
					$project->PID,
					$dataclassfilename,
					BuildSourceCacheSourceTypeEnum::$DATACLASS,
					$dataclass->PID,				// Data Class PID
					-1,								// DA Class PID
					$result->Source
				);
				
				set_last_build_time($LastBuild);
				
			} else {
				AddMtoolErrorBuildMessage(" -> Error! Failed to update");
			}
			
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					$ReplaceParameterList = array(
							array("KEY"=>"__ARRAY_DEFINE__", "VALUE"=>GetMtoolListDefinitionSource($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
							array("KEY"=>"__ARRAY_INITIALIZE__", "VALUE"=>GetMtoolListInitializeSource($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
							array("KEY"=>"__ARRAY_INITIALIZE_BY_DESERIALIZE__", "VALUE"=>GetMtoolListInitializeSourceByDeserialize($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
							array("KEY"=>"__ARRAY_SERIALIZE__", "VALUE"=>GetMtoolListSerializeSource($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
					  		array("KEY"=>"__ARRAY_ITEM_CLASS_", "VALUE"=>$dataclassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					  		array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					  		array("KEY"=>"__CLASS_NAME__", "VALUE"=>$dataclassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__LIST_DATA_CLASS_NAME__", "VALUE"=>$dataclassListName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						);
					$source_template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST");
					$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $dataclasslistfilename, $dataclass->StoreBasePath, $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
					if ($result->Success) {
						// OK
						SaveIntoBuildSourceCache(
							$project->PID,
							$dataclasslistfilename,
							BuildSourceCacheSourceTypeEnum::$DATACLASS,
							$dataclass->PID,				// Data Class PID
							-1,								// DA Class PID
							$result->Source
						);
						
					} else {
						AddMtoolErrorBuildMessage(" -> Error! Failed to update");
					}
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			
			save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$DATACLASS, $dataclass->PID, NULL);
		}
		
		// Update PartlyCompletedFlag for this Output Source
		$DABuildTokenProjectSourceOutput->UpdatePartlyCompletedFlag($updateClass_BuildTokenProjectSourceOutput);
		
	} else {
		// AddMtoolGeneralBuildMessage(" => Data Class is not a target");
	}
	
	if ($updateFunc_BuildTokenProjectSourceOutput && $updateFunc_BuildTokenProjectSourceOutput->IsPartlyCompleted == 0) {
		if (check_build_timeout_for_mtool()) {
			return false;				// Timeout. Do the rest in next try.
		}
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$DA);
		
		$DAda = new daDBAccess();
		$dalist = $DAda->GetdaList($project->PID);
		for($j = 0 ; $j < count($dalist); $j++) {
			$da = $dalist[$j];
			
			if (check_build_timeout_for_mtool()) {
				return false;				// Timeout. Do the rest in next try.
			}
			if (check_if_this_item_is_already_build_by_mtool($BuildTokenCompletedItemList, $da->PID)) {
				continue;
			}
			
			AddMtoolGeneralBuildMessage(" Data Access Class");
			AddMtoolGeneralBuildMessage(" => Name:" . $da->name);
			AddMtoolDebugBuildMessage(" => Project PID:" . $da->ProjectPID . " PID:" . $da->PID . " StoreBasePath:" . $da->StoreBasePath);
			
			$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$DA;
			$LastBuild->EachTargetPID = $da->PID;
			if (check_if_skip_for_quick_build($quick_build, $LastBuild, $da->LastModifiedDT)) {
				AddMtoolGeneralBuildMessage(" => Skip");
				AddMtoolGeneralBuildMessage("\n");
				continue;
			}
			
			$databaseaccessclassName = CreateDatabaseAccessClassName($da->name);
			
			$localfilename = GetMtoolDAFilenameForBuild($ProjectSourceOutput, $da);
			
			$automaticallyOutputSource = GetdafuncSource($BuildToken, $project, $ProjectSourceOutput, $da);
			
			DeleteBuildSourceCache(
				$project->PID,
				BuildSourceCacheSourceTypeEnum::$DA,
				-1,							// Data Class PID
				$da->PID					// DA Class PID
			);
			
			$ReplaceParameterList = array(
			  		array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			  		array("KEY"=>"__CLASS_NAME__", "VALUE"=>$databaseaccessclassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__AUTOMATED_CODE_COMES_HERE__", "VALUE"=>$automaticallyOutputSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
					// array("KEY"=>"__DB_OBJECT__", "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
				);
			$source_template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS");
			$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, $da->StoreBasePath, $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			if ($result->Success) {
				// OK
				SaveIntoBuildSourceCache(
					$project->PID,
					$localfilename,
					BuildSourceCacheSourceTypeEnum::$DA,
					-1,							// Data Class PID
					$da->PID,					// DA Class PID
					$result->Source
				);
				
				set_last_build_time($LastBuild);
				
			} else {
				AddMtoolErrorBuildMessage(" -> Error! Failed to update");
			}
			
			save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$DA, $da->PID, NULL);
		}
		
		// Update PartlyCompletedFlag for this Output Source
		$DABuildTokenProjectSourceOutput->UpdatePartlyCompletedFlag($updateFunc_BuildTokenProjectSourceOutput);
		
	} else {
		// AddMtoolGeneralBuildMessage(" => DA Class is not a target");
	}
	
	if ($updateProxyServer_BuildTokenProjectSourceOutput && $updateProxyServer_BuildTokenProjectSourceOutput->IsPartlyCompleted == 0) {
		if (check_build_timeout_for_mtool()) {
			return false;				// Timeout. Do the rest in next try.
		}
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$PROXYSERVER);
		
		$DAda = new daDBAccess();
		$dalist = $DAda->GetdaList($project->PID);
		for($j = 0 ; $j < count($dalist); $j++) {
			$da = $dalist[$j];
			
			if (check_build_timeout_for_mtool()) {
				return false;				// Timeout. Do the rest in next try.
			}
			if (check_if_this_item_is_already_build_by_mtool($BuildTokenCompletedItemList, $da->PID)) {
				continue;
			}
			
			AddMtoolGeneralBuildMessage(" Proxy Server");
			AddMtoolGeneralBuildMessage(" => Name:" . $da->name);
			AddMtoolDebugBuildMessage(" => Project PID:" . $da->ProjectPID . " PID:" . $da->PID . " StoreBasePath:" . $da->StoreBasePath);
			
			$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$PROXYSERVER;
			$LastBuild->EachTargetPID = $da->PID;
			if (check_if_skip_for_quick_build($quick_build, $LastBuild, $da->LastModifiedDT)) {
				AddMtoolGeneralBuildMessage(" => Skip");
				AddMtoolGeneralBuildMessage("");
				continue;
			}
			
			MakeproxyserverSource($BuildToken, $project, $ProjectSourceOutput, $da, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			
			save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$PROXYSERVER, $da->PID, NULL);
			
			set_last_build_time($LastBuild);
		}
		
		// Output Custom Functions
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$CUSTOMPROXYSERVER);
		
		// Output Custom Functions
		$DAda = new daDBAccess();
		$DAdafunc = new dafuncDBAccess();
		$DAdaCustomProxy = new daCustomProxyDBAccess();
		$DAdaCustomProxyFuncDBAccess = new daCustomProxyFuncDBAccess();
		$daCustomProxyList = $DAdaCustomProxy->GetdaCustomProxyList($project->PID);
		for($i = 0 ; $i < count($daCustomProxyList); $i++) {
			$daCustomProxy = $daCustomProxyList[$i];
			
			if (check_build_timeout_for_mtool()) {
				return false;				// Timeout. Do the rest in next try.
			}
			if (check_if_this_item_is_already_build_by_mtool($BuildTokenCompletedItemList, $daCustomProxy->PID)) {
				continue;
			}
			
			AddMtoolGeneralBuildMessage(" Custom Proxy Server");
			AddMtoolGeneralBuildMessage(" => Name:" . $daCustomProxy->name);
			AddMtoolDebugBuildMessage(" => Project PID:" . $da->ProjectPID . " PID:" . $daCustomProxy->PID);
			
			$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$CUSTOMPROXYSERVER;
			$LastBuild->EachTargetPID = $daCustomProxy->PID;
			if (check_if_skip_for_quick_build($quick_build, $LastBuild, $daCustomProxy->LastModifiedDT)) {
				continue;
			}
			
			// AddMtoolGeneralBuildMessage("Custom Proxy: " . $daCustomProxy->name);
			if (CheckIfItIsATargetOfCustomProxy($project->PID, $daCustomProxy->PID, $ProjectSourceOutput->PID)) {
				// Yes. It's a target of output
			} else {
				// Not a target of output.
				// AddMtoolGeneralBuildMessage("-> Not a target of output");
				continue;
			}
			
			$transaction = ($daCustomProxy->InTransaction == 1);
			
			$localfilename = CreateProxyServerFileName($ProjectSourceOutput->ProgramLanguage, $ProjectSourceOutput->CustomFileExtention, $daCustomProxy->basename, $daCustomProxy->name);
			
			$result_initialize = "";
			$func_source = "";
			$func_result_param = "";
			$THIS_RESULT_NAME = "thisresult";
			
			$string_json_begin  = "{";
			$ProxyParameterFormat = $string_json_begin;
			$ProxyParameterFormatLineList = array();
			$ProxyParameterExample = "__PREFIX_FOR_EXAMPLE__";
			$ProxyParameterExampleLineList = array();
			
			InitializeTopLevelSecurityCheckFormatAndExampleList($BuildToken, $project, $ProjectSourceOutput, $daCustomProxy->AuthType, $daCustomProxy->SingleGetFuncPID, $ProxyParameterFormatLineList, $ProxyParameterExampleLineList);
			// array_push($ProxyParameterFormatLineList, GetProxyParameterFormatForToken());
			// array_push($ProxyParameterExampleLineList, GetProxyParameterExampleForToken());
			
			$ProxyResultFormat = $string_json_begin;
			$ProxyResultFormatLineList = array();
			AddProxyResultFormatLineListForTopLevel($ProxyResultFormatLineList);
			
			$ProxyResultExample = $string_json_begin;
			$ProxyResultExampleLineList = array();
			
			$InsertIDNoList = array();
			
			$daCustomProxyFuncList = $DAdaCustomProxyFuncDBAccess->GetdaCustomProxyFuncList($project->PID, $daCustomProxy->PID);
			for($j = 0 ; $j < count($daCustomProxyFuncList); $j++) {
				$daCustomProxyFunc = $daCustomProxyFuncList[$j];
				
				$no = ($j + 1);
				$ForList = $daCustomProxyFunc->ForList();
				
				$dafunc = $DAdafunc->Getdafunc($daCustomProxyFunc->dafuncPID, $project->PID);
				if ($dafunc != NULL) {
					
					$check_function_result = true;
					switch($dafunc->ActionType)
					{
						case dafuncActionTypeEnum::$SELECTSINGLE:
						case dafuncActionTypeEnum::$SELECTLIST:
							break;
						case dafuncActionTypeEnum::$INSERT:
							$continue_even_if_failed_to_insert = ($daCustomProxy->ContinueEvenIfFailedToInsert == 1);
							$check_function_result = !$continue_even_if_failed_to_insert;
							break;
						case dafuncActionTypeEnum::$UPDATE:
						case dafuncActionTypeEnum::$DELETE:
							break;
						default:
							AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
							return;
					}
					
					$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
					$da = $DAda->Getda($dafunc->daPID, $project->PID);
					if ($da != NULL) {
						
						$thisStep_ProxyParameterFormat = "";
						$thisStep_ProxyParameterExample = "";
						$thisStep_ProxyResultFormat = "";
						$thisStep_ProxyResultExample = "";
						
						$template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-COMMENT-BETWEEN-CUSTOM-PROXY-STEPS-START");
						$template = ReplaceOutputSourceInfoByKeyValue($template, array(
								array("KEY"=>"__NO__", "VALUE"=>$no, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));
						
						$this_func_source  = $template;
						$this_func_source .= GetMakeproxyserverFunctionSource($BuildToken, $project, $ProjectSourceOutput, $da, $da->name, $dafunc, $sourceFunctionName, $no, $THIS_RESULT_NAME, false, $ForList, $thisStep_ProxyParameterFormat, $thisStep_ProxyParameterExample, $thisStep_ProxyResultFormat, $thisStep_ProxyResultExample, $daCustomProxy->AuthType, $daCustomProxy->SingleGetFuncPID, $check_function_result, $transaction, $output_to_temp_folder, $output_after_copy_to_temp_folder);
						
						$template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-COMMENT-BETWEEN-CUSTOM-PROXY-STEPS-END");
						$template = ReplaceOutputSourceInfoByKeyValue($template, array(
								array("KEY"=>"__NO__", "VALUE"=>$no, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));
						$this_func_source .= $template;
						
						//if ($daCustomProxyFunc->AddIndentCount > 0) {
						//	$this_indent = str_repeat("\t", $daCustomProxyFunc->AddIndentCount);
							
						//	$this_lines_splitted = preg_split("/\r?\n/", $this_func_source);
						//	$this_lines_buffer = array();
						//	for($k = 0 ; $k < count($this_lines_splitted); $k++) {
						//		$this_line = $this_lines_splitted[$k];
						//		
						//		if (($k == 0) ||
						//		    ($k == count($this_lines_splitted) - 1) ||
						//			(
						//				($k == count($this_lines_splitted) - 2) &&
						//				($this_lines_splitted[count($this_lines_splitted) - 1] == "")
						//			)
						//			) {
						//			array_push($this_lines_buffer, $this_line);
						//		} else {
						//			array_push($this_lines_buffer, $this_indent . $this_line);
						//		}
						//	}
						//	$this_func_source = implode("\n", $this_lines_buffer);
							
							
							// if ($this_func_source != "") {
							// 	$this_func_source = $this_indent . $this_func_source;
							// }
							// $this_func_source = preg_replace("/(\r?\n)/", "$1" . $this_indent, $this_func_source);
							// $this_func_source = preg_replace("/(\r?\n)$this_indent$/", "$1", $this_func_source);
						//}
						
						$func_source .= $this_func_source;
						
						switch($dafunc->ActionType)
						{
							case dafuncActionTypeEnum::$SELECTSINGLE:
							case dafuncActionTypeEnum::$SELECTLIST:
								$result_initialize .= GetMakeproxyserverValueInitializeSource($BuildToken, $project, $ProjectSourceOutput, $no, $THIS_RESULT_NAME, $ForList);
								$func_result_param .= GetMakeproxyserverResultParamSource($BuildToken, $project, $ProjectSourceOutput, $no, $THIS_RESULT_NAME);
								break;
							case dafuncActionTypeEnum::$INSERT:
							case dafuncActionTypeEnum::$UPDATE:
							case dafuncActionTypeEnum::$DELETE:
								break;
							default:
								AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
								return;
						}
						
						switch($dafunc->ActionType)
						{
							case dafuncActionTypeEnum::$SELECTSINGLE:
							case dafuncActionTypeEnum::$SELECTLIST:
								break;
							case dafuncActionTypeEnum::$INSERT:
								$result_initialize .= GetMakeproxyserverValueInitializeSource($BuildToken, $project, $ProjectSourceOutput, $no, $MTOOL_INSERT_ID_PROPERTY_NAME, $ForList);
								break;
							case dafuncActionTypeEnum::$UPDATE:
							case dafuncActionTypeEnum::$DELETE:
								break;
							default:
								AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
								return;
						}
						
						if ($ForList) {
							$array_string_start = GetArrayStringStartProxyParamOrResult();
							$array_string_end = GetArrayStringEndProxyParamOrResult();
							
							$thisStep_ProxyParameterFormat  = $array_string_start . $thisStep_ProxyParameterFormat  . $array_string_end;
							$thisStep_ProxyParameterExample = $array_string_start . $thisStep_ProxyParameterExample . $array_string_end;
							$thisStep_ProxyResultFormat     = $array_string_start . $thisStep_ProxyResultFormat     . $array_string_end;
							$thisStep_ProxyResultExample    = $array_string_start . $thisStep_ProxyResultExample    . $array_string_end;
						}
						$thisStep_ProxyParameterFormat = ReplaceOutputSourceInfoByKeyValue($thisStep_ProxyParameterFormat, array(
								array("KEY"=>"__PARAM_OF_OBJECT__", "VALUE"=>GetReplacementStringForParamOfObjectForStep($no), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));
						$thisStep_ProxyParameterExample = ReplaceOutputSourceInfoByKeyValue($thisStep_ProxyParameterExample, array(
								array("KEY"=>"__PARAM_OF_OBJECT__", "VALUE"=>GetReplacementStringForParamOfObjectForStep($no), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));
						
						array_push($ProxyParameterFormatLineList, "\n    \"" . GetCustomProxyRequestParamObjectName($no) . "\": " . $thisStep_ProxyParameterFormat);
						array_push($ProxyParameterExampleLineList, "\n    \"" . GetCustomProxyRequestParamObjectName($no) . "\"__DELIMITER_FOR_CUSTOM_PROXY__ " . $thisStep_ProxyParameterExample);
						
						switch($dafunc->ActionType)
						{
							case dafuncActionTypeEnum::$SELECTSINGLE:
							case dafuncActionTypeEnum::$SELECTLIST:
								array_push($ProxyResultFormatLineList, "\n    \"Result" . $no . "\": " . $thisStep_ProxyResultFormat);
								
								array_push($ProxyResultExampleLineList, "\n    \"Result" . $no . "\"=> " . $thisStep_ProxyResultExample);
								break;
							case dafuncActionTypeEnum::$INSERT:
							case dafuncActionTypeEnum::$UPDATE:
							case dafuncActionTypeEnum::$DELETE:
								break;
							default:
								AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
								return;
						}
						
						switch($dafunc->ActionType)
						{
							case dafuncActionTypeEnum::$SELECTSINGLE:
							case dafuncActionTypeEnum::$SELECTLIST:
							case dafuncActionTypeEnum::$UPDATE:
							case dafuncActionTypeEnum::$DELETE:
								break;
							case dafuncActionTypeEnum::$INSERT:
								AddProxyResultFormatLineListForInsertID($ProxyResultFormatLineList, $no, $ForList);
								array_push($InsertIDNoList, $no);
								break;
							default:
								AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
								return;
						}
						
					}
				}
			}
			$string_json_end = "\n}";
			$ProxyParameterFormat .= implode(",", $ProxyParameterFormatLineList);
			$ProxyParameterFormat .= $string_json_end;
			$ProxyParameterExample .= implode(",", $ProxyParameterExampleLineList);
			$ProxyParameterExample .= "__SUFFIX_FOR_EXAMPLE__";
			$ProxyResultFormat .= implode(",", $ProxyResultFormatLineList);
			$ProxyResultFormat .= $string_json_end;
			$ProxyResultExample .= implode(",", $ProxyResultExampleLineList);
			$ProxyResultExample .= $string_json_end;
			
			MakeproxyserverSourceWriteToFile($BuildToken, $project, $ProjectSourceOutput, $da, NULL, $daCustomProxy, $daCustomProxyFuncList, $localfilename, $result_initialize, $func_source, $func_result_param, $transaction, $output_to_temp_folder, $output_after_copy_to_temp_folder, $ProxyParameterFormat, $ProxyParameterExample, $ProxyResultFormat, $ProxyResultExample, $InsertIDNoList, $daCustomProxy->AuthType, $daCustomProxy->SingleGetFuncPID);
			
			save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$CUSTOMPROXYSERVER, $daCustomProxy->PID, NULL);
		}
		
		// Update PartlyCompletedFlag for this Output Source
		$DABuildTokenProjectSourceOutput->UpdatePartlyCompletedFlag($updateProxyServer_BuildTokenProjectSourceOutput);
	}
	
	if ($updateProxyClient_BuildTokenProjectSourceOutput && $updateProxyClient_BuildTokenProjectSourceOutput->IsPartlyCompleted == 0) {
		if (check_build_timeout_for_mtool()) {
			return false;				// Timeout. Do the rest in next try.
		}
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$PROXYCLIENT);
		
		$anyOutput = false;
		$DAda = new daDBAccess();
		$dalist = $DAda->GetdaList($project->PID);
		for($j = 0 ; $j < count($dalist); $j++) {
			$da = $dalist[$j];
			
			if (check_build_timeout_for_mtool()) {
				return false;				// Timeout. Do the rest in next try.
			}
			if (check_if_this_item_is_already_build_by_mtool_with_anyOutputOption($BuildTokenCompletedItemList, $da->PID, $anyOutput)) {
				continue;
			}
			
			AddMtoolGeneralBuildMessage(" Proxy Client");
			AddMtoolGeneralBuildMessage(" => Name:" . $da->name);
			AddMtoolDebugBuildMessage(" => Project PID:" . $da->ProjectPID . " PID:" . $da->PID . " StoreBasePath:" . $da->StoreBasePath);
			
			$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$PROXYCLIENT;
			$LastBuild->EachTargetPID = $da->PID;
			if (check_if_skip_for_quick_build($quick_build, $LastBuild, $da->LastModifiedDT)) {
				AddMtoolGeneralBuildMessage(" => Skip");
				AddMtoolGeneralBuildMessage("");
				continue;
			}
			
			$localfilename = MakeMtoolProxyClientFileName($ProjectSourceOutput, $da->name);
			
			$CHeaderIncludeForProxyResultList = array();
			$CHeaderIncludeForRequestParamsList = array();
			$CHeaderIncludeForClassesInFunctionList = array();
			$CHeaderIncludeForDefineDelegate = array();
			$automaticallyOutputSource = MakeproxyclientSource($BuildToken, $project, $ProjectSourceOutput, $da, $da->name, $anyOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, $CHeaderIncludeForProxyResultList, $CHeaderIncludeForRequestParamsList, $CHeaderIncludeForClassesInFunctionList, $CHeaderIncludeForDefineDelegate);
			
			if ($automaticallyOutputSource != "") {
				$ReplaceParameterList = array(
						array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$da->name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__BASE_URL__", "VALUE"=>$ProjectSourceOutput->TargtServerPSOProxyBaseURL, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__FUNCTIONS__", "VALUE"=>$automaticallyOutputSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__INCLUDE_PROXY_RESULT_HEADER__", "VALUE"=>GetHeaderSourceForProxyResultFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForProxyResultList), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__INCLUDE_PROXY_REQUEST_PARAMS_HEADER__", "VALUE"=>GetHeaderSourceForRequestParamsFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForRequestParamsList), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__INCLUDE_HEADER_FOR_FUNCTION__", "VALUE"=>GetHeaderSourceForClassesInFunctionFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForClassesInFunctionList), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__DEFINE_DELEGATE__", "VALUE"=>GetHeaderSourceForDefineDelegateFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForDefineDelegate), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__DB_OBJECT__",   "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					);
				$source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT");
				$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
				if ($result->Success) {
					// OK
				} else {
					AddMtoolErrorBuildMessage(" -> Error! Failed to update");
				}
			}
			save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$PROXYCLIENT, $da->PID, $anyOutput);
			
			set_last_build_time($LastBuild);
		}
		
		// Output Custom Functions
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$CUSTOMPROXYCLIENT);
		
		$DAda = new daDBAccess();
		$DAdafunc = new dafuncDBAccess();
		$DAdaCustomProxy = new daCustomProxyDBAccess();
		$DAdaCustomProxyFuncDBAccess = new daCustomProxyFuncDBAccess();
		
		$daCustomProxyListGroupHT = MakeMtoolCustomProxyHT($project, $ProjectSourceOutput);
		
		foreach ($daCustomProxyListGroupHT as $key => $daCustomProxyList)
		{
			if (check_build_timeout_for_mtool()) {
				return false;				// Timeout. Do the rest in next try.
			}
			
			$check_if_this_item_is_already_build_by_mtool_with_anyOutputOption_count = 0;
			$check_if_skip_for_quick_build_count = 0;
			for($i = 0 ; $i < count($daCustomProxyList); $i++) {
				$daCustomProxy = $daCustomProxyList[$i];
				
				if (check_if_this_item_is_already_build_by_mtool_with_anyOutputOption($BuildTokenCompletedItemList, $daCustomProxy->PID, $anyOutput)) {
					// This may be able to Skip
					$check_if_this_item_is_already_build_by_mtool_with_anyOutputOption_count++;
				}
				
				$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$CUSTOMPROXYCLIENT;
				$LastBuild->EachTargetPID = $daCustomProxy->PID;
				if (check_if_skip_for_quick_build($quick_build, $LastBuild, $daCustomProxy->LastModifiedDT)) {
					// This may be able to Skip
					$check_if_skip_for_quick_build_count++;
				}
			}
			if ($check_if_this_item_is_already_build_by_mtool_with_anyOutputOption_count == count($daCustomProxyList) ||
			    $check_if_skip_for_quick_build_count == count($daCustomProxyList)) {
				print "need to skip\n";
				continue;
			}
			
			AddMtoolGeneralBuildMessage(" Custom Proxy Client");
			AddMtoolGeneralBuildMessage(" => Base Name:" . $daCustomProxy->basename);
			
			$combinedDataClassPropetyBaseClassName = "";
			$automaticallyOutputSource = "";
			// $combined_dataclass_property_source = "";

			$CHeaderIncludeForProxyResultList = array();
			$CHeaderIncludeForRequestParamsList = array();
			$CHeaderIncludeForClassesInFunctionList = array();
			$CHeaderIncludeForDefineDelegate = array();
			$HeaderSourceForSerializeAndDeserializeFromListForDataClassList = array();
			
			for($i = 0 ; $i < count($daCustomProxyList); $i++) {
				$daCustomProxy = $daCustomProxyList[$i];
				
				AddMtoolGeneralBuildMessage(" => Name:" . $daCustomProxy->name);
				AddMtoolDebugBuildMessage(" => Project PID:" . $da->ProjectPID . " PID:" . $daCustomProxy->PID);
				
				$combinedDataClassPropetyBaseClassName = CreateCustomProxyDataBaseClassName($daCustomProxy->basename);
				$combinedDataClassPropetyClassName = CreateDataClassName($combinedDataClassPropetyBaseClassName);
				
				$ParamClassNameList = InitializeMtoolCustomProxyParamClassNameList($project, $ProjectSourceOutput, $daCustomProxy);
				// $ParamClassNameList = array();
				// $thisObj = new ProxyclientSourceForCreatingRequestParamsClassNameInfo();
				// $thisObj->ClassName = $CONFIG_TOKEN_VALUE_NAME;
				// $thisObj->SetValueNameByNo = false;
				// $thisObj->IsToken = true;
				// array_push($ParamClassNameList, $thisObj);
				
				$ResultClassNameList = array();
				
				$daCustomProxyFuncList = $DAdaCustomProxyFuncDBAccess->GetdaCustomProxyFuncList($project->PID, $daCustomProxy->PID);
				for($j = 0 ; $j < count($daCustomProxyFuncList); $j++) {
					$daCustomProxyFunc = $daCustomProxyFuncList[$j];
					
					$no = ($j + 1);
					$ForList = $daCustomProxyFunc->ForList();
					
					AddMtoolGeneralBuildMessage("  => " . $no . "/" . count($daCustomProxyFuncList) . " Func in Custom Proxy");

					$dafunc = $DAdafunc->Getdafunc($daCustomProxyFunc->dafuncPID, $project->PID);
					if ($dafunc != NULL) {
						$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
						$da = $DAda->Getda($dafunc->daPID, $project->PID);
						if ($da != NULL) {
							
							// Create Necessary Sources
							$thisBaseClassName = MakeMtoolProxyRequestParamBaseClassName($combinedDataClassPropetyBaseClassName, $no, $da);
							
							$dummyoutput = MakeproxyclientSourceForOneFunc($BuildToken, $project, $ProjectSourceOutput, $da, $thisBaseClassName, $dafunc, true, $ForList, $anyOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, $CHeaderIncludeForProxyResultList, $CHeaderIncludeForRequestParamsList, $CHeaderIncludeForClassesInFunctionList, $CHeaderIncludeForDefineDelegate);
							
							$thisClassName = CreateDataClassName($thisBaseClassName);
							$thisClassName = CheckIfListAndAddListClassSuffixIfList($ProjectSourceOutput, $dafunc, $thisClassName);
							
							// Result Param
							switch($dafunc->ActionType)
							{
								case dafuncActionTypeEnum::$SELECTSINGLE:
								case dafuncActionTypeEnum::$SELECTLIST:
									$baseClassNameForResult = CreateDataClassName($da->name);
									$thisClassNameForResult = CheckIfListAndAddListClassSuffixIfList($ProjectSourceOutput, $dafunc, $baseClassNameForResult);
									$targetClassNameForResultList = "";
									$param_source_template = "";
									if (!$ForList) {
										// Single
										$param_source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-PROXY-RESULT-PARAM-CLASS");
										$targetClassNameForResultList = $thisClassNameForResult;
										
									} else {
										// List
										if ($baseClassNameForResult == $thisClassNameForResult) {
											// Not a list
											$param_source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-PROXY-RESULT-PARAM-CLASS");
											$targetClassNameForResultList = GetMtoolDataListClassName($ProjectSourceOutput, $thisClassNameForResult);
										} else {
											// List
											$param_source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-PROXY-RESULT-PARAM-CLASS-LIST");
											$targetClassNameForResultList = $thisClassNameForResult;
										}
									}
									$thisClassNameForResult = CreateProxyResultParamName($thisBaseClassName, $sourceFunctionName);
									$localfilename = CreateRequestParamsClassName($ProjectSourceOutput, $thisClassNameForResult);
									
									$ReplaceParameterList = array(
											array("KEY"=>"__ARRAY_DEFINE__", "VALUE"=>GetMtoolListDefinitionSource($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
											array("KEY"=>"__ARRAY_INITIALIZE__", "VALUE"=>GetMtoolListInitializeSource($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
											array("KEY"=>"__ARRAY_INITIALIZE_BY_DESERIALIZE__", "VALUE"=>GetMtoolListInitializeSourceByDeserialize($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
											array("KEY"=>"__ARRAY_SERIALIZE__", "VALUE"=>GetMtoolListSerializeSource($BuildToken, $project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
									  		array("KEY"=>"__ARRAY_ITEM_CLASS_", "VALUE"=>$targetClassNameForResultList, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
											array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
											array("KEY"=>"__BASE_CLASS_NAME__", "VALUE"=>$targetClassNameForResultList, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
											array("KEY"=>"__CLASS_NAME__", "VALUE"=>$thisClassNameForResult, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
											array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
										);
									$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, "", $ReplaceParameterList, $param_source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
									if ($result->Success) {
										// OK
									} else {
										AddMtoolErrorBuildMessage(" -> Error! Failed to update list param");
									}
									
									$thisResultClassNameData = new ResultClassNameDataClass();
									$thisResultClassNameData->ParamName = "Result";
									$thisResultClassNameData->ParamClassName = $thisClassNameForResult;
									$thisResultClassNameData->No = $no;
									$thisResultClassNameData->ForList = false;
									$thisResultClassNameData->IsClass = true;
									$thisResultClassNameData->IsNullable = false;
									
									array_push($ResultClassNameList, $thisResultClassNameData);
									break;
								case dafuncActionTypeEnum::$INSERT:
								case dafuncActionTypeEnum::$UPDATE:
								case dafuncActionTypeEnum::$DELETE:
									break;
								default:
									AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
									return;
							}
							
							// $paramclassname = CreateProxyRequestParamName($thisBaseClassName, $sourceFunctionName);
							// if ($ForList) {
							// 	$paramclassname = GetMtoolDataListClassName($ProjectSourceOutput, $paramclassname);
							// }
							// 
							// $thisObj = new ProxyclientSourceForCreatingRequestParamsClassNameInfo();
							// $thisObj->ClassName = $paramclassname;
							// $thisObj->SetValueNameByNo = true;
							// $thisObj->IsToken = false;
							// array_push($ParamClassNameList, $thisObj);
							
							AddMtoolCustomProxyParamClassNameList($ParamClassNameList, $ProjectSourceOutput, $thisBaseClassName, $sourceFunctionName, $ForList);
							
							// $combined_dataclass_property_source .= GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, GetCustomProxyRequestParamObjectName($no), $thisClassName);
							
							switch($dafunc->ActionType)
							{
								case dafuncActionTypeEnum::$SELECTSINGLE:
								case dafuncActionTypeEnum::$SELECTLIST:
									break;
								case dafuncActionTypeEnum::$INSERT:
									$thisResultClassNameData = new ResultClassNameDataClass();
									$thisResultClassNameData->ParamName = $MTOOL_INSERT_ID_PROPERTY_NAME;
									$thisResultClassNameData->ParamClassName = "int";
									$thisResultClassNameData->No = $no;
									$thisResultClassNameData->ForList = $ForList;
									$thisResultClassNameData->IsClass = false;
									$thisResultClassNameData->IsNullable = !$ForList;
									
									array_push($ResultClassNameList, $thisResultClassNameData);
									break;
								case dafuncActionTypeEnum::$UPDATE:
								case dafuncActionTypeEnum::$DELETE:
									break;
								default:
									AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
									return;
							}
						}
					}
				}
				
				AddMtoolCHeaderIncludeForClassesInFunction($ProjectSourceOutput, $CHeaderIncludeForClassesInFunctionList, $combinedDataClassPropetyBaseClassName, $daCustomProxy->name);
				AddMtoolCHeaderIncludeForDefineDelegate($ProjectSourceOutput, $CHeaderIncludeForDefineDelegate, $combinedDataClassPropetyBaseClassName, $daCustomProxy->name);
				
				AddMtoolGeneralBuildMessage("   => Create Request Params Class");
				// Create Request Params Class
				$classnamesuffix = "";
				$sourceParamInfoList = NULL;
				MakeproxyclientSourceForCreatingRequestParamsClass($BuildToken, $project, $ProjectSourceOutput, NULL, $combinedDataClassPropetyBaseClassName, NULL, $combinedDataClassPropetyClassName, "", $daCustomProxy->name, true, false, false, true, $ParamClassNameList, $sourceParamInfoList, $output_to_temp_folder, $output_after_copy_to_temp_folder, $daCustomProxy->AuthType, $daCustomProxy->SingleGetFuncPID, false, $classnamesuffix);
				
				// Create Proxy Result
				AddMtoolGeneralBuildMessage("   => Create Proxy Result");
				MakeproxyclientSourceForCreatingProxyResult($BuildToken, $project, $ProjectSourceOutput, NULL, $combinedDataClassPropetyBaseClassName, NULL, $combinedDataClassPropetyClassName, "", $daCustomProxy->name, true, false, $ResultClassNameList, $output_to_temp_folder, $output_after_copy_to_temp_folder);
				
				$requestParamsSourceForDBFunc = "";
				$requestParamsSourceForJQuery = "";
				MtoolCreateRequestParamsSourceForDBFuncAndJQuery($BuildToken, $project, $ProjectSourceOutput, $sourceParamInfoList, $requestParamsSourceForDBFunc, $requestParamsSourceForJQuery);
				
				// Async Task Loader
				MakeproxyclientSourceForAsyncTaskLoader($BuildToken, $project, $ProjectSourceOutput, $combinedDataClassPropetyBaseClassName, $daCustomProxy->name, $output_to_temp_folder, $output_after_copy_to_temp_folder);
				
				$proxyServerFileName = CreateProxyServerFileName($ProjectSourceOutput->TargtServerPSOProgramLanguage, $ProjectSourceOutput->TargtServerPSOCustomFileExtention, $daCustomProxy->basename, $daCustomProxy->name);
				$template = GetproxyclientFunctionTemplateSourceBasedOnLangAndFunctionType($BuildToken, $project, $ProjectSourceOutput, true);
				$template = ReplaceOutputSourceInfoByKeyValue($template, array(
						array("KEY"=>"__DB_OBJECT__",   "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$combinedDataClassPropetyBaseClassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$daCustomProxy->name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__BASE_URL__", "VALUE"=>$ProjectSourceOutput->GetTargtServerPSOProxyBaseURLWithLastSlush(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__REQUEST_URL__", "VALUE"=>$proxyServerFileName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__REQUEST_PARAMS_FOR_DBFUNC__", "VALUE"=>$requestParamsSourceForDBFunc, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__REQUEST_PARAMS_FOR_JQUERY__", "VALUE"=>$requestParamsSourceForJQuery, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
					));
				$automaticallyOutputSource .= $template;
			}
			
			if ($automaticallyOutputSource != "") {
				
				$localfilename = MakeMtoolProxyClientFileName($ProjectSourceOutput, $combinedDataClassPropetyBaseClassName);
				
				$ReplaceParameterList = array(
						array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$combinedDataClassPropetyBaseClassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__BASE_URL__", "VALUE"=>$ProjectSourceOutput->TargtServerPSOProxyBaseURL, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__FUNCTIONS__", "VALUE"=>$automaticallyOutputSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__INCLUDE_PROXY_RESULT_HEADER__", "VALUE"=>GetHeaderSourceForProxyResultFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForProxyResultList), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__INCLUDE_PROXY_REQUEST_PARAMS_HEADER__", "VALUE"=>GetHeaderSourceForRequestParamsFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForRequestParamsList), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__INCLUDE_HEADER_FOR_FUNCTION__", "VALUE"=>GetHeaderSourceForClassesInFunctionFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForClassesInFunctionList), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
						array("KEY"=>"__DEFINE_DELEGATE__", "VALUE"=>GetHeaderSourceForDefineDelegateFromList($BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForDefineDelegate), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
					);
				$source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT");
				// AddMtoolGeneralBuildMessage("   => Local Filename: " . $localfilename);
				$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
				if ($result->Success) {
					// OK
				} else {
					AddMtoolErrorBuildMessage(" -> Error! Failed to update");
				}
			}
			for($i = 0 ; $i < count($daCustomProxyList); $i++) {
				$daCustomProxy = $daCustomProxyList[$i];
				
				save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$CUSTOMPROXYCLIENT, $daCustomProxy->PID, $anyOutput);
			}
		}
		
		if ($anyOutput && $createdBaseClasses == false) {
			
			// Create Base Class
			// MakeproxyclientSourceForCreatingBaseClass($BuildToken, $project, $ProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			// MakeproxyclientSourceForCreatingProxyResultStatusType($BuildToken, $project, $ProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			// MakeproxyclientSourceForCreatingProxyResultStatusTypeString($BuildToken, $project, $ProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			
			// Create Base General Request
			// MakeproxyclientSourceForCreatingBaseGeneralRequest($BuildToken, $project, $ProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			
			// Create Base Result Class
			// MakeproxyclientSourceForCreatingBaseResultClass($BuildToken, $project, $ProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			
			$createdBaseClasses = true;
		}
		
		// Update PartlyCompletedFlag for this Output Source
		$DABuildTokenProjectSourceOutput->UpdatePartlyCompletedFlag($updateProxyClient_BuildTokenProjectSourceOutput);
	}
	
	if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER) {
		// Proxy Server is not a target of autoload
	} else {
		// Other class type is a target of autoload

		$is_output_for_class_and_dbaccess = ($updateClass_BuildTokenProjectSourceOutput && $updateFunc_BuildTokenProjectSourceOutput);

		$is_output_for_proxy = false;
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				switch($ProjectSourceOutput->ClassType)
				{
					case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
						$is_output_for_proxy = ($updateClass_BuildTokenProjectSourceOutput && $updateProxyClient_BuildTokenProjectSourceOutput);
						break;
					case ProjectSourceOutputClassTypeEnum::$DBACCESS:
					case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
					case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
					case ProjectSourceOutputClassTypeEnum::$HTML:
					case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
						break;
					default:
						die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
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
				return;
		}
		
		// if (!$quick_build) {
			if ($is_output_for_class_and_dbaccess || $is_output_for_proxy)
			{
				AddMtoolGeneralBuildMessage("Making Autoload Code");

				$autoloadfilename = GetAutomatedSourceFilename($ProjectSourceOutput);
				$autoloadCode = "";

				if ($is_output_for_class_and_dbaccess)
				{
					$DAdataclass = new dataclassDBAccess();
					$dataclasslist = $DAdataclass->GetdataclassList($project->PID);
					for($k = 0 ; $k < count($dataclasslist); $k++) {
						$dataclass = $dataclasslist[$k];

						if ($project->Getoption_all_source_include() || $dataclass->GetIsAutoloadBoolean()) {
							$dataclassfilename = GetMtoolDataClassFileName($ProjectSourceOutput, $dataclass);

							switch ($ProjectSourceOutput->ProgramLanguage) {
								case ProjectSourceOutputProgramLanguageEnum::$PHP:
									$relativePathToLocalFile = GetRelativePathToLocalFile(GetTempFolderForSourceOutputIfNeeded($project, $ProjectSourceOutput, $output_to_temp_folder, $ProjectSourceOutput->SourceOutputDir), $dataclass->StoreBasePath);
									$template_autoload = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "AUTOLOAD-INCLUDE");
									$template_autoload = ReplaceOutputSourceInfoByKeyValue($template_autoload, array(
											array("KEY"=>"__FILE__", "VALUE"=>$relativePathToLocalFile . $dataclassfilename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
										));
									$autoloadCode .= $template_autoload;
									break;
								case ProjectSourceOutputProgramLanguageEnum::$CS:
								case ProjectSourceOutputProgramLanguageEnum::$JAVA:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
								case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
									break;
								default:
									AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
									return;
							}
						}
					}

					$DAda = new daDBAccess();
					$dalist = $DAda->GetdaList($project->PID);
					for($j = 0 ; $j < count($dalist); $j++) {
						$da = $dalist[$j];

						if ($project->Getoption_all_source_include() || $da->GetIsAutoloadBoolean()) {
							$localfilename = GetMtoolDAFilenameForBuild($ProjectSourceOutput, $da);

							switch ($ProjectSourceOutput->ProgramLanguage) {
								case ProjectSourceOutputProgramLanguageEnum::$PHP:
									$relativePathToLocalFile = GetRelativePathToLocalFile(GetTempFolderForSourceOutputIfNeeded($project, $ProjectSourceOutput, $output_to_temp_folder, $ProjectSourceOutput->SourceOutputDir), $da->StoreBasePath);
									$template_autoload = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "AUTOLOAD-INCLUDE");
									$template_autoload = ReplaceOutputSourceInfoByKeyValue($template_autoload, array(
											array("KEY"=>"__FILE__", "VALUE"=>$relativePathToLocalFile . $localfilename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
										));
									$autoloadCode .= $template_autoload;
									break;
								case ProjectSourceOutputProgramLanguageEnum::$CS:
								case ProjectSourceOutputProgramLanguageEnum::$JAVA:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
								case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
									break;
								default:
									AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
									return;
							}
						}
					}

					switch ($ProjectSourceOutput->ProgramLanguage) {
						case ProjectSourceOutputProgramLanguageEnum::$PHP:

							if (trim($project->DBConnectionObjectNameForPHP) != "" &&
								trim($project->ServerIP) != "" &&
								trim($project->DBUserUser) != "" &&
								trim($project->DBUserPassword) != "" &&
								trim($project->DBConnectionDBName) != "")
							{
								$template_autoload_connect = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "AUTOLOAD-CONNECT");
								$template_autoload_connect = ReplaceOutputSourceInfoByKeyValue($template_autoload_connect, array(
										array("KEY"=>"__DB_OBJECT__",   "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
										array("KEY"=>"__DB_HOST__",     "VALUE"=>$project->ServerIP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
										array("KEY"=>"__DB_USER__",     "VALUE"=>$project->DBUserUser, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
										array("KEY"=>"__DB_PASSWORD__", "VALUE"=>$project->DBUserPassword, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
										array("KEY"=>"__DB_NAME__",     "VALUE"=>$project->DBConnectionDBName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
									));
								$autoloadCode = $template_autoload_connect . $autoloadCode;
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
							return;
					}
				}
				if ($is_output_for_proxy)
				{
					$autoload_filename_for_proxyclient_list = array();
					
					InitializeToSkipUnusedDataClassForProxy($project, $ProjectSourceOutput);
					
					$DAdataclass = new dataclassDBAccess();
					$DAdataclassfields = new dataclassfieldsDBAccess();
					$dataclasslist = $DAdataclass->GetdataclassList($project->PID);
					for($k = 0 ; $k < count($dataclasslist); $k++) {
						$dataclass = $dataclasslist[$k];
						
						AddMtoolGeneralBuildMessage(" Data Class for Autoload");
						AddMtoolGeneralBuildMessage(" => Name:" . $dataclass->name);
						AddMtoolDebugBuildMessage(" => Project PID:" . $dataclass->ProjectPID . "  PID:" . $dataclass->PID);
						
						if (CheckIfSkipUnusedDataClassForProxy($project, $ProjectSourceOutput, $dataclass, $dataclasslist)) {
							AddMtoolGeneralBuildMessage(" => Skip. This Data Class Source is not created because Proxy Client is not exist");
							continue;
						}
						
						$dataclassfilename = GetMtoolDataClassFileName($ProjectSourceOutput, $dataclass);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $dataclassfilename);
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
								break;
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								return;
						}
					}
					
					// Simple Proxy Client
					$DAda = new daDBAccess();
					$dalist = $DAda->GetdaList($project->PID);
					for($j = 0 ; $j < count($dalist); $j++) {
						$da = $dalist[$j];
						
						$DAdafunc = new dafuncDBAccess();
						$dafunclist = $DAdafunc->GetdafuncList($project->PID, $da->PID);
						for($k = 0 ; $k < count($dafunclist); $k++) {
							$dafunc = $dafunclist[$k];
							
							$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
							
							$DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
							$dafuncSimpleProxyForOneOutputSource = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxyForOneOutputSource($project->PID, $da->PID, $dafunc->PID, $ProjectSourceOutput->PID);
							if ($dafuncSimpleProxyForOneOutputSource) {
								// It's a target.
								
								$dataclassname = CreateDataClassName($da->name);
								
								$proxy_client_filename = MakeMtoolProxyClientFileName($ProjectSourceOutput, $da->name);
								AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $proxy_client_filename);
								
								// Request Param
								$request_params_class_name = GetClassNameForRequestParams($ProjectSourceOutput, $da->name, $sourceFunctionName);
								$request_params_class_filename = CreateRequestParamsClassName($ProjectSourceOutput, $request_params_class_name);
								AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $request_params_class_filename);
								
								// Security Request Param
								AddMtoolAutoloadFilenameForProxyClientForSecurity($BuildToken, $project, $ProjectSourceOutput, $da, $dataclassname, $dafunc, $dataclassname, $sourceFunctionName, false, true, true, NULL, $dafunc->SingleProxy_AuthType, $dafunc->SingleProxy_SingleGetFuncPID, true, $autoload_filename_for_proxyclient_list);
								
							} else {
								// No data. // Not a target.
							}
						}
					}
					
					// Custom Proxy Client
					$daCustomProxyListGroupHT = MakeMtoolCustomProxyHT($project, $ProjectSourceOutput);
					foreach ($daCustomProxyListGroupHT as $key => $daCustomProxyList)
					{
						for($i = 0 ; $i < count($daCustomProxyList); $i++) {
							$daCustomProxy = $daCustomProxyList[$i];
							
							$combinedDataClassPropetyBaseClassName = CreateCustomProxyDataBaseClassName($daCustomProxy->basename);
							$combinedDataClassPropetyClassName = CreateDataClassName($combinedDataClassPropetyBaseClassName);
							
							$custom_proxyclient_filename = MakeMtoolProxyClientFileName($ProjectSourceOutput, $combinedDataClassPropetyBaseClassName);
							AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $custom_proxyclient_filename);
							
							// Request Param
							$request_params_class_name = GetClassNameForRequestParams($ProjectSourceOutput, $combinedDataClassPropetyBaseClassName, $daCustomProxy->name);
							$request_params_class_filename = CreateRequestParamsClassName($ProjectSourceOutput, $request_params_class_name);
							AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $request_params_class_filename);
							
							$ParamClassNameList = InitializeMtoolCustomProxyParamClassNameList($project, $ProjectSourceOutput, $daCustomProxy);
							$daCustomProxyFuncList = $DAdaCustomProxyFuncDBAccess->GetdaCustomProxyFuncList($project->PID, $daCustomProxy->PID);
							for($j = 0 ; $j < count($daCustomProxyFuncList); $j++) {
								$daCustomProxyFunc = $daCustomProxyFuncList[$j];
								
								$no = ($j + 1);
								$ForList = $daCustomProxyFunc->ForList();
								
								$dafunc = $DAdafunc->Getdafunc($daCustomProxyFunc->dafuncPID, $project->PID);
								if ($dafunc != NULL) {
									$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
									$da = $DAda->Getda($dafunc->daPID, $project->PID);
									if ($da != NULL) {
										
										// Create Necessary Sources
										$thisBaseClassName = MakeMtoolProxyRequestParamBaseClassName($combinedDataClassPropetyBaseClassName, $no, $da);
										
										$this_step_dataclassname = GetClassNameForRequestParams($ProjectSourceOutput, $thisBaseClassName, $sourceFunctionName);
										$this_step_localfilename = CreateRequestParamsClassName($ProjectSourceOutput, $this_step_dataclassname);
										
										AddMtoolCustomProxyParamClassNameList($ParamClassNameList, $ProjectSourceOutput, $thisBaseClassName, $sourceFunctionName, $ForList);
										
										AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $this_step_localfilename);
									}
								}
							}
							
							// Security Request Param
							AddMtoolAutoloadFilenameForProxyClientForSecurity($BuildToken, $project, $ProjectSourceOutput, NULL, $combinedDataClassPropetyBaseClassName, NULL, $combinedDataClassPropetyClassName, $daCustomProxy->name, true, false, false, $ParamClassNameList, $daCustomProxy->AuthType, $daCustomProxy->SingleGetFuncPID, true, $autoload_filename_for_proxyclient_list);
						}
					}
					
					for($i = 0 ; $i < count($autoload_filename_for_proxyclient_list) ; $i++) {
						$autoload_filename_for_proxyclient = $autoload_filename_for_proxyclient_list[$i];

						$template_autoload = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "AUTOLOAD-INCLUDE");
						$template_autoload = ReplaceOutputSourceInfoByKeyValue($template_autoload, array(
								array("KEY"=>"__FILE__", "VALUE"=>$autoload_filename_for_proxyclient, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));
						$autoloadCode .= $template_autoload;
					}
				}
				$source_template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "AUTOLOAD");
				$ReplaceParameterList = array(
						array("KEY"=>"__AUTOMATED_CODE_COMES_HERE__", "VALUE"=>$autoloadCode, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
					);
				$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $autoloadfilename, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
				if ($result->Success) {
					// OK
				} else {
					AddMtoolErrorBuildMessage(" -> Error! Failed to update");
				}
			}
		// }
	}
	
	$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$HTML;
	if ($updateHtml_BuildTokenProjectSourceOutput && $updateHtml_BuildTokenProjectSourceOutput->IsPartlyCompleted == 0) {
		if (check_build_timeout_for_mtool()) {
			return false;				// Timeout. Do the rest in next try.
		}
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$HTML);
		
		$DAhtml_leftouterjoin_htmlTemplate = new html_leftouterjoin_htmlTemplateDBAccess();
		$htmlList = $DAhtml_leftouterjoin_htmlTemplate->GethtmlList($project->PID);
		
		if (count($htmlList) > 0) {
			for($i = 0 ; $i < count($htmlList); $i++) {
				$html = $htmlList[$i];
				
				if ($html->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					
					if (check_build_timeout_for_mtool()) {
						return false;				// Timeout. Do the rest in next try.
					}
					if (check_if_this_item_is_already_build_by_mtool($BuildTokenCompletedItemList, $html->PID)) {
						continue;
					}
					
					AddMtoolGeneralBuildMessage(" => Html: " . $html->name);
					
					$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$HTML;
					$LastBuild->EachTargetPID = $html->PID;
					if (check_if_skip_for_quick_build($quick_build, $LastBuild, $html->LastModifiedDT)) {
						AddMtoolGeneralBuildMessage(" => Skip");
						AddMtoolGeneralBuildMessage("");
						continue;
					}
					
					BuildHtmlFromTemplate($BuildToken, $project, $ProjectSourceOutput, $html, $output_to_temp_folder, $output_after_copy_to_temp_folder);
					
					save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$HTML, $html->PID, NULL);
					
					set_last_build_time($LastBuild);
					
				} else {
					// Not a target
				}
			}
		}
		// Update PartlyCompletedFlag for this Output Source
		$DABuildTokenProjectSourceOutput->UpdatePartlyCompletedFlag($updateHtml_BuildTokenProjectSourceOutput);
	}
	
	$LastBuild->BuildClassType = LastBuildBuildClassTypeEnum::$LANGUAGERESOURCE;
	if ($updateLanguageResource_BuildTokenProjectSourceOutput && $updateLanguageResource_BuildTokenProjectSourceOutput->IsPartlyCompleted == 0) {
		if (check_build_timeout_for_mtool()) {
			return false;				// Timeout. Do the rest in next try.
		}
		$BuildTokenCompletedItemList = $DABuildTokenCompletedItem->GetBuildTokenCompletedItemList($BuildToken->PID, $project->PID, $ProjectSourceOutput->PID, BuildTokenCompletedItemBuildTargetTypeEnum::$LANGUAGERESOURCE);
		
		$DALanguageResourceGroupProjectSourceOutput = new LanguageResourceGroupProjectSourceOutputDBAccess();
		$LanguageResourceGroupProjectSourceOutputList = $DALanguageResourceGroupProjectSourceOutput->GetLanguageResourceGroupProjectSourceOutputList($project->PID);
		
		if (count($LanguageResourceGroupProjectSourceOutputList) > 0) {
			for($i = 0 ; $i < count($LanguageResourceGroupProjectSourceOutputList); $i++) {
				$LanguageResourceGroupProjectSourceOutput = $LanguageResourceGroupProjectSourceOutputList[$i];
				
				if ($LanguageResourceGroupProjectSourceOutput->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					AddMtoolGeneralBuildMessage(" => Language Resource: " . $LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupName);
					if (check_if_skip_for_quick_build($quick_build, $LastBuild, $LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupLastModifiedDT)) {
						AddMtoolGeneralBuildMessage(" => Skip");
						AddMtoolGeneralBuildMessage("");
						continue;
					}
					BuildLanguageResource($BuildToken, $project, $ProjectSourceOutput, $LanguageResourceGroupProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder);
					
					save_already_build_flag_by_mtool($BuildToken, $project, $ProjectSourceOutput, BuildTokenCompletedItemBuildTargetTypeEnum::$LANGUAGERESOURCE, $LanguageResourceGroupProjectSourceOutput->PID, NULL);
					
					set_last_build_time($LastBuild);
				}
			}
		}
		// Update PartlyCompletedFlag for this Output Source
		$DABuildTokenProjectSourceOutput->UpdatePartlyCompletedFlag($updateLanguageResource_BuildTokenProjectSourceOutput);
	}
	return true;
}

function CheckIfItIsATargetOfCustomProxy($ProjectPID, $daCustomProxyPID, $ProjectSourceOutputPID)
{
	$DAdaCustomProxySourceOutputTarget = new daCustomProxySourceOutputTargetDBAccess();
	$daCustomProxySourceOutputTarget = $DAdaCustomProxySourceOutputTarget->GetdaCustomProxySourceOutputTargetForOneOutputSource($ProjectPID, $daCustomProxyPID, $ProjectSourceOutputPID);
	if ($daCustomProxySourceOutputTarget) {
		// Yes. It's a target of output
		return true;
	} else {
		// Not a target of output.
	}
	return false;
}

?>
