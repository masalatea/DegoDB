<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_USER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content

function GetReverseFilterArray($str)
{
	$result = array();
	$tmp = preg_split("/\s*,\s*/", $str);
	for($i = 0 ; $i < count($tmp); $i++) {
		$thisFilter = trim($tmp[$i]);
		if ($thisFilter != "") {
			array_push($result, $thisFilter);
		}
	}
	return $result;
}
function GetShowTargetHTKey($CandicateOfdafuncselectwhereData)
{
	return $CandicateOfdafuncselectwhereData->ProjectPID . "/" . $CandicateOfdafuncselectwhereData->daPID . "/" . $CandicateOfdafuncselectwhereData->dafuncPID . "/" . $CandicateOfdafuncselectwhereData->PID . "/" . $CandicateOfdafuncselectwhereData->targetTableName . "/" . $CandicateOfdafuncselectwhereData->targetTableColumnName . "/" . $CandicateOfdafuncselectwhereData->ParameterType . "/" . $CandicateOfdafuncselectwhereData->FixedParameter . "/" . $CandicateOfdafuncselectwhereData->AnotherTableName . "/" . $CandicateOfdafuncselectwhereData->AnotherFieldName;
}

function OutputFilterLinkTag($TargetName, $currentValue, $filterValue, $hideList)
{
	global $AlreadhOutputFilterLinkTagHT;
	global $WHERE_INPUT_AID_FILTER_TYPE_SHOW;
	global $WHERE_INPUT_AID_FILTER_TYPE_HIDE;
	global $WHERE_INPUT_AID_FILTER_TYPE_RESET;
	
	if (trim($currentValue) == "") {
		return;
	}
	
	$key = "TargetName: " . $TargetName . " Value: " . $currentValue;
	if (array_key_exists($key, $AlreadhOutputFilterLinkTagHT)) {
		// Already output
		return;
	}
	$AlreadhOutputFilterLinkTagHT[$key] = true;
	
	$showFilterLink = false;
	$showReverseFilterLink = true;
	$showResetFilterLink = false;
	
	if (CheckShowFilterForInputAid($filterValue, $currentValue)) {
		$showReverseFilterLink = false;
	} else {
		$showFilterLink = true;
	}
	if (CheckHideFilterForInputAid($hideList, $currentValue)) {
		$showReverseFilterLink = false;
	}
	
	if (trim($filterValue) != "" ||
	    count($hideList) > 0) {
		$showResetFilterLink = true;
	}
	
	$thisFilterTarget = "FilterBy" . $TargetName;
	$thisReverseFilterTarget = "ReverseFilterBy" . $TargetName;
	$thisFilterValue = $currentValue;
	
	if ($showFilterLink) {
		?>
        <span class="LoadCompleteArea" style="display:none">
		<br>
		[<a class="filterbycolumn" FilterType="<?php print $WHERE_INPUT_AID_FILTER_TYPE_SHOW; ?>" FilterTarget="<?php print $thisFilterTarget; ?>" FilterValue="<?php print htmlspecialchars($thisFilterValue); ?>">Show only This</a>]</span>
		<?php
	}
	if ($showReverseFilterLink) {
		?>
        <span class="LoadCompleteArea" style="display:none">
		<br>
		[<a class="filterbycolumn" FilterType="<?php print $WHERE_INPUT_AID_FILTER_TYPE_HIDE; ?>" ReverseFilterTarget="<?php print $thisReverseFilterTarget; ?>" FilterValue="<?php print htmlspecialchars($thisFilterValue); ?>">Hide This</a>]</span>
		<?php
	}
	if ($showResetFilterLink) {
		?>
        <span class="LoadCompleteArea" style="display:none">
		<br>
		[<a class="filterbycolumn" FilterType="<?php print $WHERE_INPUT_AID_FILTER_TYPE_RESET; ?>" FilterTarget="<?php print $thisFilterTarget; ?>" ReverseFilterTarget="<?php print $thisReverseFilterTarget; ?>">Reset Filter</a>]</span>
		<?php
	}
}
$AlreadhOutputFilterLinkTagHT = array();

function CheckShowFilterForInputAid($filterValue, $currentValue)
{
	return ($filterValue != "" && $filterValue == $currentValue);
}
function CheckIfSkipByShowFilterForInputAid($filterValue, $currentValue)
{
	return ($filterValue != "" && $filterValue != $currentValue);
}
function CheckHideFilterForInputAid($hideList, $currentValue)
{
	return (is_array($hideList) && in_array($currentValue, $hideList));
}

