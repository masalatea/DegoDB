<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-HtmlTemplateBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-HtmlTemplate.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-HtmlTemplate.php` and extend `HtmlTemplateDataBase` for project-specific customizations.

    class HtmlTemplateData extends HtmlTemplateDataBase
    {
    }
}
function GethtmlTemplateDataLanguageCaption($lang)
{
	switch($lang)
	{
		case HtmlTemplateProgramLanguageEnum::$PHP:
			return "PHP";
		case HtmlTemplateProgramLanguageEnum::$CS:
			return "C#";
		case HtmlTemplateProgramLanguageEnum::$JAVA:
			return "Java";
		case HtmlTemplateProgramLanguageEnum::$OBJECTIVECH:
			return "Objective-C Header";
		case HtmlTemplateProgramLanguageEnum::$OBJECTIVECM:
			return "Objective-C Implementation";
	}
	return $lang;
}



function MakehtmlTemplateTree($HtmlTemplateList, $targetPID)
{
	$HtmlTemplateTree = array();
	
	for($i = 0 ; $i < count($HtmlTemplateList); $i++) {
		$HtmlTemplate = $HtmlTemplateList[$i];
		
		if ($HtmlTemplate->ParentHtmlTemplatePID == $targetPID) {
			
			$SortedhtmlTemplateDataContainerObj = new SortedhtmlTemplateDataContainer();
			$SortedhtmlTemplateDataContainerObj->HtmlTemplate = $HtmlTemplate;
			$SortedhtmlTemplateDataContainerObj->ChildList = MakehtmlTemplateTree($HtmlTemplateList, $HtmlTemplate->PID);
			array_push($HtmlTemplateTree, $SortedhtmlTemplateDataContainerObj);
		}
	}
	return $HtmlTemplateTree;
}
function MakehtmlTemplateListFromTree(&$HtmlTemplateList, $HtmlTemplateTree)
{
	for($i = 0 ; $i < count($HtmlTemplateTree); $i++) {
		$SortedhtmlTemplate = $HtmlTemplateTree[$i];
		array_push($HtmlTemplateList, $SortedhtmlTemplate->HtmlTemplate);
		MakehtmlTemplateListFromTree($HtmlTemplateList, $SortedhtmlTemplate->ChildList);
	}
}

function SorthtmlTemplateDataListByTree($originalhtmlTemplateList)
{
	$HtmlTemplateTree = MakehtmlTemplateTree($originalhtmlTemplateList, 0);
	$HtmlTemplateList = array();
	MakehtmlTemplateListFromTree($HtmlTemplateList, $HtmlTemplateTree);
	
	for($i = 0 ; $i < count($originalhtmlTemplateList) ; $i++) {
		$originalhtmlTemplate = $originalhtmlTemplateList[$i];
		
		$found = false;
		for($j = 0 ; $j < count($HtmlTemplateList) ; $j++) {
			$HtmlTemplate = $HtmlTemplateList[$j];
			
			if ($originalhtmlTemplate->PID == $HtmlTemplate->PID) {
				$found = true;
				break;
			}
		}
		if (!$found) {
			array_push($HtmlTemplateList, $originalhtmlTemplate);
		}
	}
	
	return $HtmlTemplateList;
}

function GethtmlTemplateTargetTypeCaption($targettype)
{
	switch($targettype)
	{
		case HtmlTemplateTargetTypeEnum::$HTML:
			return "HTML (for each Project)";
		case HtmlTemplateTargetTypeEnum::$DB:
			return "DB (common for all Project)";
		case HtmlTemplateTargetTypeEnum::$PROXYSERVER:
			return "Proxy Server (common for all Project)";
		case HtmlTemplateTargetTypeEnum::$PROXYCLIENT:
			return "Proxy Client (common for all Project)";
		case HtmlTemplateTargetTypeEnum::$DBAASPROXYSERVER:
			return "Proxy Server (common for all Project) for DBaaS";
		case HtmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT:
			return "Proxy Client (common for all Project) for DBaaS";
		case HtmlTemplateTargetTypeEnum::$UNITTEST:
			return "Unit Test (common for all Project)";
		case HtmlTemplateTargetTypeEnum::$UPLOADSETTING:
			return "Upload Setting";
		case HtmlTemplateTargetTypeEnum::$LANGUAGERESOURCE:
			return "Language Resource";
	}
	return $targettype;
}

function GetAllhtmlTemplateTargetType()
{
	return array(
		HtmlTemplateTargetTypeEnum::$HTML,
		HtmlTemplateTargetTypeEnum::$DB,
		HtmlTemplateTargetTypeEnum::$PROXYSERVER,
		HtmlTemplateTargetTypeEnum::$PROXYCLIENT,
		HtmlTemplateTargetTypeEnum::$DBAASPROXYSERVER,
		HtmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT,
		HtmlTemplateTargetTypeEnum::$UNITTEST,
		HtmlTemplateTargetTypeEnum::$UPLOADSETTING,
		HtmlTemplateTargetTypeEnum::$LANGUAGERESOURCE
	);
	// この順番で出力されることがあるので注意
}


?>
