<?PHP

include_once($MTOOL_LIB . "/lib_mtool_build.php");

function GetRelatedDBTableList($tablelist, $dbtable, $dafuncselecttargetfieldlist, $dafuncselectwherelist, $dafunc, $dataclasslist)
{
	$DBTableList = array();
	
	if ($dbtable != NULL) {
		AddRelatedDBTableResult($DBTableList, $dbtable->name, "");
	}
	if ($dafuncselecttargetfieldlist != NULL) {
		for($j = 0 ; $j < count($dafuncselecttargetfieldlist); $j++) {
			$dafuncselecttargetfield = $dafuncselecttargetfieldlist[$j];
			
			AddRelatedDBTableResult($DBTableList, $dafuncselecttargetfield->targetTableName, $dafuncselecttargetfield->targetTableAliasName);
		}
	}
	if ($dafuncselectwherelist != NULL) {
		for($j = 0 ; $j < count($dafuncselectwherelist); $j++) {
			$dafuncselectwhere = $dafuncselectwherelist[$j];
			
			AddRelatedDBTableResult($DBTableList, $dafuncselectwhere->targetTableName, $dafuncselectwhere->targetTableAliasName);
			
			if ($dafuncselectwhere->ParameterType == dafuncselectwhereParameterTypeEnum::$ANOTHERFIELD) {
				AddRelatedDBTableResult($DBTableList, $dafuncselectwhere->AnotherTableName, $dafuncselectwhere->AnotherTableAliasName);
			}
		}
	}
	for($i = 0 ; $i < count($tablelist); $i++) {
		$table = $tablelist[$i];
		
		if ($table->name == "") {
			continue;
		}
		if ($dafunc != NULL) {
			// Note: Only here, comparing with Table Name and Class Name
			if (CheckIfNameIsSameByCheckingParentClassName($table->name, $dafunc->GetBaseDataClassName(), $dataclasslist) ||
				CheckIfNameIsSameByCheckingParentClassName($table->name, $dafunc->GetInsertUpdateDeleteTargetTable(), $dataclasslist)
				) {
				AddRelatedDBTableResult($DBTableList, $table->name, "");
			}
		}
	}
	
	return $DBTableList;
}
class RelatedDBTableData
{
	public $TableName;
	public $AnotherTableName;
}
function AddRelatedDBTableResult(&$ResultList, $TableName, $AnotherTableName)
{
	if (CheckIfAlreadyExistRelatedDBTableResult($ResultList, $TableName, $AnotherTableName)) {
		// Already Exist. Just return
		return;
	}
	$thisObj = new RelatedDBTableData();
	$thisObj->TableName = $TableName;
	$thisObj->AnotherTableName = $AnotherTableName;
	array_push($ResultList, $thisObj);
}
function CheckIfAlreadyExistRelatedDBTableResult($ResultList, $TableName, $AnotherTableName)
{
	for($i = 0 ; $i < count($ResultList); $i++) {
		$Result = $ResultList[$i];
		
		if ($Result->TableName == $TableName &&
		    $Result->AnotherTableName == $AnotherTableName)
		{
			// Already Exist
			return true;
		}
	}
	// Not yet exist
	return false;
}
function CheckIfAlreadyExistTableNameOnlyInRelatedDBTableResult($ResultList, $TableName)
{
	for($i = 0 ; $i < count($ResultList); $i++) {
		$Result = $ResultList[$i];
		if ($Result->TableName == $TableName)
		{
			// Already Exist
			return true;
		}
	}
	// Not yet exist
	return false;
}

?>
