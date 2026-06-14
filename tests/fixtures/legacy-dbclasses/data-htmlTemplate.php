<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlTemplateData
{
	public $PID;
	public $TargetType;
	public $ParentHtmlTemplatePID;
	public $name;
	public $ProgramLanguage;
	public $FileName;
	public $Comment;
	
	public function __construct() {
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==

function GethtmlTemplateDataLanguageCaption($lang)
{
	switch($lang)
	{
		case htmlTemplateProgramLanguageEnum::$PHP:
			return "PHP";
		case htmlTemplateProgramLanguageEnum::$CS:
			return "C#";
		case htmlTemplateProgramLanguageEnum::$JAVA:
			return "Java";
		case htmlTemplateProgramLanguageEnum::$OBJECTIVECH:
			return "Objective-C Header";
		case htmlTemplateProgramLanguageEnum::$OBJECTIVECM:
			return "Objective-C Implementation";
	}
	return $lang;
}

class SortedhtmlTemplateDataContainer
{
	public $htmlTemplate;
	public $ChildList;
}

function MakehtmlTemplateTree($htmlTemplateList, $targetPID)
{
	$htmlTemplateTree = array();
	
	for($i = 0 ; $i < count($htmlTemplateList); $i++) {
		$htmlTemplate = $htmlTemplateList[$i];
		
		if ($htmlTemplate->ParentHtmlTemplatePID == $targetPID) {
			
			$SortedhtmlTemplateDataContainerObj = new SortedhtmlTemplateDataContainer();
			$SortedhtmlTemplateDataContainerObj->htmlTemplate = $htmlTemplate;
			$SortedhtmlTemplateDataContainerObj->ChildList = MakehtmlTemplateTree($htmlTemplateList, $htmlTemplate->PID);
			array_push($htmlTemplateTree, $SortedhtmlTemplateDataContainerObj);
		}
	}
	return $htmlTemplateTree;
}
function MakehtmlTemplateListFromTree(&$htmlTemplateList, $htmlTemplateTree)
{
	for($i = 0 ; $i < count($htmlTemplateTree); $i++) {
		$SortedhtmlTemplate = $htmlTemplateTree[$i];
		array_push($htmlTemplateList, $SortedhtmlTemplate->htmlTemplate);
		MakehtmlTemplateListFromTree($htmlTemplateList, $SortedhtmlTemplate->ChildList);
	}
}

function SorthtmlTemplateDataListByTree($originalhtmlTemplateList)
{
	$htmlTemplateTree = MakehtmlTemplateTree($originalhtmlTemplateList, 0);
	$htmlTemplateList = array();
	MakehtmlTemplateListFromTree($htmlTemplateList, $htmlTemplateTree);
	
	for($i = 0 ; $i < count($originalhtmlTemplateList) ; $i++) {
		$originalhtmlTemplate = $originalhtmlTemplateList[$i];
		
		$found = false;
		for($j = 0 ; $j < count($htmlTemplateList) ; $j++) {
			$htmlTemplate = $htmlTemplateList[$j];
			
			if ($originalhtmlTemplate->PID == $htmlTemplate->PID) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			array_push($htmlTemplateList, $originalhtmlTemplate);
		}
	}
	
	return $htmlTemplateList;
}

function GethtmlTemplateTargetTypeCaption($targettype)
{
	switch($targettype)
	{
		case htmlTemplateTargetTypeEnum::$HTML:
			return "HTML (for each Project)";
		case htmlTemplateTargetTypeEnum::$DB:
			return "DB (common for all Project)";
		case htmlTemplateTargetTypeEnum::$PROXYSERVER:
			return "Proxy Server (common for all Project)";
		case htmlTemplateTargetTypeEnum::$PROXYCLIENT:
			return "Proxy Client (common for all Project)";
		case htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER:
			return "Proxy Server (common for all Project) for DBaaS";
		case htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT:
			return "Proxy Client (common for all Project) for DBaaS";
		case htmlTemplateTargetTypeEnum::$UNITTEST:
			return "Unit Test (common for all Project)";
		case htmlTemplateTargetTypeEnum::$UPLOADSETTING:
			return "Upload Setting";
		case htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE:
			return "Language Resource";
	}
	return $targettype;
}

function GetAllhtmlTemplateTargetType()
{
	return array(
		htmlTemplateTargetTypeEnum::$HTML,
		htmlTemplateTargetTypeEnum::$DB,
		htmlTemplateTargetTypeEnum::$PROXYSERVER,
		htmlTemplateTargetTypeEnum::$PROXYCLIENT,
		htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER,
		htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT,
		htmlTemplateTargetTypeEnum::$UNITTEST,
		htmlTemplateTargetTypeEnum::$UPLOADSETTING,
		htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE
	);
	// この順番で出力されることがあるので注意
}

// == END OF EDITABLE AREA FOR BOTTOM ==

class htmlTemplateTargetTypeEnum
{
	static $UNKNOWN = "Unknown";
	static $HTML = "html";
	static $DB = "db";
	static $PROXYSERVER = "proxyserver";
	static $PROXYCLIENT = "proxyclient";
	static $DBAASPROXYSERVER = "dbaasproxyserver";
	static $DBAASPROXYCLIENT = "dbaasproxyclient";
	static $UNITTEST = "unittest";
	static $UPLOADSETTING = "uploadsetting";
	static $LANGUAGERESOURCE = "LanguageResource";
}

class htmlTemplateProgramLanguageEnum
{
	static $UNKNOWN = "Unknown";
	static $PHP = "php";
	static $CS = "cs";
	static $JAVA = "java";
	static $OBJECTIVECH = "objectivech";
	static $OBJECTIVECM = "objectivecm";
	static $SWIFT = "swift";
}

?>