<?PHP

function InitializeArgumentParameterStringAndEquality($BuildToken, &$thisEquality, $project, $ProjectSourceOutput, $parameterName, $valueName, $ParameterDataType, $thisBaseEquality)
{
	$thisEquality = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			switch ($ParameterDataType) {
				case ParameterDataTypeCommonEnum::$DEFAULT:
					$template_escape = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING", $parameterName, $valueName);
					$thisEquality = $thisBaseEquality . GetValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $template_escape);
					break;
					
				case ParameterDataTypeCommonEnum::$RAW:
					$template = GetRAWValueOfDBAccessClass($project, $ProjectSourceOutput, $valueName);
					$thisEquality = $thisBaseEquality . GetRawValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $template);
					break;
					
				case ParameterDataTypeCommonEnum::$FILE:
					$thisEquality = $thisBaseEquality . GetFileValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $valueName);
					break;
					
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Data Type: " . $ParameterDataType);
					break;
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
			$template_escape = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-PARAMETER", $parameterName, $valueName);
			break;
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$template_escape = "";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$thisEquality = $thisBaseEquality . GetValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $template_escape);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
}

function InitializeFixedParameterStringAndEquality($BuildToken, &$FixedParameterString, &$thisEquality, $project, $ProjectSourceOutput, $parameterName, $FixedParameter, $ParameterDataType, $thisBaseEquality, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns)
{
	$FixedParameterString = "";
	$thisEquality = "";
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			switch ($ParameterDataType) {
				case ParameterDataTypeCommonEnum::$DEFAULT:
					$FixedParameterString = GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $FixedParameter);
					$template_escape = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING", $parameterName, $FixedParameterString);
					$thisEquality = $thisBaseEquality . GetValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $template_escape);
					break;
					
				case ParameterDataTypeCommonEnum::$RAW:
					$FixedParameterString = GetRAWValueOfDBAccessClass($project, $ProjectSourceOutput, $FixedParameter);
					$thisEquality = $thisBaseEquality . $FixedParameterString;
					break;
					
				case ParameterDataTypeCommonEnum::$FILE:
					$thisEquality = $thisBaseEquality . GetFileValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $FixedParameter);
					break;
					
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Data Type: " . $ParameterDataType);
					break;
			}
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$datatypeInProgLang = "";
			if ($targetTableName != "" && $targetTableColumnName != "") {
				$dbdatabype = "";
				$nullable = false;
				$datatypeInProgLang = GetDataTypeInProgLangBasedOnSQLSelect($project, $ProjectSourceOutput, $tablelist, $targetTableName, $targetTableColumnName, $DAdbtablecolumns, $dbdatabype, $nullable);
			}
			if ($datatypeInProgLang != "" && CheckIfEnumDataType($datatypeInProgLang)) {
				$FixedParameterString = GetRAWValueOfDBAccessClass($project, $ProjectSourceOutput, $FixedParameter);
			} else {
				$FixedParameterString = GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $FixedParameter);
			}
			$template_escape = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-PARAMETER", $parameterName, $FixedParameterString);
			$thisEquality = $thisBaseEquality . GetValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $template_escape);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
}

function GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, $templateName, $parameterName, $valueName)
{
	$template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $templateName);
	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
			array("KEY"=>"__PARAMETER_NAME__", "VALUE"=>$parameterName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__VAL__",            "VALUE"=>$valueName,     "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template;
}
function GetValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $val)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "'\" . " . $val . " . \"'";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return $val;
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	return $val;
}
function GetRawValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $val)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "\" . " . $val . " . \"";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return $val;
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	return $val;
}
function GetFileValueOfDBAccessClassEscapeVariable($project, $ProjectSourceOutput, $val)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "?";
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
	return $val;
}

