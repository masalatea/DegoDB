<?PHP

class BuildSelectWhereData
{
	public $Equality;
	public $ORGroup;
}
class BuildSelectJoinData
{
	public $Equality;
	public $JoinType;
	public $JoinTargetTableName;
	public $JoinTargetTableAliasName;
	public $JoinONTableName;
	public $JoinONTableAliasName;
	public $AlreayOutput = false;
	public $ORGroup = "";
}

function PushCorrespondingDataObject($dafuncselectwhere, $thisEquality, &$sourceSelectWhereList, &$sourceSelectJoinList, &$sourceSelectJoinONTargetTableHT)
{
	switch($dafuncselectwhere->JoinType) {
		case "":
			$thisWhereData = new BuildSelectWhereData();
			$thisWhereData->Equality = $thisEquality;
			$thisWhereData->ORGroup = $dafuncselectwhere->ORGroup;
			array_push($sourceSelectWhereList, $thisWhereData);
			break;
		case dafuncselectwhereJoinTypeEnum::$INNER:
		case dafuncselectwhereJoinTypeEnum::$LEFT:
		case dafuncselectwhereJoinTypeEnum::$RIGHT:
			$thisJoinData = new BuildSelectJoinData();
			$thisJoinData->Equality = $thisEquality;
			$thisJoinData->JoinType = $dafuncselectwhere->JoinType;
			$thisJoinData->JoinTargetTableAliasName = $dafuncselectwhere->targetTableAliasName;
			$thisJoinData->JoinTargetTableName = $dafuncselectwhere->targetTableName;
			$thisJoinData->JoinONTableName = $dafuncselectwhere->AnotherTableName;
			$thisJoinData->JoinONTableAliasName = $dafuncselectwhere->AnotherTableAliasName;
			$thisJoinData->ORGroup = $dafuncselectwhere->ORGroup;
			array_push($sourceSelectJoinList, $thisJoinData);
			$sourceSelectJoinONTargetTableHT[CreateSelectJoinONTargetTableKey($dafuncselectwhere->AnotherTableName, $dafuncselectwhere->AnotherTableAliasName)] = true;
			break;
		default:
			PrintOutMtoolBuildResultMessage();
			die("Fatal error. Unknown Join Type");
	}
}

function CreateSelectJoinONTargetTableKey($tablename, $aliasname)
{
	return $tablename . " -- table name and alias name -- " .  $aliasname;
}

class SourceParamInfoData
{
	public $name;
	public $datatype;
	public $IsObject;
	public $IsEnum;
	public $IsNullable;
	public $IsOnlyForProxy;
	public $BaseClassName;	// Mainly for Enum
}
function AddSourceParamInfoData($paramName, $dataType, $is_object, $is_enum, $is_nullable, $base_class_name, $is_only_for_proxy, &$sourceParamInfoList)
{
	$paraminfo = new SourceParamInfoData();
	$paraminfo->name = preg_replace("/^\\\$/", "", $paramName);
	$paraminfo->datatype = $dataType;
	$paraminfo->IsObject = $is_object;
	$paraminfo->IsEnum = $is_enum;
	$paraminfo->IsNullable = $is_nullable;
	$paraminfo->IsOnlyForProxy = $is_only_for_proxy;
	$paraminfo->BaseClassName = $base_class_name;
	array_push($sourceParamInfoList, $paraminfo);
}

