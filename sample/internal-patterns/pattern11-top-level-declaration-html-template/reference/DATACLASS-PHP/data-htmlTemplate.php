<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-htmlTemplateBase.php';

class htmlTemplateData extends htmlTemplateDataBase
{
}
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

?>