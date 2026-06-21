<?PHP

function CreateDataClassName($basename)
{
	global $DATA_CLASS_SUFFIX;
	return $basename . $DATA_CLASS_SUFFIX;
}
function GetDataClasBaseNameFromDataClassName($classname)
{
	global $DATA_CLASS_SUFFIX;
	return preg_replace("/" . $DATA_CLASS_SUFFIX . "$/", "", $classname);
}
function CreateDataClassBaseNameFromDAFunc($dafunc)
{
	if (trim($dafunc->DataClassBaseNameForSelectAction) != "") {
		$DataClassBaseName = trim($dafunc->DataClassBaseNameForSelectAction);
	} else {
		$DataClassBaseName = trim($dafunc->name);
	}
	return $DataClassBaseName;
}
function CreateDataClassNameFromDAFunc($dafunc)
{
	return CreateDataClassName(CreateDataClassBaseNameFromDAFunc($dafunc));
}

$DATA_CLASS_SUFFIX = "Data";

function CreateDatabaseAccessClassName($basename)
{
	global $DB_ACCESS_CLASS_SUFFIX;
	return $basename . $DB_ACCESS_CLASS_SUFFIX;
}
$DB_ACCESS_CLASS_SUFFIX = "DBAccess";

function CreateProxyServerFileName($lang, $customFileExtention, $basename, $sourceFunctionName)
{
	switch ($lang) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "proxyserver-" . $basename . "-" . $sourceFunctionName . ".php";
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			die("Aborted. This is not a target for Proxy Server: " . $lang);
		default:
			die("Error! Aborted. Unknown Program Language: " . $lang);
	}
	return "";
}

function CreateProxyClientAsyncTaskLoaderFileName($lang, $basename, $sourceFunctionName)
{
	switch ($lang) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			die("Aborted. This is not a target for Async Task Loader of Proxy Client: " . $lang);
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			return $basename . "ProxyClient" . $sourceFunctionName . "AsyncTaskLoader.java";
		default:
			die("Error! Aborted. Unknown Program Language: " . $lang);
	}
	return "";
}

function GetFunctionNameFromFunctionActionType($dafuncname, $ActionType)
{
	$sourceFunctionName = "";
	switch($ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
			$sourceFunctionName = "Get" . $dafuncname;
			break;
		case dafuncActionTypeEnum::$SELECTLIST:
			$sourceFunctionName = "Get" . $dafuncname . "List";
			break;
		case dafuncActionTypeEnum::$INSERT:
			$sourceFunctionName = "Insert" . $dafuncname;
			break;
		case dafuncActionTypeEnum::$UPDATE:
			$sourceFunctionName = "Update" . $dafuncname;
			break;
		case dafuncActionTypeEnum::$DELETE:
			$sourceFunctionName = "Delete" . $dafuncname;
			break;
		default:
			print "INTERNAL ERROR! Unknown Action Type: " . $ActionType . "\n";
			break;
	}
	return $sourceFunctionName;
}

function GetMtoolDataClassFileName($ProjectSourceOutput, $dataclass)
{
	$dataclassfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$dataclassfilename = "data-" . $dataclass->name . ".php";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$dataclassfilename = CreateDataClassName($dataclass->name) . ".cs";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			$dataclassfilename = CreateDataClassName($dataclass->name) . ".java";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			$dataclassfilename = CreateDataClassName($dataclass->name) . ".h";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$dataclassfilename = CreateDataClassName($dataclass->name) . ".m";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$dataclassfilename = CreateDataClassName($dataclass->name) . ".swift";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return NULL;
	}
	return $dataclassfilename;
}
function GetMtoolDataClassListFileName($ProjectSourceOutput, $dataclass)
{
	$dataclasslistfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$dataclasslistfilename = GetMtoolDataListClassName($ProjectSourceOutput, CreateDataClassName($dataclass->name)) . ".cs";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			$dataclasslistfilename = GetMtoolDataListClassName($ProjectSourceOutput, CreateDataClassName($dataclass->name)) . ".java";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			$dataclasslistfilename = GetMtoolDataListClassName($ProjectSourceOutput, CreateDataClassName($dataclass->name)) . ".h";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$dataclasslistfilename = GetMtoolDataListClassName($ProjectSourceOutput, CreateDataClassName($dataclass->name)) . ".m";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$dataclasslistfilename = GetMtoolDataListClassName($ProjectSourceOutput, CreateDataClassName($dataclass->name)) . ".swift";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return NULL;
	}
	return $dataclasslistfilename;
}
function GetMtoolDAFilenameForBuild($ProjectSourceOutput, $da)
{
	$localfilename = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$localfilename = "dbaccess-" . $da->name . ".php";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$localfilename = $da->name . "DBAccess.cs";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			$localfilename = $da->name . "DBAccess.java";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			$localfilename = $da->name . "DBAccess.h";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			$localfilename = $da->name . "DBAccess.m";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$localfilename = $da->name . "DBAccess.swift";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			return NULL;
	}
	return $localfilename;
}

