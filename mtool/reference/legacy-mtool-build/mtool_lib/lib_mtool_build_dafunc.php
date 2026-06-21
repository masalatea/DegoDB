<?PHP

function CreateFunctionParameterBaseName(&$existingParamHT, $basename, $action)
{
	$param = "";
	$param_candidate = "param_" . $basename . "_" . $action;
	
	for($try_num = 1 ; $try_num <= 100 ; $try_num++) {
		$param = "";
		if ($try_num == 1) {
			$param = $param_candidate;				// 1は何もつけない
		} else {
			$param = $param_candidate . $try_num;	// 2以降は数字を付ける
		}
		if (array_key_exists($param, $existingParamHT)) {
			continue;
		}
		break;
	}
	$existingParamHT[$param] = true;
	return $param;
}

function GetdafuncSource($BuildToken, $project, $ProjectSourceOutput, $da)
{
	$automaticallyOutputSource = "";
	
	$DAdafunc = new dafuncDBAccess();
	$dafunclist = $DAdafunc->GetdafuncList($project->PID, $da->PID);
	AddMtoolDebugBuildMessage("  => Get dafunc");
	for($k = 0 ; $k < count($dafunclist); $k++) {
		$dafunc = $dafunclist[$k];
		AddMtoolDebugBuildMessage("  => Action Type: " . GetDAFuncActionTypeCaption($dafunc->ActionType) . " Name:" . $dafunc->name);
		AddMtoolDebugBuildMessage("  => Project PID:" . $dafunc->ProjectPID . "  daPID:" . $dafunc->daPID . " PID:" . $dafunc->PID);
		
		$sourceParamInfoList = NULL;
		$thisSource = GetdafuncSourceSub($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, $sourceParamInfoList, false);
		
		$automaticallyOutputSource .= $thisSource;
	}
	return $automaticallyOutputSource;
}

