<?PHP

// =================================================================

class MtoolCHeaderIncludeForProxyResult
{
	public $ClassBaseName;
	public $FunctionName;
}
function AddMtoolCHeaderIncludeForProxyResult($ProjectSourceOutput, &$list, $class_base_name, $function_name)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					if ($item->ClassBaseName == $class_base_name &&
					    $item->FunctionName  == $function_name)
					{
						// Already Exist. No need to add
						return;
					}
				}
				$newObj = new MtoolCHeaderIncludeForProxyResult();
				$newObj->ClassBaseName = $class_base_name;
				$newObj->FunctionName = $function_name;
				array_push($list, $newObj);
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}
function GetHeaderSourceForProxyResultFromList($BuildToken, $project, $ProjectSourceOutput, $list)
{
	$result = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					$template = GetHeaderSourceForProxyResult($BuildToken, $project, $ProjectSourceOutput, $item->ClassBaseName, $item->FunctionName);
					
					$result .= $template;
				}
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $result;
}
function GetHeaderSourceForProxyResult($BuildToken, $project, $ProjectSourceOutput, $class_base_name, $function_name)
{
	$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-INCLUDE-PROXY-RESULT-HEADER");
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$class_base_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$function_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}

// =================================================================

class MtoolCHeaderIncludeForRequestParams
{
	public $ClassBaseName;
	public $FunctionName;
	public $ClassNameSuffix;
}
function AddMtoolCHeaderIncludeForRequestParams($ProjectSourceOutput, &$list, $class_base_name, $function_name, $class_name_suffix)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					if ($item->ClassBaseName    == $class_base_name &&
					    $item->FunctionName     == $function_name &&
					    $item->ClassNameSuffix  == $class_name_suffix)
					{
						// Already Exist. No need to add
						return;
					}
				}
				$newObj = new MtoolCHeaderIncludeForRequestParams();
				$newObj->ClassBaseName = $class_base_name;
				$newObj->FunctionName = $function_name;
				$newObj->ClassNameSuffix = $class_name_suffix;
				array_push($list, $newObj);
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}
function GetHeaderSourceForRequestParamsFromList($BuildToken, $project, $ProjectSourceOutput, $list)
{
	$result = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					$template = GetHeaderSourceForRequestParams($BuildToken, $project, $ProjectSourceOutput, $item->ClassBaseName, $item->FunctionName, $item->ClassNameSuffix);
					
					$result .= $template;
				}
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $result;
}
function GetHeaderSourceForRequestParams($BuildToken, $project, $ProjectSourceOutput, $class_base_name, $function_name, $class_name_suffix)
{
	$template = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-INCLUDE-PROXY-REQUEST-PARAMS-HEADER");
			$template = ReplaceOutputSourceInfoByKeyValue($template, array(
					array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$class_base_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$function_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__CLASS_NAME_SUFFIX__", "VALUE"=>$class_name_suffix, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $template;
}

// =================================================================

class MtoolCHeaderIncludeForMiscClass
{
	public $ClassName;
}
function AddMtoolCHeaderIncludeForMiscClass($ProjectSourceOutput, &$list, $class_name)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					if ($item->ClassName == $class_name)
					{
						// Already Exist. No need to add
						return;
					}
				}
				$newObj = new MtoolCHeaderIncludeForMiscClass();
				$newObj->ClassName = $class_name;
				array_push($list, $newObj);
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}
function GetHeaderSourceForMiscClassFromList($target, $TemplateName, $BuildToken, $project, $ProjectSourceOutput, $list)
{
	$result = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					$template = GetHeaderSourceForMiscClass($target, $TemplateName, $BuildToken, $project, $ProjectSourceOutput, $item->ClassName);
					
					$result .= $template;
				}
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $result;
}
function GetHeaderSourceForMiscClass($target, $TemplateName, $BuildToken, $project, $ProjectSourceOutput, $class_name)
{
	$template = "";
	switch($target){
		case HeaderSourceForMiscClassTypeEnum::$DATACLASS:
			$template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $TemplateName);
			break;
		case HeaderSourceForMiscClassTypeEnum::$PROXYCLIENT:
			$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, $TemplateName);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Target: " . $target);
			return;
	}
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__CLASS_NAME__", "VALUE"=>$class_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}
class HeaderSourceForMiscClassTypeEnum
{
	static $DATACLASS = "Data Class";
	static $PROXYCLIENT = "Proxy Client";
}

