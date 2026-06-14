<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$daCustomProxyFunc = new daCustomProxyFuncData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$daCustomProxyFunc->ProjectPID = trim(GetParam("ProjectPID"));
$daCustomProxyFunc->daCustomProxyPID = trim(GetParam("daCustomProxyPID"));
$daCustomProxyFunc->PID = trim(GetParam("daCustomProxyFuncPID"));
$daCustomProxyFunc->dafuncPID = trim(GetParam("dafuncPID"));
$daCustomProxyFunc->AddIndentCount = trim(GetParam("AddIndentCount"));
if (!is_numeric($daCustomProxyFunc->AddIndentCount)) {
	$daCustomProxyFunc->AddIndentCount = 0;
}
$daCustomProxyFunc->AddIndentType = trim(GetParam("AddIndentType"));

$daCustomProxyFunc->IsList = trim(GetParam("IsList"));
if (!is_numeric($daCustomProxyFunc->IsList)) {
	$daCustomProxyFunc->IsList = 0;
}

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($daCustomProxyFunc->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$daCustomProxyFunc->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($daCustomProxyFunc->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdaCustomProxyFunc = new daCustomProxyFuncDBAccess();
			$DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da = new daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccess();
			$insertResult = $DAdaCustomProxyFunc->InsertdaCustomProxyFunc($daCustomProxyFunc);
			// == END OF EDITABLE AREA FOR "Insert Data" ==
			if($insertResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Insert Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to insert</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Failed" ==
			} else {
				// Success
				$daCustomProxyFunc->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_PROXY_CUSTOM_FUNC"); ?></font></h3>
                <?php
				update_custom_proxy_LastModifiedDT($daCustomProxyFunc->daCustomProxyPID, $daCustomProxyFunc->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $daCustomProxyFunc->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($daCustomProxyFunc->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $daCustomProxyFunc->PID)) {
					// Success
					$insertToken = "";
				} else {
					// Failed
					?>
					<h3><font color="red">Internal Error! Failed to complete Insert</font></h3>
					<?php
				}
			}
		}
		
	} else if (is_numeric($daCustomProxyFunc->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdaCustomProxyFunc = new daCustomProxyFuncDBAccess();
		$DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da = new daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdaCustomProxyFunc->UpdatedaCustomProxyFunc($daCustomProxyFunc);
			// == END OF EDITABLE AREA FOR "Update Data" ==
			if($updateResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Update Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Failed" ==
				$needToLoad = false;
				
			} else {
				// Success
				// == START OF EDITABLE AREA FOR "Update Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_UPDATED_PROXY_CUSTOM_FUNC"); ?></font></h3>
                <?php
				update_custom_proxy_LastModifiedDT($daCustomProxyFunc->daCustomProxyPID, $daCustomProxyFunc->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdaCustomProxyFunc->DeletedaCustomProxyFunc($daCustomProxyFunc);
			// == END OF EDITABLE AREA FOR "Delete Data" ==
			if($deleteResult === FALSE) {
				// Failed
				// == START OF EDITABLE AREA FOR "Delete Data - Failed" ==
				?>
                <h3><font color="red">Error! Failed to delete</font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Failed" ==
				$needToLoad = false;
				
			} else {
				// Success
				// == START OF EDITABLE AREA FOR "Delete Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_DELETED_PROXY_CUSTOM_FUNC"); ?></font></h3>
                <?php
				update_custom_proxy_LastModifiedDT($daCustomProxyFunc->daCustomProxyPID, $daCustomProxyFunc->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$daCustomProxyFunc = $DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da->GetdaCustomProxyFunc_leftouterjoin_dafunc_and_da($daCustomProxyFunc->PID, $daCustomProxyFunc->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Class PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($daCustomProxyFunc->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_PROXY_CUSTOM_FUNC");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_PROXY_CUSTOM_FUNC");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $daCustomProxyFunc != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForProxyCustom("Proxy Target Setting [Custom, Multi]", $daCustomProxyFunc->ProjectPID, $daCustomProxyFunc->daCustomProxyPID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
<script>
var CheckAddIndentTypeAreaVisible = function()
{
	var AddIndentCountValue = $('#AddIndentCount').val();
	
	if (AddIndentCountValue > 0) {
		return true;
	}
	return false;
};
</script>        
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_proxy_custom_func_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		$functionselectionlist = array();
		
		$DAda = new daDBAccess();
		$DAdafunc = new dafuncDBAccess();
		
		$dalist = $DAda->GetdaList($daCustomProxyFunc->ProjectPID); 
		for($i = 0 ; $i < count($dalist); $i++) {
			$da = $dalist[$i];
			$dafunclist = $DAdafunc->GetdafuncList($daCustomProxyFunc->ProjectPID, $da->PID); 
			
			for($j = 0 ; $j < count($dafunclist); $j++) {
				$dafunc = $dafunclist[$j];
				
				$functionaname = $da->name . ": " . $dafunc->name . ": " . GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType);
				array_push($functionselectionlist, array("VALUE"=>$dafunc->PID, "CAPTION"=>$functionaname));
			}
		}
		mtoolCommonFormSelect("dafuncPID", $daCustomProxyFunc->dafuncPID,
			array($LANG_ENGLISH=>"Function", $LANG_JAPANESE=>"関数"),
			array($LANG_ENGLISH=>"Please select Function", $LANG_JAPANESE=>"関数を選択して下さい"), 
			$functionselectionlist, array(), "");
		mtoolCommonFormCheckBoxForValue("IsList", $daCustomProxyFunc->IsList,
			array($LANG_ENGLISH=>"Access Multiple times by List", $LANG_JAPANESE=>"リストで複数回アクセス"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		
		$AddIndentCountSelectionList = array();
		for($indent_count = 1; $indent_count <= 20; $indent_count++) {
			array_push($AddIndentCountSelectionList, array("VALUE"=>$indent_count, "CAPTION"=>$indent_count));
		}
		mtoolCommonFormSelect("AddIndentCount", $daCustomProxyFunc->AddIndentCount,
			array($LANG_ENGLISH=>"Add Indent Count", $LANG_JAPANESE=>"追加インデント数"),
			array($LANG_ENGLISH=>"Please select Indent Count to be added on Source to increase visivility", $LANG_JAPANESE=>"追加インデント数を選択して下さい。Stepをスコープで囲う場合に視認性を向上させます"), 
			$AddIndentCountSelectionList, array(
				array("VALUE"=>"", "SHOW"=>"AddIndentTypeArea", "CUSTOMFUNCTION"=>"CheckAddIndentTypeAreaVisible"),
			), "");
		mtoolCommonFormSelect("AddIndentType", $daCustomProxyFunc->AddIndentType,
			array($LANG_ENGLISH=>"Indent Type", $LANG_JAPANESE=>"インデント種類"),
			array($LANG_ENGLISH=>"Please select Indent Type", $LANG_JAPANESE=>"インデント種類を選択して下さい"), 
			array(
				array("VALUE"=>daCustomProxyFuncAddIndentTypeEnum::$DEFAULT, "CAPTION"=>GetCustomProxyFuncAddIndentTypeEnumCaption(daCustomProxyFuncAddIndentTypeEnum::$DEFAULT)),
				array("VALUE"=>daCustomProxyFuncAddIndentTypeEnum::$START, "CAPTION"=>GetCustomProxyFuncAddIndentTypeEnumCaption(daCustomProxyFuncAddIndentTypeEnum::$START)),
				array("VALUE"=>daCustomProxyFuncAddIndentTypeEnum::$END, "CAPTION"=>GetCustomProxyFuncAddIndentTypeEnumCaption(daCustomProxyFuncAddIndentTypeEnum::$END)),
				array("VALUE"=>daCustomProxyFuncAddIndentTypeEnum::$CONTINUE, "CAPTION"=>GetCustomProxyFuncAddIndentTypeEnumCaption(daCustomProxyFuncAddIndentTypeEnum::$CONTINUE))
			), array(), "AddIndentTypeArea");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($daCustomProxyFunc->PID != "") {
				?>
				<p align="right">
				<input name="DELETE" type="submit" value="<?php print htmlspecialchars(getres("ACTION_DELETE")); ?>" onClick="return confirm('<?php print htmlspecialchars(getres("ACTION_DELETE_CONFIRM")); ?>');">
				</p>
				<?php
			}
			?>
			</div>
		</div>
		<?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($daCustomProxyFunc->ProjectPID); ?>">
		<input name="daCustomProxyPID" type="hidden" value="<?php print htmlspecialchars($daCustomProxyFunc->daCustomProxyPID); ?>">
		<input name="daCustomProxyFuncPID" type="hidden" value="<?php print htmlspecialchars($daCustomProxyFunc->PID); ?>">
		<?php
		// == END OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="insertToken" type="hidden" value="<?php print htmlspecialchars($insertToken); ?>">
		</form>
		<?php
	}
	?>
	<br>
	<br>
	<br>
	<?php
	// == START OF EDITABLE AREA FOR "Bottom Links" ==
	?>
    <p><a href="da_proxy_custom_func.php?ProjectPID=<?php print urlencode($daCustomProxyFunc->ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxyFunc->daCustomProxyPID); ?>&<?php print makeRandStr(8); ?>">Back to Function List to be called</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