function GetdafuncSourceSub($BuildToken, $project, $ProjectSourceOutput, $da, $dafunc, &$sourceParamInfoList, $ParamInfoOnlyMode)
{
	$sourceParamInfoList = array();
	
	$existingParamHT = array();
	$ExampleCodeForCreatingObjectInitializeList = array();
	$ExampleCodeForCreatingObjectList = array();
	$ParameterListStringForProxyBasedOnDAList = array();
	$ParamNameForFile = "";
	
	$sourceFunctionName = "";
	$dafunc_template = "";
	if (!$ParamInfoOnlyMode) {
		$sourceFunctionName = GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
		$dafunc_template_file = "";
		switch($dafunc->ActionType)
		{
			case dafuncActionTypeEnum::$SELECTSINGLE:
				$dafunc_template_file = "DBACCESSCLASS-FUNCTION-SELECT-SINGLE";
				break;
			case dafuncActionTypeEnum::$SELECTLIST:
				$dafunc_template_file = "DBACCESSCLASS-FUNCTION-SELECT-LIST";
				break;
			case dafuncActionTypeEnum::$INSERT:
				$dafunc_template_file = "DBACCESSCLASS-FUNCTION-INSERT";	// Default
				
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						if ($dafunc->IsBlobTarget == 1) {
							$dafunc_template_file = "DBACCESSCLASS-FUNCTION-INSERT-BLOB";
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
				break;
			case dafuncActionTypeEnum::$UPDATE:
				$dafunc_template_file = "DBACCESSCLASS-FUNCTION-UPDATE";
				
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						if ($dafunc->IsBlobTarget == 1) {
							$dafunc_template_file = "DBACCESSCLASS-FUNCTION-UPDATE-BLOB";
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
				break;
			case dafuncActionTypeEnum::$DELETE:
				$dafunc_template_file = "DBACCESSCLASS-FUNCTION-DELETE";
				break;
			default:
				AddMtoolErrorBuildMessage("INTERNAL ERROR! Unknown Action Type: " . $dafunc->ActionType);
				return;
		}
		$dafunc_template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $dafunc_template_file);
		$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
				array("KEY"=>"__FUNCTION_NAME__", "VALUE"=>$sourceFunctionName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
			));
	}
	
	$classObjectBaseName = "";
	$classObjectName = "";
	$dataClassNameOfClassObject = "";
	$sourceParamList = array();
	$setParameter = "";
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			break;
		case dafuncActionTypeEnum::$INSERT:
		case dafuncActionTypeEnum::$UPDATE:
		case dafuncActionTypeEnum::$DELETE:
			if ($dafunc->IsInsertUpdateDeleteTargetClassObject() ||
			    $dafunc->IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()
				)
			{
				$classObjectBaseName = $dafunc->GetInsertUpdateDeleteTargetTable() . "Obj";
				
				$DAdataclass = new dataclassDBAccess();
				$dataClassNameOfClassObject = "ERROR-CorrespondingClassIsNotExistForTheTable" . $dafunc->GetInsertUpdateDeleteTargetTable();
				$correspondingDataclass = $DAdataclass->GetdataclassByName($project->PID, $dafunc->GetInsertUpdateDeleteTargetTable());
				if ($correspondingDataclass != NULL) {
					$dataClassNameOfClassObject = CreateDataClassName($dafunc->GetInsertUpdateDeleteTargetTable());
				}
				
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
						$classObjectName = "\$" . $classObjectBaseName;
						AddToUnduplicatedList($sourceParamList, $classObjectName);
						AddSourceParamInfoData($classObjectName, $dataClassNameOfClassObject, true, false, false, "", false, $sourceParamInfoList);
						AddToUnduplicatedList($ExampleCodeForCreatingObjectInitializeList, $classObjectName . " = new " . CreateDataClassName($dafunc->GetInsertUpdateDeleteTargetTable()) . "();");
						break;
					case ProjectSourceOutputProgramLanguageEnum::$CS:
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						$classObjectName = $classObjectBaseName;
						AddToUnduplicatedList($sourceParamList, $dataClassNameOfClassObject . " " . $classObjectName);
						AddSourceParamInfoData($classObjectName, $dataClassNameOfClassObject, true, false, false, "", false, $sourceParamInfoList);
						AddToUnduplicatedList($ExampleCodeForCreatingObjectInitializeList, CreateDataClassName($dafunc->GetInsertUpdateDeleteTargetTable()) . " " . $classObjectName . " = new " . CreateDataClassName($dafunc->GetInsertUpdateDeleteTargetTable()) . "();");
						break;
					default:
						AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
						break;
				}
			}
			break;
	}
	
	$sourceDataClass = "";
	
	$sourceSelectFromList = array();
	$sourceSelectWhereList = array();
	$sourceSelectJoinList = array();
	$sourceSelectJoinONTargetTableHT = array();
	
	$DAdbtable = new dbtableDBAccess();
	$DAdbtablecolumns = new dbtablecolumnsDBAccess();
	$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
	
	$tablelist = $DAdbtable->GetdbtableList($project->PID);
	
	InitializeParameterNameFromProjectSetting();
	
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			
			if (!$ParamInfoOnlyMode) {
				$sourceDataClass = CreateDataClassNameFromDAFunc($dafunc);
				
				$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
						array("KEY"=>"__DATA_CLASS_NAME__",      "VALUE"=>$sourceDataClass,          "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__LIST_DATA_CLASS_NAME__", "VALUE"=>$sourceDataClass . "List", "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					));
				
				if ($dafunc->ActionType == dafuncActionTypeEnum::$SELECTLIST) {
					$sourceSelectByDistinct = "";
					if ($dafunc->SelectByDistinct == 1) {
						$sourceSelectByDistinct = "distinct ";
					}
					$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
							array("KEY"=>"__SELECT_BY_DISTINCT__", "VALUE"=>$sourceSelectByDistinct, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
						));
				}
				
				$sourceSelectColumns = "";
				$sourceStoreDataCode = "";
				
				$dafuncselecttargetfieldslist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($project->PID, $da->PID, $dafunc->PID);
				AddMtoolDebugBuildMessage("   => Get dafunc Target Fields");
				
				for($l = 0 ; $l < count($dafuncselecttargetfieldslist); $l++) {
					$dafuncselecttargetfields = $dafuncselecttargetfieldslist[$l];
					AddMtoolDebugBuildMessage("    => Project PID:" . $dafuncselecttargetfields->ProjectPID . "  daPID:" . $dafuncselecttargetfields->daPID . " dafuncPID:" . $dafuncselecttargetfields->dafuncPID . " PID:" . $dafuncselecttargetfields->PID . " targetTableName:" . $dafuncselecttargetfields->targetTableName . " targetTableColumnName:" . $dafuncselecttargetfields->targetTableColumnName . " storeClassFieldName:" . $dafuncselecttargetfields->storeClassFieldName);
					
					$thisTargetTableNameConsideringAlias = $dafuncselecttargetfields->targetTableName;
					if (trim($dafuncselecttargetfields->targetTableAliasName) != "") {
						$thisTargetTableNameConsideringAlias = $dafuncselecttargetfields->targetTableAliasName;
					}
					
					if ($sourceSelectColumns != "") {
						$sourceSelectColumns .= ", ";
					}
					$sourceSelectColumns .= $dafuncselecttargetfields->targetTableColumnPrefix . $thisTargetTableNameConsideringAlias . "." . $dafuncselecttargetfields->targetTableColumnName . $dafuncselecttargetfields->targetTableColumnSuffix;
					
					$template_select = "";
					if (!$ParamInfoOnlyMode) {
						$template_select = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-FUNCTION-SELECT-DATASTORE");
						$template_select = ReplaceOutputSourceInfoByKeyValue($template_select, array(
								array("KEY"=>"__CLASS_PROPERTY_NAME__", "VALUE"=>$dafuncselecttargetfields->storeClassFieldName, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
								array("KEY"=>"__INDEX__",               "VALUE"=>$l,                                             "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
							));
					}
					
					switch ($ProjectSourceOutput->ProgramLanguage) {
						case ProjectSourceOutputProgramLanguageEnum::$PHP:
							break;
						case ProjectSourceOutputProgramLanguageEnum::$CS:
						case ProjectSourceOutputProgramLanguageEnum::$JAVA:
						case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
						case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							$template_select = SetEnumTranslateFrunctionFromStringToEnum($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafuncselecttargetfields->targetTableName, $dafuncselecttargetfields->targetTableColumnName, $DAdbtablecolumns, $template_select);
							break;
						default:
							AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
							break;
					}
					
					$sourceStoreDataCode .= $template_select;
					
					// $INDENT = "			";		// Indent is fixed for the time being.
					// $sourceStoreDataCode .= $INDENT . "\$thisresult->" .  . " = \$thisline[" . $l . "];\n";
					
					// AddToUnduplicatedList($sourceSelectFromList, $dafuncselecttargetfields->targetTableName);
					AddToSelectWhereInfoUnduplicatedList($sourceSelectFromList, $dafuncselecttargetfields->targetTableName, $dafuncselecttargetfields->targetTableAliasName);
				}
				$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
						array("KEY"=>"__SELECT_COLUMNS__",        "VALUE"=>$sourceSelectColumns, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
						array("KEY"=>"__STORE_DATA_CODE__[ \t]*", "VALUE"=>$sourceStoreDataCode, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>true)
					));
			}
			break;
			
		case dafuncActionTypeEnum::$INSERT:
			
			$sourceInsertTargetColumns = "";
			$sourceInsertValueList = array();
			
			$DAdafuncinserttargetfields = new dafuncinserttargetfieldsDBAccess();
			$dafuncinserttargetfieldslist = $DAdafuncinserttargetfields->GetdafuncinserttargetfieldsList($project->PID, $da->PID, $dafunc->PID);
			AddMtoolDebugBuildMessage("   => Get dafunc insert target fields");
			for($l = 0 ; $l < count($dafuncinserttargetfieldslist); $l++) {
				$dafuncinserttargetfields = $dafuncinserttargetfieldslist[$l];
				AddMtoolDebugBuildMessage("    => Project PID:" . $dafuncinserttargetfields->ProjectPID . "  daPID:" . $dafuncinserttargetfields->daPID . " dafuncPID:" . $dafuncinserttargetfields->dafuncPID . " PID:" . $dafuncinserttargetfields->PID . " targetTableColumnName:" . $dafuncinserttargetfields->targetTableColumnName . " ParameterType:" . $dafuncinserttargetfields->ParameterType . " FixedParameter:" . $dafuncinserttargetfields->FixedParameter);
				
				$parameterBaseName = CreateFunctionParameterBaseName($existingParamHT, $dafunc->GetInsertUpdateDeleteTargetTable() . "_" . $dafuncinserttargetfields->targetTableColumnName, "insert");
				$parameterName = GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName);
				$variableName = GetVariableNameFromProjectSetting($project, $ProjectSourceOutput, $dafunc, $classObjectName, $dafuncinserttargetfields->targetTableColumnName, $parameterBaseName);
				
				if ($dafuncinserttargetfields->IsFileDataType()) {
					$ParamNameForFile = $variableName;
				}
				
				if ($dafuncinserttargetfields->ParameterType == dafuncinserttargetfieldsParameterTypeEnum::$ARGUMENT) {
					
					if ($dafunc->IsInsertUpdateDeleteTargetClassObject()) {
						// AddToUnduplicatedList($ExampleCodeForCreatingObjectList, $variableName);
						AddUnduplicateCodeForDAFunc($ExampleCodeForCreatingObjectList, $variableName, true, false, false);
						AddUnduplicateCodeForDAFunc($ParameterListStringForProxyBasedOnDAList, $dafuncinserttargetfields->targetTableColumnName, true, false, false);
						
					} else if ($dafunc->IsInsertUpdateDeleteTargetVal()) {
						AddSourceParamListBasedOnLanguage($project, $ProjectSourceOutput, $tablelist, $variableName, $sourceParamList, $sourceParamInfoList, $dafunc, $dafuncinserttargetfields->targetTableColumnName, $DAdbtablecolumns);
						
					} else if ($dafunc->IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()) {
						AddMtoolErrorBuildMessage("This Parameter Type is not supported for Insert:" . $dafunc->InsertUpdateDeleteParamType);
						
					} else {
						// Unknown
						AddMtoolErrorBuildMessage("Unknown Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
					}
					
					if (!$ParamInfoOnlyMode) {
						$thisEquality = "";
						InitializeArgumentParameterStringAndEquality($BuildToken, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $variableName, $dafuncinserttargetfields->ParameterDataType, "");
						array_push($sourceInsertValueList, $thisEquality);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $variableName);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncinserttargetfields->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								printAddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
				} else if ($dafuncinserttargetfields->ParameterType == dafuncinserttargetfieldsParameterTypeEnum::$FIXED) {
					
					if (!$ParamInfoOnlyMode) {
						$FixedParameterString = "";
						$thisEquality = "";
						InitializeFixedParameterStringAndEquality($BuildToken, $FixedParameterString, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $dafuncinserttargetfields->FixedParameter, $dafuncinserttargetfields->ParameterDataType, "", $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncinserttargetfields->targetTableColumnName, $DAdbtablecolumns);
						array_push($sourceInsertValueList, $thisEquality);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $FixedParameterString);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncinserttargetfields->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
				} else {
					AddMtoolErrorBuildMessage("Error! Unknown Parameter Type: " . $dafuncinserttargetfields->ParameterType);
				}
				
				if ($sourceInsertTargetColumns != "") {
					$sourceInsertTargetColumns .= ", ";
				}
				$sourceInsertTargetColumns .= $dafuncinserttargetfields->targetTableColumnName;
			}
			
			$sourceInsertValues = CommaConnectedStringsFromArray($sourceInsertValueList);
			
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__INSERT_TARGET_TABLE__",   "VALUE"=>$dafunc->GetInsertUpdateDeleteTargetTable(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__INSERT_TARGET_COLUMNS__", "VALUE"=>$sourceInsertTargetColumns,                  "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__INSERT_VALUES__",         "VALUE"=>$sourceInsertValues,                         "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			
			break;
			
		case dafuncActionTypeEnum::$UPDATE:
			
			$sourceUpdateValueList = array();
			
			$DAdafuncupdatetargetfields = new dafuncupdatetargetfieldsDBAccess();
			$dafuncupdatetargetfieldslist = $DAdafuncupdatetargetfields->GetdafuncupdatetargetfieldsList($project->PID, $da->PID, $dafunc->PID);
			AddMtoolDebugBuildMessage("   => Get dafunc update target fields");
			for($l = 0 ; $l < count($dafuncupdatetargetfieldslist); $l++) {
				$dafuncupdatetargetfields = $dafuncupdatetargetfieldslist[$l];
				AddMtoolDebugBuildMessage("    => Project PID:" . $dafuncupdatetargetfields->ProjectPID . "  daPID:" . $dafuncupdatetargetfields->daPID . " dafuncPID:" . $dafuncupdatetargetfields->dafuncPID . " PID:" . $dafuncupdatetargetfields->PID . " targetTableColumnName:" . $dafuncupdatetargetfields->targetTableColumnName . " ParameterType:" . $dafuncupdatetargetfields->ParameterType . " FixedParameter:" . $dafuncupdatetargetfields->FixedParameter);
				
				$thisbasewhere = $dafuncupdatetargetfields->targetTableColumnName . " = ";
				$parameterBaseName = CreateFunctionParameterBaseName($existingParamHT, $dafunc->GetInsertUpdateDeleteTargetTable() . "_" . $dafuncupdatetargetfields->targetTableColumnName, "update");
				$parameterName = GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName);
				$variableName = GetVariableNameFromProjectSettingForUpdateForSetTarget($project, $ProjectSourceOutput, $dafunc, $classObjectName, $dafuncupdatetargetfields->targetTableColumnName, $parameterBaseName);
				
				if ($dafuncupdatetargetfields->IsFileDataType()) {
					$ParamNameForFile = $variableName;
				}
				
				if ($dafuncupdatetargetfields->ParameterType == dafuncupdatetargetfieldsParameterTypeEnum::$ARGUMENT) {
					
					if ($dafunc->IsInsertUpdateDeleteTargetClassObject() ||
					    $dafunc->IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate()) {
						// AddToUnduplicatedList($ExampleCodeForCreatingObjectList, $variableName);
						AddUnduplicateCodeForDAFunc($ExampleCodeForCreatingObjectList, $variableName, false, true, false);
						AddUnduplicateCodeForDAFunc($ParameterListStringForProxyBasedOnDAList, $dafuncupdatetargetfields->targetTableColumnName, false, true, false);
						
					} else if ($dafunc->IsInsertUpdateDeleteTargetVal()) {
						AddSourceParamListBasedOnLanguage($project, $ProjectSourceOutput, $tablelist, $variableName, $sourceParamList, $sourceParamInfoList, $dafunc, $dafuncupdatetargetfields->targetTableColumnName, $DAdbtablecolumns);
						
					} else {
						// Unknown
						AddMtoolErrorBuildMessage("Unknown Insert Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
					}
					
					if (!$ParamInfoOnlyMode) {
						$thisEquality = "";
						InitializeArgumentParameterStringAndEquality($BuildToken, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $variableName, $dafuncupdatetargetfields->ParameterDataType, $thisbasewhere);
						AddToUnduplicatedList($sourceUpdateValueList, $thisEquality);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $variableName);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncupdatetargetfields->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
				} else if ($dafuncupdatetargetfields->ParameterType == dafuncupdatetargetfieldsParameterTypeEnum::$FIXED) {
					
					if (!$ParamInfoOnlyMode) {
						$FixedParameterString = "";
						$thisEquality = "";
						InitializeFixedParameterStringAndEquality($BuildToken, $FixedParameterString, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $dafuncupdatetargetfields->FixedParameter, $dafuncupdatetargetfields->ParameterDataType, $thisbasewhere, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncupdatetargetfields->targetTableColumnName, $DAdbtablecolumns);
						AddToUnduplicatedList($sourceUpdateValueList, $thisEquality);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $FixedParameterString);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncupdatetargetfields->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
				} else {
					AddMtoolErrorBuildMessage("Error! Unknown Parameter Type: " . $dafuncupdatetargetfields->ParameterType);
				}
			}
			
			$sourceUpdateValues = CommaConnectedStringsFromArray($sourceUpdateValueList);
			
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__UPDATE_TARGET_TABLE__", "VALUE"=>$dafunc->GetInsertUpdateDeleteTargetTable(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
					array("KEY"=>"__SET__",                 "VALUE"=>$sourceUpdateValues,                         "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			
			break;
			
		case dafuncActionTypeEnum::$DELETE:
			
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__DELETE_TARGET_TABLE__", "VALUE"=>$dafunc->GetInsertUpdateDeleteTargetTable(), "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			
			break;
	}
	
	$DAdbtable = new dbtableDBAccess();
	$DAdbtablecolumns = new dbtablecolumnsDBAccess();
	$tablelist = NULL;
	switch ($ProjectSourceOutput->ProgramLanguage) {
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			$tablelist = $DAdbtable->GetdbtableList($project->PID);
			break;
		default:
			AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
			break;
	}
	
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			
			$dafuncselecttargetfieldslist = NULL;
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
					break;
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					$dafuncselecttargetfieldslist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($project->PID, $da->PID, $dafunc->PID);
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					break;
			}
			
			$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
			$dafuncselectwherelist = $DAdafuncselectwhere->GetdafuncselectwhereList($project->PID, $da->PID, $dafunc->PID);
			AddMtoolDebugBuildMessage("   => Get dafunc Where for select");
			for($l = 0 ; $l < count($dafuncselectwherelist); $l++) {
				$dafuncselectwhere = $dafuncselectwherelist[$l];
				AddMtoolDebugBuildMessage("    => Project PID:" . $dafuncselectwhere->ProjectPID . "  daPID:" . $dafuncselectwhere->daPID . " dafuncPID:" . $dafuncselectwhere->dafuncPID . " PID:" . $dafuncselectwhere->PID . " targetTableName:" . $dafuncselectwhere->targetTableName . " targetTableColumnName:" . $dafuncselectwhere->targetTableColumnName . " ParameterType:" . $dafuncselectwhere->ParameterType . " FixedParameter:" . $dafuncselectwhere->FixedParameter . " AnotherTableName:" . $dafuncselectwhere->AnotherTableName . " AnotherFieldName:" . $dafuncselectwhere->AnotherFieldName);
				
				if (trim($dafuncselectwhere->targetTableName) != "") {
					// AddToUnduplicatedList($sourceSelectFromList, trim($dafuncselectwhere->targetTableName));
					AddToSelectWhereInfoUnduplicatedList($sourceSelectFromList, trim($dafuncselectwhere->targetTableName), trim($dafuncselectwhere->targetTableAliasName));
				}
				if (trim($dafuncselectwhere->AnotherTableName) != "") {
					// AddToUnduplicatedList($sourceSelectFromList, trim($dafuncselectwhere->AnotherTableName));
					AddToSelectWhereInfoUnduplicatedList($sourceSelectFromList, trim($dafuncselectwhere->AnotherTableName), trim($dafuncselectwhere->AnotherTableAliasName));
				}
				
				$thisTargetTableNameConsideringAlias = $dafuncselectwhere->targetTableName;
				if (trim($dafuncselectwhere->targetTableAliasName) != "") {
					$thisTargetTableNameConsideringAlias = $dafuncselectwhere->targetTableAliasName;
				}
				
				$thisbasewhere = $thisTargetTableNameConsideringAlias . "." . $dafuncselectwhere->targetTableColumnName . " " . $dafuncselectwhere->GetRelationalOperatorSQL() . " ";
				$parameterBaseName = CreateFunctionParameterBaseName($existingParamHT, $thisTargetTableNameConsideringAlias . "_" . $dafuncselectwhere->targetTableColumnName, "where");
				$parameterName = GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName);
				$variableName = GetVariableNameFromProjectSetting($project, $ProjectSourceOutput, $dafunc, $classObjectName, $dafuncselectwhere->targetTableColumnName, $parameterBaseName);
				
				if ($dafuncselectwhere->ParameterType == dafuncselectwhereParameterTypeEnum::$ARGUMENT) {
					
					if (!$ParamInfoOnlyMode) {
						$thisEquality = "";
						InitializeArgumentParameterStringAndEquality($BuildToken, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $variableName, $dafuncselectwhere->ParameterDataType, $thisbasewhere);
						
						PushCorrespondingDataObject($dafuncselectwhere, $thisEquality,
							$sourceSelectWhereList, $sourceSelectJoinList, $sourceSelectJoinONTargetTableHT);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $variableName);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafuncselectwhere->targetTableName, $dafuncselectwhere->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					UpdateMtoolTemplateDBAccessClassEscapeStringForSourceParam($project, $ProjectSourceOutput, $variableName, $sourceParamList, $sourceParamInfoList, $dafuncselecttargetfieldslist, $dafuncselectwhere, $tablelist, $DAdbtablecolumns);
					
				} else if ($dafuncselectwhere->ParameterType == dafuncselectwhereParameterTypeEnum::$FIXED) {
					
					if (!$ParamInfoOnlyMode) {
						$FixedParameterString = "";
						$thisEquality = "";
						InitializeFixedParameterStringAndEquality($BuildToken, $FixedParameterString, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $dafuncselectwhere->FixedParameter, $dafuncselectwhere->ParameterDataType, $thisbasewhere, $tablelist, $dafuncselectwhere->targetTableName, $dafuncselectwhere->targetTableColumnName, $DAdbtablecolumns);
						
						PushCorrespondingDataObject($dafuncselectwhere, $thisEquality,
							$sourceSelectWhereList, $sourceSelectJoinList, $sourceSelectJoinONTargetTableHT);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $FixedParameterString);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafuncselectwhere->targetTableName, $dafuncselectwhere->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
				} else if ($dafuncselectwhere->ParameterType == dafuncselectwhereParameterTypeEnum::$ANOTHERFIELD) {
					if (!$ParamInfoOnlyMode) {
						$thisTargetTableNameConsideringAlias = $dafuncselectwhere->AnotherTableName;
						if (trim($dafuncselectwhere->AnotherTableAliasName) != "") {
							$thisTargetTableNameConsideringAlias = $dafuncselectwhere->AnotherTableAliasName;
						}
						$thisEquality = $thisbasewhere . $thisTargetTableNameConsideringAlias . "." . $dafuncselectwhere->AnotherFieldName;
						PushCorrespondingDataObject($dafuncselectwhere, $thisEquality,
							$sourceSelectWhereList, $sourceSelectJoinList, $sourceSelectJoinONTargetTableHT);
					}
				} else {
					AddMtoolErrorBuildMessage("Error! Unknown Parameter Type: " . $dafuncselectwhere->ParameterType);
				}
			}
			
			break;
			
		case dafuncActionTypeEnum::$UPDATE:
		case dafuncActionTypeEnum::$DELETE:
			
			$DAdafuncupdatedeletewhere = new dafuncupdatedeletewhereDBAccess();
			$dafuncupdatedeletewherelist = $DAdafuncupdatedeletewhere->GetdafuncupdatedeletewhereList($project->PID, $da->PID, $dafunc->PID);
			AddMtoolDebugBuildMessage("   => Get dafunc Where for update/delete");
			for($l = 0 ; $l < count($dafuncupdatedeletewherelist); $l++) {
				$dafuncupdatedeletewhere = $dafuncupdatedeletewherelist[$l];
				AddMtoolDebugBuildMessage("    => Project PID:" . $dafuncupdatedeletewhere->ProjectPID . "  daPID:" . $dafuncupdatedeletewhere->daPID . " dafuncPID:" . $dafuncupdatedeletewhere->dafuncPID . " PID:" . $dafuncupdatedeletewhere->PID . " targetTableColumnName:" . $dafuncupdatedeletewhere->targetTableColumnName . " ParameterType:" . $dafuncupdatedeletewhere->ParameterType . " FixedParameter:" . $dafuncupdatedeletewhere->FixedParameter);
				
				$thisbasewhere = $dafunc->GetInsertUpdateDeleteTargetTable() . "." . $dafuncupdatedeletewhere->targetTableColumnName . " " . $dafuncupdatedeletewhere->GetRelationalOperatorSQL() . " ";
				$parameterBaseName = CreateFunctionParameterBaseName($existingParamHT, $dafunc->GetInsertUpdateDeleteTargetTable() . "_" . $dafuncupdatedeletewhere->targetTableColumnName, "where");
				$parameterName = GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName);
				$variableName = "";
				switch($dafunc->ActionType)
				{
					case dafuncActionTypeEnum::$SELECTSINGLE:
					case dafuncActionTypeEnum::$SELECTLIST:
					case dafuncActionTypeEnum::$INSERT:
						AddMtoolErrorBuildMessage("Internal Error! Not supported Action Type: " . $dafunc->ActionType);
						break;
					case dafuncActionTypeEnum::$UPDATE:
						$variableName = GetVariableNameFromProjectSettingForUpdateForWhereTarget($project, $ProjectSourceOutput, $dafunc, $classObjectName, $dafuncupdatedeletewhere->targetTableColumnName, $parameterBaseName);
						break;
					case dafuncActionTypeEnum::$DELETE:
						$variableName = GetVariableNameFromProjectSetting($project, $ProjectSourceOutput, $dafunc, $classObjectName, $dafuncupdatedeletewhere->targetTableColumnName, $parameterBaseName);
						break;
				}
				
				
				if ($dafuncupdatedeletewhere->ParameterType == dafuncupdatedeletewhereParameterTypeEnum::$ARGUMENT) {
					
					if (!$ParamInfoOnlyMode) {
						$thisEquality = "";
						InitializeArgumentParameterStringAndEquality($BuildToken, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $variableName, $dafuncupdatedeletewhere->ParameterDataType, $thisbasewhere);
						
						$thisWhereData = new BuildSelectWhereData();
						$thisWhereData->Equality = $thisEquality;
						$thisWhereData->ORGroup = $dafuncupdatedeletewhere->ORGroup;
						array_push($sourceSelectWhereList, $thisWhereData);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $variableName);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncupdatedeletewhere->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
					if ($dafunc->IsInsertUpdateDeleteTargetClassObject()) {
						// No need to add into $sourceParamList
						// AddToUnduplicatedList($ExampleCodeForCreatingObjectList, $variableName);
						AddUnduplicateCodeForDAFunc($ExampleCodeForCreatingObjectList, $variableName, false, false, true);
						AddUnduplicateCodeForDAFunc($ParameterListStringForProxyBasedOnDAList, $dafuncupdatedeletewhere->targetTableColumnName, false, false, true);
						
					} else if ($dafunc->IsInsertUpdateDeleteTargetVal() ||
					           $dafunc->IsInsertUpdateDeleteTargetSetByClassObjectAndWhereByValForUpdate())
					{
						AddSourceParamListBasedOnLanguage($project, $ProjectSourceOutput, $tablelist, $variableName, $sourceParamList, $sourceParamInfoList, $dafunc, $dafuncupdatedeletewhere->targetTableColumnName, $DAdbtablecolumns);
					} else {
						// Unknown
						AddMtoolErrorBuildMessage("Unknown Parameter Type:" . $dafunc->InsertUpdateDeleteParamType);
					}
					
				} else if ($dafuncupdatedeletewhere->ParameterType == dafuncupdatedeletewhereParameterTypeEnum::$FIXED) {
					
					if (!$ParamInfoOnlyMode) {
						$FixedParameterString = "";
						$thisEquality = "";
						InitializeFixedParameterStringAndEquality($BuildToken, $FixedParameterString, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $dafuncupdatedeletewhere->FixedParameter, $dafuncupdatedeletewhere->ParameterDataType, $thisbasewhere, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncupdatedeletewhere->targetTableColumnName, $DAdbtablecolumns);
						
						$thisWhereData = new BuildSelectWhereData();
						$thisWhereData->Equality = $thisEquality;
						$thisWhereData->ORGroup = $dafuncupdatedeletewhere->ORGroup;
						array_push($sourceSelectWhereList, $thisWhereData);
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $FixedParameterString);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, $dafunc->GetInsertUpdateDeleteTargetTable(), $dafuncupdatedeletewhere->targetTableColumnName, $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					
				} else {
					AddMtoolErrorBuildMessage("Error! Unknown Parameter Type: " . $dafuncupdatedeletewhere->ParameterType);
				}
			}
			break;
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			
			$sourceSelectFrom = "";
			for ($i = 0 ; $i < count($sourceSelectFromList) ; $i++) {
				$thisTableName = $sourceSelectFromList[$i]->TableName;
				$thisAliasName = $sourceSelectFromList[$i]->AliasName;
				
				$existInJoinONTargetTable = array_key_exists(CreateSelectJoinONTargetTableKey($thisTableName, $thisAliasName), $sourceSelectJoinONTargetTableHT);
				if ($existInJoinONTargetTable) {
					// Join Target. Skip.
					
				} else {
					if ($sourceSelectFrom != "") {
						$sourceSelectFrom .= " join ";
					}
					$sourceSelectFrom .= $thisTableName;
					if (trim($thisAliasName) != "") {
						$sourceSelectFrom .= " as " . trim($thisAliasName);
					}
					
					// Added Join Clause if there is a match
					$sourceSelectFrom .= GetSelectJoinClause($thisTableName, $thisAliasName, $sourceSelectJoinList, $dafunc->ORGroupType);
				}
			}
			
			// Output Isolated Join Clause
			// このパターンは実際にはあまりないと思うが一応出力しておく
			$RestTableList = array();
			for ($i = 0 ; $i < count($sourceSelectJoinList) ; $i++) {
				if (isset($sourceSelectJoinList[$i])) {
					$sourceSelectJoin = $sourceSelectJoinList[$i];
					
					AddToSelectWhereInfoUnduplicatedList($RestTableList, $sourceSelectJoin->JoinTargetTableName, $sourceSelectJoin->JoinTargetTableAliasName);
				}
			}
			for ($i = 0 ; $i < count($RestTableList); $i++) {
				$thisRestTableName      = $RestTableList[$i]->TableName;
				$thisRestTableAliasName = $RestTableList[$i]->AliasName;
				$joinClause = GetSelectJoinClause($thisRestTableName, $thisRestTableAliasName, $sourceSelectJoinList, $dafunc->ORGroupType);
				if ($joinClause != "") {
					$existInJoinONTargetTable = array_key_exists(CreateSelectJoinONTargetTableKey($thisRestTableName, $thisRestTableAliasName), $sourceSelectJoinONTargetTableHT);
					if ($existInJoinONTargetTable) {
						// Join Target. Just connect
						
					} else {
						if ($sourceSelectFrom != "") {
							$sourceSelectFrom .= " join ";
						}
						$sourceSelectFrom .= $thisRestTableName;	// これがないと受け口になるテーブルが存在しないので追加
						if (trim($thisRestTableAliasName) != "") {
							$sourceSelectFrom .= " as " . $thisRestTableAliasName;
						}
					}
					$sourceSelectFrom .= $joinClause;
				}
			}
			
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__SELECT_FROM__", "VALUE"=>$sourceSelectFrom, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			break;
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
		case dafuncActionTypeEnum::$UPDATE:
		case dafuncActionTypeEnum::$DELETE:
			
			$sourceSelectWhere = "";
			for ($i = 0 ; $i < count($sourceSelectWhereList) ; $i++) {
				$thisWhereObj = $sourceSelectWhereList[$i];
				if ($thisWhereObj->ORGroup == "") {
					if ($sourceSelectWhere == "") {
						$sourceSelectWhere = " where ";
					} else {
						$sourceSelectWhere .= " and ";
					}
					$sourceSelectWhere .= $thisWhereObj->Equality;
				}
			}
			
			$ORGroupList = array();
			for ($i = 0 ; $i < count($sourceSelectWhereList) ; $i++) {
				$thisWhereObj = $sourceSelectWhereList[$i];
				
				if ($thisWhereObj->ORGroup != "") {
					AddToUnduplicatedList($ORGroupList, $thisWhereObj->ORGroup);
				}
			}
			
			$need_to_close_AND_OR_AND_kakko = false;
			for ($i = 0 ; $i < count($ORGroupList) ; $i++) {
				$ORGroup = $ORGroupList[$i];
				
				$ThisWhereStr = "";
				for ($j = 0 ; $j < count($sourceSelectWhereList) ; $j++) {
					$thisWhereObj = $sourceSelectWhereList[$j];
					if ($thisWhereObj->ORGroup == $ORGroup) {
						if ($ThisWhereStr == "") {
							$ThisWhereStr = "(";
						} else {
							switch(GetWorkingORGroupType($dafunc->ORGroupType))
							{
								case dafuncORGroupTypeEnum::$DEFAULT:
									PrintOutMtoolBuildResultMessage();
									die("Internal Error! Something Wrong. GetWorkingORGroupType must return valid value.");
									
								case dafuncORGroupTypeEnum::$ORANDOR:
									$ThisWhereStr .= " or ";
									break;
								case dafuncORGroupTypeEnum::$ANDORAND:
									$ThisWhereStr .= " and ";
									break;
								default:
									PrintOutMtoolBuildResultMessage();
									die("Internal Error! Something Wrong. Unknown OR Group Type");
							}
						}
						$ThisWhereStr .= $thisWhereObj->Equality;
					}
				}
				if ($ThisWhereStr != "") {
					$ThisWhereStr .= ")";
					
					if ($sourceSelectWhere == "") {
						$sourceSelectWhere = " where ";
					} else {
						switch(GetWorkingORGroupType($dafunc->ORGroupType))
						{
							case dafuncORGroupTypeEnum::$DEFAULT:
								PrintOutMtoolBuildResultMessage();
								die("Internal Error! Something Wrong. GetWorkingORGroupType must return valid value.");
							case dafuncORGroupTypeEnum::$ORANDOR:
								$sourceSelectWhere .= " and ";
								break;
							case dafuncORGroupTypeEnum::$ANDORAND:
								if ($i == 0) {
									$sourceSelectWhere .= " and (";
									$need_to_close_AND_OR_AND_kakko = true;
								} else {
									$sourceSelectWhere .= " or ";
								}
								break;
							default:
								PrintOutMtoolBuildResultMessage();
								die("Internal Error! Something Wrong. Unknown OR Group Type");
						}
					}
					$sourceSelectWhere .= $ThisWhereStr;
				}
			}
			if ($need_to_close_AND_OR_AND_kakko) {
				$sourceSelectWhere .= ")";
			}
			
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__WHERE__", "VALUE"=>$sourceSelectWhere, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			break;
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			
			if (!$ParamInfoOnlyMode) {
				$sourceGroupBy = "";
				$dafuncselecttargetfieldslist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($project->PID, $da->PID, $dafunc->PID);
				AddMtoolDebugBuildMessage("   => Get dafunc Target Fields");
				
				for($l = 0 ; $l < count($dafuncselecttargetfieldslist); $l++) {
					$dafuncselecttargetfields = $dafuncselecttargetfieldslist[$l];
					
					if ($dafuncselecttargetfields->GroupByTarget == "1") {
						
						$thisTargetTableNameConsideringAlias = $dafuncselecttargetfields->targetTableName;
						if (trim($dafuncselecttargetfields->targetTableAliasName) != "") {
							$thisTargetTableNameConsideringAlias = $dafuncselecttargetfields->targetTableAliasName;
						}
						$groupByName = $dafuncselecttargetfields->targetTableColumnPrefix . $thisTargetTableNameConsideringAlias . "." . $dafuncselecttargetfields->targetTableColumnName . $dafuncselecttargetfields->targetTableColumnSuffix;
						// MEMO: MySQL では、GROUP BY 句で式を使用することが許可されているため、エイリアスは必要ありません。
						//       https://dev.mysql.com/doc/refman/5.6/ja/group-by-handling.html
						if ($sourceGroupBy == "") {
							$sourceGroupBy = " Group By ";
						} else {
							$sourceGroupBy .= ", ";
						}
						$sourceGroupBy .= $groupByName;
					}
				}
				$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
						array("KEY"=>"__GROUP_BY__", "VALUE"=>$sourceGroupBy, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
			}
			break;
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTSINGLE:
		case dafuncActionTypeEnum::$SELECTLIST:
			
			if (!$ParamInfoOnlyMode) {
				$sourceHavingList = array();
				
				$DAdafuncselecthaving_leftouterjoin_targetfields = new dafuncselecthaving_leftouterjoin_targetfieldsDBAccess();
				$dafuncselecthavinglist = $DAdafuncselecthaving_leftouterjoin_targetfields->GetdafuncselecthavingList($project->PID, $da->PID, $dafunc->PID); 
				for($i = 0 ; $i < count($dafuncselecthavinglist); $i++) {
					$dafuncselecthaving = $dafuncselecthavinglist[$i];
					$no = ($i + 1);
					
					$thisbasehaving = "";
					$thisLeftEquality = $dafuncselecthaving->LeftTargetPrefix . GetReferencingFieldColumnIfThereis($project->PID, $dafuncselecthaving->LeftTargetFieldPID) . $dafuncselecthaving->LeftTargetSuffix . " " . $dafuncselecthaving->GetRelationalOperatorSQL() . " ";
					
					$parameterBaseName = CreateFunctionParameterBaseName($existingParamHT, "having", $no);
					$parameterName = GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName);
					$variableName = GetVariableNameFromProjectSetting($project, $ProjectSourceOutput, $dafunc, $classObjectName, "", $parameterBaseName);
					$thisRightEquality = "";
					
					if ($dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$ARGUMENT) {
						
						if (!$ParamInfoOnlyMode) {
							InitializeArgumentParameterStringAndEquality($BuildToken, $thisRightEquality, $project, $ProjectSourceOutput, $parameterName, $variableName, $dafuncselecthaving->RightParameterDataType, $thisbasehaving);
							
							switch ($ProjectSourceOutput->ProgramLanguage) {
								case ProjectSourceOutputProgramLanguageEnum::$PHP:
									break;
								case ProjectSourceOutputProgramLanguageEnum::$CS:
									$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $variableName);
									$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, "", "", $DAdbtablecolumns, $template_set);
									$setParameter .= $template_set;
									break;
								case ProjectSourceOutputProgramLanguageEnum::$JAVA:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
								case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
								default:
									AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
									break;
							}
						}
						UpdateMtoolTemplateDBAccessClassEscapeStringForSourceParam($project, $ProjectSourceOutput, $variableName, $sourceParamList, $sourceParamInfoList, NULL, NULL, $tablelist, $DAdbtablecolumns);
						
					} else if ($dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$FIXED) {
						
						if (!$ParamInfoOnlyMode) {
							$FixedParameterString = "";
							InitializeFixedParameterStringAndEquality($BuildToken, $FixedParameterString, $thisRightEquality, $project, $ProjectSourceOutput, $parameterName, $dafuncselecthaving->RightFixedParameter, $dafuncselecthaving->RightParameterDataType, $thisbasehaving, $tablelist, "", "", $DAdbtablecolumns);
							switch ($ProjectSourceOutput->ProgramLanguage) {
								case ProjectSourceOutputProgramLanguageEnum::$PHP:
									break;
								case ProjectSourceOutputProgramLanguageEnum::$CS:
									$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $FixedParameterString);
									$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, "", "", $DAdbtablecolumns, $template_set);
									$setParameter .= $template_set;
									break;
								case ProjectSourceOutputProgramLanguageEnum::$JAVA:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
								case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
								case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
								default:
									AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
									break;
							}
						}
						
					} else if ($dafuncselecthaving->RightParameterType == dafuncselecthavingRightParameterTypeEnum::$FIELD) {
						if (!$ParamInfoOnlyMode) {
							$thisRightEquality = $thisbasehaving . GetReferencingFieldColumnIfThereis($project->PID, $dafuncselecthaving->RightTargetFieldPID);
						}
					} else {
						AddMtoolErrorBuildMessage("Error! Unknown Parameter Type: " . $dafuncselecthaving->RightParameterType);
					}
					
					if ($thisRightEquality != "") {
						array_push($sourceHavingList, $thisLeftEquality . $dafuncselecthaving->RightTargetPrefix . $thisRightEquality . $dafuncselecthaving->RightTargetSuffix);
					}
				}
				
				$sourceHaving = "";
				for($i = 0 ; $i < count($sourceHavingList); $i++) {
					$thisSourceHaving = $sourceHavingList[$i];
					if ($sourceHaving == "") {
						$sourceHaving = " HAVING ";
					} else {
						$sourceHaving .= " and ";
					}
					$sourceHaving .= $thisSourceHaving;
				}
				$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
						array("KEY"=>"__HAVING__", "VALUE"=>$sourceHaving, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
			}
			break;
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTLIST:
			
			$sourceSelectOrderBy = "";
			
			if (trim($dafunc->SortOrderColumns) != "") {
				$sourceSelectOrderBy = " order by " . $dafunc->SortOrderColumns;
			}
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__ORDER_BY__", "VALUE"=>$sourceSelectOrderBy, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			
			break;
	}
	switch($dafunc->ActionType)
	{
		case dafuncActionTypeEnum::$SELECTLIST:
			
			$sourceSelectLimit = "";
			
			$thisbaselimit = " limit ";
			$parameterBaseName = CreateFunctionParameterBaseName($existingParamHT, "limit", "offset_row_count");
			$parameterName = GetParameterNameFromProjectSetting($project, $ProjectSourceOutput, $parameterBaseName);
			$variableName = GetVariableNameFromProjectSetting($project, $ProjectSourceOutput, $dafunc, $classObjectName, "", $parameterBaseName);
			
			switch($dafunc->limitParameterType)
			{
				case dafunclimitParameterTypeEnum::$DEFAULT:
					break;
				case dafunclimitParameterTypeEnum::$ARGUMENT:
					if (!$ParamInfoOnlyMode) {
						$thisEquality = "";
						InitializeArgumentParameterStringAndEquality($BuildToken, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $variableName, ParameterDataTypeCommonEnum::$RAW, $thisbaselimit);
						
						$sourceSelectLimit = $thisEquality;
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $variableName);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, "", "", $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					UpdateMtoolTemplateDBAccessClassEscapeStringForSourceParam($project, $ProjectSourceOutput, $variableName, $sourceParamList, $sourceParamInfoList, NULL, NULL, $tablelist, $DAdbtablecolumns);
					
					break;
				case dafunclimitParameterTypeEnum::$FIXED:
					if (!$ParamInfoOnlyMode) {
						$FixedParameterString = "";
						$thisEquality = "";
						InitializeFixedParameterStringAndEquality($BuildToken, $FixedParameterString, $thisEquality, $project, $ProjectSourceOutput, $parameterName, $dafunc->limitFixedParameter, ParameterDataTypeCommonEnum::$RAW, $thisbaselimit, $tablelist, "", "", "");
						
						$sourceSelectLimit = $thisEquality;
						
						switch ($ProjectSourceOutput->ProgramLanguage) {
							case ProjectSourceOutputProgramLanguageEnum::$PHP:
								break;
							case ProjectSourceOutputProgramLanguageEnum::$CS:
								$template_set = GetDBAccessClassEscapeString($BuildToken, $project, $ProjectSourceOutput, "DBACCESSCLASS-ESCAPE-STRING-SET-VALUE", $parameterName, $FixedParameterString);
								$template_set = SetEnumTranslateFrunctionFromEnumToString($BuildToken, $project, $ProjectSourceOutput, $tablelist, "", "", $DAdbtablecolumns, $template_set);
								$setParameter .= $template_set;
								break;
							case ProjectSourceOutputProgramLanguageEnum::$JAVA:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
							case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
							case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
							default:
								AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
								break;
						}
					}
					break;
			}
			$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
					array("KEY"=>"__SELECT_LIMIT__", "VALUE"=>$sourceSelectLimit, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
				));
			
			break;
	}
	
	$sourceParams = CommaConnectedStringsFromArray($sourceParamList);
	
	$dafunc_template = ReplaceOutputSourceInfoByKeyValue($dafunc_template, array(
			array("KEY"=>"__PARAMS__",         "VALUE"=>$sourceParams,                          "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__SET_PARAMETER__",  "VALUE"=>$setParameter,                          "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__DB_OBJECT__",      "VALUE"=>$project->DBConnectionObjectNameForPHP, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PARAM_FOR_FILE__", "VALUE"=>$ParamNameForFile,                      "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	
	// Save to Build Source Cache for DA
	switch($ProjectSourceOutput->ClassType)
	{
		case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			$thisBuildCache = new BuildSourceFuncCacheData();
			$thisBuildCache->ProjectPID = $project->PID;
			$thisBuildCache->daPID = $da->PID;
			$thisBuildCache->dafuncPID = $dafunc->PID;
			$thisBuildCache->BuildTargetType = BuildSourceFuncCacheBuildTargetTypeEnum::$DA;
			$thisBuildCache->ReleaseTargetType = GetBuildSourceFuncCacheReleaseTargetTypeFromProjectSourceOutputReleaseTargetType($ProjectSourceOutput->ReleaseTargetType);
			$thisBuildCache->FunctionName = $sourceFunctionName;
			$thisBuildCache->SourceCode = $dafunc_template;
			$thisBuildCache->ParameterListString = $sourceParams;
			$thisBuildCache->ParameterListStringForProxyBasedOnDA = MakeParameterListStringForProxyBasedOnDACode($ParameterListStringForProxyBasedOnDAList);
			$thisBuildCache->ParameterListStringForProxyBasedOnDAForExample = MakeParameterListStringForProxyBasedOnDACodeForExample($ParameterListStringForProxyBasedOnDAList);
			$thisBuildCache->ExampleCodeForCreatingObject = MakeExampleCodeForCreatingObjectCode($ExampleCodeForCreatingObjectInitializeList, $ExampleCodeForCreatingObjectList);
			$thisBuildCache->DataClassName = $sourceDataClass;
			$thisBuildCache->DAName = $da->name;
			$thisBuildCache->DAClassName = CreateDatabaseAccessClassName($da->name);
			$thisBuildCache->AutoloadFilename = GetAutomatedSourceFilename($ProjectSourceOutput);
			$thisBuildCache->ProxyURL = "";
			$thisBuildCache->ProxyParameterFormat = "";
			$thisBuildCache->ProxyParameterExample = "";
			$thisBuildCache->ProxyResultFormat = "";
			$thisBuildCache->ProxyResultExample = "";
			$thisBuildCache->ProxyParameterForJquery = "";
			$thisBuildCache->ProxyParameterExampleForJquery = "";
			$thisBuildCache->ProxyParameterExampleForPHP = "";
			$thisBuildCache->ProxyParameterExampleForPerl = "";
			$thisBuildCache->ProxyParameterExampleForRuby = "";
			$thisBuildCache->ProxyResultFormatForJquery = "";
			$DABuildSourceFuncCache = new BuildSourceFuncCacheDBAccess();
			$DABuildSourceFuncCache->DeleteBuildSourceFuncCacheByDAFunc($thisBuildCache);
			$DABuildSourceFuncCache->InsertBuildSourceFuncCache($thisBuildCache);
			break;
		case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
		case ProjectSourceOutputClassTypeEnum::$HTML:
		case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
		case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
			return true;
			
		default:
			PrintOutMtoolBuildResultMessage();
			die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
	}
	
	return $dafunc_template;
}

function MakeParameterListStringForProxyBasedOnDACode($ParameterListStringForProxyBasedOnDAList)
{
	return MakeParameterListStringForProxyBasedOnDACodeSub($ParameterListStringForProxyBasedOnDAList, "__INDENT_FOR_PARAM_OF_OBJECT__        ", "__INDENT_FOR_PARAM_OF_OBJECT__    ", ":");
}
function MakeParameterListStringForProxyBasedOnDACodeForExample($ParameterListStringForProxyBasedOnDAList)
{
	return MakeParameterListStringForProxyBasedOnDACodeSub($ParameterListStringForProxyBasedOnDAList, "__INDENT_FOR_PARAM_OF_OBJECT__    ", "__INDENT_FOR_PARAM_OF_OBJECT__", "__DELIMITER_FOR_CUSTOM_PROXY__");
}

function MakeParameterListStringForProxyBasedOnDACodeSub($ParameterListStringForProxyBasedOnDAList, $indent, $last_indent, $separater)
{
	$result_list = array();
	array_push($result_list, "__ARRAY_START_CODE_FOR_EACH_LANGUAGE__");
	for($i = 0 ; $i < count($ParameterListStringForProxyBasedOnDAList) ; $i++) {
		$ParameterListStringForProxyBasedOnDA = $ParameterListStringForProxyBasedOnDAList[$i];
		
		$value = "";
		$value_list = array();
		if ($ParameterListStringForProxyBasedOnDA->ForInsertTarget) {
			array_push($value_list, "Insert Target");
		}
		if ($ParameterListStringForProxyBasedOnDA->ForUpdateTarget) {
			array_push($value_list, "Update Target");
		}
		if ($ParameterListStringForProxyBasedOnDA->ForWhereTarget) {
			array_push($value_list, "Where Parameter Target");
		}
		if (count($value_list) == 0) {
			$value = "Value";
		} else {
			$value = "Value for " . implode(" and ", $value_list);
		}
		
		$comma = "";
		if ($i != count($ParameterListStringForProxyBasedOnDAList) -1) {
			$comma = ",";
		}
		array_push($result_list, "__INDENT_FOR_CUSTOM_PROXY__" . $indent . "\"". $ParameterListStringForProxyBasedOnDA->Code . "\"" . $separater . " <" . $value . ">" . $comma);
	}
	array_push($result_list, "__INDENT_FOR_CUSTOM_PROXY__" . $last_indent . "__ARRAY_END_CODE_FOR_EACH_LANGUAGE__");
	return implode("\n", $result_list);
}

class UnduplicateCodeForDAFuncContainer
{
	public $Code;
	public $ForInsertTarget = false;
	public $ForUpdateTarget = false;
	public $ForWhereTarget = false;
}
function MakeExampleCodeForCreatingObjectCode($ExampleCodeForCreatingObjectInitializeList, $ExampleCodeForCreatingObjectList)
{
	$result_list = array();
	for($i = 0 ; $i < count($ExampleCodeForCreatingObjectInitializeList) ; $i++) {
		$ExampleCodeForCreatingObjectInitialize = $ExampleCodeForCreatingObjectInitializeList[$i];
		array_push($result_list, $ExampleCodeForCreatingObjectInitialize);
	}
	for($i = 0 ; $i < count($ExampleCodeForCreatingObjectList) ; $i++) {
		$ExampleCodeForCreatingObject = $ExampleCodeForCreatingObjectList[$i];
		
		$comment = "";
		$comment_list = array();
		if ($ExampleCodeForCreatingObject->ForInsertTarget) {
			array_push($comment_list, "Insert Target");
		}
		if ($ExampleCodeForCreatingObject->ForUpdateTarget) {
			array_push($comment_list, "Update Target");
		}
		if ($ExampleCodeForCreatingObject->ForWhereTarget) {
			array_push($comment_list, "Where Parameter Target");
		}
		if (count($comment_list) > 0) {
			$comment = "	// " . implode(", ", $comment_list);
		}
		array_push($result_list, $ExampleCodeForCreatingObject->Code . " = \"xxx\";" . $comment);
	}
	return implode("\n", $result_list);
}
function AddUnduplicateCodeForDAFunc(&$ExampleCodeForCreatingObjectList, $new_code, $for_insert, $for_update, $for_where)
{
	$need_to_add = true;
	for($i = 0 ; $i < count($ExampleCodeForCreatingObjectList) ; $i++) {
		$ExampleCodeForCreatingObject = $ExampleCodeForCreatingObjectList[$i];
		
		if ($ExampleCodeForCreatingObject->Code == $new_code)
		{
			$ExampleCodeForCreatingObject->ForInsertTarget |= $for_insert;
			$ExampleCodeForCreatingObject->ForUpdateTarget |= $for_update;
			$ExampleCodeForCreatingObject->ForWhereTarget |= $for_where;
			$need_to_add = false;
			break;
		}
	}
	if ($need_to_add) {
		$newObj = new UnduplicateCodeForDAFuncContainer();
		$newObj->Code = $new_code;
		$newObj->ForInsertTarget = $for_insert;
		$newObj->ForUpdateTarget = $for_update;
		$newObj->ForWhereTarget = $for_where;
		array_push($ExampleCodeForCreatingObjectList, $newObj);
	}
}

function GetReferencingFieldColumnIfThereis($ProjectPID, $FieldPID)
{
	$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
	
	if ($FieldPID != "" && $FieldPID != 0) {
		$dafuncselecttargetfield = $DAdafuncselecttargetfields->Getdafuncselecttargetfields($FieldPID, $ProjectPID);
		return GetReferencingFieldColumnIfThereisFromFieldData($dafuncselecttargetfield);
	}
	return "";
}
function GetReferencingFieldColumnIfThereisFromFieldData($dafuncselecttargetfield)
{
	if ($dafuncselecttargetfield != NULL) {
		$thisTargetTableNameConsideringAlias = $dafuncselecttargetfield->targetTableName;
		if (trim($dafuncselecttargetfield->targetTableAliasName) != "") {
			$thisTargetTableNameConsideringAlias = $dafuncselecttargetfield->targetTableAliasName;
		}
		return $dafuncselecttargetfield->targetTableColumnPrefix . $thisTargetTableNameConsideringAlias . "." . $dafuncselecttargetfield->targetTableColumnName . $dafuncselecttargetfield->targetTableColumnSuffix;
	}
	return "";
}

?>