function GetValueOfDBAccessClassEscapeFixedString($project, $ProjectSourceOutput, $FixedValue)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return "\"" . quotemeta($FixedValue) . "\"";
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return "@\"" . preg_replace('/"/', '""', $FixedValue) . "\"";
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	return $FixedValue;
}
function GetRAWValueOfDBAccessClass($project, $ProjectSourceOutput, $thisValue)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			return $thisValue;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			return $thisValue;
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	return $thisValue;
}
function UpdateMtoolTemplateDBAccessClassEscapeStringForSourceParam($project, $ProjectSourceOutput, $variableName, 
&$sourceParamList, &$sourceParamInfoList, $dafuncselecttargetfieldslist, $dafuncselectwhere, $tablelist, $DAdbtablecolumns)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			AddToUnduplicatedList($sourceParamList, $variableName);
			AddSourceParamInfoData($variableName, "", false, false, false, "", false, $sourceParamInfoList);
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$datatype = "object";	// default
			$isEnum = false;
			$basedataclassname_for_enum = "";
			if ($dafuncselecttargetfieldslist != NULL && $dafuncselectwhere != NULL && $tablelist != NULL)
			{
				$correspondingdafuncselecttargetfields = NULL;
				for ($m = 0 ; $m < count($dafuncselecttargetfieldslist); $m++) {
					$dafuncselecttargetfields = $dafuncselecttargetfieldslist[$m];
					
					if ($dafuncselectwhere->targetTableColumnName == $dafuncselecttargetfields->targetTableColumnName) {
						$correspondingdafuncselecttargetfields = $dafuncselecttargetfields;
						break;
					}
				}
				$this_targetTableName = "";
				$this_targetTableColumnName = "";
				if ($correspondingdafuncselecttargetfields) {
					$this_targetTableName = $correspondingdafuncselecttargetfields->targetTableName;
					$this_targetTableColumnName = $correspondingdafuncselecttargetfields->targetTableColumnName;
				}
				
				$dbdatabype = "";
				$nullable = false;
				$datatypeInProgLang = GetDataTypeInProgLangBasedOnSQLSelect($project, $ProjectSourceOutput, $tablelist, $this_targetTableName, $this_targetTableColumnName, $DAdbtablecolumns, $dbdatabype, $nullable);
				$isEnum = CheckIfEnumDataType($datatypeInProgLang);
				if ($isEnum) {
					$basedataclassname_for_enum = CreateDataClassName($this_targetTableName);
				}
				
				$datatype = GetProgramLangBasedOnEnum($ProjectSourceOutput, $datatypeInProgLang, $this_targetTableName, $this_targetTableColumnName);
			}
			AddToUnduplicatedList($sourceParamList, $datatype . " " . $variableName);
			AddSourceParamInfoData($variableName, $datatype, false, $isEnum, false, $basedataclassname_for_enum, false, $sourceParamInfoList);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
}

function GetVariableNameFromProjectSetting($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName)
{
	$variableName = "";
	
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
					$variableName = "\$" . $parameterBaseName;
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					$variableName = $parameterBaseName;
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					break;
			}
			break;
		case dafuncActionTypeEnum::$INSERT:
		case dafuncActionTypeEnum::$UPDATE:
		case dafuncActionTypeEnum::$DELETE:
			if ($dafunc->IsInsertUpdateDeleteTargetClassObject()) {
				$variableName = GetVariableNameFromProjectSettingForUpdateByClassObject($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName);
				
			} else if ($dafunc->IsInsertUpdateDeleteTargetVal()) {
				$variableName = GetVariableNameFromProjectSettingForUpdateByVal($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName);
				
			} else {
				// Unknown
				AddMtoolErrorBuildMessage("Unknown Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
			}
			break;
	}
	return $variableName;
}

