<?PHP

function CheckIfProjectIncludeProxy($ProjectPID)
{
	$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
	
	$IncludeProxy = false;
	for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$i];
		switch($ProjectSourceOutput->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
				break;
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
				$IncludeProxy = true;
				break;
			case ProjectSourceOutputClassTypeEnum::$HTML:
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
	}
	return $IncludeProxy;
}

function CheckIfProjectIncludeHtml($ProjectPID)
{
	$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
	
	$IncludeHtml = false;
	for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$i];
		switch($ProjectSourceOutput->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
				break;
			case ProjectSourceOutputClassTypeEnum::$HTML:
				$IncludeHtml = true;
				break;
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
	}
	return $IncludeHtml;
}

function CheckIfProjectIncludeLanguageResource($ProjectPID)
{
	$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
	
	$IncludeLanguageResource = false;
	for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$i];
		switch($ProjectSourceOutput->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$HTML:
				break;
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				$IncludeLanguageResource = true;
				break;
			default:
				die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
		}
	}
	return $IncludeLanguageResource;
}

?>
