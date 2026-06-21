<?PHP

$CONFIG_TOKEN_VALUE_NAME = "TOKEN";		// TODO: Add Setting in somewhere
$CONFIG_TOKEN_TYPE_NAME = "string";		// TODO: Add Setting in somewhere
$CONFIG_LOGIN_COOKIE_TOKEN_VALUE_NAME = "LOGIN_COOKIE_TOKEN";		// TODO: Add Setting in somewhere
$CONFIG_LOGIN_COOKIE_TOKEN_TYPE_NAME = "string";		// TODO: Add Setting in somewhere
$CONFIG_INSERT_TOKEN_VALUE_NAME = "InsertToken";	// TODO: Add Setting in somewhere
$CONFIG_INSERT_TOKEN_TYPE_NAME = "string";			// TODO: Add Setting in somewhere

function MakeproxyclientSource($BuildToken, $project, $ProjectSourceOutput, $da, $basename, &$anyOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, &$CHeaderIncludeForProxyResultList, &$CHeaderIncludeForRequestParamsList, &$CHeaderIncludeForClassesInFunctionList, &$CHeaderIncludeForDefineDelegate)
{
	$automaticallyOutputSource = "";
	
	$DAdafunc = new dafuncDBAccess();
	$dafunclist = $DAdafunc->GetdafuncList($project->PID, $da->PID);
	AddMtoolDebugBuildMessage("  => Get dafunc");
	for($k = 0 ; $k < count($dafunclist); $k++) {
		$dafunc = $dafunclist[$k];
		AddMtoolDebugBuildMessage("  => Action Type: " . GetDAFuncActionTypeCaption($dafunc->ActionType) . " Name:" . $dafunc->name);
		AddMtoolDebugBuildMessage("  => Project PID:" . $dafunc->ProjectPID . "  daPID:" . $dafunc->daPID . " PID:" . $dafunc->PID);
		
		$automaticallyOutputSource .= MakeproxyclientSourceForOneFunc($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, false, false, $anyOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, $CHeaderIncludeForProxyResultList, $CHeaderIncludeForRequestParamsList, $CHeaderIncludeForClassesInFunctionList, $CHeaderIncludeForDefineDelegate);
	}
	return $automaticallyOutputSource;
}
$createdBaseClasses = false;