function GetVariableNameFromProjectSettingForUpdateForSetTarget($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName)
{
	$variableName = "";
	
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
		case dafuncActionTypeEnum::$INSERT:
		case dafuncActionTypeEnum::$DELETE:
			AddMtoolErrorBuildMessage("Internal Error! Parameter Type :" . $dafunc->InsertUpdateDeleteParamType . " is not supported for Action:" . $dafunc->ActionType . " in function GetVariableNameFromProjectSettingForUpdateForSetTarget");
			break;
		case dafuncActionTypeEnum::$UPDATE:
			if ($dafunc->IsInsertUpdateDeleteTargetClassObject() ||
			    $dafunc->IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()) {
				$variableName = GetVariableNameFromProjectSettingForUpdateByClassObject($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName);
				
			} else if ($dafunc->IsInsertUpdateDeleteTargetVal()) {
				$variableName = GetVariableNameFromProjectSettingForUpdateByVal($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName);
				
			} else {
				// Unknown
				AddMtoolErrorBuildMessage("Unknown Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
			}
			break;
	}
	return $variableName;
}
function GetVariableNameFromProjectSettingForUpdateForWhereTarget($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName)
{
	$variableName = "";
	
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
		case dafuncActionTypeEnum::$INSERT:
		case dafuncActionTypeEnum::$DELETE:
			AddMtoolErrorBuildMessage("Internal Error! Parameter Type :" . $dafunc->InsertUpdateDeleteParamType . " is not supported for Action:" . $dafunc->ActionType . " in function GetVariableNameFromProjectSettingForUpdateForSetTarget");
			break;
		case dafuncActionTypeEnum::$UPDATE:
			if ($dafunc->IsInsertUpdateDeleteTargetClassObject()) {
				$variableName = GetVariableNameFromProjectSettingForUpdateByClassObject($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName);
				
			} else if ($dafunc->IsInsertUpdateDeleteTargetVal() ||
			    $dafunc->IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()) {
				$variableName = GetVariableNameFromProjectSettingForUpdateByVal($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName);
				
			} else {
				// Unknown
				AddMtoolErrorBuildMessage("Unknown Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
			}
			break;
	}
	return $variableName;
}

function GetVariableNameFromProjectSettingForUpdateByClassObject($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName)
{
	$variableName = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$variableName = $classObjectName . "->" . $targetTableColumnName;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$variableName = $classObjectName . "." . $targetTableColumnName;
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	return $variableName;
}
function GetVariableNameFromProjectSettingForUpdateByVal($project, $ProjectSourceOutput, $dafunc, $classObjectName, $targetTableColumnName, $parameterBaseName)
{
	$variableName = "";
	
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$variableName = "\$" . $parameterBaseName;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$variableName = $parameterBaseName;
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	return $variableName;
}


function InitializeParameterNameFromProjectSetting()
{
	global $ParameterNameCache;
	
	$ParameterNameCache = array();
}

function GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName)
{
	global $ParameterNameCache;
	
	$resultBase = $parameterBaseName;
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			$resultBase = "\$" . $parameterBaseName;
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$resultBase = $parameterBaseName;
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	$result = $resultBase;	// default
	for($suffix = 2 ; $suffix <= 1000 ; $suffix++) {
		if (!array_key_exists($result, $ParameterNameCache)) {
			break;
		}
		$result = $resultBase . $suffix;
	}
	$ParameterNameCache[$result] = true;
	return $result;
}
$ParameterNameCache = array();

function AddSourceParamListBasedOnLanguage($project, $ProjectSourceOutput, $tablelist, $variableName, &$sourceParamList, &$sourceParamInfoList, $dafunc, $targetTableColumnName, $DAdbtablecolumns)
{
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			AddToUnduplicatedList($sourceParamList, $variableName);
			AddSourceParamInfoData($variableName, "", false, false, false, "", false, $sourceParamInfoList);
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$dbdatabype = "";
			$nullable = false;
			$datatypeInProgLang = GetDataTypeInProgLangBasedOnSQLSelect($project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $targetTableColumnName, $DAdbtablecolumns, $dbdatabype, $nullable);
			$datatype = GetProgramLangBasedOnEnum($ProjectSourceOutput, $datatypeInProgLang, $dafunc->GetInsertUpdateDeleteTargetTable(), $targetTableColumnName);
			$isEnum = CheckIfEnumDataType($datatypeInProgLang);
			$basedataclassname_for_enum = "";
			if ($isEnum) {
				$basedataclassname_for_enum = CreateDataClassName($dafunc->GetInsertUpdateDeleteTargetTable());
			}
			AddToUnduplicatedList($sourceParamList, $datatype . " " . $variableName);
			AddSourceParamInfoData($variableName, $datatype, false, $isEnum, false, $basedataclassname_for_enum, false, $sourceParamInfoList);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
}

?>
