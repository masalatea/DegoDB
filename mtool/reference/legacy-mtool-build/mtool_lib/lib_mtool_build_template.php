<?PHP

function GetMtoolCommonTemplateFile($project, $templatename)
{
	$templatefile = "";
	switch($templatename)
	{
		case "DATATYPE-TRANSLATION-TABLE":
			$templatefile = "datatype-translation-table.txt";
			break;
		case "DBACCESS-DATATYPE-NO-DEFAULT-CHECK":
			$templatefile = "dbaccess-datatype-no-default-check.txt";
			break;
		case "DATATYPE-NULLABLE":
			$templatefile = "datatype-nullable.txt";
			break;
		case "DATATYPE-INITIALIZE-TABLE":
			$templatefile = "datatype-initialize-table.txt";
			break;
	}
	if ($templatefile == "") {
		AddMtoolErrorBuildMessage("No template. Skip: " . $templatename);
		return "";
	}
	return GetMtoolTemplateFile(
		NULL, 
		$project, 
		NULL, 			// $ProjectSourceOutput
		$templatefile,
		$project->SettingDir,	// $TemplateBaseDir
		GetCommonTemplateDir()
		);
}

function GetCommonTemplateDir()
{
	return "project_settings_system_default";
}

function GetDefaultTemplateDir($targettype)
{
	switch($targettype)
	{
		case htmlTemplateTargetTypeEnum::$HTML:
			return "html_template_system_default";
		case htmlTemplateTargetTypeEnum::$DB:
			return "dbclasses_template_system_default";
		case htmlTemplateTargetTypeEnum::$PROXYSERVER:
			return "proxyserver_template_system_default";
		case htmlTemplateTargetTypeEnum::$PROXYCLIENT:
			return "proxyclient_template_system_default";
		case htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER:
			return "dbaas_proxyserver_template_system_default";
		case htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT:
			return "dbaas_proxyclient_template_system_default";
		case htmlTemplateTargetTypeEnum::$UNITTEST:
			return "unit_test_template_system_default";
		case htmlTemplateTargetTypeEnum::$UPLOADSETTING:
			return "upload_setting_template_system_default";
		case htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE:
			return "language_resource";
	}
	PrintOutMtoolBuildResultMessage();
	die("Unknown Target Type: " . $targettype);
}

function GetMtoolTemplateFileByName($BuildToken, $project, $ProjectSourceOutput, $targettype, $templatename, $TemplateBaseDir)
{
	$DAhtmlTemplate = new htmlTemplateDBAccess();
	$htmlTemplate = $DAhtmlTemplate->GethtmlTemplateByName($targettype, $templatename, $ProjectSourceOutput->ProgramLanguage);
	if ($htmlTemplate) {
		return GetMtoolTemplateFile($BuildToken, $project, $ProjectSourceOutput, $htmlTemplate->FileName, $TemplateBaseDir, GetDefaultTemplateDir($targettype, $ProjectSourceOutput->ClassType));
	}
	AddMtoolDebugBuildMessage("No template. Skip: " . $templatename);
	return "";
}

function GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatename)
{
	return GetMtoolTemplateFileByName($BuildToken, $project, $ProjectSourceOutput, htmlTemplateTargetTypeEnum::$DB, $templatename, $ProjectSourceOutput->SourceTemplateDir);
}
function GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatename)
{
	$html_template_target_type = NULL;
	switch($ProjectSourceOutput->ClassType)
	{
		case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			$html_template_target_type = htmlTemplateTargetTypeEnum::$PROXYSERVER;
			break;
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			$html_template_target_type = htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER;
			break;
	}
	return GetMtoolTemplateFileByName($BuildToken, $project, $ProjectSourceOutput, $html_template_target_type, $templatename, $ProjectSourceOutput->SourceTemplateDir);
}
function GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatename)
{
	$html_template_target_type = NULL;
	switch($ProjectSourceOutput->ClassType)
	{
		case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			$html_template_target_type = htmlTemplateTargetTypeEnum::$PROXYCLIENT;
			break;
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			$html_template_target_type = htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT;
			break;
	}
	return GetMtoolTemplateFileByName($BuildToken, $project, $ProjectSourceOutput, $html_template_target_type, $templatename, $ProjectSourceOutput->SourceTemplateDir);
}

function GetMtoolUnitTestTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatename, $TestGroup)
{
	$TemplateBaseDir = $ProjectSourceOutput->UnitTestTemplateDir;
	if ($TestGroup->UnitTestTemplateBaseDir != "") {
		$TemplateBaseDir = $TestGroup->UnitTestTemplateBaseDir;
	}
	return GetMtoolTemplateFileByName($BuildToken, $project, $ProjectSourceOutput, htmlTemplateTargetTypeEnum::$UNITTEST, $templatename, $TemplateBaseDir);
}
function GetMtoolHtmlTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatefile)
{
	return GetMtoolTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatefile, $ProjectSourceOutput->SourceTemplateDir, GetDefaultTemplateDir(htmlTemplateTargetTypeEnum::$HTML, NULL));
}
function GetMtoolLanguageResourceTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatename)
{
	return GetMtoolTemplateFileByName($BuildToken, $project, $ProjectSourceOutput, htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE, $templatename, $ProjectSourceOutput->SourceTemplateDir);
}

function GetMtoolTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templatefile, $TemplateBaseDir, $defaultTemplateDir)
{
	global $mtoolTemplateFileCacheHTForDropBox;
	global $mtoolTemplateNotFoundHTForDropBox;
	global $mtoolTemplateFileCacheHTForSystemDefault;
	global $DEFAULT_MTOOL_SETTING_DIR;
	
	$LoadedTemplateFromDropBox = false;
	if ($project->StorageType == ProjectStorageTypeEnum::$DROPBOX) {
		if (trim($TemplateBaseDir) != "") {
			
			$templatefileOnDropbox = pathCombine($TemplateBaseDir, $templatefile);
			
			// AddMtoolDebugBuildMessage("Template file on Dropbox: " . $templatefileOnDropbox);
			
			$key_for_template = $project->DropboxBaseFolderName . " - " . $templatefileOnDropbox;
			if (array_key_exists($key_for_template, $mtoolTemplateFileCacheHTForDropBox)) {
				return $mtoolTemplateFileCacheHTForDropBox[$key_for_template];
			}
			
			$fileIsNotFoundOnServerLastTime = false;
			if (array_key_exists($key_for_template, $mtoolTemplateNotFoundHTForDropBox)) {
				if ($mtoolTemplateNotFoundHTForDropBox[$key_for_template]) {
					$fileIsNotFoundOnServerLastTime = true;
				}
			}
			
			if (!$fileIsNotFoundOnServerLastTime) {
				
				// Check From Cache in DB
				$DABuildTokenTemplateCache = new BuildTokenTemplateCacheDBAccess();
				$BuildTokenTemplateCache = $DABuildTokenTemplateCache->GetBuildTokenTemplateCache($key_for_template, $project->PID, $BuildToken->PID);
				if ($BuildTokenTemplateCache) {
					// Cache Exists in Database
					if ($BuildTokenTemplateCache->FileExist == 1) {
						$template = $BuildTokenTemplateCache->Source;
						$mtoolTemplateFileCacheHTForDropBox[$key_for_template] = $template;	// Save to Cache
						
					} else {
						$mtoolTemplateNotFoundHTForDropBox[$key_for_template] = true;
					}
					
				} else {
					// Cache is not exist in Database
					$tempTemplateFileToBeDeleted = tempnam(sys_get_temp_dir(), $templatefile);
					AddMtoolDebugBuildMessage("     => Loading Template file into local from DropBox Server. " . $ProjectSourceOutput->ProgramLanguage . "/" . $templatefile);
					
					$result = GetFileFromDropBoxByDropboxBaseFolderPID("", $project->DropboxBaseFolderPID, $templatefileOnDropbox, $tempTemplateFileToBeDeleted, -1, 3);
					
					if ($result->Success) {
						
						$thisNewTemplateCache = new BuildTokenTemplateCacheData();
						$thisNewTemplateCache->ProjectPID = $project->PID;
						$thisNewTemplateCache->BuildTokenPID = $BuildToken->PID;
						$thisNewTemplateCache->TemplateKey = $key_for_template;
						
						if ($result->FileExist) {
							AddMtoolDebugBuildMessage("     => Loaded Template file into local from DropBox Server. Cached.");
							// print_r($res);
							
							$template = file_get_contents($tempTemplateFileToBeDeleted);
							$mtoolTemplateFileCacheHTForDropBox[$key_for_template] = $template;	// Save to Cache
							
							if(is_file($tempTemplateFileToBeDeleted)) {
								unlink($tempTemplateFileToBeDeleted);
							}
							
							$thisNewTemplateCache->FileExist = "1";
							$thisNewTemplateCache->Source = $template;
							$DABuildTokenTemplateCache->InsertBuildTokenTemplateCache($thisNewTemplateCache);
							
							return $template;
							
						} else {
							$mtoolTemplateNotFoundHTForDropBox[$key_for_template] = true;
							if(is_file($tempTemplateFileToBeDeleted)) {
								unlink($tempTemplateFileToBeDeleted);
							}
							$thisNewTemplateCache->FileExist = "0";
							$DABuildTokenTemplateCache->InsertBuildTokenTemplateCache($thisNewTemplateCache);
							
							AddMtoolDebugBuildMessage("     => Template File is not exist in DropBox Server. Use System Default.");
						}
						
					} else {
						// Failed to connect to DropBox
						PrintOutMtoolBuildResultMessage();
						die("     => Failed to connect DropBox. Fatal Error. Aborted.");
					}
				}
			}
		}
	}
	$templatefileondir = "";
	if ($ProjectSourceOutput == NULL) {
		$templatefileondir = $DEFAULT_MTOOL_SETTING_DIR . $defaultTemplateDir . "/" . $templatefile;
	} else {
		$templatefileondir = $DEFAULT_MTOOL_SETTING_DIR . $defaultTemplateDir . "/" . $ProjectSourceOutput->ProgramLanguage . "/" . $templatefile;
	}
	
	if (array_key_exists($templatefileondir, $mtoolTemplateFileCacheHTForSystemDefault)) {
		return $mtoolTemplateFileCacheHTForSystemDefault[$templatefileondir];
	}
	if (file_exists($templatefileondir)) {
		$template = file_get_contents($templatefileondir);
		$mtoolTemplateFileCacheHTForSystemDefault[$templatefileondir] = $template;	// Save to Cache
		return $template;
	} else {
		AddMtoolDebugBuildMessage("Template file is not exist. Skip: " . $templatefileondir);
	}
	return "";
}
$mtoolTemplateFileCacheHTForDropBox = array();
$mtoolTemplateNotFoundHTForDropBox = array();
$mtoolTemplateFileCacheHTForSystemDefault = array();