function GetSelectJoinClause($thisTableName, $thisTableAliasName, &$sourceSelectJoinList, $ORGroupType)
{
	$joinClause = "";
	
	$JoinOnTableList = array();
	for ($j = 0 ; $j < count($sourceSelectJoinList) ; $j++) {
		if (isset($sourceSelectJoinList[$j])) {
			$sourceSelectJoin = $sourceSelectJoinList[$j];
			
			// AddToUnduplicatedList($JoinOnTableList, $sourceSelectJoin->JoinONTableName);
			AddToSelectWhereInfoUnduplicatedList($JoinOnTableList, $sourceSelectJoin->JoinONTableName, $sourceSelectJoin->JoinONTableAliasName);
		}
	}
	
	for ($i = 0 ; $i < count($JoinOnTableList); $i++) {
		$JoinOnTableName      = $JoinOnTableList[$i]->TableName;
		$JoinOnTableAliasName = $JoinOnTableList[$i]->AliasName;
		
		$TargetSourceSelectJoinList = GetTargetSourceSelectJoinList($sourceSelectJoinList, $thisTableName, $thisTableAliasName, $JoinOnTableName, $JoinOnTableAliasName);
		
		$ORGroupList = array();
		AddToUnduplicatedList($ORGroupList, "");							// Blank Group is first
		for ($j = 0 ; $j < count($TargetSourceSelectJoinList) ; $j++) {
			$sourceSelectJoin = $TargetSourceSelectJoinList[$j];
			
			AddToUnduplicatedList($ORGroupList, $sourceSelectJoin->ORGroup);
		}
		
		$addedCountForThisJoin = 0;
		$need_to_close_AND_OR_AND_kakko = false;
		
		for ($j = 0 ; $j < count($ORGroupList); $j++) {
			$ORGroup = $ORGroupList[$j];
			
			$thisJoinBase = "";
			$thisJoinClause = "";
			// $output_something_in_or_group = false;
			for ($k = 0 ; $k < count($TargetSourceSelectJoinList) ; $k++) {
				$sourceSelectJoin = $TargetSourceSelectJoinList[$k];
				
				if ($sourceSelectJoin->ORGroup == $ORGroup) {
					
					if ($addedCountForThisJoin == 0) {
						switch($sourceSelectJoin->JoinType) {
							case "":
								PrintOutMtoolBuildResultMessage();
								die("Internal Error. Join Type is not set.");
							case dafuncselectwhereJoinTypeEnum::$INNER:
								$thisJoinBase .= " INNER JOIN ";
								break;
							case dafuncselectwhereJoinTypeEnum::$LEFT:
								$thisJoinBase .= " LEFT OUTER JOIN ";
								break;
							case dafuncselectwhereJoinTypeEnum::$RIGHT:
								$thisJoinBase .= " RIGHT OUTER JOIN ";
								break;
							default:
								PrintOutMtoolBuildResultMessage();
								die("Internal Error. Unknown Join Type:" . $sourceSelectJoin->JoinType);
						}
						if ($sourceSelectJoin->JoinTargetTableName == "") {
							$thisJoinBase .= "<<Join Table Name is not set. Please check setting.>>";
						} else {
							$thisJoinBase .= $JoinOnTableName;
							if (trim($JoinOnTableAliasName) != "") {
								$thisJoinBase .= " as " . trim($JoinOnTableAliasName);
							}
						}
						$thisJoinBase .= " ON ";
					} else {
						if ($ORGroup == "") {
							$thisJoinClause .= " and ";
						} else {
							if ($thisJoinClause == "") {
								$thisJoinClause = "(";
								
							} else {
								switch(GetWorkingORGroupType($ORGroupType))
								{
									case dafuncORGroupTypeEnum::$DEFAULT:
										PrintOutMtoolBuildResultMessage();
										die("Internal Error! Something Wrong. GetWorkingORGroupType must return valid value.");
									case dafuncORGroupTypeEnum::$ORANDOR:
										$thisJoinClause .= " or ";
										break;
									case dafuncORGroupTypeEnum::$ANDORAND:
										$thisJoinClause .= " and ";
										break;
									default:
										PrintOutMtoolBuildResultMessage();
										die("Internal Error! Something Wrong. Unknown OR Group Type");
								}
							}
						}
					}
					$thisJoinClause .= $sourceSelectJoin->Equality;
					
					// if (!$output_something_in_or_group) {
					// 	if ($ORGroup != "") {
					// 		$thisJoinClause .= "(";
					// 	}
					// }
					
					$addedCountForThisJoin++;
					// $output_something_in_or_group = true;
					
					// unset($sourceSelectJoinList[$j]);
					$sourceSelectJoin->AlreayOutput = true;
				}
			}
			if (trim($ORGroup) == "") {
				// No Need
			} else {
				if ($thisJoinClause != "") {
					$thisJoinClause .= ")";
					
					switch(GetWorkingORGroupType($ORGroupType))
					{
						case dafuncORGroupTypeEnum::$DEFAULT:
							PrintOutMtoolBuildResultMessage();
							die("Internal Error! Something Wrong. GetWorkingORGroupType must return valid value.");
						case dafuncORGroupTypeEnum::$ORANDOR:
							$joinClause .= " and ";
							break;
						case dafuncORGroupTypeEnum::$ANDORAND:
							if ($j == 1) {		// $j==0は「ORGroup=""」の時なので1の時が最初
								$joinClause .= " and (";
								$need_to_close_AND_OR_AND_kakko = true;
							} else {
								$joinClause .= " or ";
							}
							break;
						default:
							PrintOutMtoolBuildResultMessage();
							die("Internal Error! Something Wrong. Unknown OR Group Type");
					}
				}
			}
			$joinClause .= $thisJoinBase . $thisJoinClause;
		}
		if ($need_to_close_AND_OR_AND_kakko) {
			$joinClause .= ")";
		}
		// if ($output_something_in_or_group) {
		// 	if ($ORGroup != "") {
		// 		$thisJoinClause .= ")";
		// 	}
		// }
	}
	return $joinClause;
}