function MakeproxyclientSourceForOneFunc($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $forCustom, $ForList, &$anyOutput, $output_to_temp_folder, $output_after_copy_to_temp_folder, &$CHeaderIncludeForProxyResultList, &$CHeaderIncludeForRequestParamsList, &$CHeaderIncludeForClassesInFunctionList, &$CHeaderIncludeForDefineDelegate)
{
	$automaticallyOutputSource = "";
	
	if ($forCustom) {
		// For Custom, Force Create Classes
	} else {
		$DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
		$dafuncSimpleProxyForOneOutputSource = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxyForOneOutputSource($project->PID, $da->PID, $dafunc->PID, $ProjectSourceOutput->PID);
		if ($dafuncSimpleProxyForOneOutputSource) {
			// It's a target.
		} else {
			// No data. // Not a target.
			AddMtoolDebugBuildMessage("  => Not a target for Proxy");
			return;
		}
	}
	
	$dataclassname = CreateDataClassName($basename);		// DA ClassとData Classの名前が一致しているという前提。$basenameはDA由来で呼び出し可能
	$non_list_dataclassname = $dataclassname;
	$dataclassname = CheckIfListAndAddListClassSuffixIfList($ProjectSourceOutput, $dafunc, $dataclassname);
	
	$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
	
	if ($forCustom) {
		// For Custom, No need to output Function Source
	} else {
		// Normal Mode
		$proxyServerFileName = CreateProxyServerFileName($ProjectSourceOutput->TargtServerPSOProgramLanguage, $ProjectSourceOutput->TargtServerPSOCustomFileExtention, $basename, $sourceFunctionName);
		
		$databaseaccessclassName = "";
		if ($da) {
			$databaseaccessclassName = CreateDatabaseAccessClassName($da->name);
		}
		
		$requestParamsSourceForDBFunc = "";
		$requestParamsSourceForJQuery = "";
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				$sourceParamInfoList = NULL;
				GetdafuncSourceSub($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $sourceParamInfoList, true);
				
				$need_to_create_class_for_security = false;
				$security_check_dafunc = NULL;
				$security_check_da = NULL;
				CheckIfNeedToSetSecurityForProxyClient($project, $ProjectSourceOutput, $dataclassname, $dafunc->SingleProxy_AuthType, $dafunc->SingleProxy_SingleGetFuncPID, $security_check_dafunc, $security_check_da, $sourceParamInfoList, $need_to_create_class_for_security);
				
				MtoolCreateRequestParamsSourceForDBFuncAndJQuery($BuildToken, $project, $ProjectSourceOutput, $sourceParamInfoList, $requestParamsSourceForDBFunc, $requestParamsSourceForJQuery);
				
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				break;
			default:
				AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
				break;
		}
		
		$template = GetproxyclientFunctionTemplateSourceBasedOnLangAndFunctionType($BuildToken, $project, $ProjectSourceOutput, false);
		$template = ReplaceOutputSourceInfoByKeyValue($template, array(
				array("KEY"=>"__DB_OBJECT__",   "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__DB_ACCESS_CLASS_NAME__",   "VALUE"=>$databaseaccessclassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$basename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$sourceFunctionName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__BASE_URL__", "VALUE"=>$ProjectSourceOutput->GetTargtServerPSOProxyBaseURLWithLastSlush(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__REQUEST_URL__", "VALUE"=>$proxyServerFileName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__REQUEST_PARAMS_FOR_DBFUNC__", "VALUE"=>$requestParamsSourceForDBFunc, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
				array("KEY"=>"__REQUEST_PARAMS_FOR_JQUERY__", "VALUE"=>$requestParamsSourceForJQuery, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
			));
		$automaticallyOutputSource .= $template;
		
		AddMtoolCHeaderIncludeForClassesInFunction($ProjectSourceOutput, $CHeaderIncludeForClassesInFunctionList, $basename, $sourceFunctionName);
		AddMtoolCHeaderIncludeForDefineDelegate($ProjectSourceOutput, $CHeaderIncludeForDefineDelegate, $basename, $sourceFunctionName);
	}
	
	// Create Request Params Class
	$AddTokenParam = !$forCustom;
	$classnamesuffix = "";
	$sourceParamInfoList = NULL;
	MakeproxyclientSourceForCreatingRequestParamsClass($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $dataclassname, $non_list_dataclassname, $sourceFunctionName, $forCustom, true, $ForList, $AddTokenParam, NULL, $sourceParamInfoList, $output_to_temp_folder, $output_after_copy_to_temp_folder, $dafunc->SingleProxy_AuthType, $dafunc->SingleProxy_SingleGetFuncPID, false, $classnamesuffix);

	if ($forCustom) {
		// For Custom, No need to output Function Source
	} else {
		// Normal Mode
		AddMtoolCHeaderIncludeForProxyResult($ProjectSourceOutput, $CHeaderIncludeForProxyResultList, $basename, $sourceFunctionName);
		AddMtoolCHeaderIncludeForRequestParams($ProjectSourceOutput, $CHeaderIncludeForRequestParamsList, $basename, $sourceFunctionName, $classnamesuffix);
	}
	
	if (!$forCustom) {
		// Create Proxy Result
		MakeproxyclientSourceForCreatingProxyResult($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $dataclassname, $non_list_dataclassname, $sourceFunctionName, $forCustom, $ForList, NULL, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	}
	
	// Async Task Loader
	if ($forCustom) {
		// For Custom
	} else {
		MakeproxyclientSourceForAsyncTaskLoader($BuildToken, $project, $ProjectSourceOutput, $basename, $sourceFunctionName, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	}
	
	$anyOutput = true;
	
	return $automaticallyOutputSource;
}

function MtoolMakeMultipleParameterList($sourceParamInfoList, $template, &$requestParamsSource)
{
	for ($j = 0 ; $j < count($sourceParamInfoList) ; $j++) {
		$sourceParamInfo = $sourceParamInfoList[$j];

		$comma = "";
		if ($j != count($sourceParamInfoList) - 1) {
			$comma = ",";
		}
		$requestParamsSource .= ReplaceOutputSourceInfoByKeyValue($template, array(
				array("KEY"=>"__PARAM_NAME__", "VALUE"=>$sourceParamInfo->name,     "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__DATA_TYPE__",  "VALUE"=>$sourceParamInfo->datatype, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
				array("KEY"=>"__COMMA__",      "VALUE"=>$comma,                     "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
	}
}

function MtoolCreateRequestParamsSourceForDBFuncAndJQuery($BuildToken, $project, $ProjectSourceOutput, $sourceParamInfoList, &$requestParamsSourceForDBFunc, &$requestParamsSourceForJQuery)
{
	$requestParamsSourceForDBFunc = "";
	$requestParamsSourceForJQuery = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			if ($sourceParamInfoList != NULL && is_array($sourceParamInfoList)) {
				$sourceParamInfoListForDBFunc = array();
				for ($j = 0 ; $j < count($sourceParamInfoList) ; $j++) {
					$sourceParamInfo = $sourceParamInfoList[$j];
					
					if (!$sourceParamInfo->IsOnlyForProxy) {
						array_push($sourceParamInfoListForDBFunc, $sourceParamInfo);
					}
				}
				MtoolMakeMultipleParameterList($sourceParamInfoListForDBFunc, GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-PARAM-FOR-DB"), $requestParamsSourceForDBFunc);
				MtoolMakeMultipleParameterList($sourceParamInfoList,          GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-PARAM-FOR-PROXY"), $requestParamsSourceForJQuery);
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
			break;
	}
}

function GetproxyclientFunctionTemplateSourceBasedOnLangAndFunctionType($BuildToken, $project, $ProjectSourceOutput, $for_custom)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-FOR-DB-OR-PROXY") .
			            GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-FOR-PROXY");
			$dbcall_template = "";
			if (!$for_custom) {
				$dbcall_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-DB-CALL");
			}
			$template = ReplaceOutputSourceInfoByKeyValue($template, array(
					array("KEY"=>"__DB_CALL__", "VALUE"=>$dbcall_template, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
				));
			return $template;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			switch($ProjectSourceOutput->JavaFunctionType)
			{
				case ProjectSourceOutputJavaFunctionTypeEnum::$DEFAULT:
				case ProjectSourceOutputJavaFunctionTypeEnum::$BOTH:
					return GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION") .
					       GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-FOR-ANDROID");
				case ProjectSourceOutputJavaFunctionTypeEnum::$ANDROIDASYNCTASKLOADERONLY;
					return GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION-FOR-ANDROID");
				case ProjectSourceOutputJavaFunctionTypeEnum::$DIRECTONLY:
					return GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION");
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Java Function Type: " . $ProjectSourceOutput->JavaFunctionType);
					break;
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-FUNCTION");
}

function MakeproxyclientSourceForAsyncTaskLoader($BuildToken, $project, $ProjectSourceOutput, $basename, $sourceFunctionName, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			switch($ProjectSourceOutput->JavaFunctionType)
			{
				case ProjectSourceOutputJavaFunctionTypeEnum::$DEFAULT:
				case ProjectSourceOutputJavaFunctionTypeEnum::$BOTH:
				case ProjectSourceOutputJavaFunctionTypeEnum::$ANDROIDASYNCTASKLOADERONLY;
					AddMtoolGeneralBuildMessage(" => Create Async Task Loader");
					$async_task_loader_filename = CreateProxyClientAsyncTaskLoaderFileName($ProjectSourceOutput->ProgramLanguage, $basename, $sourceFunctionName);
					$template_async_task_loader = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-ASYNC-TASK-LOADER");
					$template_async_task_loader = ReplaceOutputSourceInfoByKeyValue($template_async_task_loader, array(
							array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$sourceFunctionName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$basename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $async_task_loader_filename, "", array(), $template_async_task_loader, $output_to_temp_folder, $output_after_copy_to_temp_folder);
					
					if ($result->Success) {
						// OK
					} else {
						AddMtoolErrorBuildMessage(" -> Error! Failed to update Async Task Loader file");
					}
					break;
				case ProjectSourceOutputJavaFunctionTypeEnum::$DIRECTONLY:
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Java Function Type: " . $ProjectSourceOutput->JavaFunctionType);
					break;
					
			}
			break;
			
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
}

function CheckIfNeedToSetSecurityForProxyClient($project, $ProjectSourceOutput, $thisclassname, $Proxy_AuthType, $Proxy_SingleGetFuncPID, &$security_check_dafunc, &$security_check_da, &$sourceParamInfoList, &$need_to_create_class_for_security)
{
	global $CONFIG_TOKEN_VALUE_NAME;
	global $CONFIG_TOKEN_TYPE_NAME;
	global $CONFIG_LOGIN_COOKIE_TOKEN_VALUE_NAME;
	global $CONFIG_LOGIN_COOKIE_TOKEN_TYPE_NAME;
	
	$datatype_for_token = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $CONFIG_TOKEN_TYPE_NAME);
	$datatype_for_login_cookie_token = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $CONFIG_LOGIN_COOKIE_TOKEN_TYPE_NAME);
	
	$need_to_add_token = false;
	$need_to_add_login_cookie_token = false;
	$need_to_add_get_func = false;
	CheckIfNeedToSetSecurityForProxyClientSub($project, $ProjectSourceOutput, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $need_to_add_token, $need_to_add_login_cookie_token, $need_to_add_get_func, $security_check_dafunc, $security_check_da);
	if ($need_to_add_token) {
		AddSourceParamInfoData($CONFIG_TOKEN_VALUE_NAME, $datatype_for_token, false, false, false, "", true, $sourceParamInfoList);
	}
	if ($need_to_add_login_cookie_token) {
		AddSourceParamInfoData($CONFIG_LOGIN_COOKIE_TOKEN_VALUE_NAME, $datatype_for_login_cookie_token, false, false, false, "", true, $sourceParamInfoList);
	}
	if ($need_to_add_get_func) {
		AddSourceParamInfoData(GetSecurityCheckJsonKeyName(), $thisclassname . GetSecurityCheckClassNameSuffixForProxyClient(), true, false, false, "", true, $sourceParamInfoList);
		$need_to_create_class_for_security = true;
	}
}
function CheckIfNeedToSetSecurityForProxyClientSub($project, $ProjectSourceOutput, $Proxy_AuthType, $Proxy_SingleGetFuncPID, &$need_to_add_token, &$need_to_add_login_cookie_token, &$need_to_add_get_func, &$security_check_dafunc, &$security_check_da)
{
	$need_to_add_token = false;
	$need_to_add_login_cookie_token = false;
	$need_to_add_get_func = false;
	
	switch($Proxy_AuthType) {
		case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			$need_to_add_token = true;
			break;
		case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
		case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
		case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
			break;
		case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
		$need_to_add_login_cookie_token = true;
			break;
		default:
			AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Auth Type: " . $Proxy_AuthType);
			return "";
	}
	switch($Proxy_AuthType) {
		case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
		case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
		case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
		case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
			break;
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
		case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
			if ($Proxy_SingleGetFuncPID > 0) {
				$DAdafunc = new dafuncDBAccess();
				$security_check_dafunc = $DAdafunc->Getdafunc($Proxy_SingleGetFuncPID, $project->PID);
				if ($security_check_dafunc) {
					$thisBaseDataClassName = $security_check_dafunc->GetBaseDataClassName();
					
					$DAda = new daDBAccess();
					$security_check_da = $DAda->Getda($security_check_dafunc->daPID, $project->PID);
					if ($security_check_da) {
						// $dataclassname = GetClassNameForRequestParams($ProjectSourceOutput, $thisBaseDataClassName, GetFunctionNameFromFunctionActionType($security_check_dafunc->name, $security_check_dafunc->ActionType)) . GetSecurityCheckClassNameSuffixForProxyClient();
					
						$need_to_add_get_func = true;
					}
				}
			}
			break;
		default:
			AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Auth Type: " . $Proxy_AuthType);
			return "";
	}
}

class ProxyclientSourceForCreatingRequestParamsClassNameInfo
{
	public $ClassName;
	public $SetValueNameByNo = false;
	public $IsToken = false;
}

function MakeproxyclientSourceForCreatingRequestParamsClass($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $dataclassname, $non_list_dataclassname, $sourceFunctionName, $forCustom, $ForceSimple, $ForList, $AddTokenParam, $ParamClassNameList, &$sourceParamInfoList, $output_to_temp_folder, $output_after_copy_to_temp_folder, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $forSecurityParam, &$classnamesuffix)
{
	global $CONFIG_INSERT_TOKEN_VALUE_NAME;
	global $CONFIG_INSERT_TOKEN_TYPE_NAME;
	
	$CHeaderIncludeForPropertyClass = array();
	$HeaderSourceForSerializeAndDeserializeForProxyResult = array();
	
	$datatype_for_insert_token = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $CONFIG_INSERT_TOKEN_TYPE_NAME);
	
	$thisclassname = GetClassNameForRequestParams($ProjectSourceOutput, $basename, $sourceFunctionName);
	if ($forSecurityParam) {
		$thisclassname .= GetSecurityCheckClassNameSuffixForProxyClient();
	}
	$classnamesuffix = "";
	if ($forSecurityParam) {
		$classnamesuffix = GetSecurityCheckClassNameSuffixForProxyClient();
	}
	$localfilename = CreateRequestParamsClassName($ProjectSourceOutput, $thisclassname);
	
	$need_to_create_class_for_security = false;
	$security_check_dafunc = NULL;
	$security_check_da = NULL;
	
	$sourceParamInfoList = NULL;
	$requestParamsSource = "";
	if ($ForceSimple || !$forCustom) {
		// Simple Mode
		GetdafuncSourceSub($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $sourceParamInfoList, true);
		if ($AddTokenParam) {
			CheckIfNeedToSetSecurityForProxyClient($project, $ProjectSourceOutput, $thisclassname, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $security_check_dafunc, $security_check_da, $sourceParamInfoList, $need_to_create_class_for_security);
		}
		if ($dafunc->IsInsertFunction()) {
			AddSourceParamInfoData($CONFIG_INSERT_TOKEN_VALUE_NAME, $datatype_for_insert_token, false, false, false, "", true, $sourceParamInfoList);
		}

	} else {
		// For Custom
		$sourceParamInfoList = array();
		$no = 0;
		for($i = 0 ; $i < count($ParamClassNameList); $i++) {
			$thisObj = $ParamClassNameList[$i];
			
			$ParamName = "";
			$ParamClassName = "";
			if ($thisObj->IsToken) {
				CheckIfNeedToSetSecurityForProxyClient($project, $ProjectSourceOutput, $thisclassname, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $security_check_dafunc, $security_check_da, $sourceParamInfoList, $need_to_create_class_for_security);
				
			} else if ($thisObj->SetValueNameByNo) {
				$no++;
				$ParamName = GetCustomProxyRequestParamObjectName($no);
				$ParamClassName = $thisObj->ClassName;
				AddSourceParamInfoData(GetCustomProxyRequestParamObjectName($no), $thisObj->ClassName, true, false, false, "", true, $sourceParamInfoList);
			} else {
				PrintOutMtoolBuildResultMessage();
				die("Something wrong... This is not expected while making custom proxy param");
			}
		}
	}
	
	for ($j = 0 ; $j < count($sourceParamInfoList) ; $j++) {
		$sourceParamName       = $sourceParamInfoList[$j]->name;
		$sourceParamDataType   = $sourceParamInfoList[$j]->datatype;
		$sourceParamIsObject   = $sourceParamInfoList[$j]->IsObject;
		$sourceParamIsEnum     = $sourceParamInfoList[$j]->IsEnum;
		$sourceParamIsNullable = $sourceParamInfoList[$j]->IsNullable;
		$sourceParamBaseClassName = $sourceParamInfoList[$j]->BaseClassName;
		
		if (!$sourceParamIsObject) {
			$sourceParamDataType = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $sourceParamDataType);
		}
		
		$datatype_for_request_param_each_property = $sourceParamDataType;
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				if ($sourceParamIsEnum) {
					$datatype_for_request_param_each_property = "string";
				}
				if ($sourceParamIsNullable) {
					$datatype_for_request_param_each_property .= "?";
				}
				break;
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				if ($sourceParamIsObject) {
					AddMtoolCHeaderIncludeForMiscClass($ProjectSourceOutput, $CHeaderIncludeForPropertyClass, $sourceParamDataType);
					$datatype_for_request_param_each_property .= "*";
				}
				break;
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				break;
		}
		AddMtoolCHeaderIncludeForSerializeAndDeserialize($ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult, $sourceParamName, $sourceParamDataType, $sourceParamIsObject, $sourceParamIsEnum);
		
		$initial_value = "";
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
			case ProjectSourceOutputProgramLanguageEnum::$CS:
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				if ($sourceParamIsObject) {
					$initial_value = " = new " . $sourceParamDataType . "()";
				}
				$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-REQUEST-PARAMS-EACH-PROPERTY");
				$template = ReplaceOutputSourceInfoByKeyValue($template, array(
						array("KEY"=>"__PARAM_NAME__", "VALUE"=>$sourceParamName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__DATA_TYPE__", "VALUE"=>$datatype_for_request_param_each_property, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__INITIALIZE_VALUE__", "VALUE"=>$initial_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				$requestParamsSource .= $template;
				
				if ($sourceParamIsEnum) {
					$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-REQUEST-PARAMS-EACH-PROPERTY-ENUM");
					$template = ReplaceOutputSourceInfoByKeyValue($template, array(
							array("KEY"=>"__PARAM_NAME__", "VALUE"=>$sourceParamName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__DATA_TYPE__", "VALUE"=>$sourceParamDataType, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__DATA_CLASS_NAME__", "VALUE"=>$sourceParamBaseClassName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					$requestParamsSource .= $template;
				}
				break;
				
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				$InitialValue = GetMtoolInitialValueBasedOnDataType($project, $ProjectSourceOutput, $datatype_for_request_param_each_property, $sourceParamIsObject, $sourceParamIsEnum);
				
				$is_optional_val = false;
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
					case ProjectSourceOutputProgramLanguageEnum::$CS:
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						if ($sourceParamIsObject) {
							$is_optional_val = true;
						}
						break;
				}
				$requestParamsSource .= GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, $sourceParamName, $datatype_for_request_param_each_property, false, $InitialValue, $sourceParamIsEnum, $is_optional_val);
				break;
		}
	}
	if ($requestParamsSource != "") {
		$requestParamsSource .= GetReturnCodeForEachLang($ProjectSourceOutput);
	}
	
	$ReplaceParameterList = array(
			array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CLASS_NAME__", "VALUE"=>$dataclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$basename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CLASS_NAME_SUFFIX__", "VALUE"=>$classnamesuffix, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$sourceFunctionName, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PROPERTIES__", "VALUE"=>$requestParamsSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__INCLUDE_PROXY_REQUEST_PARAMS_HEADER__", "VALUE"=>GetHeaderSourceForRequestParams($BuildToken, $project, $ProjectSourceOutput, $basename, $sourceFunctionName, $classnamesuffix), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INCLUDE_FOR_PROPERTY___", "VALUE"=>GetHeaderSourceForMiscClassFromList(HeaderSourceForMiscClassTypeEnum::$PROXYCLIENT, "PROXYCLIENT-INCLUDE-PROPERTY-CLASS-HEADER", $BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForPropertyClass), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__SERIALIZE__", "VALUE"=>GetHeaderSourceForSerializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__SERIALIZE_DEFINE__", "VALUE"=>GetHeaderSourceForSerializetDefineForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INITIALIZE_PROPERTY__", "VALUE"=>GetHeaderSourceForInitializePropertyForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__DESERIALIZE__", "VALUE"=>GetHeaderSourceForDeserializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
		);
	$source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-REQUEST-PARAMS");
	
	$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	if ($result->Success) {
		// OK
	} else {
		AddMtoolErrorBuildMessage(" -> Error! Failed to update");
	}
	
	if (!$forCustom) {
		// Simple Mode
	} else {
		// For Custom
		if (!$ForList) {
			// Single
		} else {
			// List
			$listclassname = GetMtoolDataListClassName($ProjectSourceOutput, $thisclassname);
			$localfilename_for_list = CreateRequestParamsClassName($ProjectSourceOutput, $listclassname);
			
			$list_source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-REQUEST-PARAMS-LIST");
			
			$array_define_template = GetMtoolListDefinitionSource($BuildToken, $project, $ProjectSourceOutput);
			$array_define_template = ReplaceOutputSourceInfoByKeyValue($array_define_template, array(
				array("KEY"=>"__CLASS_NAME__", "VALUE"=>$thisclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
			$array_initialize_template = GetMtoolListInitializeSource($BuildToken, $project, $ProjectSourceOutput);
			$array_initialize_template = ReplaceOutputSourceInfoByKeyValue($array_initialize_template, array(
				array("KEY"=>"__CLASS_NAME__", "VALUE"=>$thisclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
			$array_serialize_template = GetMtoolListSerializeSource($BuildToken, $project, $ProjectSourceOutput);
			$array_serialize_template = ReplaceOutputSourceInfoByKeyValue($array_serialize_template, array(
				array("KEY"=>"__CLASS_NAME__", "VALUE"=>$thisclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
			
			$ReplaceParameterList = array(
					array("KEY"=>"__ARRAY_DEFINE__", "VALUE"=>$array_define_template, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__ARRAY_INITIALIZE__", "VALUE"=>$array_initialize_template, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
					array("KEY"=>"__ARRAY_SERIALIZE__", "VALUE"=>$array_serialize_template, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			  		array("KEY"=>"__ARRAY_ITEM_CLASS_", "VALUE"=>$thisclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			  		array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__BASE_CLASS_NAME__", "VALUE"=>$thisclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__CLASS_NAME__", "VALUE"=>$listclassname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				);
			$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename_for_list, "", $ReplaceParameterList, $list_source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
			if ($result->Success) {
				// OK
			} else {
				AddMtoolErrorBuildMessage(" -> Error! Failed to update list param");
			}
		}
	}
	if ($need_to_create_class_for_security) {
		$sourceParamInfoList_for_security = NULL;
		MakeproxyclientSourceForCreatingRequestParamsClass($BuildToken, $project, $ProjectSourceOutput, $security_check_da, $basename, $security_check_dafunc, $dataclassname, $non_list_dataclassname, $sourceFunctionName, false, true, false, false, NULL, $sourceParamInfoList_for_security, $output_to_temp_folder, $output_after_copy_to_temp_folder, NULL, NULL, true, $classnamesuffix);
	}
}

function GetSecurityCheckClassNameSuffixForProxyClient()
{
	return "ForSecurity";
}

function GetClassNameForRequestParams($ProjectSourceOutput, $basename, $sourceFunctionName)
{
	$thisclassname = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$thisclassname = CreateProxyRequestParamName($basename, $sourceFunctionName);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $thisclassname;
}

function CreateRequestParamsClassName($ProjectSourceOutput, $classname)
{
	$localfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return $classname . ".php";
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			return $classname . ".cs";
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			return $classname . ".java";
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			return $classname . ".h";
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			return $classname . ".m";
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return $classname . ".swift";
		default:
			PrintOutMtoolBuildResultMessage();
			die("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
	}
	PrintOutMtoolBuildResultMessage();
	die("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
}

function MakeproxyclientSourceForCreatingProxyResult($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $dataclassname, $non_list_dataclassname, $sourceFunctionName, $forCustom, $ForList, $ParamClassNameList, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	global $MTOOL_INSERT_ID_PROPERTY_NAME;
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			// Not a Target
			AddMtoolDebugBuildMessage("     => Not a target of Proxy Result Class");
			return;
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
	
//	$createObjectSource = "";
	$CHeaderIncludeForPropertyClass = array();
	$HeaderSourceForSerializeAndDeserializeForProxyResult = array();
	
	$localfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$localfilename = $basename . "Proxy" . $sourceFunctionName . "ProxyResult.php";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$localfilename = $basename . "Proxy" . $sourceFunctionName . "ProxyResult.cs";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			$localfilename = $basename . "Proxy" . $sourceFunctionName . "ProxyResult.java";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			$localfilename = $basename . "Proxy" . $sourceFunctionName . "ProxyResult.h";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$localfilename = $basename . "Proxy" . $sourceFunctionName . "ProxyResult.m";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$localfilename = $basename . "Proxy" . $sourceFunctionName . "ProxyResult.swift";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	
	$dataclassname_with_symbol = $dataclassname;
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$dataclassname_with_symbol .= "*";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
	}
	
	$propertySource = "";
	if (!$forCustom) {
		// Simple Mode
		switch($dafunc->ActionType) {
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
				$this_param_name = "Result";
				
				$propertySource .= GetMtoolProxyClientProxyResultEachProperty($BuildToken, $project, $ProjectSourceOutput, $this_param_name, $dataclassname_with_symbol, GetMtoolInitialValueForClass($ProjectSourceOutput, false));
				
				AddMtoolCHeaderIncludeForMiscClass($ProjectSourceOutput, $CHeaderIncludeForPropertyClass, $dataclassname);
				AddMtoolCHeaderIncludeForSerializeAndDeserialize($ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult, $this_param_name, $dataclassname, true, false);
				
//				$createObjectSource .= GetDataClassCreateObjectSource($BuildToken, $project, $ProjectSourceOutput, $this_param_name, $dataclassname);
				
				break;
			case dafuncActionTypeEnum::$INSERT:
				$this_data_type = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, "int");
				$InitialValue = GetMtoolInitialValueBasedOnDataType($project, $ProjectSourceOutput, $this_data_type, false, false);
				$propertySource .= GetMtoolProxyClientProxyResultEachProperty($BuildToken, $project, $ProjectSourceOutput, $MTOOL_INSERT_ID_PROPERTY_NAME, $this_data_type, $InitialValue);
				
				AddMtoolCHeaderIncludeForSerializeAndDeserialize($ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult, $MTOOL_INSERT_ID_PROPERTY_NAME, $this_data_type, false, false);
				
				break;
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				break;
		}
	} else {
		// For Custom
		for($i = 0 ; $i < count($ParamClassNameList); $i++) {
			$thisObj = $ParamClassNameList[$i];
			$no = $thisObj->No;
			$ParamName = $thisObj->ParamName;
			$ParamNameWithNo = $ParamName . $no;
			$ParamClassName = $thisObj->ParamClassName;
			$ForList = $thisObj->ForList;
			$IsClass = $thisObj->IsClass;
			$IsNullable = $thisObj->IsNullable;
			$IsEnum = false;
			
			if (!$IsClass) {
				$ParamClassName = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $ParamClassName);
			}
			if ($ForList) {
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						$ParamClassName .= "[]";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
						$ParamClassName = "System.Collections.Generic.List<" . $ParamClassName . ">";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						$ParamClassName .= "[]";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						$ParamClassName .= "NSArray";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						$ParamClassName .= "Array<" . $ParamClassName . ">";
						break;
				}
			}
			$ParamClassNameWithSymbol = $ParamClassName;
			
			if ($IsNullable) {
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
						$ParamClassNameWithSymbol .= "?";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						break;
				}
			}
			if ($IsClass) {
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
					case ProjectSourceOutputProgramLanguageEnum::$CS:
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						AddMtoolCHeaderIncludeForMiscClass($ProjectSourceOutput, $CHeaderIncludeForPropertyClass, $ParamClassName);
						$ParamClassNameWithSymbol .= "*";
						break;
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						break;
				}
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
					case ProjectSourceOutputProgramLanguageEnum::$CS:
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
//						$createObjectSource .= GetDataClassCreateObjectSource($BuildToken, $project, $ProjectSourceOutput, $ParamNameWithNo, $ParamClassName);
						break;
				}
			}
			AddMtoolCHeaderIncludeForSerializeAndDeserialize($ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult, $ParamNameWithNo, $ParamClassName, $IsClass, false);
			
			$InitialValue = GetMtoolInitialValueBasedOnDataType($project, $ProjectSourceOutput, $ParamClassName, $IsClass, $IsEnum);
			$propertySource .= GetMtoolProxyClientProxyResultEachProperty($BuildToken, $project, $ProjectSourceOutput, $ParamNameWithNo, $ParamClassNameWithSymbol, $InitialValue);
			
			if ($IsNullable) {
				$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-PROXY-RESULT-GET-PROPERTY-FOR-NULLABLE");
				$template = ReplaceOutputSourceInfoByKeyValue($template, array(
						array("KEY"=>"__PARAM_NAME__", "VALUE"=>$ParamNameWithNo, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__DATA_TYPE__", "VALUE"=>$ParamClassNameWithSymbol, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
			}
		}
	}
	if ($propertySource != "") {
		$propertySource .= GetReturnCodeForEachLang($ProjectSourceOutput);
	}
	
//	$initialize_property = ...;
//	$initialize_property_with_indent = "";
//	if ($initialize_property != "") {
//		$initialize_property_with_indent_list = array();
//		$initialize_property_list = preg_split("/\r?\n/", $initialize_property);
//		$add_indent = GetMtoolDefaultIndentForSource($ProjectSourceOutput);
//		for($i = 0 ; $i < count($initialize_property_list); $i++) {
//			$this_initialize_property = $initialize_property_list[$i];
//			if ($i != count($initialize_property_list) -1) {
//				$this_initialize_property = $add_indent . $this_initialize_property;
//			}
//			array_push($initialize_property_with_indent_list, $this_initialize_property);
//		}
//		$initialize_property_with_indent = implode("\n", $initialize_property_with_indent_list);
//	}
	
	$ReplaceParameterList = array(
			array("KEY"=>"__CS_NAMESPACE__", "VALUE"=>$ProjectSourceOutput->GetCSNameSpaceByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CLASS_NAME__", "VALUE"=>$dataclassname_with_symbol, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$basename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$sourceFunctionName, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>false),
//			array("KEY"=>"__CREATE_OBJECT__", "VALUE"=>$createObjectSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__PROPERTIES__", "VALUE"=>$propertySource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__JAVA_PACKAGE_NAME__", "VALUE"=>$ProjectSourceOutput->GetJavaPackageNameByConsideringDefault(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__INCLUDE_PROXY_RESULT_HEADER__", "VALUE"=>GetHeaderSourceForProxyResult($BuildToken, $project, $ProjectSourceOutput, $basename, $sourceFunctionName), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INCLUDE_FOR_PROPERTY___", "VALUE"=>GetHeaderSourceForMiscClassFromList(HeaderSourceForMiscClassTypeEnum::$PROXYCLIENT, "PROXYCLIENT-INCLUDE-PROPERTY-CLASS-HEADER", $BuildToken, $project, $ProjectSourceOutput, $CHeaderIncludeForPropertyClass), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__SERIALIZE__", "VALUE"=>GetHeaderSourceForSerializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__SERIALIZE_DEFINE__", "VALUE"=>GetHeaderSourceForSerializetDefineForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INITIALIZE_PROPERTY__", "VALUE"=>GetHeaderSourceForInitializePropertyForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
//			array("KEY"=>"__INITIALIZE_PROPERTY_WITH_INDENT__", "VALUE"=>$initialize_property_with_indent, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__DESERIALIZE__", "VALUE"=>GetHeaderSourceForDeserializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeForProxyResult), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
		);
	$source_template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-PROXY-RESULT");
	$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, "", $ReplaceParameterList, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	if ($result->Success) {
		// OK
	} else {
		AddMtoolErrorBuildMessage(" -> Error! Failed to update");
	}
}

class ResultClassNameDataClass
{
	public $ParamName;
	public $ParamClassName;
	public $No;
	public $ForList;
	public $IsClass;
	public $IsNullable;
}

function GetMtoolProxyClientProxyResultEachProperty($BuildToken, $project, $ProjectSourceOutput, $param_name, $data_type, $initial_value)
{
	$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-PROXY-RESULT-EACH-PROPERTY");
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__PARAM_NAME__", "VALUE"=>$param_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__DATA_TYPE__", "VALUE"=>$data_type, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__INITIAL_VALUE__", "VALUE"=>$initial_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}

function MakeMtoolProxyClientFileName($ProjectSourceOutput, $basename)
{
	$localfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$localfilename = $basename . "ProxyClient.php";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$localfilename = $basename . "ProxyClient.cs";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			$localfilename = $basename . "ProxyClient.java";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			$localfilename = $basename . "ProxyClient.h";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$localfilename = $basename . "ProxyClient.m";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$localfilename = $basename . "ProxyClient.swift";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return "";
	}
	return $localfilename;
}

function MakeMtoolCustomProxyHT($project, $ProjectSourceOutput)
{
	$DAda = new daDBAccess();
	$DAdafunc = new dafuncDBAccess();
	$DAdaCustomProxy = new daCustomProxyDBAccess();
	$DAdaCustomProxyFuncDBAccess = new daCustomProxyFuncDBAccess();
	$daCustomProxyList = $DAdaCustomProxy->GetdaCustomProxyList($project->PID);
	
	$daCustomProxyListGroupHT = array();
	for($i = 0 ; $i < count($daCustomProxyList); $i++) {
		$daCustomProxy = $daCustomProxyList[$i];

		if (CheckIfItIsATargetOfCustomProxy($project->PID, $daCustomProxy->PID, $ProjectSourceOutput->PID)) {
			// Yes. It's a target of output
		} else {
			// Not a target of output.
			// AddMtoolGeneralBuildMessage("-> Not a target of output");
			continue;
		}

		$key = $daCustomProxy->basename;

		if (!array_key_exists($key, $daCustomProxyListGroupHT)) {
			$daCustomProxyListGroupHT[$key] = array();
		}
		array_push($daCustomProxyListGroupHT[$key], $daCustomProxy);
	}
	return $daCustomProxyListGroupHT;
}

function InitializeMtoolCustomProxyParamClassNameList($project, $ProjectSourceOutput, $daCustomProxy)
{
	global $CONFIG_TOKEN_VALUE_NAME;
	
	$ParamClassNameList = array();
	$thisObj = new ProxyclientSourceForCreatingRequestParamsClassNameInfo();
	$thisObj->ClassName = $CONFIG_TOKEN_VALUE_NAME;
	$thisObj->SetValueNameByNo = false;
	$thisObj->IsToken = true;
	array_push($ParamClassNameList, $thisObj);
	
	return $ParamClassNameList;
}

function AddMtoolCustomProxyParamClassNameList(&$ParamClassNameList, $ProjectSourceOutput, $thisBaseClassName, $sourceFunctionName, $ForList)
{
	$paramclassname = CreateProxyRequestParamName($thisBaseClassName, $sourceFunctionName);
	if ($ForList) {
		$paramclassname = GetMtoolDataListClassName($ProjectSourceOutput, $paramclassname);
	}
	$thisObj = new ProxyclientSourceForCreatingRequestParamsClassNameInfo();
	$thisObj->ClassName = $paramclassname;
	$thisObj->SetValueNameByNo = true;
	$thisObj->IsToken = false;
	array_push($ParamClassNameList, $thisObj);
}

function AddMtoolAutoloadFilenameForProxyClientForSecurity($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $dataclassname, $sourceFunctionName, $forCustom, $ForceSimple, $AddTokenParam, $ParamClassNameList, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $forSecurityParam, &$autoload_filename_for_proxyclient_list)
{
	$thisclassname = GetClassNameForRequestParams($ProjectSourceOutput, $basename, $sourceFunctionName);
	if ($forSecurityParam) {
		$thisclassname .= GetSecurityCheckClassNameSuffixForProxyClient();
	}
	
	$need_to_create_class_for_security = false;
	$security_check_dafunc = NULL;
	$security_check_da = NULL;
	
	$sourceParamInfoList = NULL;
	if ($ForceSimple || !$forCustom) {
		// Simple Mode
		GetdafuncSourceSub($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $sourceParamInfoList, true);
		if ($AddTokenParam) {
			CheckIfNeedToSetSecurityForProxyClient($project, $ProjectSourceOutput, $thisclassname, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $security_check_dafunc, $security_check_da, $sourceParamInfoList, $need_to_create_class_for_security);
		}

	} else {
		// For Custom
		$sourceParamInfoList = array();
		$no = 0;
		for($i = 0 ; $i < count($ParamClassNameList); $i++) {
			$thisObj = $ParamClassNameList[$i];
			
			$ParamName = "";
			$ParamClassName = "";
			if ($thisObj->IsToken) {
				CheckIfNeedToSetSecurityForProxyClient($project, $ProjectSourceOutput, $thisclassname, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $security_check_dafunc, $security_check_da, $sourceParamInfoList, $need_to_create_class_for_security);
			}
		}
	}
	if ($need_to_create_class_for_security) {
		$security_request_filename = CreateRequestParamsClassName($ProjectSourceOutput, $dataclassname);
		AddToUnduplicatedList($autoload_filename_for_proxyclient_list, $security_request_filename);
	}
}

function MakeMtoolProxyRequestParamBaseClassName($combinedDataClassPropetyBaseClassName, $no, $da)
{
	return $combinedDataClassPropetyBaseClassName . "Step" . $no . $da->name;
}

?>