$DEFAULT_MTOOL_SETTING_DIR = rtrim($MTOOL_SETTINGS_DIR, "/") . "/";

function GetReplaceParameterListFromTemplate($BuildToken, &$ReplaceParameterList, $project, $ProjectSourceOutput, $html, $htmlTemplatePID)
{
	$DAhtmlParameterDBAccess = new htmlParameterDBAccess();
	$DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate = new htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDBAccess();
	$htmlTemplateParameterList = $DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate->GethtmlTemplateParameterList($htmlTemplatePID);
	
	for($i = 0 ; $i < count($htmlTemplateParameterList) ; $i++) {
		$htmlTemplateParameter = $htmlTemplateParameterList[$i];
		
		$thisVariable = "";
		switch($htmlTemplateParameter->TargetValueType)
		{
			case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
				$htmlParameterList = NULL;
				if ($html != NULL) {
					$htmlParameterList = $DAhtmlParameterDBAccess->GethtmlParameterList($project->PID, $html->PID);
				}
				
				$isMatched = false;
				if ($htmlParameterList != NULL) {
					for($j = 0 ; $j < count($htmlParameterList) ; $j++) {
						$htmlParameter = $htmlParameterList[$j];
						if ($htmlTemplateParameter->ParameterName == $htmlParameter->ParameterName) {
							
							if ($isMatched) {
								AddMtoolErrorBuildMessage("WARNING: HTML Parameter Value setting is duplicated. Please check setting.");
							} else {
								$thisVariable = $htmlParameter->ParameterValue;
								$isMatched = true;
							}
						}
					}
				}
				if (!$isMatched) {
					AddMtoolErrorBuildMessage("WARNING: HTML Parameter Value is not set. Please check setting.");
				}
				break;
			case htmlTemplateParameterTargetValueTypeEnum::$CODE:
				$thisVariableOrClassObject = ${trim($htmlTemplateParameter->TargetVariableOrClassObject)};
				if (trim($htmlTemplateParameter->TargetPropertyOfClassObject) != "") {
					$thisPropertyName = trim($htmlTemplateParameter->TargetPropertyOfClassObject);
					$thisVariable = $thisVariableOrClassObject->$thisPropertyName;
				} else {
					$thisVariable = $thisVariableOrClassObject;
				}
				break;
			case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
				$DAhtmlTemplate = new htmlTemplateDBAccess();
				$AnotherHtmlTemplate = $DAhtmlTemplate->GethtmlTemplate($htmlTemplateParameter->AnotherTemplatePID);
				if ($AnotherHtmlTemplate) {
					$ReplaceParameterListForAnotherTemplate = array();
					GetReplaceParameterListFromTemplate($BuildToken, $ReplaceParameterListForAnotherTemplate, $project, $ProjectSourceOutput, $html, $htmlTemplateParameter->AnotherTemplatePID);
					
					$thisVariable = GetMtoolHtmlTemplateFile($BuildToken, $project, $ProjectSourceOutput, $AnotherHtmlTemplate->FileName);
					$thisVariable = ReplaceOutputSourceInfoByKeyValue($thisVariable, $ReplaceParameterListForAnotherTemplate);
					
				} else {
					AddMtoolErrorBuildMessage("Warning: HTML Template is not found for PID: " . $htmlTemplateParameter->AnotherTemplatePID . "  ... Skip.");
				}
				break;
		}
		array_push($ReplaceParameterList, 
			array("KEY"=>$htmlTemplateParameter->ParameterName, "VALUE"=>$thisVariable, "TRIMLASTSPACE"=>($htmlTemplateParameter->TrimLastSpace == 1), "TRIMLASTRETURN"=>($htmlTemplateParameter->TrimLastReturn == 1))
			);
	}
}