// =================================================================

class MtoolCHeaderIncludeForClassesInFunction
{
	public $ClassBaseName;
	public $FunctionName;
}
function AddMtoolCHeaderIncludeForClassesInFunction($ProjectSourceOutput, &$list, $class_base_name, $function_name)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					if ($item->ClassBaseName == $class_base_name &&
					    $item->FunctionName  == $function_name)
					{
						// Already Exist. No need to add
						return;
					}
				}
				$newObj = new MtoolCHeaderIncludeForClassesInFunction();
				$newObj->ClassBaseName = $class_base_name;
				$newObj->FunctionName = $function_name;
				array_push($list, $newObj);
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}
function GetHeaderSourceForClassesInFunctionFromList($BuildToken, $project, $ProjectSourceOutput, $list)
{
	$result = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					$template = GetHeaderSourceForClassesInFunction($BuildToken, $project, $ProjectSourceOutput, $item->ClassBaseName, $item->FunctionName);
					
					$result .= $template;
				}
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $result;
}
function GetHeaderSourceForClassesInFunction($BuildToken, $project, $ProjectSourceOutput, $class_base_name, $function_name)
{
	$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-INCLUDE-HEADER-IN-FUNCTION");
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$class_base_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$function_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}

// =================================================================

class MtoolCHeaderIncludeForDefineDelegate
{
	public $ClassBaseName;
	public $FunctionName;
}
function AddMtoolCHeaderIncludeForDefineDelegate($ProjectSourceOutput, &$list, $class_base_name, $function_name)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					if ($item->ClassBaseName == $class_base_name &&
					    $item->FunctionName  == $function_name)
					{
						// Already Exist. No need to add
						return;
					}
				}
				$newObj = new MtoolCHeaderIncludeForDefineDelegate();
				$newObj->ClassBaseName = $class_base_name;
				$newObj->FunctionName = $function_name;
				array_push($list, $newObj);
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
}
function GetHeaderSourceForDefineDelegateFromList($BuildToken, $project, $ProjectSourceOutput, $list)
{
	$result = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			if (is_array($list)) {
				for($i = 0; $i < count($list); $i++) {
					$item = $list[$i];
					$template = GetHeaderSourceForDefineDelegate($BuildToken, $project, $ProjectSourceOutput, $item->ClassBaseName, $item->FunctionName);
					
					$result .= $template;
				}
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return $result;
}
function GetHeaderSourceForDefineDelegate($BuildToken, $project, $ProjectSourceOutput, $class_base_name, $function_name)
{
	$template = GetMtoolProxyClientTemplateFile($BuildToken, $project, $ProjectSourceOutput, "PROXYCLIENT-DEFINE-DELEGATE");
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__CLASS_BASE_NAME__", "VALUE"=>$class_base_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$function_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}

// =================================================================

class MtoolCHeaderIncludeForSerializeAndDeserialize
{
	public $PropertyName;
	public $PropertyDataType;
	public $IsClass;
	public $IsEnum;
}
function AddMtoolCHeaderIncludeForSerializeAndDeserialize($ProjectSourceOutput, &$list, $property_name, $property_data_type, $is_class, $is_enum)
{
	if (is_array($list)) {
		for($i = 0; $i < count($list); $i++) {
			$item = $list[$i];
			if ($item->PropertyName     == $property_name &&
			    $item->PropertyDataType == $property_data_type &&
				$item->IsClass          == $is_class &&
				$item->IsEnum           == $is_enum)
			{
				// Already Exist. No need to add
				return;
			}
		}
		$newObj = new MtoolCHeaderIncludeForSerializeAndDeserialize();
		$newObj->PropertyName = $property_name;
		$newObj->PropertyDataType = $property_data_type;
		$newObj->IsClass = $is_class;
		$newObj->IsEnum = $is_enum;
		array_push($list, $newObj);
	}
}

function GetHeaderSourceForSerializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $list)
{
	return GetHeaderSourceForSerializeAndDeserializeFromList(HeaderSourceForSerializeAndDeserializeType::$SERIALIZE, $BuildToken, $project, $ProjectSourceOutput, $list);
}
function GetHeaderSourceForSerializetDefineForDataClass($BuildToken, $project, $ProjectSourceOutput, $list)
{
	return GetHeaderSourceForSerializeAndDeserializeFromList(HeaderSourceForSerializeAndDeserializeType::$SERIALIZE_DEFINE, $BuildToken, $project, $ProjectSourceOutput, $list);
}
function GetHeaderSourceForInitializePropertyForDataClass($BuildToken, $project, $ProjectSourceOutput, $list)
{
	return GetHeaderSourceForSerializeAndDeserializeFromList(HeaderSourceForSerializeAndDeserializeType::$INITIALIZE_PROPERTY, $BuildToken, $project, $ProjectSourceOutput, $list);
}
function GetHeaderSourceForDeserializetForDataClass($BuildToken, $project, $ProjectSourceOutput, $list)
{
	return GetHeaderSourceForSerializeAndDeserializeFromList(HeaderSourceForSerializeAndDeserializeType::$DESERIALIZE, $BuildToken, $project, $ProjectSourceOutput, $list);
}

function GetDatatypeInitilaizeTableListForOutputSourceForThisLang($project, $ProjectSourceOutput, $target)
{
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_PHP;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT;

	$result = array();
	
	$setting_list = GetDatatypeInitilaizeTableListForOutputSource($project);
	for ($i = 0 ; $i < count($setting_list) ; $i++) {
		$item = $setting_list[$i];
		
		$lang_matched = false;
		
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				$lang_matched = preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_PHP, $item->LangType);
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				$lang_matched = preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS, $item->LangType);
				break;
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				$lang_matched = preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA, $item->LangType);
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				$lang_matched = preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $item->LangType);
				break;
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				$lang_matched = preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT, $item->LangType);
				break;
		}
		if ($lang_matched) {
			$target_matched = false;
			switch($target) {
				case HeaderSourceForSerializeAndDeserializeType::$SERIALIZE:
					$target_matched = preg_match("/^Serialize/i", $item->ExecType);
					break;
				case HeaderSourceForSerializeAndDeserializeType::$SERIALIZE_DEFINE:
					// Not a target
					break;
				case HeaderSourceForSerializeAndDeserializeType::$DESERIALIZE:
					$target_matched = preg_match("/^Deserialize/i", $item->ExecType);
					break;
				case HeaderSourceForSerializeAndDeserializeType::$INITIALIZE_PROPERTY:
					$target_matched = preg_match("/Initialize/i", $item->ExecType);
					break;
			}
			if ($target_matched) {
				array_push($result, $item);
			}
		}
	}
	return $result;
}

