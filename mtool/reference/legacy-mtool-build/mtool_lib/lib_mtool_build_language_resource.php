<?PHP

function BuildLanguageResource($BuildToken, $project, $ProjectSourceOutput, $LanguageResourceGroupProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	$DALanguageResourceLang = new LanguageResourceLangDBAccess();
	$LanguageResourceLangList = $DALanguageResourceLang->GetLanguageResourceLangList();
	
    $DALanguageResourceGroupLang = new LanguageResourceGroupLangDBAccess();
    $LanguageResourceGroupLangList = $DALanguageResourceGroupLang->GetLanguageResourceGroupLangList($project->PID, $LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupPID);
    
	$DefaultLanguageResourceGroupLang = NULL;
	for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
		$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
		if ($LanguageResourceGroupLang->LanguageResourceLangIsDefault == 1) {
			// This is Default
			$DefaultLanguageResourceGroupLang = $LanguageResourceGroupLang;
			break;
		}
	}
    
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			BuildLanguageResourceSub($BuildToken, $project, $ProjectSourceOutput, $LanguageResourceGroupProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, BuildLanguageResourceTargetLanguageType::$DEFAULT, $LanguageResourceLangList, $LanguageResourceGroupLangList, NULL, $DefaultLanguageResourceGroupLang);
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			
			// Output Default
			for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
				$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
				
				if ($LanguageResourceGroupLang->LanguageResourceLangIsDefault == 1) {
					// This is Default
					BuildLanguageResourceSub($BuildToken, $project, $ProjectSourceOutput, $LanguageResourceGroupProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, BuildLanguageResourceTargetLanguageType::$DEFAULT, $LanguageResourceLangList, $LanguageResourceGroupLangList, $LanguageResourceGroupLang, $DefaultLanguageResourceGroupLang);
					
					break;
				}
			}
			$need_to_output_other_lang = true;
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
					switch($ProjectSourceOutput->DotNetLanguageResourceType)
					{
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
							break;
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
							break;
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
							$need_to_output_other_lang = false;
							break;
						default:
							AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
							return;
					}
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			if ($need_to_output_other_lang) {
				for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
					$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
					
					if ($LanguageResourceGroupLang->LanguageResourceLangIsDefault == 0) {
						BuildLanguageResourceSub($BuildToken, $project, $ProjectSourceOutput, $LanguageResourceGroupProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, BuildLanguageResourceTargetLanguageType::$OTHER, $LanguageResourceLangList, $LanguageResourceGroupLangList, $LanguageResourceGroupLang, $DefaultLanguageResourceGroupLang);
					}
				}
			}
			break;
			
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}
class BuildLanguageResourceTargetLanguageType
{
	static $DEFAULT  = "default";		// English
	static $OTHER = "other";
}

function GetPHPEscapeValueForLanguageResource($value)
{
	return preg_replace("/([\"])/", "\\\\$1", $value);
}
function GetXMLEscapeValueForLanguageResource($value)
{
	return htmlspecialchars(preg_replace("/(['\"])/", "\\\\$1", $value));
}
function GetCSEscapeValueForLanguageResource($value)
{
	return preg_replace("/([\"])/", "\"$1", $value);
}
function GetXcodeEscapeValueForLanguageResource($value)
{
	return preg_replace("/([\"])/", "\\\\$1", $value);
}
function GetEscapeValueIfNecessaryForLanguageResource($ProjectSourceOutput, $value)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return GetPHPEscapeValueForLanguageResource($value);
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			switch($ProjectSourceOutput->DotNetLanguageResourceType)
			{
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
					return GetXMLEscapeValueForLanguageResource($value);
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
					return GetCSEscapeValueForLanguageResource($value);
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
					return;
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			return GetXMLEscapeValueForLanguageResource($value);
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return GetXcodeEscapeValueForLanguageResource($value);
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $value;
}