function CheckIfNeedToUpdate($tempOriginalFilePath, $tempOutputFilePath)
{
	$needToUpdate = true;
	
	if (file_exists($tempOriginalFilePath) && file_exists($tempOutputFilePath)) {
		$originalLinesOneText = file_get_contents($tempOriginalFilePath);
		$outputLinesOneText = file_get_contents($tempOutputFilePath);
		
		$needToUpdate = CheckIfNeedToUpdateForMultiText($originalLinesOneText, $outputLinesOneText);
	}
	return $needToUpdate;
}
function CheckIfNeedToUpdateForMultiText($originalLinesOneText, $outputLinesOneText)
{
	$needToUpdate = true;
	
	$originalLines = preg_split("/\r?\n/", $originalLinesOneText);
	$outputLines = preg_split("/\r?\n/", $outputLinesOneText);
	
	if (count($originalLines) == count($outputLines)) {
		for ($i = 0 ; $i < count($originalLines) ; $i++) {
			if (rtrim($originalLines[$i]) != rtrim($outputLines[$i])) {
				return true;
			}
		}
		return false;
	}
	return $needToUpdate;
}

function GetRelativePathToLocalFile($DefaultStoreBasePath, $StoreBasePath)
{
	// print "GetRelativePathToLocalFile\n";
	// print "  DefaultStoreBasePath: $DefaultStoreBasePath\n";
	// print "  StoreBasePath: $StoreBasePath\n";
	
	$relativePathToTargetFile = "";
	
	if ($StoreBasePath == "") {
		// In this case, use Project's default. So, return ""
		return "";
	}
	if ( $DefaultStoreBasePath == $StoreBasePath) {
		// If same path, there is no relative path needed
		return "";
	}
	if ( $DefaultStoreBasePath == "") {
		// Something strange but just return ""
		return "";
	}
	
	$defaultPaths = preg_split("/\\//", $DefaultStoreBasePath);
	$storePaths = preg_split("/\\//", $StoreBasePath);
	// for ($i = 0 ; $i < count($defaultPaths) ; $i++) {
	// 	print "    $i:  $defaultPaths[$i]\n";
	// }
	// for ($i = 0 ; $i < count($storePaths) ; $i++) {
	// 	print "    $i:  $storePaths[$i]\n";
	// }
	
	$startIndex = 0;
	for ($i = 0 ; $i < count($storePaths) ; $i++) {
		if ($i < count($defaultPaths)) {
			if ($storePaths[$i] == $defaultPaths[$i]) {
				// Same Path until here. So, it can be start next
				$startIndex = $i + 1;
			} else {
				break;
			}
		} else {
			break;
		}
	}
	$result = "";
	if ($startIndex < count($defaultPaths)) {
		for ($i = $startIndex ; $i < count($defaultPaths) ; $i++) {
			if ($result != "") {
				$result .= "/";
			}
			$result .= "..";
		}
	}

	if ($startIndex < count($storePaths)) {
		for ($i = $startIndex ; $i < count($storePaths) ; $i++) {
			if ($result != "") {
				$result .= "/";
			}
			$result .= $storePaths[$i];
		}
	}
	if ($result != "") {
		$result .= "/";
	}
	// print "Relative Path: $result\n";
	return $result;
}

function GetAllEditableAreaName($SourceLines)
{
	$editableAreaNameList = array();
	
	if (is_array($SourceLines)) {
		
		for($i = 0 ; $i < count($SourceLines); $i++) {
			$line = $SourceLines[$i];
			
			if (preg_match("/\s*\/\/\s*=+\s*START\s*OF\s*EDITABLE\s*AREA\s*FOR\s*\"?([^=\"]+)\"?\s*=+/i", $line, $matches)) {
				$thisName = trim($matches[1]);
				
				array_push($editableAreaNameList, $thisName);
				
				// print "Found Editable Area by Name: " . $thisName . "\n";
			}
		}
	}
	return $editableAreaNameList;
}

