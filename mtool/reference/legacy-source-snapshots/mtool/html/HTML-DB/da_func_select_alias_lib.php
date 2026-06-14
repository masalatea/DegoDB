<?php

$TargetTableAliasNameInfoList = array();
$alltablelistinprojectByConsideringAliasList = array();

class TargetTableAliasNameInfo
{
	public $TargetTableName;
	public $AliasNameList = array();
	
	public function GetAliasNameList()
	{
		return implode(",", $this->AliasNameList);
	}
	public function AddAliasNameUnduplicated($name)
	{
		if (!in_array($name, $this->AliasNameList)) {
			array_push($this->AliasNameList, $name);
		}
	}
}
function InitializeTargetTableAliasNameHTFromDB($dafuncselecttargetfieldlist)
{
	global $TargetTableAliasNameInfoList;
	
	$TargetTableAliasNameInfoList = array();
	for($j = 0 ; $j < count($dafuncselecttargetfieldlist); $j++) {
		$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$j];
		
		// Empty StringでもOKにするのでEmpty Checkは行わない。Emptyの場合"Aliasなし"になる
		AddIntoTargetTableAliasNameInfo($dafuncselecttargetfield->targetTableName, $dafuncselecttargetfield->targetTableAliasName);
	}
}
function AddIntoTargetTableAliasNameInfo($tablename, $aliasname)
{
	global $TargetTableAliasNameInfoList;
	
	if (!array_key_exists($tablename, $TargetTableAliasNameInfoList)) {
		$TargetTableAliasNameInfoList[$tablename] = new TargetTableAliasNameInfo();
	}
	$TargetTableAliasNameInfoList[$tablename]->AddAliasNameUnduplicated($aliasname);
}
function InitializeTargetTableAliasNameHTFromSumit($alltablelistinproject)
{
	global $TargetTableAliasNameInfoList;
	
	$TargetTableAliasNameInfoList = array();
	for($i = 0 ; $i < count($alltablelistinproject); $i++) {
		$thisdbtable = $alltablelistinproject[$i];
		
		$thisAnotherNameListString = trim(GetParam("AnotherName" . $thisdbtable->PID));
		if ($thisAnotherNameListString != "") {
			$anotherNames = explode(",", $thisAnotherNameListString);
			for($j = 0 ; $j < count($anotherNames); $j++) {
				$anotherName = trim($anotherNames[$j]);
				
				// Empty StringでもOKにするのでEmpty Checkは行わない。Emptyの場合"Aliasなし"になる
				AddIntoTargetTableAliasNameInfo($thisdbtable->name, $anotherName);
			}
		}
	}
}
function GetAliasTableNames($tablename)
{
	global $TargetTableAliasNameInfoList;
	
	if (array_key_exists($tablename, $TargetTableAliasNameInfoList)) {
		return $TargetTableAliasNameInfoList[$tablename]->GetAliasNameList();
	}
	return "";
}

class TableInfoWithAlias
{
	public $DBTableInfo;
	public $AliasName;
	public $AliasInfo;
}
function InitializeAlltablelistinprojectByConsideringAlias($alltablelistinproject)
{
	global $TargetTableAliasNameInfoList;
	
	$alltablelistinprojectByConsideringAliasList = array();
	
	for($i = 0 ; $i < count($alltablelistinproject); $i++) {
		$thisdbtable = $alltablelistinproject[$i];
		
		if (array_key_exists($thisdbtable->name, $TargetTableAliasNameInfoList)) {
			$TargetTableAliasNameInfo = $TargetTableAliasNameInfoList[$thisdbtable->name];
			for($j = 0 ; $j < count($TargetTableAliasNameInfo->AliasNameList); $j++) {
				$aliasName = trim($TargetTableAliasNameInfo->AliasNameList[$j]);
				
				$tableinfowithaliasObj = new TableInfoWithAlias();
				$tableinfowithaliasObj->DBTableInfo = $thisdbtable;
				$tableinfowithaliasObj->AliasName = $aliasName;
				$tableinfowithaliasObj->AliasInfo = $TargetTableAliasNameInfo;
				array_push($alltablelistinprojectByConsideringAliasList, $tableinfowithaliasObj);
			}
		} else {
			$tableinfowithaliasObj = new TableInfoWithAlias();
			$tableinfowithaliasObj->DBTableInfo = $thisdbtable;
			$tableinfowithaliasObj->AliasName = "";
			$tableinfowithaliasObj->AliasInfo = "";
			array_push($alltablelistinprojectByConsideringAliasList, $tableinfowithaliasObj);
		}
	}
	return $alltablelistinprojectByConsideringAliasList;
}

?>