?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DA_FUNC_SELECT_WHERE_INPUT_AID"); ?> - <?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

include_once("da_func_select_alias_lib.php");

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));
$ShowAllPattern = trim(GetParam("ShowAllPattern"));

$FilterBytargetTableName = trim(GetParam("FilterBytargetTableName"));
$FilterBytargetTableColumnName = trim(GetParam("FilterBytargetTableColumnName"));
$FilterByParameterType = trim(GetParam("FilterByParameterType"));
$FilterByAnotherTableName = trim(GetParam("FilterByAnotherTableName"));
$FilterByAnotherFieldName = trim(GetParam("FilterByAnotherFieldName"));
$ReverseFilterBytargetTableName = trim(GetParam("ReverseFilterBytargetTableName"));
$ReverseFilterBytargetTableColumnName = trim(GetParam("ReverseFilterBytargetTableColumnName"));
$ReverseFilterByParameterType = trim(GetParam("ReverseFilterByParameterType"));
$ReverseFilterByAnotherTableName = trim(GetParam("ReverseFilterByAnotherTableName"));
$ReverseFilterByAnotherFieldName = trim(GetParam("ReverseFilterByAnotherFieldName"));

$DoNext = trim(GetParam("DoNext"));

// Array Parameter
$DBTableList = GetParam("DBTableList");
$SelectTargetList = GetParam("SelectTargetList");

$ReverseFilterBytargetTableNameList = GetReverseFilterArray($ReverseFilterBytargetTableName);
$ReverseFilterBytargetTableColumnNameList = GetReverseFilterArray($ReverseFilterBytargetTableColumnName);
$ReverseFilterByParameterTypeList = GetReverseFilterArray($ReverseFilterByParameterType);
$ReverseFilterByAnotherTableNameList = GetReverseFilterArray($ReverseFilterByAnotherTableName);
$ReverseFilterByAnotherFieldNameList = GetReverseFilterArray($ReverseFilterByAnotherFieldName);

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Class PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAFuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}

$NOT_PATTERN_PREFIX = "!!!!!!!!!!";

$DAdafunc = new dafuncDBAccess();
$dafunc = NULL;

$DAdataclass = new dataclassDBAccess();
$dataclass = NULL;

$DAdbtable = new dbtableDBAccess();
$dbtable = NULL;

$thisBaseDataClassName = "";
$dataclass = NULL;
$dbtable = NULL;

$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
$dafuncselecttargetfieldlist = NULL;

$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
$dafuncselectwherelist = NULL;

$DAdbtable = new dbtableDBAccess();
$alltablelistinproject = NULL;

$DAdbtablecolumns = new dbtablecolumnsDBAccess();

$DAdataclass = new dataclassDBAccess();
$dataclasslist = NULL;