function GetMtoolDataListClassName($ProjectSourceOutput, $dataclassName)
{
	return $dataclassName . "List";
}

function GetSecurityCheckJsonKeyName()
{
	return "Security";
}

function GetAliasTableNameListString($RelatedDBTableNameList, $targetTableName)
{
	$AliasTableNameList = array();
		
	for($j = 0 ; $j < count($RelatedDBTableNameList) ; $j++) {
		$RelatedDBTableName = $RelatedDBTableNameList[$j];
		
		if ($targetTableName == $RelatedDBTableName->TableName) {
			if (!in_array($RelatedDBTableName->AnotherTableName, $AliasTableNameList)) {
				array_push($AliasTableNameList, $RelatedDBTableName->AnotherTableName);
			}
		}
	}
	return implode(",", $AliasTableNameList);
}

function CheckIfNameIsSameByCheckingParentClassName($tablename, $classname, $dataclasslist)
{
	if ($tablename == $classname) {
		return true;
	}
	
	for($i = 0 ; $i < count($dataclasslist); $i++) {
		$dataclass = $dataclasslist[$i];
		
		if ($dataclass->name == $classname) {
			if ($dataclass->InheritParentDataClassName != "") {
				return CheckIfNameIsSameByCheckingParentClassName($tablename, GetDataClasBaseNameFromDataClassName($dataclass->InheritParentDataClassName), $dataclasslist);
			}
		}
	}
	return false;
}

function GetRegExPatternListToTranslateDataType($project)
{
	global $RegExPatternListToTranslateDataTypeCache;
	
	if ($RegExPatternListToTranslateDataTypeCache != NULL) {
		return $RegExPatternListToTranslateDataTypeCache;
	}
	$RegExPatternListToTranslateDataTypeCache = array();
	
	$template = GetMtoolCommonTemplateFile($project, "DATATYPE-TRANSLATION-TABLE");
    $responseHeaderLines = preg_split("/\r?\n/", $template);
	
	for($i = 0 ; $i < count($responseHeaderLines);$i++) {
		$thisline = $responseHeaderLines[$i];
		
		if (preg_match("/^\s*ConvertType:\s*(\S*)\s*(Lang:\s*(\S*))?\s*IfMatchedRegex:(.*)\s*DataType:(.*)$/i", $thisline, $matched)) {
			$thisConvertType = trim($matched[1]);
			$thisLang        = trim($matched[3]);
			$thisRegex       = trim($matched[4]);
			$thisDatatype    = trim($matched[5]);
			
			if ($thisRegex != "" && $thisDatatype != "") {
				$thisPattern = new DataTypeTranslationPatternTable();
				$thisPattern->ConvertType  = $thisConvertType;
				$thisPattern->LangType     = $thisLang;
				$thisPattern->RegexPattern = $thisRegex;
				$thisPattern->LangDataType = $thisDatatype;
				
				array_push($RegExPatternListToTranslateDataTypeCache, $thisPattern);
			}
		}
	}
	return $RegExPatternListToTranslateDataTypeCache;
}
class DataTypeTranslationPatternTable
{
	public $ConvertType;
	public $LangType;
	public $RegexPattern;
	public $LangDataType;
}
$RegExPatternListToTranslateDataTypeCache = NULL;