function GetDatatypeInitilaizeTableListForOutputSource($project)
{
	global $DatatypeInitilaizeTableListForOutputSourceCache;
	
	if ($DatatypeInitilaizeTableListForOutputSourceCache != NULL) {
		return $DatatypeInitilaizeTableListForOutputSourceCache;
	}
	$DatatypeInitilaizeTableListForOutputSourceCache = array();
	
	$template = GetMtoolCommonTemplateFile($project, "DATATYPE-INITIALIZE-TABLE");
    $responseHeaderLines = preg_split("/\r?\n/", $template);
	
	for($i = 0 ; $i < count($responseHeaderLines);$i++) {
		$thisline = $responseHeaderLines[$i];
		
		if (preg_match("/^\s*Lang:(\S*)\s*ExecType:(.*)\s*IfMatchedRegex:(.*)\s*TemplateName:(.*)\s*ConvertFunc:(.*)\s*InitialValue:(.*)$/i", $thisline, $matched)) {
			$thisLang         = trim($matched[1]);
			$thisExecType     = trim($matched[2]);
			$thisRegex        = trim($matched[3]);
			$thisTemplateName = trim($matched[4]);
			$thisConvertFunc  = trim($matched[5]);
			$thisInitialValue = trim($matched[6]);
			
			if ($thisLang != "" && $thisRegex != "") {
				$thisPattern = new DatatypeInitilaizeTableListForOutputSourceCachePatternTable();
				$thisPattern->LangType               = $thisLang;
				$thisPattern->ExecType               = $thisExecType;
				$thisPattern->DataTypeRegex          = $thisRegex;
				$thisPattern->TemplateName           = $thisTemplateName;
				$thisPattern->ConversionFunctionName = $thisConvertFunc;
				$thisPattern->InitialValue           = $thisInitialValue;
				
				array_push($DatatypeInitilaizeTableListForOutputSourceCache, $thisPattern);
			}
		}
	}
	return $DatatypeInitilaizeTableListForOutputSourceCache;
}
$DatatypeInitilaizeTableListForOutputSourceCache = NULL;