function BuildLanguageResourceSub($BuildToken, $project, $ProjectSourceOutput, $LanguageResourceGroupProjectSourceOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, $target_lang, $LanguageResourceLangList, $LanguageResourceGroupLangList, $LanguageResourceGroupLang, $DefaultLanguageResourceGroupLang)
{
    $DALanguageResourceCaption = new LanguageResourceCaptionDBAccess();
	
	$languageresourcefilebasename = "";
	$languageresourcefilenameonly = "";
	$languageresourcefilepath = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$languageresourcefilebasename = "lang_lib" . trim($LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupFilenameSuffixForPHP);
			$languageresourcefilenameonly = $languageresourcefilebasename . ".php";
			$languageresourcefilepath = $languageresourcefilenameonly;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$base_dir = "";
			switch($ProjectSourceOutput->DotNetLanguageResourceType)
			{
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
					$base_dir = $LanguageResourceGroupLang->LanguageResourceLangLangForCS . "/";
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
					return;
			}
			
			$lang_filename_suffix = "";
			switch($ProjectSourceOutput->DotNetLanguageResourceType)
			{
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
					switch ($target_lang) {
						case BuildLanguageResourceTargetLanguageType::$DEFAULT:
							break;
						case BuildLanguageResourceTargetLanguageType::$OTHER:
							$lang_filename_suffix = "." . $LanguageResourceGroupLang->LanguageResourceLangLangForCS;
							break;
						default:
							AddMtoolErrorBuildMessage("Error! Aborted. Unknown Target Language: " . $target_lang);
							return;
					}
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
					return;
			}
			$languageresourcefilebasename = "Resources" . trim($LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupFilenameSuffix) . $lang_filename_suffix;
			$languageresourcefilenameonly = $languageresourcefilebasename;
			
			switch($ProjectSourceOutput->DotNetLanguageResourceType)
			{
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
					$languageresourcefilenameonly .= ".resx";
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
					$languageresourcefilenameonly .= ".resw";
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
					$languageresourcefilenameonly .= ".cs";
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
					return;
			}
			$languageresourcefilepath = $base_dir . $languageresourcefilenameonly;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			$base_dir = "res/" . $LanguageResourceGroupLang->LanguageResourceLangLangForAndroid . "/";
			
			$languageresourcefilebasename = "strings" . trim($LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupFilenameSuffix);
			$languageresourcefilenameonly = $languageresourcefilebasename . ".xml";
			$languageresourcefilepath = $base_dir . $languageresourcefilenameonly;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$base_dir = $LanguageResourceGroupLang->LanguageResourceLangLangForiOS . ".lproj";
			
			$languageresourcefilebasename = trim($LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupFilenameForXcode);
			$languageresourcefilenameonly = $languageresourcefilebasename . ".strings";
			$languageresourcefilepath = $base_dir . "/" . $languageresourcefilenameonly;
			break;
			
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	
	$language_resource_case_list_source = "";
	
	$LanguageResourceList = GetLanguageResourceListWithAdditionalGroup($LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupPID, $project->PID);
	if ($LanguageResourceList) {
		
		$current_group_key_name = "";
		for($i = 0 ; $i < count($LanguageResourceList); $i++) {
			$LanguageResource = $LanguageResourceList[$i];
			
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					if ($current_group_key_name != $LanguageResource->SortGroup) {
						
						$thisSortGroupForCaption = $LanguageResource->SortGroup;
						if (trim($thisSortGroupForCaption) == "") {
							$thisSortGroupForCaption = "Misc";
						}
						
						$switch_case_group_source = GetMtoolLanguageResourceTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DEFINITION_GROUP");
						$ReplaceParameterList = array(
							array("KEY"=>"__GROUP__", "VALUE"=>$thisSortGroupForCaption, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							);
						$switch_case_group_source = ReplaceOutputSourceInfoByKeyValue($switch_case_group_source, $ReplaceParameterList);
						
						$language_resource_case_list_source .= $switch_case_group_source;
						$current_group_key_name = $LanguageResource->SortGroup;
					}
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}

			$KeyName = $LanguageResource->KeyName;
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
					switch($ProjectSourceOutput->DotNetLanguageResourceType)
					{
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
							break;
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
							$KeyName .= $LanguageResource->GetUWPTargetPropertyWithDot();
							break;
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
							break;
						default:
							AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
							return;
					}
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			
			$KeyNameForXcode = "";
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					$KeyNameForXcode = $LanguageResource->KeyNameForXcode;
					if (trim($KeyNameForXcode) == "") {
						$KeyNameForXcode = $LanguageResource->KeyName;
					}
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			$template_name_for_one_definition = "ONE_DEFINITION";
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
					switch($ProjectSourceOutput->DotNetLanguageResourceType)
					{
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
							break;
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
							$template_name_for_one_definition = "ONE_DEFINITION_FOR_UWP";
							break;
						case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
							$template_name_for_one_definition = "ONE_DEFINITION_FOR_CODE";
							break;
						default:
							AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
							return;
					}
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			$switch_case_source = GetMtoolLanguageResourceTemplateFile($BuildToken, $project, $ProjectSourceOutput, $template_name_for_one_definition);
			
			$target_lang_text = "";
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					
					$LanguageResourceCaption = $DALanguageResourceCaption->GetLanguageResourceCaption($project->PID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID, $LanguageResourceGroupLang->LanguageResourceLangPID);
					if (!$LanguageResourceCaption && $DefaultLanguageResourceGroupLang) {
						$LanguageResourceCaption = $DALanguageResourceCaption->GetLanguageResourceCaption($project->PID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID, $DefaultLanguageResourceGroupLang->LanguageResourceLangPID);
					}
					if ($LanguageResourceCaption) {
						$target_lang_text = $LanguageResourceCaption->Caption;
					}
					
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			$ReplaceParameterList = array(
				array("KEY"=>"__GROUP__",   "VALUE"=>$LanguageResource->SortGroup, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__KEY__",     "VALUE"=>GetEscapeValueIfNecessaryForLanguageResource($ProjectSourceOutput, $KeyName), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__KEY_FOR_XCODE__",     "VALUE"=>GetEscapeValueIfNecessaryForLanguageResource($ProjectSourceOutput, $KeyNameForXcode), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__TEXT__",    "VALUE"=>GetEscapeValueIfNecessaryForLanguageResource($ProjectSourceOutput, $target_lang_text),          "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				);
			
			switch ($target_lang) {
				case BuildLanguageResourceTargetLanguageType::$DEFAULT:
					
					for($j = 0 ; $j < count($LanguageResourceLangList) ; $j++) {
						$LanguageResourceLang = $LanguageResourceLangList[$j];
						
						$IsTargetLang = false;
						for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
							$thisLanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
							
							if ($LanguageResourceLang->PID == $thisLanguageResourceGroupLang->LanguageResourceLangPID) {
								$IsTargetLang = true;
								break;
							}
						}
						
						$thisCaption = "";
						if ($IsTargetLang) {
							$LanguageResourceCaption = $DALanguageResourceCaption->GetLanguageResourceCaption($project->PID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID, $LanguageResourceLang->PID);
							
							// If data is not exist, try to use Deffault Language's Caption
							if (!$LanguageResourceCaption && $DefaultLanguageResourceGroupLang) {
								$LanguageResourceCaption = $DALanguageResourceCaption->GetLanguageResourceCaption($project->PID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID, $DefaultLanguageResourceGroupLang->LanguageResourceLangPID);
							}
							if ($LanguageResourceCaption) {
								$thisCaption = $LanguageResourceCaption->Caption;
								
								// If Caption is specified to use default, try to use Deffault Language's Caption
								if ($thisCaption == "" && $LanguageResource->UseDefaultIfCaptionIsBlank == 1) {
									$LanguageResourceCaption = $DALanguageResourceCaption->GetLanguageResourceCaption($project->PID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID, $DefaultLanguageResourceGroupLang->LanguageResourceLangPID);
									if ($LanguageResourceCaption) {
										$thisCaption = $LanguageResourceCaption->Caption;
									}
								}
							}
						}
						array_push($ReplaceParameterList, 
							array("KEY"=>$LanguageResourceLang->TemplateKey, "VALUE"=>GetEscapeValueIfNecessaryForLanguageResource($ProjectSourceOutput, $thisCaption), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						);
					}
					break;
				case BuildLanguageResourceTargetLanguageType::$OTHER:
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Target Language: " . $target_lang);
					return;
			}
			
			$switch_case_source = ReplaceOutputSourceInfoByKeyValue($switch_case_source, $ReplaceParameterList);
			
			$language_resource_case_list_source .= $switch_case_source;
		}
	}
	
	$ReplaceParameterList = array(
		array("KEY"=>"__FILE_BASE_NAME__", "VALUE"=>$languageresourcefilebasename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		array("KEY"=>"__FILENAME__", "VALUE"=>$languageresourcefilenameonly, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		array("KEY"=>"__RESOURCE_FUNCTION_PREFIX__", "VALUE"=>$LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupFunctionNamePrefix, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		array("KEY"=>"__RESOURCE_FUNCTION_SUFFIX__", "VALUE"=>$LanguageResourceGroupProjectSourceOutput->LanguageResourceGroupFunctionNameSuffix, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		array("KEY"=>"__LANGUAGE_RESOURCE_LIST__", "VALUE"=>$language_resource_case_list_source, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
		
  		array("KEY"=>"__CLASS_NAME__", "VALUE"=>$languageresourcefilebasename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
  		array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		);
	
	$template_name_for_lang_res = "DEFAULT";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			switch($ProjectSourceOutput->DotNetLanguageResourceType)
			{
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT:
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP:
					$template_name_for_lang_res = "UWP";
					break;
				case ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE:
					$template_name_for_lang_res = "CODE";
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Dot Net Language Resource Type: " . $ProjectSourceOutput->DotNetLanguageResourceType);
					return;
			}
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	
	$source_template = GetMtoolLanguageResourceTemplateFile($BuildToken, $project, $ProjectSourceOutput, $template_name_for_lang_res);
	$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $languageresourcefilepath, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	if ($result->Success) {
		// OK
	} else {
		AddMtoolErrorBuildMessage(" -> Error! Failed to update");
	}
}

?>