function GetSourceDataTypeFromDatabaseDataTypeForGeneral($project, $datatype)
{
	return GetSourceDataTypeFromDatabaseDataType($project, "DB2GeneralLang", "", $datatype);
}
function GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $datatype)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$datatype = GetSourceDataTypeFromDatabaseDataType($project, "GeneralLang2SpecificLang", $ProjectSourceOutput->ProgramLanguage, $datatype);
			break;
	}
	return $datatype;
}
function GetSourceDataTypeFromDatabaseDataType($project, $converttype, $langtype, $datatype)
{
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT;
	
	$RegExPatternListToTranslateDataType = GetRegExPatternListToTranslateDataType($project);
	
	if ($RegExPatternListToTranslateDataType != NULL) {
		for($i = 0 ; $i < count($RegExPatternListToTranslateDataType); $i++) {
			$RegExPatternToTranslateDataType = $RegExPatternListToTranslateDataType[$i];
			
			if (trim(strtoupper($converttype)) == trim(strtoupper($RegExPatternToTranslateDataType->ConvertType))) {
				if (
				    (
					  ($RegExPatternToTranslateDataType->ConvertType == "DB2GeneralLang")
					)
					||
				    (
					  ($RegExPatternToTranslateDataType->ConvertType == "GeneralLang2SpecificLang") &&
					  (
					    ($langtype == ProjectSourceOutputProgramLanguageEnum::$CS   && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS,   $RegExPatternToTranslateDataType->LangType)) ||
					    ($langtype == ProjectSourceOutputProgramLanguageEnum::$JAVA && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA, $RegExPatternToTranslateDataType->LangType)) ||
					    ($langtype == ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $RegExPatternToTranslateDataType->LangType)) ||
					    ($langtype == ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $RegExPatternToTranslateDataType->LangType)) ||
					    ($langtype == ProjectSourceOutputProgramLanguageEnum::$SWIFT && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT, $RegExPatternToTranslateDataType->LangType))
					  )
					)
				) {
					if (preg_match($RegExPatternToTranslateDataType->RegexPattern, $datatype)) {
						return $RegExPatternToTranslateDataType->LangDataType;
						
						
					}
				}
			}
		}
	}
	// Return Default
	if (preg_match("/DB2GeneralLang/i", $converttype)) {
		// default
	} else if (preg_match("/GeneralLang2SpecificLang/i", $converttype)) {
		return $datatype;
	} else {
		die("Unknown Convert Type: " . $converttype);
	}
	return "object";
}

function GetDataTypeInProgLangBasedOnSQLSelect($project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, &$dbdatabype, &$nullable)
{
	global $dbtablecolumnslistCacheHT;
	
	$datatypeInProgLang = "object";		// default;
	$dbdatabype = "";
	$nullable = false;
	for ($m = 0 ; $m < count($tablelist) ; $m++) {
		$table = $tablelist[$m];
		if ($table->name == $targetTableName) {
			
			$dbtablecolumnslist = NULL;
			$key = $project->PID . "-" . $table->PID;
			if (array_key_exists($key, $dbtablecolumnslistCacheHT)) {
				$dbtablecolumnslist = $dbtablecolumnslistCacheHT[$key];
			} else {
				$dbtablecolumnslist = $DAdbtablecolumns->GetdbtablecolumnsList($project->PID, $table->PID);
				$dbtablecolumnslistCacheHT[$key] = $dbtablecolumnslist;
			}
			
			for ($n = 0 ; $n < count($dbtablecolumnslist) ; $n++) {
				$dbtablecolumns = $dbtablecolumnslist[$n];
				
				if ($dbtablecolumns->name == $targetTableColumnName) {
					$datatypeInProgLang = GetSourceDataTypeFromDatabaseDataTypeForGeneral($project, $dbtablecolumns->datatype);
					$dbdatabype = $dbtablecolumns->datatype;
					$nullable = preg_match("/YES/i", $dbtablecolumns->IsNull);
				}
			}
		}
	}
	return $datatypeInProgLang;
}
$dbtablecolumnslistCacheHT = array();

function GetCustomProxyRequestParamObjectName($no)
{
	return "step" . $no;
}
function GetReplacementStringForParamOfObjectForStep($no)
{
	return "__PARAM_OF_OBJECT_FOR_STEP" . $no . "__";
}
function CreateCustomProxyDataBaseClassName($name)
{
	return $name . "Custom";
}
function CreateProxyRequestParamName($basename, $sourceFunctionName)
{
	return $basename . "Proxy" . $sourceFunctionName . "RequestParams";
}
function CreateProxyResultParamName($basename, $sourceFunctionName)
{
	return $basename . "Proxy" . $sourceFunctionName . "ResultParams";
}

function GetTargetStoreClassFieldNameForCombination($tablename, $columnname, $with_highlight_tag)
{
	if ($with_highlight_tag) {
		return "<b>" . $tablename . "</b>" . $columnname;
	} else {
		return $tablename . $columnname;
	}
}

function CommaConnectedStringsFromArray($strList)
{
	$result = "";
	if ($strList != NULL && is_array($strList)) {
		for ($i = 0 ; $i < count($strList) ; $i++) {
			if ($i != 0) {
				$result .= ", ";
			}
			$result .= $strList[$i];
		}
	}
	return $result;
}

function GetYesOrNoBasedOnMySQLBooleanValue($DBValue)
{
	if ($DBValue == "1") {
		return "Yes";
	} else {
		return "No";
	}
}

?>