class DatatypeInitilaizeTableListForOutputSourceCachePatternTable
{
	public $LangType;
	public $ExecType;
	public $DataTypeRegex;
	public $TemplateName;
	public $ConversionFunctionName;
	public $InitialValue;
}

class HeaderSourceForSerializeAndDeserializeType
{
	static $SERIALIZE = "Serialize";
	static $SERIALIZE_DEFINE = "Serialize Define";
	static $INITIALIZE_PROPERTY = "Initialize Property";
	static $DESERIALIZE = "Deserialize";
}

function GetHeaderSourceForSerializeAndDeserializeFromList($target, $BuildToken, $project, $ProjectSourceOutput, $list)
{
	// global $HEADER_SOURCE_FOR_SERIALIZE_FOR_OBJECTIVEC_TABLE_FOR_PROXY_CLIENT;
	// global $HEADER_SOURCE_FOR_DESERIALIZE_FOR_OBJECTIVEC_TABLE_FOR_PROXY_CLIENT;
	
	$template_list_source = "";
	
	if (is_array($list)) {
		
		for($i = 0; $i < count($list); $i++) {
			$item = $list[$i];
			$thisTemplateName = "";
			$thisConversionFunctionName = "";
			$thisInitialValue = "";
			switch($target) {
				case HeaderSourceForSerializeAndDeserializeType::$SERIALIZE:
					if ($item->IsClass) {
						$thisTemplateName = "DATACLASS-DATA-SERIALIZE-OBJECT";
					} else if ($item->IsEnum) {
						$thisTemplateName = "DATACLASS-DATA-SERIALIZE-ENUM";
					} else {
						// print "Property: " . $item->PropertyName . "\n";
						
						$setting_array = GetDatatypeInitilaizeTableListForOutputSourceForThisLang($project, $ProjectSourceOutput, $target);
						for($j = 0 ; $j < count($setting_array) ; $j++) {
							$conv_info = $setting_array[$j];
							// print "preg_match( " . $conv_info->DataTypeRegex . ", " . $item->PropertyDataType . ")\n";
							if (preg_match($conv_info->DataTypeRegex, $item->PropertyDataType)) {
								$thisTemplateName = $conv_info->TemplateName;
								$thisConversionFunctionName = $conv_info->ConversionFunctionName;
								// print "============> Matched! $thisTemplateName  $thisConversionFunctionName for  \n";
								break;
							}
						}
						// Default
						if ($thisTemplateName == "") {
							$thisTemplateName = "DATACLASS-DATA-SERIALIZE-ITEM";
						}
					}
					break;
					
				case HeaderSourceForSerializeAndDeserializeType::$SERIALIZE_DEFINE:
					switch ($ProjectSourceOutput->ProgramLanguage) {
						case ProjectSourceOutputProgramLanguageEnum::$PHP:
						case ProjectSourceOutputProgramLanguageEnum::$CS:
						case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							break;
						case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
						case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							$thisTemplateName = "DATACLASS-DATA-SERIALIZE-DEFINE-ITEM";
							break;
						case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							break;
						default:
							AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
							return;
					}
				
					break;
				
				case HeaderSourceForSerializeAndDeserializeType::$INITIALIZE_PROPERTY:
					if ($item->IsClass) {
						$thisTemplateName = "DATACLASS-DATA-INITIALIZE-OBJECT";
					} else if ($item->IsEnum) {
						$thisTemplateName = "DATACLASS-DATA-INITIALIZE-ENUM";
					} else {
						$setting_array = GetDatatypeInitilaizeTableListForOutputSourceForThisLang($project, $ProjectSourceOutput, $target);
						for($j = 0 ; $j < count($setting_array) ; $j++) {
							$conv_info = $setting_array[$j];
							// print "preg_match( " . $conv_info->DataTypeRegex . ", " . $item->PropertyDataType . ")\n";
							if (preg_match($conv_info->DataTypeRegex, $item->PropertyDataType)) {
								$thisTemplateName = $conv_info->TemplateName;
								$thisInitialValue = $conv_info->InitialValue;
								break;
							}
						}
						// Default
						if ($thisTemplateName == "") {
							$thisTemplateName = "DATACLASS-DATA-INITIALIZE-ITEM";
						}
						if ($thisInitialValue == "") {
							switch ($ProjectSourceOutput->ProgramLanguage) {
								case ProjectSourceOutputProgramLanguageEnum::$PHP:
									$thisInitialValue = "\"\"";
									break;
								case ProjectSourceOutputProgramLanguageEnum::$CS:
								case ProjectSourceOutputProgramLanguageEnum::$JAVA:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
								case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
									$thisInitialValue = GetMtoolInitialValueForClass($ProjectSourceOutput, false);
									break;
								default:
									AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
									return;
							}
						}
					}
					break;
					
				case HeaderSourceForSerializeAndDeserializeType::$DESERIALIZE:
					if ($item->IsClass) {
						$thisTemplateName = "DATACLASS-DATA-DESERIALIZE-OBJECT";
					} else if ($item->IsEnum) {
						$thisTemplateName = "DATACLASS-DATA-DESERIALIZE-ENUM";
					} else {
						// print "Property: " . $item->PropertyName . "\n";
						
						$setting_array = GetDatatypeInitilaizeTableListForOutputSourceForThisLang($project, $ProjectSourceOutput, $target);
						for($j = 0 ; $j < count($setting_array) ; $j++) {
							$conv_info = $setting_array[$j];
							// print "preg_match( " . $conv_info->DataTypeRegex . ", " . $item->PropertyDataType . ")\n";
							if (preg_match($conv_info->DataTypeRegex, $item->PropertyDataType)) {
								$thisTemplateName = $conv_info->TemplateName;
								$thisConversionFunctionName = $conv_info->ConversionFunctionName;
								// print "============> Matched! $thisTemplateName  $thisConversionFunctionName for  \n";
								break;
							}
						}
						// Default
						if ($thisTemplateName == "") {
							$thisTemplateName = "DATACLASS-DATA-DESERIALIZE-ITEM";
						}
					}
					break;
			}
			
			$template = GetHeaderSourceForSerializeAndDeserialize($thisTemplateName, $BuildToken, $project, $ProjectSourceOutput, $item->PropertyName, $item->PropertyDataType, $thisConversionFunctionName, $thisInitialValue);
			
			$template_list_source .= $template;
		}
	}
	
	$result = $template_list_source;
	switch($target) {
		case HeaderSourceForSerializeAndDeserializeType::$SERIALIZE:
			break;
		case HeaderSourceForSerializeAndDeserializeType::$SERIALIZE_DEFINE:
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
					if (is_array($list) && count($list) > 0) {
						$base_template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-DATA-SERIALIZE-DEFINE");
						$result = ReplaceOutputSourceInfoByKeyValue($base_template, array(
								array("KEY"=>"__SERIALIZE_DEFINE_ITEM__", "VALUE"=>$template_list_source, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
							));
					} else {
						$result = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-DATA-SERIALIZE-DEFINE-ZERO");
					}
					break;
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					$result = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-DATA-SERIALIZE-DEFINE");
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return;
			}
			break;
		case HeaderSourceForSerializeAndDeserializeType::$INITIALIZE_PROPERTY:
		case HeaderSourceForSerializeAndDeserializeType::$DESERIALIZE:
			break;
	}
	return $result;
}
function GetHeaderSourceForSerializeAndDeserialize($TemplateName, $BuildToken, $project, $ProjectSourceOutput, $property_name, $property_data_type, $conversion_func_name, $initial_value)
{
	$template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $TemplateName);
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__PROPERTY_NAME__", "VALUE"=>$property_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PROPERTY_DATA_TYPE__", "VALUE"=>$property_data_type, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__CONV_FUNC__", "VALUE"=>$conversion_func_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__INITIAL_VALUE__", "VALUE"=>$initial_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}