function GetTargetSourceSelectJoinList($sourceSelectJoinList, $thisTableName, $thisTableAliasName, $JoinOnTableName, $JoinOnTableAliasName)
{
	$TargetSourceSelectJoinList = array();
	
	for ($j = 0 ; $j < count($sourceSelectJoinList) ; $j++) {
		$sourceSelectJoin = $sourceSelectJoinList[$j];
		if (!$sourceSelectJoin->AlreayOutput) {
			
			if ($thisTableName == $sourceSelectJoin->JoinTargetTableName &&
				$thisTableAliasName == $sourceSelectJoin->JoinTargetTableAliasName &&
				$JoinOnTableName   == $sourceSelectJoin->JoinONTableName &&
				$JoinOnTableAliasName == $sourceSelectJoin->JoinONTableAliasName)
			{
				array_push($TargetSourceSelectJoinList, $sourceSelectJoin);
			}
		}
	}
	return $TargetSourceSelectJoinList;
}

function CheckIfIncludedInJoinON($thisTableName, $sourceSelectJoinList)
{
	print_r($thisTableName);
	print_r($sourceSelectJoinList);
	
	for ($i = 0 ; $i < count($sourceSelectJoinList) ; $i++) {
		if (isset($sourceSelectJoinList[$i])) {
			$sourceSelectJoin = $sourceSelectJoinList[$i];
			
			if ($thisTableName == $sourceSelectJoin->JoinONTableName)
			{
				// print "YES. JOIN ON TABLE NAME MATCHED";
				return true;
			}
		}
	}
	return false;
}

class SelectWhereInfo
{
	public $TableName;
	public $AliasName;
}

function AddToSelectWhereInfoUnduplicatedList(&$list, $tablename, $aliasname)
{
	for($i = 0 ; $i < count($list) ; $i++) {
		$thisitem = $list[$i];
		
		if ($thisitem->TableName == $tablename && 
		    $thisitem->AliasName == $aliasname)
		{
			// Already exist
			return;
		}
	}
	$newObj = new SelectWhereInfo();
	$newObj->TableName = $tablename;
	$newObj->AliasName = $aliasname;
	array_push($list, $newObj);
}

?>
