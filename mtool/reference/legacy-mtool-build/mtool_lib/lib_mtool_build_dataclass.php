<?PHP

function GetclassfieldsSource($BuildToken, $project, $ProjectSourceOutput, $dataclass, &$automaticallyOutputSource, &$property_copy_from_value_source, &$property_copy_from_raise_event_source, &$CHeaderIncludeForClassOrEnumList, &$HeaderSourceForSerializeAndDeserializeFromListForDataClassList)
{
	$automaticallyOutputSource = "";
	
	$DAdataclass = new dataclassDBAccess();
	$DAdataclassfields = new dataclassfieldsDBAccess();
	
	$ParentDataclass = NULL;
	$Parentdataclassfieldlist = NULL;
	if (trim($dataclass->InheritParentDataClassName) != "") {
		$ParentDataclass = $DAdataclass->GetdataclassByName($project->PID, trim($dataclass->InheritParentDataClassName));
		if ($ParentDataclass != NULL) {
			$Parentdataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($project->PID, $ParentDataclass->PID); 
		}
	}
	
	$dataclassfieldslist = $DAdataclassfields->GetdataclassfieldsList($project->PID, $dataclass->PID);
	AddMtoolDebugBuildMessage("   => Get Data Class Fields");
	for($l = 0 ; $l < count($dataclassfieldslist); $l++) {
		$dataclassfields = $dataclassfieldslist[$l];
		AddMtoolDebugBuildMessage("    => Project PID:" . $dataclassfields->ProjectPID . "  dataclassPID:" . $dataclassfields->dataclassPID . " PID:" . $dataclassfields->PID . " name:" . $dataclassfields->name);
		
		$needToSkipBecauseOfInherit = false;
		if ($Parentdataclassfieldlist != NULL) {
			for($m = 0 ; $m < count($Parentdataclassfieldlist) ; $m++) {
				$Parentdataclassfield = $Parentdataclassfieldlist[$m];
				if ($dataclassfields->name == $Parentdataclassfield->name) {
					// Yes. Inherited Property.
					$needToSkipBecauseOfInherit = true;
					break;
				}
			}
		}
		if ($needToSkipBecauseOfInherit) {
			continue;
		}
		
		$datatype = "";
		$isEnum = false;
		if ($dataclassfields->RefDataClassName != "" && $dataclassfields->RefDataClassFieldName != "") {
			$RefDataclass = $DAdataclass->GetdataclassByName($project->PID, trim($dataclassfields->RefDataClassName));
			if ($RefDataclass != "") {
				$Refdataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($project->PID, $RefDataclass->PID); 
				for ($j = 0 ; $j < count($Refdataclassfieldlist); $j++) {
					$Refdataclassfield = $Refdataclassfieldlist[$j];
					if ($Refdataclassfield->name == trim($dataclassfields->RefDataClassFieldName)) {
						$datatype = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $Refdataclassfield->datatype);
						$datatype = GetProgramLangBasedOnEnum($ProjectSourceOutput, $datatype, $dataclassfields->RefDataClassName, $dataclassfields->RefDataClassFieldName);
						if (CheckIfEnumDataType($Refdataclassfield->datatype)) {
							$isEnum = true;
						}
						break;
					}
				}
			}
		}
		if ($datatype == "") {
			$datatype = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $dataclassfields->datatype);
			$datatype = GetProgramLangBasedOnEnum($ProjectSourceOutput, $datatype, $dataclass->name, $dataclassfields->name);
			if (CheckIfEnumDataType($dataclassfields->datatype)) {
				$isEnum = true;
			}
		}
		
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				$automaticallyOutputSource .= GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, $dataclassfields->name, $datatype, false, "", $isEnum, false);
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				if (!$isEnum) {
					$is_nullable = CheckIfNullableForThisLangAndDataType($project, $ProjectSourceOutput->ProgramLanguage, $datatype);
					$automaticallyOutputSource .= GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, $dataclassfields->name, $datatype, $is_nullable, "", $isEnum, false);
				} else {
					$CONFIG_ENUM_TYPE_NAME = "string";
					$this_datatype = GetSourceDataTypeFromDatabaseDataTypeFromGeneralToLang($project, $ProjectSourceOutput, $CONFIG_ENUM_TYPE_NAME);
					$automaticallyOutputSource .= GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, $dataclassfields->name, $this_datatype, false, "", $isEnum, false);
					
					$enum_func_class_suffix = "";
					if ($dataclassfields->RefDataClassName != "" && $dataclassfields->RefDataClassFieldName != "") {
						$RefDataclass = CreateDataClassName(trim($dataclassfields->RefDataClassName));
						$enum_func_class_suffix = $RefDataclass . ".";
					}
					$automaticallyOutputSource .= GetDataClassPropertySourceFromTemplateForEnumForCS($BuildToken, $project, $ProjectSourceOutput, $enum_func_class_suffix, $dataclassfields->name, $datatype);
				}
				break;
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				$InitialValue = "";
				switch ($ProjectSourceOutput->ProgramLanguage) {
					case ProjectSourceOutputProgramLanguageEnum::$PHP:
					case ProjectSourceOutputProgramLanguageEnum::$CS:
					case ProjectSourceOutputProgramLanguageEnum::$JAVA:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
					case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
						break;
					case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
						$InitialValue = GetMtoolInitialValueForDataTypeBasedOnSerializeAndDeserializeSetting($project, $ProjectSourceOutput, $datatype);
						break;
				}
				$automaticallyOutputSource .= GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, $dataclassfields->name, $datatype, false, $InitialValue, $isEnum, false);
				break;
		}
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
			case ProjectSourceOutputProgramLanguageEnum::$CS:
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				if ($isEnum) {
					AddMtoolCHeaderIncludeForMiscClass($ProjectSourceOutput, $CHeaderIncludeForClassOrEnumList, $datatype);
				}
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				break;
		}
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
			case ProjectSourceOutputProgramLanguageEnum::$CS:
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				AddMtoolCHeaderIncludeForSerializeAndDeserialize($ProjectSourceOutput, $HeaderSourceForSerializeAndDeserializeFromListForDataClassList, $dataclassfields->name, $datatype, false, $isEnum);
				break;
		}
		
		// for "Copy From" function
		switch ($ProjectSourceOutput->ProgramLanguage) {
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				$template_copy_from_value = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-PROPERTY-COPY-FROM-VALUE");
				$template_copy_from_value = ReplaceOutputSourceInfoByKeyValue($template_copy_from_value, array(
						array("KEY"=>"__PROPERTY_NAME__", "VALUE"=>$dataclassfields->name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				$property_copy_from_value_source .= $template_copy_from_value;
				
				$template_copy_from_raise_event = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-PROPERTY-COPY-FROM-RAISE-EVENT");
				$template_copy_from_raise_event = ReplaceOutputSourceInfoByKeyValue($template_copy_from_raise_event, array(
						array("KEY"=>"__PROPERTY_NAME__", "VALUE"=>$dataclassfields->name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
					));
				$property_copy_from_raise_event_source .= $template_copy_from_raise_event;
				
				break;
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				break;
		}
	}
}

function GetDataClassPropertySourceFromTemplate($BuildToken, $project, $ProjectSourceOutput, $property_name, $property_data_type, $is_nullable, $initial_value, $isEnum, $is_optional_val)
{
	$TemplateName = "DATACLASS-PROPERTY";
	switch($ProjectSourceOutput->ProgramLanguage)
	{
		case ProjectSourceOutputProgramLanguageEnum::$PHP:
		case ProjectSourceOutputProgramLanguageEnum::$CS:
		case ProjectSourceOutputProgramLanguageEnum::$JAVA:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
		case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			break;
		case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
			if ($isEnum) {
				$TemplateName = "DATACLASS-PROPERTY-ENUM";
			}
			break;
	}


	$nullable_value = "";
	if ($is_nullable) {
		switch($ProjectSourceOutput->ProgramLanguage)
		{
			case ProjectSourceOutputProgramLanguageEnum::$PHP:
				break;
			case ProjectSourceOutputProgramLanguageEnum::$CS:
				$nullable_value = "?";
				break;
			case ProjectSourceOutputProgramLanguageEnum::$JAVA:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
			case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
			case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
				break;
		}
	}
	
	$optional_symbol_value = "";
	if ($is_optional_val) {
		$optional_symbol_value = "?";
	}
	
	$template_dataclass_property = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, $TemplateName);
	if ($is_nullable) {
		$template_dataclass_property .= GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-PROPERTY-GET-PROPERTY-FOR-NULLABLE");
	}
	
	$template_dataclass_property = ReplaceOutputSourceInfoByKeyValue($template_dataclass_property, array(
			array("KEY"=>"__PROPERTY_NAME__", "VALUE"=>$property_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PROPERTY_DATA_TYPE__", "VALUE"=>$property_data_type, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PROPERTY_DATA_NULLABLE__", "VALUE"=>$nullable_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__OPTIONAL_SYMBOL__", "VALUE"=>$optional_symbol_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__INITIALIZE_VALUE__", "VALUE"=>$initial_value, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template_dataclass_property;
}
function GetDataClassPropertySourceFromTemplateForEnumForCS($BuildToken, $project, $ProjectSourceOutput, $class_suffix, $property_name, $property_data_type)
{
	$template_dataclass_property = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-PROPERTY-ENUM");
	$template_dataclass_property = ReplaceOutputSourceInfoByKeyValue($template_dataclass_property, array(
			array("KEY"=>"__CLASS_SUFFIX__", "VALUE"=>$class_suffix, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PROPERTY_NAME__", "VALUE"=>$property_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
			array("KEY"=>"__PROPERTY_DATA_TYPE__", "VALUE"=>$property_data_type, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
		));
	return $template_dataclass_property;
}

function InitializeToSkipUnusedDataClassForProxy($project, $ProjectSourceOutput)
{
	global $ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList;
	global $ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList;
	
	if (!$project->Getoption_build_dataclass_for_proxy_client_only_if_proxy_exist()) {
		return;
	}
	if (!CheckIfTargetToCheckIfSkipUnusedDataClassForProxy($project, $ProjectSourceOutput)) {
		// Not a Target
		return false;	// Not Skip
	}
	
	$DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da = new daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccess();
	$ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList = $DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da->GetAlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForProjectSourceOutputList($project->PID, $ProjectSourceOutput->PID);
	
	$DAdafuncSimpleProxySourceOutputTarget = new dafuncSimpleProxySourceOutputTargetDBAccess();
	$ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList = $DAdafuncSimpleProxySourceOutputTarget->GetAlldafuncSimpleProxySourceOutputTargetForProjectSourceOutputList($project->PID, $ProjectSourceOutput->PID);
}
$ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList = NULL;
$ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList = NULL;

function CheckIfTargetToCheckIfSkipUnusedDataClassForProxy($project, $ProjectSourceOutput)
{
	switch($ProjectSourceOutput->ClassType)
	{
		case ProjectSourceOutputClassTypeEnum::$DBACCESS:
		case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
		case ProjectSourceOutputClassTypeEnum::$HTML:
		case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
			return false;	// Not a Target
			
		case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
		case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			
			switch ($ProjectSourceOutput->ProgramLanguage) {
				case ProjectSourceOutputProgramLanguageEnum::$PHP:
					// AddMtoolErrorBuildMessage("Aborted. This is not a target for Proxy Client: " . $ProjectSourceOutput->ProgramLanguage);
					// return false;	// Not a Target
					
				case ProjectSourceOutputProgramLanguageEnum::$CS:
				case ProjectSourceOutputProgramLanguageEnum::$JAVA:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH:
				case ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM:
				case ProjectSourceOutputProgramLanguageEnum::$SWIFT:
					break;
				default:
					AddMtoolErrorBuildMessage("Error! Aborted. Unknown Program Language: " . $ProjectSourceOutput->ProgramLanguage);
					return false;	// Not a Target
			}
			break;
		default:
			die("Unknown Class Type:" . $ProjectSourceOutput->ClassType);
	}
	return true;		// Target
}

function CheckIfSkipUnusedDataClassForProxy($project, $ProjectSourceOutput, $dataclass, $dataclasslist)
{
	global $ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList;
	global $ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList;
	
	if (!$project->Getoption_build_dataclass_for_proxy_client_only_if_proxy_exist()) {
		return false;	// Not Skip
	}
	if (!CheckIfTargetToCheckIfSkipUnusedDataClassForProxy($project, $ProjectSourceOutput)) {
		// Not a Target
		return false;	// Not Skip
	}
	
	if ($ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList) {
		for($i = 0 ; $i < count($ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList) ; $i++) {
			$daCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProject = $ToSkipUnusedDataClassForProxy_AlldaCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProjectList[$i];
			
			if (CheckIfDataClassIsMatchedOrInheritedToSkipUnusedDataClassForProxy($project, $dataclass, $dataclass->name, $daCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProject->daPID, $daCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProject->daname, $daCustomProxyFunc_leftouterjoin_dafunc_and_da_ForAllProject->dafuncPID, $dataclasslist)) {
				return false;	// Not Skip
			}
		}
	}
	
	if ($ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList) {
		for($i = 0 ; $i < count($ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList) ; $i++) {
			$dafuncSimpleProxySourceOutputTargetForTheProject = $ToSkipUnusedDataClassForProxy_AlldafuncSimpleProxySourceOutputTargetForTheProjectList[$i];
			
			if (CheckIfDataClassIsMatchedOrInheritedToSkipUnusedDataClassForProxy($project, $dataclass, $dataclass->name, $dafuncSimpleProxySourceOutputTargetForTheProject->daPID, $dafuncSimpleProxySourceOutputTargetForTheProject->daname, $dafuncSimpleProxySourceOutputTargetForTheProject->dafuncPID, $dataclasslist)) {
				return false;	// Not Skip
			}
		}
	}
	return true;	// Skip
}
function CheckIfDataClassIsMatchedOrInheritedToSkipUnusedDataClassForProxy($project, $dataclass, $classname, $daPID, $daname, $dafuncPID, $dataclasslist)
{
	$already_checked_class_PID_HT = array();
	if (CheckIfDataClassIsMatchedOrInheritedToSkipUnusedDataClassForProxySub($project, $dataclass, $classname, $daPID, $daname, $dafuncPID, $dataclasslist, $already_checked_class_PID_HT)) {
		return true;
	}
	
	$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
	$dafuncselecttargetfieldslist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($project->PID, $daPID, $dafuncPID);
	for($l = 0 ; $l < count($dafuncselecttargetfieldslist); $l++) {
		$dafuncselecttargetfields = $dafuncselecttargetfieldslist[$l];
		
		if ($classname == $dafuncselecttargetfields->targetTableName) {
			return true;
		}
	}
	return false;
}
function CheckIfDataClassIsMatchedOrInheritedToSkipUnusedDataClassForProxySub($project, $dataclass, $classname, $daPID, $daname, $dafuncPID, $dataclasslist, &$already_checked_class_PID_HT)
{
	if ($classname == $daname) {
		return true;
	}
	for($i = 0 ; $i < count($dataclasslist); $i++) {
		$dataclass = $dataclasslist[$i];
		
		if (array_key_exists($dataclass->PID, $already_checked_class_PID_HT)) {
			continue;
		}
		
		if ($dataclass->name == $classname) {
			if ($dataclass->InheritParentDataClassName != "") {
				$already_checked_class_PID_HT[$dataclass->PID] = true;
				return CheckIfDataClassIsMatchedOrInheritedToSkipUnusedDataClassForProxySub($project, NULL, $dataclass->InheritParentDataClassName, $daPID, $daname, $dafuncPID, $dataclasslist, $already_checked_class_PID_HT);
			}
		}
	}
	return false;
}

function GetDataClassTheMostParent($ProjectPID, $dataclass)
{
	$DAdataclass = new dataclassDBAccess();
	
	$dataclassTheMostParent = $dataclass;
	for($try = 1 ; $try <= 100 ; $try++) {
		if ($dataclassTheMostParent != NULL && $dataclassTheMostParent->InheritParentDataClassName != "") {
			$dataclassTheMostParent = $DAdataclass->GetdataclassByName($ProjectPID, $dataclassTheMostParent->InheritParentDataClassName);
		} else {
			break;
		}
	}
	return $dataclassTheMostParent;
}

// function GetDataClassCreateObjectSource($BuildToken, $project, $ProjectSourceOutput, $param_name, $data_type)
// {
// 	$template = GetMtoolDBTemplateFile($BuildToken, $project, $ProjectSourceOutput, "DATACLASS-CREATE-OBJECT");
// 	$template = ReplaceOutputSourceInfoByKeyValue($template, array(
// 			array("KEY"=>"__PARAM_NAME__", "VALUE"=>$param_name, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false),
// 			array("KEY"=>"__DATA_TYPE__", "VALUE"=>$data_type, "TRIMLASTSPACE"=>false, "TRIMLASTRETURN"=>false)
// 		));
// 	return $template;
// }

?>