// =================================================================

function GetMtoolListDefinitionSource($BuildToken, $project, $ProjectSourceOutput)
{
	return GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST-DEFINE");
}
function GetMtoolListInitializeSource($BuildToken, $project, $ProjectSourceOutput)
{
	$template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST-INITIALIZE");
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__ARRAY_INITIALIZE_BASE__", "VALUE"=>GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST-INITIALIZE-BASE"), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
		));
	return $template;
}
function GetMtoolListInitializeSourceByDeserialize($BuildToken, $project, $ProjectSourceOutput)
{
	$template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST-INITIALIZE-BY-DESERIALIZE");
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__ARRAY_INITIALIZE_BASE__", "VALUE"=>GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST-INITIALIZE-BASE"), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
		));
	return $template;
}
function GetMtoolListSerializeSource($BuildToken, $project, $ProjectSourceOutput)
{
	return GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-LIST-SERIALIZE");
}

// =================================================================

function GetMtoolInitialValueForDataTypeBasedOnSerializeAndDeserializeSetting($project, $ProjectSourceOutput, $property_type)
{
	$InitialValue = "";
	
	$setting_array = GetDatatypeInitilaizeTableListForOutputSourceForThisLang($project, $ProjectSourceOutput, HeaderSourceForSerializeAndDeserializeType::$INITIALIZE_PROPERTY);
	for($j = 0 ; $j < count($setting_array) ; $j++) {
		$conv_info = $setting_array[$j];
		if (preg_match($conv_info->DataTypeRegex, $property_type)) {
			$InitialValue = $conv_info->InitialValue;
			break;
		}
	}
	return $InitialValue;
}
function GetMtoolInitialValueBasedOnDataType($project, $ProjectSourceOutput, $property_type, $is_object, $is_enum)
{
	$initial_value = "";
	if ($is_object) {
		$initial_value = GetMtoolInitialValueForClass($ProjectSourceOutput, false);
	} else if ($is_enum) {
		// No Need Initial Value
	} else {
		$initial_value = GetMtoolInitialValueForDataTypeBasedOnSerializeAndDeserializeSetting($project, $ProjectSourceOutput, $property_type);
	}
	return $initial_value;
}

function GetMtoolInitialValueForClass($ProjectSourceOutput, $is_list)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			if ($is_list) {
				return "array()";
			}
			return "NULL";
			
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			return "null";
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return "nil";
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return;
	}
	return "";
}

// =================================================================

?>