if ($NoError) {
	printPathOnTopForDBAccessClass("Input Aid for Select's Where", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
	if ($dafunc == NULL) {
		?>
	  <H3><font color="red">DB Access Function is not found. Please ask administrator if this continues.</font></H3>
		<?php
		$NoError = false;
	}
}

$WHERE_INPUT_AID_FILTER_TYPE_SHOW = "show";
$WHERE_INPUT_AID_FILTER_TYPE_HIDE = "hide";
$WHERE_INPUT_AID_FILTER_TYPE_RESET = "reset";

if ($NoError) {
	
	$thisBaseDataClassName = $dafunc->GetBaseDataClassName();
	if ($thisBaseDataClassName != "") {
		$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $thisBaseDataClassName);
		$dbtable = $DAdbtable->GetdbtableByName($ProjectPID, $thisBaseDataClassName);
	}
	$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($ProjectPID, $DAPID, $DAFuncPID); 
	$dafuncselectwherelist = $DAdafuncselectwhere->GetdafuncselectwhereList($ProjectPID, $DAPID, $DAFuncPID); 
	$alltablelistinproject = $DAdbtable->GetdbtableList($ProjectPID);
	
	$dataclasslist = $DAdataclass->GetdataclassList($ProjectPID); 
	
	$RelatedDBTableNameList = GetRelatedDBTableList($alltablelistinproject, $dbtable, $dafuncselecttargetfieldlist, $dafuncselectwherelist, $dafunc, $dataclasslist);
	
	if ($DoNext != "") {
		// Check before going to Next
		if (($DBTableList == NULL) ||
			 !is_array($DBTableList) ||
			 count($DBTableList) == 0
			) {
			?>
			<H3><font color="red">WARNING: Please select one or more DB Table.</font></H3>
			<?php
			$DoNext = "";
		}
	}
	if ($DoNext == "") {
		
		InitializeTargetTableAliasNameHTFromDB($dafuncselecttargetfieldlist);
		// $TargetTableAliasNameInfoListが初期化される時は以下も初期化が必要
		$alltablelistinprojectByConsideringAliasList = InitializeAlltablelistinprojectByConsideringAlias($alltablelistinproject);
		
		?>
        <h3>Select DB Table</h3>
        
        <div class="LoadingArea">Loading...</div>
        <div class="LoadCompleteArea" style="display:none">
        <form action="da_func_select_where_input_aid.php" method="post">
        
        <p><input name="DoNext" type="submit" value="Next"></p>
        <table class="table">
            <thead>
            <tr bgcolor="#ECECEC">
              <th>Table Name</th>
              <th>Alias Name [Optional](*1)</th>
            </tr>
          </thead>
            <tbody>
        <?php
		for($i = 0 ; $i < count($alltablelistinproject); $i++) {
			$thisdbtable = $alltablelistinproject[$i];
			
			$isSelected = false;
			$AliasTableNameListString = "";
			if (CheckIfAlreadyExistTableNameOnlyInRelatedDBTableResult($RelatedDBTableNameList, $thisdbtable->name)) {
				$isSelected = true;
				
				$AliasTableNameListString = GetAliasTableNameListString($RelatedDBTableNameList, $thisdbtable->name);
			}
			?>
            <tr>
                <td>
                <span class="checkbox"><label><input name="DBTableList[]" class="DBTableListCheck" type="checkbox" value="<?php print $thisdbtable->name; ?>"<?php if($isSelected) { print " checked"; } ?> AnotherNameInputName="AnotherNameArea<?php print $thisdbtable->PID; ?>"><?php print $thisdbtable->name; ?></label></span>
                </td>
                <td>
                <span id="AnotherNameArea<?php print $thisdbtable->PID; ?>"<?php if(!$isSelected) { print " style=\"display:none\""; } ?>><input name="AnotherName<?php print $thisdbtable->PID; ?>" type="text" value="<?php print $AliasTableNameListString; ?>" size="40"> </span>
                </td>
            </tr>
			<?php
		}
		?>
            </tbody>
        </table>
<script>

$(function(){
	$('.DBTableListCheck').change(function(){
		var AnotherNameInputName = $(this).attr("AnotherNameInputName");
		if ($(this).is(':checked')) {
			$("#" + AnotherNameInputName).show();
		} else {
			$("#" + AnotherNameInputName).hide();
		}
	});
});

</script>        
        <input name="ProjectPID" type="hidden" value="<?php print $ProjectPID; ?>">
        <input name="DAPID" type="hidden" value="<?php print $DAPID; ?>">
        <input name="DAFuncPID" type="hidden" value="<?php print $DAFuncPID; ?>">
        </form>
        <?php
		include_once("da_func_explanation_for_alias_include.php");
		?>
        </div>
        <?php
		
	} else if (count($alltablelistinproject) > 0) {
		
		InitializeTargetTableAliasNameHTFromSumit($alltablelistinproject);
		// $TargetTableAliasNameInfoListが初期化される時は以下も初期化が必要
		$alltablelistinprojectByConsideringAliasList = InitializeAlltablelistinprojectByConsideringAlias($alltablelistinproject);
		
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th rowspan="2">Table Name</th>
			  <th rowspan="2">Table Alias Name</th>
			  <th rowspan="2">Column Name</th>
			  <th rowspan="2">Parameter Type</th>
			  <th rowspan="2">Parameter's Data Type</th>
			  <th rowspan="2">Another Table Name</th>
			  <th rowspan="2">Another Table Alias Name</th>
			  <th rowspan="2">Another Table's Field Name</th>
			  <th colspan="4">[FYI] Current Setting for Existing Setting</th>
			  <th rowspan="2" bgcolor="#E0E0E0">Already exists?</th>
			  <th rowspan="2" bgcolor="#E0E0E0"></th>
			  </tr>
			<tr bgcolor="#ECECEC">
			  <th>Fixed Parameter</th>
			  <th>Relational Operator</th>
			  <th>Join Type</th>
			  <th>OR Group</th>
			  </tr>
          </thead>
            <tbody>
		<?php
		
		$CandicateOfTables = array();
		$CandicateOfTableColumnHT = array();
		for($i = 0 ; $i < count($alltablelistinprojectByConsideringAliasList); $i++) {
			$alltablelistinprojectByConsideringAlias = $alltablelistinprojectByConsideringAliasList[$i];
			
			$thisAliasTableName = $alltablelistinprojectByConsideringAlias->AliasName;
			$thisAliasInfo = $alltablelistinprojectByConsideringAlias->AliasInfo;
			$thisdbtable   = $alltablelistinprojectByConsideringAlias->DBTableInfo;
			
			$isSelected = false;
			for($j = 0 ; $j < count($DBTableList); $j++) {
				$SelectedDBTableName = $DBTableList[$j];
				
				if ($SelectedDBTableName == $thisdbtable->name) {
					$isSelected = true;
					break;
				}
			}
			if ($isSelected) {
				array_push($CandicateOfTables, $alltablelistinprojectByConsideringAlias);
				$dbtablecolumnlist = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $thisdbtable->PID);
				$CandicateOfTableColumnHT[$thisdbtable->PID] = $dbtablecolumnlist;
			}
		}
		
		$CandicateOfdafuncselectwhereDataList = array();
		$showTargetHT = array();
		for($i = 0 ; $i < count($CandicateOfTables); $i++) {
			$alltablelistinprojectByConsideringAlias = $CandicateOfTables[$i];
			
			$thisAliasTableName = $alltablelistinprojectByConsideringAlias->AliasName;
			$thisAliasInfo = $alltablelistinprojectByConsideringAlias->AliasInfo;
			$thisdbtable   = $alltablelistinprojectByConsideringAlias->DBTableInfo;
			
			$dbtablecolumnlist = NULL;
			$thisKey = $thisdbtable->PID;
			if (array_key_exists($thisKey, $CandicateOfTableColumnHT)) {
				$dbtablecolumnlist = $CandicateOfTableColumnHT[$thisKey];
			}
			
			$parameterTypeList = array("argument", "fixed", "anotherfield");
			
			for($j = 0 ; $j < count($parameterTypeList) ; $j++) {
				$thisParameterType = $parameterTypeList[$j];
				
				for($k = 0 ; $k < count($dbtablecolumnlist) ; $k++) {
					$dbtablecolumn = $dbtablecolumnlist[$k];
					
					switch($thisParameterType) {
						case "argument":
						case "fixed":
							$CandicateOfdafuncselectwhereData = new dafuncselectwhereData();
							$CandicateOfdafuncselectwhereData->ProjectPID = $ProjectPID;
							$CandicateOfdafuncselectwhereData->daPID = $DAPID;
							$CandicateOfdafuncselectwhereData->dafuncPID = $DAFuncPID;
							$CandicateOfdafuncselectwhereData->PID = "";
							$CandicateOfdafuncselectwhereData->targetTableName = $thisdbtable->name;
							$CandicateOfdafuncselectwhereData->targetTableAliasName = $thisAliasTableName;
							$CandicateOfdafuncselectwhereData->targetTableColumnName = $dbtablecolumn->name;
							$CandicateOfdafuncselectwhereData->ParameterType = $thisParameterType;
							$CandicateOfdafuncselectwhereData->FixedParameter = "";
							$CandicateOfdafuncselectwhereData->AnotherTableName = "";
							$CandicateOfdafuncselectwhereData->AnotherTableAliasName = "";
							$CandicateOfdafuncselectwhereData->AnotherFieldName = "";
							$CandicateOfdafuncselectwhereData->JoinType = "";
							$CandicateOfdafuncselectwhereData->ORGroup = "";
							$CandicateOfdafuncselectwhereData->RelationalOperator = "";
							$CandicateOfdafuncselectwhereData->WhereOrder = "";
							array_push($CandicateOfdafuncselectwhereDataList, $CandicateOfdafuncselectwhereData);
							
							$showTargetHT[GetShowTargetHTKey($CandicateOfdafuncselectwhereData)] = ($dbtablecolumn->IsKey != "");
							
							break;
						case "anotherfield":
							for($l = 0 ; $l < count($CandicateOfTables); $l++) {
								$TargetalltablelistinprojectByConsideringAlias = $CandicateOfTables[$l];
								
								$TargetAliasTableName = $TargetalltablelistinprojectByConsideringAlias->AliasName;
								$TargetAliasInfo = $TargetalltablelistinprojectByConsideringAlias->AliasInfo;
								$TargetTable   = $TargetalltablelistinprojectByConsideringAlias->DBTableInfo;
								
								if ($thisdbtable->PID == $TargetTable->PID &&
								    $thisAliasTableName == $TargetAliasTableName) {
									// Same Table. Skip.
								} else {
									$Targetdbtablecolumnlist = NULL;
									$thisKey = $TargetTable->PID;
									if (array_key_exists($thisKey, $CandicateOfTableColumnHT)) {
										$Targetdbtablecolumnlist = $CandicateOfTableColumnHT[$thisKey];
									}
									for($m = 0 ; $m < count($Targetdbtablecolumnlist) ; $m++) {
										$Targetdbtablecolumn = $Targetdbtablecolumnlist[$m];
										
										$CandicateOfdafuncselectwhereData = new dafuncselectwhereData();
										$CandicateOfdafuncselectwhereData->ProjectPID = $ProjectPID;
										$CandicateOfdafuncselectwhereData->daPID = $DAPID;
										$CandicateOfdafuncselectwhereData->dafuncPID = $DAFuncPID;
										$CandicateOfdafuncselectwhereData->PID = "";
										$CandicateOfdafuncselectwhereData->targetTableName = $thisdbtable->name;
										$CandicateOfdafuncselectwhereData->targetTableAliasName = $thisAliasTableName;
										$CandicateOfdafuncselectwhereData->targetTableColumnName = $dbtablecolumn->name;
										$CandicateOfdafuncselectwhereData->ParameterType = $thisParameterType;
										$CandicateOfdafuncselectwhereData->FixedParameter = "";
										$CandicateOfdafuncselectwhereData->AnotherTableName = $TargetTable->name;
										$CandicateOfdafuncselectwhereData->AnotherTableAliasName = $TargetAliasTableName;
										$CandicateOfdafuncselectwhereData->AnotherFieldName = $Targetdbtablecolumn->name;
										$CandicateOfdafuncselectwhereData->JoinType = "";
										$CandicateOfdafuncselectwhereData->ORGroup = "";
										$CandicateOfdafuncselectwhereData->RelationalOperator = "";
										$CandicateOfdafuncselectwhereData->WhereOrder = "";
										array_push($CandicateOfdafuncselectwhereDataList, $CandicateOfdafuncselectwhereData);
										
										$showTargetHT[GetShowTargetHTKey($CandicateOfdafuncselectwhereData)] = ($dbtablecolumn->IsKey != "") && ($Targetdbtablecolumn->IsKey != "");
									}
								}
							}
							break;
					}
				}
			}
		}
		
		class CandicateOfdafuncselectwhereDataLinkContainer
		{
			public $PID;
			public $correspondingdafuncselectwhere;
		}
		
		for ($i = 0 ; $i < count($CandicateOfdafuncselectwhereDataList); $i++) {
			$CandicateOfdafuncselectwhereData = $CandicateOfdafuncselectwhereDataList[$i];
			
			$alreadyExists = false;
			$correspondingdafuncselectwhereList = array();
			for ($j = 0 ; $j < count($dafuncselectwherelist); $j++) {
				$dafuncselectwhere = $dafuncselectwherelist[$j];
				
				if ($CandicateOfdafuncselectwhereData->targetTableName == $dafuncselectwhere->targetTableName &&
				    $CandicateOfdafuncselectwhereData->targetTableAliasName == $dafuncselectwhere->targetTableAliasName &&
				    $CandicateOfdafuncselectwhereData->targetTableColumnName == $dafuncselectwhere->targetTableColumnName &&
					$CandicateOfdafuncselectwhereData->ParameterType == $dafuncselectwhere->ParameterType &&
					(
						$CandicateOfdafuncselectwhereData->ParameterType == dafuncselectwhereParameterTypeEnum::$FIXED ||
						$CandicateOfdafuncselectwhereData->FixedParameter == $dafuncselectwhere->FixedParameter
					) &&
					$CandicateOfdafuncselectwhereData->AnotherTableName == $dafuncselectwhere->AnotherTableName &&
					$CandicateOfdafuncselectwhereData->AnotherTableAliasName == $dafuncselectwhere->AnotherTableAliasName &&
					$CandicateOfdafuncselectwhereData->AnotherFieldName == $dafuncselectwhere->AnotherFieldName)
				{
					$alreadyExists = true;
					
					$thisContainer = new CandicateOfdafuncselectwhereDataLinkContainer();
					$thisContainer->PID = $dafuncselectwhere->PID;
					$thisContainer->correspondingdafuncselectwhere = $dafuncselectwhere;
					array_push($correspondingdafuncselectwhereList, $thisContainer);
					
					// $CandicateOfdafuncselectwhereData->PID = $dafuncselectwhere->PID;
					// $correspondingdafuncselectwhere = $dafuncselectwhere;
				}
			}
			// Check Filter
			if (CheckIfSkipByShowFilterForInputAid($FilterBytargetTableName, $CandicateOfdafuncselectwhereData->targetTableName)) {
				continue;
			}
			if (CheckIfSkipByShowFilterForInputAid($FilterBytargetTableColumnName, $CandicateOfdafuncselectwhereData->targetTableColumnName)) {
				continue;
			}
			if (CheckIfSkipByShowFilterForInputAid($FilterByParameterType, $CandicateOfdafuncselectwhereData->ParameterType)) {
				continue;
			}
			if (CheckIfSkipByShowFilterForInputAid($FilterByAnotherTableName, $CandicateOfdafuncselectwhereData->AnotherTableName)) {
				continue;
			}
			if (CheckIfSkipByShowFilterForInputAid($FilterByAnotherFieldName, $CandicateOfdafuncselectwhereData->AnotherFieldName)) {
				continue;
			}
			if (CheckHideFilterForInputAid($ReverseFilterBytargetTableNameList, $CandicateOfdafuncselectwhereData->targetTableName)) {
				continue;
			}
			if (CheckHideFilterForInputAid($ReverseFilterBytargetTableColumnNameList, $CandicateOfdafuncselectwhereData->targetTableColumnName)) {
				continue;
			}
			if (CheckHideFilterForInputAid($ReverseFilterByParameterTypeList, $CandicateOfdafuncselectwhereData->ParameterType)) {
				continue;
			}
			if (CheckHideFilterForInputAid($ReverseFilterByAnotherTableNameList, $CandicateOfdafuncselectwhereData->AnotherTableName)) {
				continue;
			}
			if (CheckHideFilterForInputAid($ReverseFilterByAnotherFieldNameList, $CandicateOfdafuncselectwhereData->AnotherFieldName)) {
				continue;
			}
			
			if ($ShowAllPattern != "" || $alreadyExists || $showTargetHT[GetShowTargetHTKey($CandicateOfdafuncselectwhereData)]) {
				?>
				<tr>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->targetTableName); 
                  OutputFilterLinkTag("targetTableName", $CandicateOfdafuncselectwhereData->targetTableName,
				  				$FilterBytargetTableName, $ReverseFilterBytargetTableNameList); ?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->targetTableAliasName); ?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->targetTableColumnName); 
                  OutputFilterLinkTag("targetTableColumnName", $CandicateOfdafuncselectwhereData->targetTableColumnName,
				  				$FilterBytargetTableColumnName, $ReverseFilterBytargetTableColumnNameList); ?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->ParameterType); 
                  OutputFilterLinkTag("ParameterType", $CandicateOfdafuncselectwhereData->ParameterType,
				  				$FilterByParameterType, $ReverseFilterByParameterTypeList);?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->GetParameterDataTypeCaptionIfParameterTypeIsNotAnotherField()); ?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->AnotherTableName);  
                  OutputFilterLinkTag("AnotherTableName", $CandicateOfdafuncselectwhereData->AnotherTableName,
				  				$FilterByAnotherTableName, $ReverseFilterByAnotherTableNameList);?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->AnotherTableAliasName); ?></td>
				  <td><?php print htmlspecialchars($CandicateOfdafuncselectwhereData->AnotherFieldName);  
                  OutputFilterLinkTag("AnotherFieldName", $CandicateOfdafuncselectwhereData->AnotherFieldName,
				  				$FilterByAnotherFieldName, $ReverseFilterByAnotherFieldNameList);?></td>
				  <td><?php
					  for($j = 0 ; $j < count($correspondingdafuncselectwhereList) ; $j++) {
						  $correspondingdafuncselectwhere = $correspondingdafuncselectwhereList[$j]->correspondingdafuncselectwhere;
						  if ($j >= 1) { print "<br>"; }
						  print htmlspecialchars($correspondingdafuncselectwhere->GetFixedParameterCaptionIfParameterTypeIsFixed());
					  }
				  ?></td>
				  <td><?php
					  for($j = 0 ; $j < count($correspondingdafuncselectwhereList) ; $j++) {
						  $correspondingdafuncselectwhere = $correspondingdafuncselectwhereList[$j]->correspondingdafuncselectwhere;
						  if ($j >= 1) { print "<br>"; }
						  print htmlspecialchars($correspondingdafuncselectwhere->GetRelationalOperatorCaption()); 
					  }
				  ?></td>
				  <td><?php
					  for($j = 0 ; $j < count($correspondingdafuncselectwhereList) ; $j++) {
						  $correspondingdafuncselectwhere = $correspondingdafuncselectwhereList[$j]->correspondingdafuncselectwhere;
						  if ($j >= 1) { print "<br>"; }
						  print htmlspecialchars(GetdafuncselectwhereJoinTypeCaption($correspondingdafuncselectwhere->JoinType));
					  }
				  ?></td>
				  <td><?php
					  for($j = 0 ; $j < count($correspondingdafuncselectwhereList) ; $j++) {
						  $correspondingdafuncselectwhere = $correspondingdafuncselectwhereList[$j]->correspondingdafuncselectwhere;
						  if ($j >= 1) { print "<br>"; }
					  	  print htmlspecialchars($correspondingdafuncselectwhere->ORGroup);
					  }
				  ?></td>
				  <td><?php
					if ($alreadyExists) {
						?>
						<font color="red">Exist</font>
						<?php
					} else {
						?>
						Not Exist
						<?php
					}
					?></td>
				   <td>
                   <?php
					$thisPIDList = array();
					if (count($correspondingdafuncselectwhereList) > 0) {
						for($j = 0 ; $j < count($correspondingdafuncselectwhereList) ; $j++) {
							array_push($thisPIDList, $correspondingdafuncselectwhereList[$j]->PID);
						}
					}
					array_push($thisPIDList, "");
					
					for ($j = 0 ; $j < count($thisPIDList) ; $j++) {
						$thisPID = $thisPIDList[$j];
					    if ($j >= 1) { print "<br>"; }
						
						$linkCaption = "";
						if ($thisPID == "") {
							$linkCaption = "Add";
						} else {
							$linkCaption = "Edit";
						}
						?>
						<a href="da_func_select_where_edit.php?ProjectPID=<?php print urlencode($CandicateOfdafuncselectwhereData->ProjectPID); ?>&DAPID=<?php print urlencode($CandicateOfdafuncselectwhereData->daPID); ?>&DAFuncPID=<?php print urlencode($CandicateOfdafuncselectwhereData->dafuncPID); ?>&PID=<?php print urlencode($thisPID); ?>&targetTableName=<?php print urlencode($CandicateOfdafuncselectwhereData->targetTableName); ?>&targetTableAliasName=<?php print urlencode($CandicateOfdafuncselectwhereData->targetTableAliasName); ?>&targetTableColumnName=<?php print urlencode($CandicateOfdafuncselectwhereData->targetTableColumnName); ?>&ParameterType=<?php print urlencode($CandicateOfdafuncselectwhereData->ParameterType); ?>&FixedParameter=<?php print urlencode($CandicateOfdafuncselectwhereData->FixedParameter); ?>&AnotherTableName=<?php print urlencode($CandicateOfdafuncselectwhereData->AnotherTableName); ?>&AnotherTableAliasName=<?php print urlencode($CandicateOfdafuncselectwhereData->AnotherTableAliasName); ?>&AnotherFieldName=<?php print urlencode($CandicateOfdafuncselectwhereData->AnotherFieldName); ?>&<?php print makeRandStr(8); ?>">
						<?php
						print $linkCaption;
						?>
					   </a>
					   <?php
					}
					?>
				   </td>
				</tr>
				<?php
			}
		}
		?>
        	</tbody>
		</table>
        
        <form action="da_func_select_where_input_aid.php" method="post" id="changeoptionform">
			<?php
			for($i = 0 ; $i < count($DBTableList); $i++) {
				$thisDBTable = $DBTableList[$i];
				?>
				<input name="DBTableList[]" type="hidden" value="<?php print $thisDBTable; ?>">
				<?php
			}
			for($i = 0 ; $i < count($alltablelistinproject); $i++) {
				$thisdbtable = $alltablelistinproject[$i];
				
				if (array_key_exists($thisdbtable->name, $TargetTableAliasNameInfoList)) {
					$TargetTableAliasNameInfo = $TargetTableAliasNameInfoList[$thisdbtable->name];
					?>
					<input name="AnotherName<?php print $thisdbtable->PID; ?>" type="hidden" value="<?php print $TargetTableAliasNameInfo->GetAliasNameList(); ?>">
					<?php
				}
			}
		    ?>
        <input name="FilterBytargetTableName" id="FilterBytargetTableName" type="hidden" value="<?php print htmlspecialchars($FilterBytargetTableName); ?>">
        <input name="FilterBytargetTableColumnName" id="FilterBytargetTableColumnName" type="hidden" value="<?php print htmlspecialchars($FilterBytargetTableColumnName); ?>">
        <input name="FilterByParameterType" id="FilterByParameterType" type="hidden" value="<?php print htmlspecialchars($FilterByParameterType); ?>">
        <input name="FilterByAnotherTableName" id="FilterByAnotherTableName" type="hidden" value="<?php print htmlspecialchars($FilterByAnotherTableName); ?>">
        <input name="FilterByAnotherFieldName" id="FilterByAnotherFieldName" type="hidden" value="<?php print htmlspecialchars($FilterByAnotherFieldName); ?>">
        <input name="ReverseFilterBytargetTableName" id="ReverseFilterBytargetTableName" type="hidden" value="<?php print htmlspecialchars($ReverseFilterBytargetTableName); ?>">
        <input name="ReverseFilterBytargetTableColumnName" id="ReverseFilterBytargetTableColumnName" type="hidden" value="<?php print htmlspecialchars($ReverseFilterBytargetTableColumnName); ?>">
        <input name="ReverseFilterByParameterType" id="ReverseFilterByParameterType" type="hidden" value="<?php print htmlspecialchars($ReverseFilterByParameterType); ?>">
        <input name="ReverseFilterByAnotherTableName" id="ReverseFilterByAnotherTableName" type="hidden" value="<?php print htmlspecialchars($ReverseFilterByAnotherTableName); ?>">
        <input name="ReverseFilterByAnotherFieldName" id="ReverseFilterByAnotherFieldName" type="hidden" value="<?php print htmlspecialchars($ReverseFilterByAnotherFieldName); ?>">
        <input name="ShowAllPattern" id="ShowAllPattern" type="hidden" value="<?php print htmlspecialchars($ShowAllPattern); ?>">
        <input name="DoNext" type="hidden" value="Next">
        <input name="ProjectPID" type="hidden" value="<?php print $ProjectPID; ?>">
        <input name="DAPID" type="hidden" value="<?php print $DAPID; ?>">
        <input name="DAFuncPID" type="hidden" value="<?php print $DAFuncPID; ?>">
        </form>
        
		<?php
	} else {
		?>
<p>none</p>
		<?php
	}
	
	if ($DoNext != "") {
		if ($ShowAllPattern == "") {
		?>
        <h4>Now Showing only Indexed column patterns and already existing setting.</h4>
        <p align="right"><a id="showallpatternlink">Show All pattern</a></p>
<script>
$(function() {
	$("#showallpatternlink").click(function() {
		$("#ShowAllPattern").val("y");
		$("#changeoptionform").submit();
	});
});
</script>
			<?php
        } else {
            ?>
        <h4>Now Showing All patterns including non indexed column.</h4>
        <p align="right"><a id="showindexedcolumnpatternonlylink">Show only Indexed column pattern and already existing setting.</a></p>
<script>
$(function() {
	$("#showindexedcolumnpatternonlylink").click(function() {
		$("#ShowAllPattern").val("");
		$("#changeoptionform").submit();
	});
});
</script>
		<?php
		}
		?>
<script>
$(function() {
	$(".filterbycolumn").click(function() {
		var FilterType = $(this).attr("FilterType");
		var FilterTarget = $(this).attr("FilterTarget");
		var ReverseFilterTarget = $(this).attr("ReverseFilterTarget");
		var FilterValue = $(this).attr("FilterValue");
		switch(FilterType)
		{
			case "<?php print $WHERE_INPUT_AID_FILTER_TYPE_SHOW; ?>":
				$("#" + FilterTarget).val(FilterValue);
				break;
			case "<?php print $WHERE_INPUT_AID_FILTER_TYPE_HIDE; ?>":
				$("#" + ReverseFilterTarget).val($("#" + ReverseFilterTarget).val() + "," + FilterValue);
				break;
			case "<?php print $WHERE_INPUT_AID_FILTER_TYPE_RESET; ?>":
				$("#" + FilterTarget).val("");
				$("#" + ReverseFilterTarget).val("");
				break;
		}
		$("#changeoptionform").submit();
	});
});
</script>
        <?php
	}
	
	?>
    <br>
    <br>
    <br>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
    <?php
}
?>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content

