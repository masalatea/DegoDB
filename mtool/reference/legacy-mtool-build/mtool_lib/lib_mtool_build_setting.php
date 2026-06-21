<?PHP

$REGEX_PATTERN_FOR_CHECK_LANG_TYPE_PHP        = "/PHP/i";
$REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS         = "/C#/i";
$REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA       = "/Java/i";
$REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC = "/Objective\-?C/i";
$REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT      = "/Swift/i";

$ENUM_UNKNOWN_VALUE_NAME = "Unknown";

function GetRegExPatternListToNoDefaultCheck($project)
{
	global $RegExPatternListToNoDefaultCheckCache;
	
	if ($RegExPatternListToNoDefaultCheckCache != NULL) {
		return $RegExPatternListToNoDefaultCheckCache;
	}
	$RegExPatternListToNoDefaultCheckCache = array();
	
	$template = GetMtoolCommonTemplateFile($project, "DBACCESS-DATATYPE-NO-DEFAULT-CHECK");
    $responseHeaderLines = preg_split("/\r?\n/", $template);
	
	for($i = 0 ; $i < count($responseHeaderLines);$i++) {
		$thisline = $responseHeaderLines[$i];
		
		if (preg_match("/^\s*Lang:(\S*)\s*IfMatchedRegex:(.*)$/i", $thisline, $matched)) {
			$thisLang        = trim($matched[1]);
			$thisRegex       = trim($matched[2]);
			
			if ($thisLang != "" && $thisRegex != "") {
				$thisPattern = new DataTypeNoDefaultCheckPatternTable();
				$thisPattern->LangType     = $thisLang;
				$thisPattern->RegexPattern = $thisRegex;
				
				array_push($RegExPatternListToNoDefaultCheckCache, $thisPattern);
			}
		}
	}
	return $RegExPatternListToNoDefaultCheckCache;
}
$RegExPatternListToNoDefaultCheckCache = NULL;

function CheckIfDefaultValueForThisLangAndDataType($project, $langtype, $datatype_in_db)
{
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT;
	
	$RegExPatternListToCheckNoDefaultCheck = GetRegExPatternListToNoDefaultCheck($project);
	
	if ($RegExPatternListToCheckNoDefaultCheck != NULL) {
		for($i = 0 ; $i < count($RegExPatternListToCheckNoDefaultCheck); $i++) {
			$RegExPatternToCheckNoDefaultCheck = $RegExPatternListToCheckNoDefaultCheck[$i];
			
			if (
				($langtype == ProjectSourceOutputProgramLanguageEnum::$CS         && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS,   $RegExPatternToCheckNoDefaultCheck->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$JAVA       && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA, $RegExPatternToCheckNoDefaultCheck->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $RegExPatternToCheckNoDefaultCheck->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $RegExPatternToCheckNoDefaultCheck->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$SWIFT      && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT, $RegExPatternToCheckNoDefaultCheck->LangType))
			) {
				if (preg_match($RegExPatternToCheckNoDefaultCheck->RegexPattern, $datatype_in_db)) {
					return false;		// no need to check
				}
			}
		}
	}
	return true;		// need to check
}
class DataTypeNoDefaultCheckPatternTable
{
	public $LangType;
	public $RegexPattern;
}

function GetRegExPatternListToNullableDataType($project)
{
	global $RegExPatternListToNullableDataTypeCache;
	
	if ($RegExPatternListToNullableDataTypeCache != NULL) {
		return $RegExPatternListToNullableDataTypeCache;
	}
	$RegExPatternListToNullableDataTypeCache = array();
	
	$template = GetMtoolCommonTemplateFile($project, "DATATYPE-NULLABLE");
    $responseHeaderLines = preg_split("/\r?\n/", $template);
	
	for($i = 0 ; $i < count($responseHeaderLines);$i++) {
		$thisline = $responseHeaderLines[$i];
		
		if (preg_match("/^\s*Lang:(\S*)\s*DataTypeRegex:(.*)$/i", $thisline, $matched)) {
			$thisLang          = trim($matched[1]);
			$thisDataTypeRegex = trim($matched[2]);
			
			print "Lang: $thisLang  Data Type Regex: $thisDataTypeRegex\n";
			
			if ($thisLang != "" && $thisDataTypeRegex != "") {
				$thisPattern = new DataTypeNullableDataTypePatternTable();
				$thisPattern->LangType             = $thisLang;
				$thisPattern->DataTypeRegexPattern = $thisDataTypeRegex;
				
				array_push($RegExPatternListToNullableDataTypeCache, $thisPattern);
			}
		}
	}
	return $RegExPatternListToNullableDataTypeCache;
}
$RegExPatternListToNullableDataTypeCache = NULL;

function CheckIfNullableForThisLangAndDataType($project, $langtype, $datatype_in_program_lang)
{
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC;
	global $REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT;
	
	$RegExPatternListToCheckNullableDataType = GetRegExPatternListToNullableDataType($project);
	
	if ($RegExPatternListToCheckNullableDataType != NULL) {
		for($i = 0 ; $i < count($RegExPatternListToCheckNullableDataType); $i++) {
			$RegExPatternToCheckNullableDataType = $RegExPatternListToCheckNullableDataType[$i];
			
			if (
				($langtype == ProjectSourceOutputProgramLanguageEnum::$CS         && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_CS,   $RegExPatternToCheckNullableDataType->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$JAVA       && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_JAVA, $RegExPatternToCheckNullableDataType->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $RegExPatternToCheckNullableDataType->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_OBJECTIVEC, $RegExPatternToCheckNullableDataType->LangType)) ||
				($langtype == ProjectSourceOutputProgramLanguageEnum::$SWIFT      && preg_match($REGEX_PATTERN_FOR_CHECK_LANG_TYPE_SWIFT, $RegExPatternToCheckNullableDataType->LangType))
			) {
				if (preg_match($RegExPatternToCheckNullableDataType->DataTypeRegexPattern, $datatype_in_program_lang)) {
					return true;
				}
			}
		}
	}
	return false;
}
class DataTypeNullableDataTypePatternTable
{
	public $LangType;
	public $DataTypeRegexPattern;
}

?>