function GetUserCustomCode($Lines, $prescan)
{
	return GetUserCustomCodeWithNo($Lines, $prescan, "");
}
function GetUserCustomCodeWithNo($Lines, $prescan, $no)
{
	return GetUserCustomCodeSub($Lines, $prescan,
				get_user_custom_code_regex_end_with_no($no),
				get_user_custom_code_regex_start_with_no($no));
}
function GetUserCustomCodeWithName($Lines, $prescan, $name)
{
	return GetUserCustomCodeSub($Lines, $prescan,
				get_user_custom_code_regex_end_with_name($name),
				get_user_custom_code_regex_start_with_name($name));
}

function GetUserCustomCodeSub($Lines, $prescan, $startRegex, $endRegex)
{
	// print "startRegex: $startRegex\n";
	// print "endRegex: $endRegex\n";
	
	$userCustomSource = NULL;
	
	if (is_array($Lines)) {
		
		$inEditableArea = false;
		$isFirstLineOfEditableArea = true;
		
		for($i = 0 ; $i < count($Lines); $i++) {
			$line = $Lines[$i];
			
			if ($prescan[$i] && preg_match("/" . $startRegex . "/i", $line)) {
				$inEditableArea = false;
			}
			if ($inEditableArea) {
				if (!$isFirstLineOfEditableArea) {
					$userCustomSource .= "\n";
				}
				$userCustomSource .= $line;
				$isFirstLineOfEditableArea = false;
			}
			if ($prescan[$i] && preg_match("/" . $endRegex . "/i", $line)) {
				$inEditableArea = true;
			}
		}
	}
	return $userCustomSource;
}
function replace_user_custom_code_with_no($no, $editable_code, $Lines, $prescan, &$is_changed)
{
	return replace_user_custom_code_sub($editable_code, $Lines, $prescan, $is_changed,
				get_user_custom_code_regex_end_with_no($no),
				get_user_custom_code_regex_start_with_no($no));
}
function replace_user_custom_code_with_name($name, $editable_code, $Lines, $prescan, &$is_changed)
{
	return replace_user_custom_code_sub($editable_code, $Lines, $prescan, $is_changed,
				get_user_custom_code_regex_end_with_name($name),
				get_user_custom_code_regex_start_with_name($name));
}
function replace_user_custom_code_sub($editable_code, $Lines, $prescan, &$is_changed, $startRegex, $endRegex)
{
	$result = array();
	
	if (is_array($Lines)) {
		
		$inEditableArea = false;
		
		for($i = 0 ; $i < count($Lines); $i++) {
			$line = $Lines[$i];
			
			if ($prescan[$i] && preg_match("/" . $startRegex . "/i", $line)) {
				$inEditableArea = false;
				$is_changed = true;
			}
			if (!$inEditableArea) {
				array_push($result, $line);
			}
			if ($prescan[$i] && preg_match("/" . $endRegex . "/i", $line)) {
				$inEditableArea = true;
				
				if ($editable_code != "") {
					array_push($result, $editable_code);
				}
			}
		}
	}
	return $result;
}
function get_user_custom_code_regex_start_with_no($no)
{
	return "\s*\/\/\s*=+\s*START\s*OF\s*EDITABLE\s*AREA" . preg_quote($no, '/') . "\s*=+";
}
function get_user_custom_code_regex_end_with_no($no)
{
	return "\s*\/\/\s*=+\s*END\s*OF\s*EDITABLE\s*AREA" . preg_quote($no, '/') . "\s*=+";
}
function get_user_custom_code_regex_start_with_name($name)
{
	return "\s*\/\/\s*=+\s*START\s*OF\s*EDITABLE\s*AREA\s*FOR\s*\"?" . preg_quote($name, '/') . "\"?\s*=+";
}
function get_user_custom_code_regex_end_with_name($name)
{
	return "\s*\/\/\s*=+\s*END\s*OF\s*EDITABLE\s*AREA\s*FOR\s*\"?" . preg_quote($name, '/') . "\"?\s*=+";
}

function DoPrescanForUserCustomTemplate($Lines)
{
	$result = array();
	
	if (is_array($Lines)) {
		for($i = 0 ; $i < count($Lines); $i++) {
			$line = $Lines[$i];
			
			$control_line = false;
			if (preg_match("/^\s*\/\//", $line)) {
				$control_line = true;
			}
			array_push($result, $control_line);
		}
	}
	return $result;
}

?>
