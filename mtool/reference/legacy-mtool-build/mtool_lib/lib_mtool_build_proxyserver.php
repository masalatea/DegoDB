<?PHP

$DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_ORIGIN = "*";
$DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_HEADERS = "Origin, X-Requested-With, Content-Type, Accept";

function MakeproxyserverSource($BuildToken, $project, $ProjectSourceOutput, $da, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	$ForList = false;		// It's fixed for now but may be changed in the future.
	
	$DAdafunc = new dafuncDBAccess();
	$dafunclist = $DAdafunc->GetdafuncList($project->PID, $da->PID);
	AddMtoolDebugBuildMessage("  => Get dafunc");
	for($k = 0 ; $k < count($dafunclist); $k++) {
		$dafunc = $dafunclist[$k];
		AddMtoolDebugBuildMessage("  => Action Type: " . GetDAFuncActionTypeCaption($dafunc->ActionType) . " Name:" . $dafunc->name);
		AddMtoolDebugBuildMessage("  => Project PID:" . $dafunc->ProjectPID . "  daPID:" . $dafunc->daPID . " PID:" . $dafunc->PID);
		
		$DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
		$dafuncSimpleProxyForOneOutputSource = $DAdafuncSimpleProxySourceOutputTarget->GetdafuncSimpleProxyForOneOutputSource($project->PID, $da->PID, $dafunc->PID, $ProjectSourceOutput->PID);
		if ($dafuncSimpleProxyForOneOutputSource) {
			// It's a target.
		} else {
			// No data. // Not a target.
			AddMtoolDebugBuildMessage("  => Not a target for Proxy");
			continue;
		}
		
		$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
		$localfilename = CreateProxyServerFileName($ProjectSourceOutput->ProgramLanguage, $ProjectSourceOutput->CustomFileExtention, $da->name, $sourceFunctionName);
		
		$RESULT_NAME = "result";
		
		$ProxyParameterFormat = "";
		$ProxyParameterExample = "";
		$ProxyResultFormat = "";
		$ProxyResultExample = "";
		
		$func_source  = GetMakeproxyserverFunctionSource($BuildToken, $project, $ProjectSourceOutput, $da, $da->name, $dafunc, $sourceFunctionName, "", $RESULT_NAME, true, $ForList, $ProxyParameterFormat, $ProxyParameterExample, $ProxyResultFormat, $ProxyResultExample, $dafunc->SingleProxy_AuthType, $dafunc->SingleProxy_SingleGetFuncPID, true, false, $output_to_temp_folder, $output_after_copy_to_temp_folder);
		
		$reqult_initialize = "";
		$func_result_param = "";
		switch($dafunc->ActionType) {
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
				
				$reqult_initialize = GetMakeproxyserverValueInitializeSource($BuildToken, $project, $ProjectSourceOutput, "", $RESULT_NAME, false);
				
				$func_result_param = GetMakeproxyserverResultParamSource($BuildToken, $project, $ProjectSourceOutput, "", $RESULT_NAME);
				$func_result_param = replace_indent_for_list_for_mtool_build($ForList, $func_result_param);
				$func_result_param = replace_indent_for_check_if_already_inserted_for_mtool_build(false, $func_result_param);
				break;
			case dafuncActionTypeEnum::$INSERT:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				break;
		}
		
		MakeproxyserverSourceWriteToFile($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, NULL, NULL, $localfilename, $reqult_initialize, $func_source, $func_result_param, false, $output_to_temp_folder, $output_after_copy_to_temp_folder, $ProxyParameterFormat, $ProxyParameterExample, $ProxyResultFormat, $ProxyResultExample, NULL, $dafunc->SingleProxy_AuthType, $dafunc->SingleProxy_SingleGetFuncPID);
	}
}
function MakeproxyserverSourceWriteToFile($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $daCustomProxy, $daCustomProxyFuncList, $localfilename, $reqult_initialize, $func_source, $func_result_param, $transaction, $output_to_temp_folder, $output_after_copy_to_temp_folder, $ProxyParameterFormat, $ProxyParameterExample, $ProxyResultFormat, $ProxyResultExample, $InsertIDNoList, $Proxy_AuthType, $Proxy_SingleGetFuncPID)
{
	global $matsuesoft_login_token_id;
	global $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_ORIGIN;
	global $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_HEADERS;
	
	$func_result_param_for_list = $func_result_param;
	$func_result_param_for_list = replace_indent_for_list_for_mtool_build(true, $func_result_param_for_list);
	$func_result_param_for_list = replace_indent_for_check_if_already_inserted_for_mtool_build(false, $func_result_param_for_list);
	
	// 3 or 4 (for list) Indent for Error Check
	$func_source = ReplaceOutputSourceInfoByKeyValue($func_source, array(
			array("KEY"=>"__RESULT_PARAM__",          "VALUE"=>$func_result_param,          "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__RESULT_PARAM_FOR_LIST__", "VALUE"=>$func_result_param_for_list, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INDENT__",                "VALUE"=>"\t\t\t",                    "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	// Default is 1 Indent for other
	$func_result_param = ReplaceOutputSourceInfoByKeyValue($func_result_param, array(
			array("KEY"=>"__INDENT__", "VALUE"=>"\t\t", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	// Auto load filename
	$autoloadfilename = "";
	switch($ProjectSourceOutput->ProgramLanguage)
	{
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$autoloadfilename = $ProjectSourceOutput->AutoLoadFilePathForPHP;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
	}
	if ($autoloadfilename == "") {
		// If Blank, Search Default
		$autoloadfilename = GetProjectSourceOutputDefaultAutoloadFilename($project->PID, $ProjectSourceOutput->SourceOutputDir);
	}
	$autoload_source = "";
	if ($autoloadfilename != "") {
		$autoload_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-INCLUDE-AUTOLOAD");
		$autoload_source = ReplaceOutputSourceInfoByKeyValue($autoload_source, array(
				array("KEY"=>"__FILENAME__", "VALUE"=>$autoloadfilename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
	}
	
	// HTTP Header
	$http_header = "";
	$http_header_list = array();
	
	$proxy_header_of_access_control_allow_origin = $project->proxy_header_of_access_control_allow_origin;
	if ($project->proxy_header_of_access_control_allow_origin == "") {
		$proxy_header_of_access_control_allow_origin = $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_ORIGIN;
	}
	array_push($http_header_list, "header('Access-Control-Allow-Origin: " . $proxy_header_of_access_control_allow_origin . "');");
	
	$proxy_header_of_access_control_allow_headers = $project->proxy_header_of_access_control_allow_headers;
	if ($project->proxy_header_of_access_control_allow_headers == "") {
		$proxy_header_of_access_control_allow_headers = $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_HEADERS;
	}
	array_push($http_header_list, "header('Access-Control-Allow-Headers: " . $proxy_header_of_access_control_allow_headers . "');");
	
	if (count($http_header_list) > 0) {
		$http_header = implode("\n", $http_header_list);
	}
	
	// Include Files
	$include_files = "";
	if (CheckIfIncludeInsertFunctionInProxy($project, $dafunc, $daCustomProxy) ||
	    CheckIfLoginByLoginCookieTokenFunctionInProxy($dafunc, $daCustomProxy)) {
		$include_files .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-INCLUDE-LIB-MTOOL-WORK-DB-INSERT");
	}
	
	// Insert ID after Insert
	$insert_id_source = "";
	if ($dafunc) {
		// Simple Proxy
		switch($dafunc->ActionType) {
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			case dafuncActionTypeEnum::$INSERT:
				$insert_id_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-INSERT-ID-FOR-SINGLE");
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				break;
		}
	} else {
		// Custom Proxy
		if ($InsertIDNoList) {
			for($i = 0 ; $i < count($InsertIDNoList); $i++) {
				$no = $InsertIDNoList[$i];
				
				$this_insert_id_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-INSERT-ID-FOR-CUSTOM");
				$this_insert_id_source = ReplaceOutputSourceInfoByKeyValue($this_insert_id_source, array(
						array("KEY"=>"__RESULT_NO__", "VALUE"=>$no, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				
				$insert_id_source .= $this_insert_id_source;
			}
		}
	}
	
	// Return SQL
	$return_sql_source = "";
	if ($dafunc) {
		// Simple Proxy
		$return_sql_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-RETURN-SQL-FOR-SINGLE");
	} else {
		// Custom Proxy
	}
	
	// Security Check
	$security_check_source = "";
	$classbasename_for_checking_token = "";
	$functionname_for_checking_token = "";
	$requestParamsSource = "";
	switch($Proxy_AuthType) {
		case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-BY-PROJECT-TOKEN");
			break;
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-BY-PROJECT-TOKEN");
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-DEFINE");
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-BY-GET-FUNC-IF-NOT-YET");
			break;
		case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-DEFINE");
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-BY-GET-FUNC");
			break;
		case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-NOTHING");
			break;
		case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-MANUAL");
			break;
		case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
			$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-LOGIN-COOKIE");
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
					$functionname_for_checking_token = GetFunctionNameFromFunctionActionType($security_check_dafunc->name, $security_check_dafunc->ActionType);
					
					$DAda = new daDBAccess();
					$security_check_da = $DAda->Getda($security_check_dafunc->daPID, $project->PID);
					if ($security_check_da) {
						$classbasename_for_checking_token = $security_check_da->name;
						
						$ProxyParameterFormatLineList = array();
						$ProxyParameterExampleLineList = array();
						CreateRequestParamsSource($BuildToken, $project, $ProjectSourceOutput, $security_check_da, $security_check_dafunc, GetSecurityCheckJsonKeyName() . "->", $ProxyParameterFormatLineList, $ProxyParameterExampleLineList, $requestParamsSource);
					}
				}
			}
			break;
		default:
			AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Auth Type: " . $Proxy_AuthType);
			return "";
	}
	if ($security_check_source == "") {
		$security_check_source .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-SECURITY-CHECK-BY-PROJECT-TOKEN");
	}
	
	$TokenForProxyAccess = "";
	if ($ProjectSourceOutput->IsDBaaSProxy()) {
		$TokenForProxyAccess = $project->TokenForProxyAccess;
	} else {
		$TokenForProxyAccess = "<Define by User>";
	}
	
	$ReplaceParameterList = array(
			array("KEY"=>"__HTTP_HEADER__", "VALUE"=>$http_header, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INCLUDE_FILES__", "VALUE"=>$include_files, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__RESULT_INITIALIZE__", "VALUE"=>$reqult_initialize, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__BEGIN_TRANSACTION__", "VALUE"=>GetproxyserverTransactionBegin($BuildToken, $project, $ProjectSourceOutput, $transaction), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__COMMIT_TRANSACTION__", "VALUE"=>GetproxyserverTransactionCommit($BuildToken, $project, $ProjectSourceOutput, $transaction), "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__SECURITY_CHECK__", "VALUE"=>$security_check_source, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__CLASS_BASE_NAME_FOR_CHECKING_TOKEN__", "VALUE"=>$classbasename_for_checking_token, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME_FOR_CHECKING_TOKEN__", "VALUE"=>$functionname_for_checking_token, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__REQUEST_PARAMS_FOR_CHECKING_TOKEN__", "VALUE"=>$requestParamsSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INSERT_ID__", "VALUE"=>$insert_id_source, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__RETURN_SQL__", "VALUE"=>$return_sql_source, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$da->name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_CALL__", "VALUE"=>$func_source, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__RESULT_PARAM__", "VALUE"=>$func_result_param, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INDENT__", "VALUE"=>"\t", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__AUTOLOAD__", "VALUE"=>$autoload_source, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__TOKEN_FOR_PROXY_ACCESS__", "VALUE"=>$TokenForProxyAccess, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__INDENT_FOR_LIST__", "VALUE"=>"", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),		// Remove remaining indent
			array("KEY"=>"__INDENT_FOR_CHECK_IF_ALREADY_INSERTED__", "VALUE"=>"", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),		// Remove remaining indent
			array("KEY"=>"__DB_OBJECT__", "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		);
	$source_template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER");
	$source_template = ReplaceOutputSourceInfoByKeyValue($source_template, $ReplaceParameterList);
	
	// Add Indent for Custom Proxy
	if($daCustomProxyFuncList) {
		
		$source_template_list = preg_split("/\r?\n/", $source_template);
		$add_any_indent = false;
		
		for($j = 0 ; $j < count($daCustomProxyFuncList); $j++) {
			$daCustomProxyFunc = $daCustomProxyFuncList[$j];
			
			$no = ($j + 1);
			
			if ($daCustomProxyFunc->AddIndentCount > 0) {
				$add_any_indent = true;
				
				$this_base_indent = str_repeat("\t", $daCustomProxyFunc->AddIndentCount - 1);
				$this_indent = str_repeat("\t", $daCustomProxyFunc->AddIndentCount);
				$this_indent_top = "";
				$this_indent_bottom = "";
				switch($daCustomProxyFunc->AddIndentType)
				{
					case daCustomProxyFuncAddIndentTypeEnum::$DEFAULT:
						$this_indent_top = $this_base_indent;
						$this_indent_bottom = $this_base_indent;
						break;
					case daCustomProxyFuncAddIndentTypeEnum::$START:
						$this_indent_top = $this_base_indent;
						$this_indent_bottom = $this_indent;
						break;
					case daCustomProxyFuncAddIndentTypeEnum::$END:
						$this_indent_top = $this_indent;
						$this_indent_bottom = $this_base_indent;
						break;
					case daCustomProxyFuncAddIndentTypeEnum::$CONTINUE:
						$this_indent_top = $this_indent;
						$this_indent_bottom = $this_indent;
						break;
					default:
						PrintOutMtoolBuildResultMessage();
						die("Something Strange. Unknown Indent Type: " . $daCustomProxyFunc->AddIndentType);
				}
				
				$in_area = false;
				for($k = 0 ; $k < count($source_template_list); $k++) {
					$this_line = $source_template_list[$k];
					
					if (preg_match('/\/\/\s*==\s*START\s*OF\s*EDITABLE\s*AREA\s*FOR\s*STEP\s*' . $no . '\s*ON\s*TOP\s*==/i', $this_line)) {
						$source_template_list[$k] = $this_indent_top . $this_line;
						$in_area = true;
					} else if (preg_match('/\/\/\s*==\s*END\s*OF\s*EDITABLE\s*AREA\s*FOR\s*STEP\s*' . $no . '\s*ON\s*BOTTOM\s*==/i', $this_line)) {
						$source_template_list[$k] = $this_indent_bottom . $this_line;
						$in_area = false;
					} else if ($in_area) {
						$source_template_list[$k] = $this_indent . $this_line;
					}
				}
			}
		}
		if ($add_any_indent) {
			$source_template = implode("\n", $source_template_list);
		}
	}
	$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $localfilename, "", NULL, $source_template, $output_to_temp_folder, $output_after_copy_to_temp_folder);
	
	if ($result->Success) {
		// OK
		
		$ProxyParameterFormat = ReplaceOutputSourceInfoByKeyValue($ProxyParameterFormat, $ReplaceParameterList);
		$ProxyParameterExample = ReplaceOutputSourceInfoByKeyValue($ProxyParameterExample, $ReplaceParameterList);
		$ProxyResultFormat = ReplaceOutputSourceInfoByKeyValue($ProxyResultFormat, $ReplaceParameterList);
		$ProxyResultExample = ReplaceOutputSourceInfoByKeyValue($ProxyResultExample, $ReplaceParameterList);
		$ProxyParameterForJquery = "";
		$ProxyParameterExampleForJquery = "";
		$ProxyParameterExampleForPHP = "";
		$ProxyParameterExampleForPerl = "";
		$ProxyParameterExampleForRuby = "";
		$ProxyResultFormatForJquery = "";
		
		$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
		if ($dafunc) {
			// Simple Proxy.
			$BuildSourceFuncCacheOfDA = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByDAFunc($project->PID, $da->PID, $dafunc->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$DA, $ProjectSourceOutput->ReleaseTargetType);
			if ($BuildSourceFuncCacheOfDA) {
				$thisParameterListStringForProxyBasedOnDA = "";
				$thisParameterListStringForProxyBasedOnDAForExample = "";
				if ($BuildSourceFuncCacheOfDA) {
					$thisParameterListStringForProxyBasedOnDA = $BuildSourceFuncCacheOfDA->ParameterListStringForProxyBasedOnDA;
					$thisParameterListStringForProxyBasedOnDAForExample = $BuildSourceFuncCacheOfDA->ParameterListStringForProxyBasedOnDAForExample;
				}
				$thisParameterListStringForProxyBasedOnDA           = ReplaceIndentForCustomProxy($thisParameterListStringForProxyBasedOnDA, "");
				$thisParameterListStringForProxyBasedOnDAForExample = ReplaceIndentForCustomProxy($thisParameterListStringForProxyBasedOnDAForExample, "");
				
				$json_parameter = ReplaceOutputSourceInfoByKeyValue($ProxyParameterFormat, array(
						array("KEY"=>"__PARAM_OF_OBJECT__", "VALUE"=>$thisParameterListStringForProxyBasedOnDA, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				$json_parameter = ReplaceIndentForCustomProxy($json_parameter, "");
				
				$ProxyParameterForJquery = $json_parameter;
				$ProxyParameterForJquery = ReplaceIndentForParamOfObject($ProxyParameterForJquery, "");
				$ProxyParameterForJquery = ReplaceArrayStartForParamOfObject("jquery", $ProxyParameterForJquery);
				$ProxyParameterForJquery = ReplaceArrayEndForParamOfObject("jquery", $ProxyParameterForJquery);
				$ProxyParameterForJquery = ReplaceArrayStringProxyParamOrResultForJquery($ProxyParameterForJquery);
				
				$json_parameter_for_example_base = ReplaceOutputSourceInfoByKeyValue($ProxyParameterExample, array(
						array("KEY"=>"__PARAM_OF_OBJECT__", "VALUE"=>$thisParameterListStringForProxyBasedOnDAForExample, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				$json_parameter_for_example_base = ReplaceIndentForCustomProxy($json_parameter_for_example_base, "");
				
				$ProxyParameterExampleForJquery = $json_parameter_for_example_base;
				$ProxyParameterExampleForJquery = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForJquery, ":");
				$ProxyParameterExampleForJquery = ReplaceIndentForParamOfObject($ProxyParameterExampleForJquery, "    ");
				$ProxyParameterExampleForJquery = ReplaceArrayStartForParamOfObject("Jquery", $ProxyParameterExampleForJquery);
				$ProxyParameterExampleForJquery = ReplaceArrayEndForParamOfObject("Jquery", $ProxyParameterExampleForJquery);
				$ProxyParameterExampleForJquery = ReplaceArrayStringProxyParamOrResultForJquery($ProxyParameterExampleForJquery);
				$ProxyParameterExampleForPHP = $json_parameter_for_example_base;
				$ProxyParameterExampleForPHP = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForPHP, "=>");
				$ProxyParameterExampleForPHP = ReplaceIndentForParamOfObject($ProxyParameterExampleForPHP, "    ");
				$ProxyParameterExampleForPHP = ReplaceArrayStartForParamOfObject("PHP", $ProxyParameterExampleForPHP);
				$ProxyParameterExampleForPHP = ReplaceArrayEndForParamOfObject("PHP", $ProxyParameterExampleForPHP);
				$ProxyParameterExampleForPHP = ReplaceArrayStringProxyParamOrResultForPHP($ProxyParameterExampleForPHP);
				$ProxyParameterExampleForPerl = $json_parameter_for_example_base;
				$ProxyParameterExampleForPerl = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForPerl, "=>");
				$ProxyParameterExampleForPerl = ReplaceIndentForParamOfObject($ProxyParameterExampleForPerl, "    ");
				$ProxyParameterExampleForPerl = ReplaceArrayStartForParamOfObject("Perl", $ProxyParameterExampleForPerl);
				$ProxyParameterExampleForPerl = ReplaceArrayEndForParamOfObject("Perl", $ProxyParameterExampleForPerl);
				$ProxyParameterExampleForPerl = ReplaceArrayStringProxyParamOrResultForPerl($ProxyParameterExampleForPerl);
				$ProxyParameterExampleForRuby = $json_parameter_for_example_base;
				$ProxyParameterExampleForRuby = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForRuby, "=>");
				$ProxyParameterExampleForRuby = ReplaceIndentForParamOfObject($ProxyParameterExampleForRuby, "    ");
				$ProxyParameterExampleForRuby = ReplaceArrayStartForParamOfObject("Ruby", $ProxyParameterExampleForRuby);
				$ProxyParameterExampleForRuby = ReplaceArrayEndForParamOfObject("Ruby", $ProxyParameterExampleForRuby);
				$ProxyParameterExampleForRuby = ReplaceArrayStringProxyParamOrResultForRuby($ProxyParameterExampleForRuby);
				$ProxyResultFormatForJquery = $ProxyResultFormat;
				$ProxyResultFormatForJquery = ReplaceOutputSourceInfoByKeyValue($ProxyResultFormatForJquery, array(
						array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>"", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				$ProxyResultFormatForJquery = ReplaceArrayStringProxyParamOrResultForJquery($ProxyResultFormatForJquery);
			}
		} else if ($daCustomProxy) {
			// Custom Proxy.
			$json_parameter = $ProxyParameterFormat;
			$json_parameter_for_example_base = $ProxyParameterExample;
			
			$DAdaCustomProxyFuncDBAccess = new daCustomProxyFuncDBAccess();
			$daCustomProxyFuncList = $DAdaCustomProxyFuncDBAccess->GetdaCustomProxyFuncList($project->PID, $daCustomProxy->PID);
			for($j = 0 ; $j < count($daCustomProxyFuncList); $j++) {
				$daCustomProxyFunc = $daCustomProxyFuncList[$j];
				
				$no = ($j + 1);
				$ForList = $daCustomProxyFunc->ForList();
				
				// $indent_for_list = "";
				// if ($ForList) {
				// 	$indent_for_list = "    ";
				// }
				
				$DAdafunc = new dafuncDBAccess();
				$this_dafunc = $DAdafunc->Getdafunc($daCustomProxyFunc->dafuncPID, $daCustomProxy->ProjectPID);
				if ($this_dafunc) {
					$thisParameterListStringForProxyBasedOnDA = "";
					$thisParameterListStringForProxyBasedOnDAForExample = "";
					$BuildSourceFuncCacheOfDA = $DABuildSourceFuncCache->GetBuildSourceFuncCacheByDAFunc($daCustomProxy->ProjectPID, $this_dafunc->daPID, $this_dafunc->PID, BuildSourceFuncCacheBuildTargetTypeEnum::$DA, $ProjectSourceOutput->ReleaseTargetType);
					if ($BuildSourceFuncCacheOfDA) {
						$thisParameterListStringForProxyBasedOnDA = $BuildSourceFuncCacheOfDA->ParameterListStringForProxyBasedOnDA;
						$thisParameterListStringForProxyBasedOnDAForExample = $BuildSourceFuncCacheOfDA->ParameterListStringForProxyBasedOnDAForExample;
					}
					// $thisParameterListStringForProxyBasedOnDA           = ReplaceIndentForCustomProxy($thisParameterListStringForProxyBasedOnDA, "    ");
					// $thisParameterListStringForProxyBasedOnDAForExample = ReplaceIndentForCustomProxy($thisParameterListStringForProxyBasedOnDAForExample, "    ");
					
					$json_parameter = ReplaceOutputSourceInfoByKeyValue($json_parameter, array(
							array("KEY"=>GetReplacementStringForParamOfObjectForStep($no), "VALUE"=>$thisParameterListStringForProxyBasedOnDA, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					$json_parameter_for_example_base = ReplaceOutputSourceInfoByKeyValue($json_parameter_for_example_base, array(
							array("KEY"=>GetReplacementStringForParamOfObjectForStep($no), "VALUE"=>$thisParameterListStringForProxyBasedOnDAForExample, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
				}
			}
			// $json_parameter = ReplaceIndentForCustomProxy($json_parameter, "    ");
			// $json_parameter_for_example_base = ReplaceIndentForCustomProxy($json_parameter_for_example_base, "    ");
			
			$ProxyParameterForJquery = $json_parameter;
			$ProxyParameterForJquery = ReplaceIndentForCustomProxy($ProxyParameterForJquery, "    ");
			$ProxyParameterForJquery = ReplaceIndentForParamOfObject($ProxyParameterForJquery, "");
			$ProxyParameterForJquery = ReplaceArrayStartForParamOfObject("Jquery", $ProxyParameterForJquery);
			$ProxyParameterForJquery = ReplaceArrayEndForParamOfObject("Jquery", $ProxyParameterForJquery);
			$ProxyParameterForJquery = ReplaceArrayStringProxyParamOrResultForJquery($ProxyParameterForJquery);
			$ProxyParameterExampleForJquery = $json_parameter_for_example_base;
			$ProxyParameterExampleForJquery = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForJquery, ":");
			$ProxyParameterExampleForJquery = ReplaceIndentForCustomProxy($ProxyParameterExampleForJquery, "        ");
			$ProxyParameterExampleForJquery = ReplaceIndentForParamOfObject($ProxyParameterExampleForJquery, "    ");
			$ProxyParameterExampleForJquery = ReplaceArrayStartForParamOfObject("Jquery", $ProxyParameterExampleForJquery);
			$ProxyParameterExampleForJquery = ReplaceArrayEndForParamOfObject("Jquery", $ProxyParameterExampleForJquery);
			$ProxyParameterExampleForJquery = ReplaceArrayStringProxyParamOrResultForJquery($ProxyParameterExampleForJquery);
			$ProxyParameterExampleForPHP = $json_parameter_for_example_base;
			$ProxyParameterExampleForPHP = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForPHP, "=>");
			$ProxyParameterExampleForPHP = ReplaceIndentForCustomProxy($ProxyParameterExampleForPHP, "        ");
			$ProxyParameterExampleForPHP = ReplaceIndentForParamOfObject($ProxyParameterExampleForPHP, "    ");
			$ProxyParameterExampleForPHP = ReplaceArrayStartForParamOfObject("PHP", $ProxyParameterExampleForPHP);
			$ProxyParameterExampleForPHP = ReplaceArrayEndForParamOfObject("PHP", $ProxyParameterExampleForPHP);
			$ProxyParameterExampleForPHP = ReplaceArrayStringProxyParamOrResultForPHP($ProxyParameterExampleForPHP);
			$ProxyParameterExampleForPerl = $json_parameter_for_example_base;
			$ProxyParameterExampleForPerl = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForPerl, "=>");
			$ProxyParameterExampleForPerl = ReplaceIndentForCustomProxy($ProxyParameterExampleForPerl, "    ");
			$ProxyParameterExampleForPerl = ReplaceIndentForParamOfObject($ProxyParameterExampleForPerl, "    ");
			$ProxyParameterExampleForPerl = ReplaceArrayStartForParamOfObject("Perl", $ProxyParameterExampleForPerl);
			$ProxyParameterExampleForPerl = ReplaceArrayEndForParamOfObject("Perl", $ProxyParameterExampleForPerl);
			$ProxyParameterExampleForPerl = ReplaceArrayStringProxyParamOrResultForPerl($ProxyParameterExampleForPerl);
			$ProxyParameterExampleForRuby = $json_parameter_for_example_base;
			$ProxyParameterExampleForRuby = ReplaceDelimiterForCustomProxy($ProxyParameterExampleForRuby, "=>");
			$ProxyParameterExampleForRuby = ReplaceIndentForCustomProxy($ProxyParameterExampleForRuby, "    ");
			$ProxyParameterExampleForRuby = ReplaceIndentForParamOfObject($ProxyParameterExampleForRuby, "    ");
			$ProxyParameterExampleForRuby = ReplaceArrayStartForParamOfObject("Ruby", $ProxyParameterExampleForRuby);
			$ProxyParameterExampleForRuby = ReplaceArrayEndForParamOfObject("Ruby", $ProxyParameterExampleForRuby);
			$ProxyParameterExampleForRuby = ReplaceArrayStringProxyParamOrResultForRuby($ProxyParameterExampleForRuby);
			$ProxyResultFormatForJquery = $ProxyResultFormat;
			$ProxyResultFormatForJquery = ReplaceOutputSourceInfoByKeyValue($ProxyResultFormatForJquery, array(
					array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>"    ", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			$ProxyResultFormatForJquery = ReplaceArrayStringProxyParamOrResultForJquery($ProxyResultFormatForJquery);
			
		} else {
			PrintOutMtoolBuildResultMessage();
			die("Something strange. No data nigher of DAFunc nor Custom Proxy");
		}
		
		// Save to Build Source Cache
		$thisBuildCache = new BuildSourceFuncCacheData();
		$thisBuildCache->ProjectPID = $project->PID;
		
		if ($dafunc) {
			// Simple Proxy.
			$thisBuildCache->BuildTargetType = BuildSourceFuncCacheBuildTargetTypeEnum::$PROXYSERVER;
			$thisBuildCache->daPID = $da->PID;
			$thisBuildCache->dafuncPID = $dafunc->PID;
			$thisBuildCache->daCustomProxyPID = -1;
			$thisBuildCache->DAName = $da->name;
			$thisBuildCache->DAClassName = CreateDatabaseAccessClassName($da->name);
		} else if ($daCustomProxy) {
			// Custom Proxy.
			$thisBuildCache->BuildTargetType = BuildSourceFuncCacheBuildTargetTypeEnum::$CUSTOMPROXYSERVER;
			$thisBuildCache->daPID = -1;
			$thisBuildCache->dafuncPID = -1;
			$thisBuildCache->daCustomProxyPID = $daCustomProxy->PID;
			$thisBuildCache->DAName = "";
			$thisBuildCache->DAClassName = "";
		} else {
			PrintOutMtoolBuildResultMessage();
			die("Something strange. No data nigher of DAFunc nor Custom Proxy");
		}
		$thisBuildCache->ReleaseTargetType = GetBuildSourceFuncCacheReleaseTargetTypeFromProjectSourceOutputReleaseTargetType($ProjectSourceOutput->ReleaseTargetType);
		$thisBuildCache->FunctionName = "";
		$thisBuildCache->SourceCode = "";
		$thisBuildCache->ParameterListString = "";
		$thisBuildCache->ParameterListStringForProxyBasedOnDA = "";
		$thisBuildCache->ParameterListStringForProxyBasedOnDAForExample = "";
		$thisBuildCache->ExampleCodeForCreatingObject = "";
		$thisBuildCache->DataClassName = "";
		$thisBuildCache->AutoloadFilename = "";
		$thisBuildCache->ProxyURL = pathCombine($ProjectSourceOutput->ProxyBaseURL, $localfilename);
		$thisBuildCache->ProxyParameterFormat = $ProxyParameterFormat;
		$thisBuildCache->ProxyParameterExample = $ProxyParameterExample;
		$thisBuildCache->ProxyResultFormat = $ProxyResultFormat;
		$thisBuildCache->ProxyResultExample = $ProxyResultExample;
		$thisBuildCache->ProxyParameterForJquery = $ProxyParameterForJquery;
		$thisBuildCache->ProxyParameterExampleForJquery = $ProxyParameterExampleForJquery;
		$thisBuildCache->ProxyParameterExampleForPHP = $ProxyParameterExampleForPHP;
		$thisBuildCache->ProxyParameterExampleForPerl = $ProxyParameterExampleForPerl;
		$thisBuildCache->ProxyParameterExampleForRuby = $ProxyParameterExampleForRuby;
		$thisBuildCache->ProxyResultFormatForJquery = $ProxyResultFormatForJquery;
		$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
		if ($dafunc) {
			$DABuildSourceFuncCache->DeleteBuildSourceFuncCacheByDAFunc($thisBuildCache);
		} else {
			// Custom Proxy.
			$DABuildSourceFuncCache->DeleteBuildSourceFuncCacheByCustomProxy($thisBuildCache);
		}
		$DABuildSourceFuncCache->InsertBuildSourceFuncCache($thisBuildCache);
		
		// ----------------------------------
		// Save into Dropbox as a reference
		
		$endpoint_document_text_list = array();
		array_push($endpoint_document_text_list, "==================================================");
		array_push($endpoint_document_text_list, "Proxy Parameter for jQuery:");
		array_push($endpoint_document_text_list, $ProxyParameterForJquery);
		array_push($endpoint_document_text_list, "");
		array_push($endpoint_document_text_list, "Proxy Parameter Example for Jquery:");
		array_push($endpoint_document_text_list, $ProxyParameterExampleForJquery);
		array_push($endpoint_document_text_list, "");
		array_push($endpoint_document_text_list, "Proxy Parameter Example for PHP:");
		array_push($endpoint_document_text_list, $ProxyParameterExampleForPHP);
		array_push($endpoint_document_text_list, "");
		array_push($endpoint_document_text_list, "Proxy Parameter Example for Perl:");
		array_push($endpoint_document_text_list, $ProxyParameterExampleForPerl);
		array_push($endpoint_document_text_list, "");
		array_push($endpoint_document_text_list, "Proxy Parameter Example for Ruby:");
		array_push($endpoint_document_text_list, $ProxyParameterExampleForRuby);
		array_push($endpoint_document_text_list, "");
		array_push($endpoint_document_text_list, "Proxy Result Format for jQuery:");
		array_push($endpoint_document_text_list, $ProxyResultFormatForJquery);
		array_push($endpoint_document_text_list, "==================================================");
		
		$localfile_basename_without_ext = basename($localfilename, ".php");
		$docfile = pathCombine("/no_upload", $localfile_basename_without_ext . "_endpoint.txt");
		
		$result = UpdateAutomatedSource($BuildToken, $project, $ProjectSourceOutput, $docfile, "", array(), implode("\n", $endpoint_document_text_list), $output_to_temp_folder, $output_after_copy_to_temp_folder);
		if ($result->Success) {
			// OK
		} else {
			AddMtoolErrorBuildMessage(" -> Error! Failed to update document file");
		}
		
	} else {
		AddMtoolErrorBuildMessage(" -> Error! Failed to update");
	}
}

function add_indent_to_json_parameter_for_proxy_server($json_parameter, $add_indent)
{
	$result = "";
	
	$this_parameter_list = explode("\n", trim($json_parameter));
	for($i = 0 ; $i < count($this_parameter_list); $i++) {
		$this_parameter = $this_parameter_list[$i];
		if ($i > 0) {
			$result .= $add_indent;
		}
		$result .= $this_parameter;
		
		if ($i < count($this_parameter_list) - 1) {
			$result .= "\n";
		}
	}
	return $result;
}

function GetProxyParameterFormatForToken()
{
	return "\n    \"TOKEN\": \"__TOKEN_FOR_PROXY_ACCESS__\"";
}
function GetProxyParameterExampleForToken()
{
	return "\n    \"TOKEN\"__DELIMITER_FOR_CUSTOM_PROXY__ \"__TOKEN_FOR_PROXY_ACCESS__\"";
}

function GetProxyParameterFormatForLoginCookieToken()
{
	return "\n    \"LOGIN_COOKIE_TOKEN\": <Login Cookie Token>";
}
function GetProxyParameterExampleForLoginCookieToken()
{
	return "\n    \"LOGIN_COOKIE_TOKEN\"__DELIMITER_FOR_CUSTOM_PROXY__ <Login Cookie Token>";
}

function AddProxyResultFormatLineListForTopLevel(&$ProxyResultFormatLineList)
{
	array_push($ProxyResultFormatLineList, "\n    \"_status\": <Return \"OK\" if success>");
	array_push($ProxyResultFormatLineList, "\n    \"Message\": <Specific message about the call>");
}
function AddProxyResultFormatLineListForInsertID(&$ProxyResultFormatLineList, $no, $ForList)
{
	global $MTOOL_INSERT_ID_PROPERTY_NAME;
	
	$array_string_start = "__PREFIX_FOR_ENCODE__";
	$array_string_end   = "";
	if ($ForList) {
		$array_string_start .= GetArrayStringStartProxyParamOrResult();
		$array_string_end   .= GetArrayStringEndProxyParamOrResult();
	}
	$array_string_end  .= "__SUFFIX_FOR_ENCODE__";
	array_push($ProxyResultFormatLineList, "\n    \"" . $MTOOL_INSERT_ID_PROPERTY_NAME . $no . "\": " . $array_string_start . "<Returns the auto generated id used in the last query>" . $array_string_end);
}

function GetArrayStringStartProxyParamOrResult()
{
	return "__PREFIX_FOR_ARRAY__ ";
}
function GetArrayStringEndProxyParamOrResult()
{
	return ", <2nd item>, <3rd item>, ... __SUFFIX_FOR_ARRAY__";
}

function ReplaceArrayStringProxyParamOrResultForJquery($line)
{
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__PREFIX_FOR_ARRAY__",   "VALUE"=>"[", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ARRAY__",   "VALUE"=>"]", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_ENCODE__",  "VALUE"=>"",  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ENCODE__",  "VALUE"=>"",  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_EXAMPLE__", "VALUE"=>"{", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_EXAMPLE__", "VALUE"=>"}", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		));
}
function ReplaceArrayStringProxyParamOrResultForPHP($line)
{
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__PREFIX_FOR_ARRAY__",   "VALUE"=>"array(",       "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ARRAY__",   "VALUE"=>")",            "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_ENCODE__",  "VALUE"=>"json_encode(", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ENCODE__",  "VALUE"=>")",            "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_EXAMPLE__", "VALUE"=>"array(",       "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_EXAMPLE__", "VALUE"=>")",            "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		));
}
function ReplaceArrayStringProxyParamOrResultForPerl($line)
{
	$line = ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__PREFIX_FOR_ARRAY__",   "VALUE"=>"(", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ARRAY__",   "VALUE"=>")", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_ENCODE__",  "VALUE"=>"",  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ENCODE__",  "VALUE"=>"",  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_EXAMPLE__", "VALUE"=>"{", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_EXAMPLE__", "VALUE"=>"}", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		));
	return $line;
}
function ReplaceArrayStringProxyParamOrResultForRuby($line)
{
	$line = ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__PREFIX_FOR_ARRAY__",   "VALUE"=>"[", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ARRAY__",   "VALUE"=>"]", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_ENCODE__",  "VALUE"=>"",  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_ENCODE__",  "VALUE"=>"",  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PREFIX_FOR_EXAMPLE__", "VALUE"=>"{", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SUFFIX_FOR_EXAMPLE__", "VALUE"=>"}", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
		));
	return $line;
}

function InitializeTopLevelSecurityCheckFormatAndExampleList($BuildToken, $project, $ProjectSourceOutput, $Proxy_AuthType, $Proxy_SingleGetFuncPID, &$ProxyParameterFormatLineList, &$ProxyParameterExampleLineList)
{
	$any_security_checked = false;
	switch($Proxy_AuthType) {
		case dafuncSingleProxy_AuthTypeEnum::$DEFAULT:
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKEN:
		case dafuncSingleProxy_AuthTypeEnum::$PROJECTTOKENORGETFUNC:
			array_push($ProxyParameterFormatLineList, GetProxyParameterFormatForToken());
			array_push($ProxyParameterExampleLineList, GetProxyParameterExampleForToken());
			$any_security_checked = true;
			break;
		case dafuncSingleProxy_AuthTypeEnum::$GETFUNC:
		case dafuncSingleProxy_AuthTypeEnum::$NOSECURITY:
		case dafuncSingleProxy_AuthTypeEnum::$MANUAL:
			break;
		case dafuncSingleProxy_AuthTypeEnum::$LOGINCOOKIETOKEN:
			array_push($ProxyParameterFormatLineList, GetProxyParameterFormatForLoginCookieToken());
			array_push($ProxyParameterExampleLineList, GetProxyParameterExampleForLoginCookieToken());
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
			
			$DAdafunc = new dafuncDBAccess();
			$security_check_dafunc = $DAdafunc->Getdafunc($Proxy_SingleGetFuncPID, $project->PID);
			if ($security_check_dafunc) {
				$DAda = new daDBAccess();
				$security_check_da = $DAda->Getda($security_check_dafunc->daPID, $project->PID);
				if ($security_check_da) {
					$thisrequestParamsSource = "";
					$thisProxyParameterFormatLineList = array();
					$thisProxyParameterExampleLineList = array();
					CreateRequestParamsSource($BuildToken, $project, $ProjectSourceOutput, $security_check_da, $security_check_dafunc, "", $thisProxyParameterFormatLineList, $thisProxyParameterExampleLineList, $thisrequestParamsSource);
					
					$thisProxyParameterFormat = "";
					$thisProxyParameterExample = "";
					MakeProxyParameterFormatAndExample($thisProxyParameterFormat, $thisProxyParameterExample, GetSecurityCheckJsonKeyName(), true);
					
					$thisParamProxyParameterFormat = "{" . implode(",", $thisProxyParameterFormatLineList) . "\n__INDENT_FOR_CUSTOM_PROXY__}";
					$thisParamProxyParameterFormat = ReplaceOutputSourceInfoByKeyValue($thisParamProxyParameterFormat, array(
							array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>"    ", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					$thisProxyParameterFormat = ReplaceOutputSourceInfoByKeyValue($thisProxyParameterFormat, array(
							array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>"", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__PARAM_OF_OBJECT__", "VALUE"=>$thisParamProxyParameterFormat, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					
					$thisParamProxyParameterExample = "__PREFIX_FOR_EXAMPLE__" . implode(",", $thisProxyParameterExampleLineList) . "\n__INDENT_FOR_CUSTOM_PROXY____SUFFIX_FOR_EXAMPLE__";
					$thisParamProxyParameterExample = ReplaceOutputSourceInfoByKeyValue($thisParamProxyParameterExample, array(
							array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>"    ", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					$thisProxyParameterExample = ReplaceOutputSourceInfoByKeyValue($thisProxyParameterExample, array(
							array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>"", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
							array("KEY"=>"__PARAM_OF_OBJECT__", "VALUE"=>$thisParamProxyParameterExample, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
					array_push($ProxyParameterFormatLineList, $thisProxyParameterFormat);
					array_push($ProxyParameterExampleLineList, $thisProxyParameterExample);
				}
			}
			$any_security_checked = true;
			break;
		default:
			AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Auth Type: " . $Proxy_AuthType);
			return "";
	}
	// if (!$any_security_checked) {
	// 	array_push($ProxyParameterFormatLineList, GetProxyParameterFormatForToken());
	// 	array_push($ProxyParameterExampleLineList, GetProxyParameterExampleForToken());
	// }
}

function GetMakeproxyserverFunctionSource($BuildToken, $project, $ProjectSourceOutput, $da, $basename, $dafunc, $sourceFunctionName, $resultno, $resultname, $output_top_level, $ForList, &$ProxyParameterFormat, &$ProxyParameterExample, &$ProxyResultFormat, &$ProxyResultExample, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $check_function_result, $transaction, $output_to_temp_folder, $output_after_copy_to_temp_folder)
{
	$ProxyParameterFormat = "{";
	$ProxyParameterFormatLineList = array();
	$ProxyResultExample = "";		// Not used now
	
	$ProxyResultFormat = "{";
	$ProxyResultFormatLineList = array();
	
	$ProxyParameterExample  = "__PREFIX_FOR_EXAMPLE__";
	$ProxyParameterExampleLineList = array();
	
	if ($output_top_level) {
		InitializeTopLevelSecurityCheckFormatAndExampleList($BuildToken, $project, $ProjectSourceOutput, $Proxy_AuthType, $Proxy_SingleGetFuncPID, $ProxyParameterFormatLineList, $ProxyParameterExampleLineList);
	}
	
	if ($output_top_level) {
		
		AddProxyResultFormatLineListForTopLevel($ProxyResultFormatLineList);
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
				array_push($ProxyResultFormatLineList, "\n    \"Result\": <result of function>");
				break;
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
			case dafuncActionTypeEnum::$INSERT:
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
				AddProxyResultFormatLineListForInsertID($ProxyResultFormatLineList, "", false);
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
	}
	
	$param_base_object = "";
	$param_object = "";
	if ($resultno != "") {
		// For Custom
		$param_base_object = GetCustomProxyRequestParamObjectName($resultno);
		$param_object = $param_base_object;
		if ($ForList) {
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
					$param_object .= "[\$index]";					// TODO: need to define in somewhere
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					PrintOutMtoolBuildResultMessage();
					die("Not supported: GetMakeproxyserverFunctionSource for " . $ProjectSourceOutput->ProgramLanguage);
					
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					break;
			}
		}
		$param_object .= "->";
	}
	
	$requestParamsSource = "";
	CreateRequestParamsSource($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $param_object, $ProxyParameterFormatLineList, $ProxyParameterExampleLineList, $requestParamsSource);
	
	$ProxyParameterFormat .= implode(",", $ProxyParameterFormatLineList);
	$ProxyParameterFormat .= "\n__INDENT_FOR_CUSTOM_PROXY__}";
	$ProxyParameterExample .= implode(",", $ProxyParameterExampleLineList);
	$ProxyParameterExample .= "\n__INDENT_FOR_CUSTOM_PROXY____SUFFIX_FOR_EXAMPLE__";
	$ProxyResultFormat .= implode(",", $ProxyResultFormatLineList);
	$ProxyResultFormat .= "\n__INDENT_FOR_CUSTOM_PROXY__}";
	
	// $this_indent = "";
	// if ($output_top_level) {
	// 	$this_indent = "";
	// } else {
	// 	$this_indent = "    ";
	// }
	// $ProxyParameterFormat  = preg_replace("/__INDENT_FOR_CUSTOM_PROXY__/", $this_indent, $ProxyParameterFormat);
	// $ProxyParameterExample = preg_replace("/__INDENT_FOR_CUSTOM_PROXY__/", $this_indent, $ProxyParameterExample);
	
	$template_func = "";
	$template_add_result = "";
	$template_insert_id_initialize = "";
	$template_insert_id_set = "";
	$do_indent_for_check_if_already_inserted = false;
	if ($ForList) {
		$template_func = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST");
		$template_add_result = "";
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
				$template_add_result = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST-ADD-RESULT");
				break;
			case dafuncActionTypeEnum::$INSERT:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
		$template_func = ReplaceOutputSourceInfoByKeyValue($template_func, array(
				array("KEY"=>"__ADD_RESULT__", "VALUE"=>$template_add_result, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
			));
		
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			case dafuncActionTypeEnum::$INSERT:
				if ($resultno != "") {
					// For Custom
				} else {
					$template_insert_id_initialize = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST-INSERT-ID-INITIALIZE");
				}
				$template_insert_id_set = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST-INSERT-ID-SET");
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
		
		$template_check_if_already_inserted = "";
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			case dafuncActionTypeEnum::$INSERT:
				$template_check_if_already_inserted = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST-CHECK-IF-ALREADY-INSERTED");
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
		$template_func = ReplaceOutputSourceInfoByKeyValue($template_func, array(
				array("KEY"=>"__CHECK_IF_ALREADY_INSERTED__", "VALUE"=>$template_check_if_already_inserted, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
			));
		
	} else {
		$template_func = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION");
		
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			case dafuncActionTypeEnum::$INSERT:
				if ($resultno != "") {
					// For Custom
					// $template_insert_id_initialize = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-INSERT-ID-INITIALIZE-FOR-CUSTOM");
					$template_insert_id_set = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-INSERT-ID-SET-FOR-CUSTOM");
				}
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
		
		$template_check_if_already_inserted_begin = "";
		$template_check_if_already_inserted_end = "";
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
			case dafuncActionTypeEnum::$SELECTLIST:
			case dafuncActionTypeEnum::$UPDATE:
			case dafuncActionTypeEnum::$DELETE:
				break;
			case dafuncActionTypeEnum::$INSERT:
				$template_check_if_already_inserted_begin = "";
				
				if ($resultno != "") {
					// For Custom
					$template_check_if_already_inserted_begin = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-IF-ALREADY-INSERTED-BEGIN-FOR-CUSTOM");
				} else {
					$template_check_if_already_inserted_begin = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-IF-ALREADY-INSERTED-BEGIN");
				}
				
				$template_check_if_already_inserted_end = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-IF-ALREADY-INSERTED-END");
				$do_indent_for_check_if_already_inserted = true;
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
		$template_func = ReplaceOutputSourceInfoByKeyValue($template_func, array(
				array("KEY"=>"__CHECK_IF_ALREADY_INSERTED_BEGIN__", "VALUE"=>$template_check_if_already_inserted_begin, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
				array("KEY"=>"__CHECK_IF_ALREADY_INSERTED_END__", "VALUE"=>$template_check_if_already_inserted_end, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
			));
	}
	$template_func = ReplaceOutputSourceInfoByKeyValue($template_func, array(
			array("KEY"=>"__INSERT_ID_INITIALIZE__", "VALUE"=>$template_insert_id_initialize, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__INSERT_ID_SET__", "VALUE"=>$template_insert_id_set, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true)
		));
	
	$single_result_for_list_source = "";
	if ($ForList) {
		$single_result_for_list_source = GetSingleResultObjectName($project, $ProjectSourceOutput);
	}
	
	$this_func_check_error = "";
	if ($check_function_result) {
		$this_func_check_error = GetMakeproxyserverCheckErrorSource($BuildToken, $project, $ProjectSourceOutput, $transaction, $resultno, $resultname, $ForList, $dafunc);
	}
	
	$set_inserted_id_by_insert_token_source = "";
	if (CheckIfIncludeInsertFunctionInProxy($project, $dafunc, NULL)) {
		
		if ($resultno != "") {
			// For Custom
			if (!$ForList) {
				$set_inserted_id_by_insert_token_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-SET-INSERTED-ID-BY-INSERT-TOKEN-FOR-CUSTOM");
			} else {
				$set_inserted_id_by_insert_token_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST-SET-INSERTED-ID-BY-INSERT-TOKEN-FOR-CUSTOM");
			}
			
		} else {
			if (!$ForList) {
				$set_inserted_id_by_insert_token_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-SET-INSERTED-ID-BY-INSERT-TOKEN");
			} else {
				$set_inserted_id_by_insert_token_source = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-FOR-LIST-SET-INSERTED-ID-BY-INSERT-TOKEN");
			}
		}
	}
	
	$editable_area_in_the_end_of_loop = "";
	if ($resultno != "") {
		// For Custom
		if ($ForList) {
			$editable_area_in_the_end_of_loop = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-EDITABLE-AREA-IN-LOOP-FOR-CUSTOM");
		}
	}
	
	$template_func = ReplaceOutputSourceInfoByKeyValue($template_func, array(
			array("KEY"=>"__CHECK_ERROR__", "VALUE"=>$this_func_check_error, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__SET_INSERTED_ID_BY_INSERT_TOKEN__", "VALUE"=>$set_inserted_id_by_insert_token_source, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__EDITABLE_AREA_IN_THE_END_OF_LOOP__", "VALUE"=>$editable_area_in_the_end_of_loop, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__REQUEST_PARAMS__", "VALUE"=>$requestParamsSource, "TRIMLASTSPACE"=>true, "TRIMLASTRETURN"=>true),
			array("KEY"=>"__LIST_OBJECT_NAME__", "VALUE"=>$param_base_object, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$sourceFunctionName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$basename, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SINGLE_RESULT_FOR_LIST__", "VALUE"=>$single_result_for_list_source, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__RESULT__", "VALUE"=>$resultname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__RESULT_NO__", "VALUE"=>$resultno, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__STEP_NO__", "VALUE"=>$resultno, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	$template_func = replace_indent_for_check_if_already_inserted_for_mtool_build($do_indent_for_check_if_already_inserted, $template_func);
	$template_func = replace_indent_for_list_for_mtool_build($ForList, $template_func);
	
	return $template_func;
}

function CreateRequestParamsSource($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $param_object, &$ProxyParameterFormatLineList, &$ProxyParameterExampleLineList, &$requestParamsSource)
{
	$sourceParamInfoList = NULL;
	GetdafuncSourceSub($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $sourceParamInfoList, true);
	if ($sourceParamInfoList != NULL && is_array($sourceParamInfoList)) {
		
		for ($j = 0 ; $j < count($sourceParamInfoList) ; $j++) {
			
			$comma = "";
			if ($j != count($sourceParamInfoList) - 1) {
				$comma = ",";
			}
			
			$sourceParamName = $sourceParamInfoList[$j]->name;
			$template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-PARAM");
			$template = ReplaceOutputSourceInfoByKeyValue($template, array(
					array("KEY"=>"__PARAM_NAME__", "VALUE"=>$param_object . $sourceParamName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__COMMA__",      "VALUE"=>$comma,        "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			
			$thisProxyParameterFormat = "";
			$thisProxyParameterExample = "";
			MakeProxyParameterFormatAndExample($thisProxyParameterFormat, $thisProxyParameterExample, $sourceParamName, $sourceParamInfoList[$j]->IsObject);
			// $thisProxyParameterFormat = "\n__INDENT_FOR_CUSTOM_PROXY__    \"" . $sourceParamName . "\": ";
			// $thisProxyParameterExample = "\n__INDENT_FOR_CUSTOM_PROXY__    \"" . $sourceParamName . "\"=> ";
			// if ($sourceParamInfoList[$j]->IsObject) {
			// 	$thisProxyParameterFormat .= "__PARAM_OF_OBJECT__";
			// 	$thisProxyParameterExample .= "__PARAM_OF_OBJECT__";
			// } else {
			// 	$thisProxyParameterFormat .= "value";
			// 	$thisProxyParameterExample .= "value";
			// }
			
			// $by_value = false;
			// switch($dafunc->ActionType)
			// {
			// 	case dafuncActionTypeEnum::$SELECTSINGLE:
			// 	case dafuncActionTypeEnum::$SELECTLIST:
			// 		$by_value = true;
			// 		break;
			// 	case dafuncActionTypeEnum::$INSERT:
			// 	case dafuncActionTypeEnum::$UPDATE:
			// 	case dafuncActionTypeEnum::$DELETE:
			//		if ($dafunc->IsInsertUpdateDeleteTargetClassObject()) {
			//			$thisProxyParameterFormat .= "__PARAM_OF_OBJECT__";
			//			$thisProxyParameterExample .= "__PARAM_OF_OBJECT__";
			//			
			//		} else if ($dafunc->IsInsertUpdateDeleteTargetVal()) {
			//			$by_value = true;
			//		} else {
			//			// Unknown
			//			AddMtoolErrorBuildMessage("Unknown Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
			//		}
			//		break;
			//	default:
			//		AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
			//		return;
			//}
			//if ($by_value) {
			//	$thisProxyParameterFormat .= "value";
			//	$thisProxyParameterExample .= "value";
			//}
			
			array_push($ProxyParameterFormatLineList, $thisProxyParameterFormat);
			array_push($ProxyParameterExampleLineList, $thisProxyParameterExample);
			
			$requestParamsSource .= $template;
		}
	}
}

function MakeProxyParameterFormatAndExample(&$thisProxyParameterFormat, &$thisProxyParameterExample, $sourceParamName, $IsObject)
{
	$thisProxyParameterFormat = "\n__INDENT_FOR_CUSTOM_PROXY__    \"" . $sourceParamName . "\": ";
	$thisProxyParameterExample = "\n__INDENT_FOR_CUSTOM_PROXY__    \"" . $sourceParamName . "\"__DELIMITER_FOR_CUSTOM_PROXY__ ";
	if ($IsObject) {
		$thisProxyParameterFormat .= "__PARAM_OF_OBJECT__";
		$thisProxyParameterExample .= "__PARAM_OF_OBJECT__";
	} else {
		$thisProxyParameterFormat .= "value";
		$thisProxyParameterExample .= "value";
	}
}

function ReplaceIndentForCustomProxy($line, $indent)
{
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__INDENT_FOR_CUSTOM_PROXY__", "VALUE"=>$indent, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}
function ReplaceDelimiterForCustomProxy($line, $delimiter)
{
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__DELIMITER_FOR_CUSTOM_PROXY__", "VALUE"=>$delimiter, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}
function ReplaceIndentForParamOfObject($line, $indent)
{
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__INDENT_FOR_PARAM_OF_OBJECT__", "VALUE"=>$indent, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}
function ReplaceArrayStartForParamOfObject($lang, $line)
{
	$array_start_code = "";
	if (preg_match("/php/i", $lang)) {
		$array_start_code = "array(";
	} else {
		$array_start_code = "{";
	}
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__ARRAY_START_CODE_FOR_EACH_LANGUAGE__", "VALUE"=>$array_start_code, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}
function ReplaceArrayEndForParamOfObject($lang, $line)
{
	$array_end_code = "";
	if (preg_match("/php/i", $lang)) {
		$array_end_code = ")";
	} else {
		$array_end_code = "}";
	}
	return ReplaceOutputSourceInfoByKeyValue($line, array(
			array("KEY"=>"__ARRAY_END_CODE_FOR_EACH_LANGUAGE__", "VALUE"=>$array_end_code, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}

function replace_indent_for_list_for_mtool_build($ForList, $source)
{
	$indent_for_list = "";
	if ($ForList) {
		$indent_for_list = "\t";
	}
	return ReplaceOutputSourceInfoByKeyValue($source, array(
			array("KEY"=>"__INDENT_FOR_LIST__", "VALUE"=>$indent_for_list, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}

function replace_indent_for_check_if_already_inserted_for_mtool_build($do_indent, $source)
{
	$this_indent = "";
	if ($do_indent) {
		$this_indent = "\t";
	}
	return ReplaceOutputSourceInfoByKeyValue($source, array(
			array("KEY"=>"__INDENT_FOR_CHECK_IF_ALREADY_INSERTED__", "VALUE"=>$this_indent, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
}

function GetSingleResultObjectName($project, $ProjectSourceOutput)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "thisResultInLoop";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			PrintOutMtoolBuildResultMessage();
			die("Not supported: GetSingleResultObjectName for " . $ProjectSourceOutput->ProgramLanguage);
			
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	PrintOutMtoolBuildResultMessage();
	die("something wrong in GetSingleResultObjectName");
}

function GetMakeproxyserverCheckErrorSource($BuildToken, $project, $ProjectSourceOutput, $transaction, $resultno, $resultname, $ForList, $dafunc)
{
	$template_check_error = "";
	
	if ($ForList) {
		$template_check_error = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-ERROR-FOR-LIST");
	} else {
		$template_check_error = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-ERROR");
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			break;
		case dafuncActionTypeEnum::$INSERT:
		case dafuncActionTypeEnum::$UPDATE:
		case dafuncActionTypeEnum::$DELETE:
			if ($ForList) {
				$template_check_error .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-ERROR-TO-CHECK-RESULT-FOR-LIST");
				$template_check_error = ReplaceOutputSourceInfoByKeyValue($template_check_error, array(
						array("KEY"=>"__SINGLE_RESULT_FOR_LIST__", "VALUE"=>GetSingleResultObjectName($project, $ProjectSourceOutput), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				
			} else {
				$template_check_error .= GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-CHECK-ERROR-TO-CHECK-RESULT");
			}
			break;
		default:
			AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
			return;
	}
	$template_check_error = ReplaceOutputSourceInfoByKeyValue($template_check_error, array(
			array("KEY"=>"__DB_OBJECT__",            "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__RESULT_NO__",            "VALUE"=>$resultno, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__RESULT__",               "VALUE"=>$resultname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__ROLLBACK_TRANSACTION__", "VALUE"=>GetproxyserverTransactionRollback($BuildToken, $project, $ProjectSourceOutput, $transaction), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
		));
	
	return $template_check_error;
}
function GetMakeproxyserverValueInitializeSource($BuildToken, $project, $ProjectSourceOutput, $resultno, $value_name, $is_list)
{
	$reqult_initialize = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-VALUE-INITIALIZE");
	$reqult_initialize = ReplaceOutputSourceInfoByKeyValue($reqult_initialize, array(
			array("KEY"=>"__VALUE_NAME__", "VALUE"=>$value_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__RESULT_NO__",  "VALUE"=>$resultno,   "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	$initial_value = GetMtoolInitialValueForClass($ProjectSourceOutput, $is_list);
//	switch ($ProjectSourceOutput->ProgramLanguage) {
//		case ProjectSourceOutputProgramLanguageEnum::$PHP:
//			if ($is_list) {
//				$initial_value = "array()";
//			} else {
//				$initial_value = "NULL";
//			}
//			break;
//		case ProjectSourceOutputProgramLanguageEnum::$CS:
//		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
//		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
//		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
//		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
//			PrintOutMtoolBuildResultMessage();
//			die("Not supported: GetMakeproxyserverResultInitializeSource for " . $ProjectSourceOutput->ProgramLanguage);
//		default:
//			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
//			return;
//	}
	$reqult_initialize = ReplaceOutputSourceInfoByKeyValue($reqult_initialize, array(
			array("KEY"=>"__INITIALIZE_VALUE__", "VALUE"=>$initial_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	return $reqult_initialize;
}
function GetMakeproxyserverResultParamSource($BuildToken, $project, $ProjectSourceOutput, $resultno, $resultname)
{
	$func_result_param = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-FUNCTION-RESULT-PARAM");
	$func_result_param = ReplaceOutputSourceInfoByKeyValue($func_result_param, array(
			array("KEY"=>"__RESULT_NO__", "VALUE"=>$resultno, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__RESULT__", "VALUE"=>$resultname, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $func_result_param;
}

function GetproxyserverTransactionBegin($BuildToken, $project, $ProjectSourceOutput, $transaction)
{
	if ($transaction) {
		$template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-TRANSACTION-BEGIN");
		$template = ReplaceOutputSourceInfoByKeyValue($template, array(
				array("KEY"=>"__DB_OBJECT__", "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
		return $template;
	} else {
		return "";
	}
}
function GetproxyserverTransactionCommit($BuildToken, $project, $ProjectSourceOutput, $transaction)
{
	if ($transaction) {
		$template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-TRANSACTION-COMMIT");
		$template = ReplaceOutputSourceInfoByKeyValue($template, array(
				array("KEY"=>"__DB_OBJECT__", "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
		return $template;
	} else {
		return "";
	}
}
function GetproxyserverTransactionRollback($BuildToken, $project, $ProjectSourceOutput, $transaction)
{
	if ($transaction) {
		$template = GetMtoolProxyServerTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYSERVER-TRANSACTION-ROLLBACK");
		$template = ReplaceOutputSourceInfoByKeyValue($template, array(
				array("KEY"=>"__DB_OBJECT__", "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
		return $template;
	} else {
		return "";
	}
}

function CheckIfIncludeInsertFunctionInProxy($project, $dafunc, $daCustomProxy)
{
	$DAdafunc = new dafuncDBAccess();
	if ($dafunc) {
		// Simple Proxy
		if($dafunc->IsInsertFunction()) {
			return true;
		}
	} else if ($daCustomProxy) {
		// Custom Proxy.
		$DAdaCustomProxyFuncDBAccess = new daCustomProxyFuncDBAccess();
		$daCustomProxyFuncList = $DAdaCustomProxyFuncDBAccess->GetdaCustomProxyFuncList($project->PID, $daCustomProxy->PID);
		if (CheckIfIncludeInsertFunctionInProxyByCustomProxyFuncList($daCustomProxy, $daCustomProxyFuncList)) {
			return true;
		}
	}
	return false;
}
function CheckIfIncludeInsertFunctionInProxyByCustomProxyFuncList($daCustomProxy, $daCustomProxyFuncList)
{
	for($j = 0 ; $j < count($daCustomProxyFuncList); $j++) {
		$daCustomProxyFunc = $daCustomProxyFuncList[$j];
		
		$DAdafunc = new dafuncDBAccess();
		$this_dafunc = $DAdafunc->Getdafunc($daCustomProxyFunc->dafuncPID, $daCustomProxy->ProjectPID);
		if ($this_dafunc) {
			if($this_dafunc->IsInsertFunction()) {
				return true;
			}
		}
	}
	return false;
}

function CheckIfLoginByLoginCookieTokenFunctionInProxy($dafunc, $daCustomProxy)
{
	if ($dafunc) {
		// Simple Proxy
		return $dafunc->IsLoginByLoginCookieToken();
	} else if ($daCustomProxy) {
		// Custom Proxy.
		return $daCustomProxy->IsLoginByLoginCookieToken();
	}
	return false;
}

?>
