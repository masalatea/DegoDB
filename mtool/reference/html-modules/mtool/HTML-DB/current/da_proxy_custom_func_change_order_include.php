<?php

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = GetParam("ProjectPID");
$daCustomProxyPID = GetParam("daCustomProxyPID");
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$DAProject = new ProjectDBAccess();
$project = $DAProject->GetProject($ProjectPID);
	
$IncludeProxy = CheckIfProjectIncludeProxy($ProjectPID);
	
$DAdaCustomProxy = new daCustomProxyDBAccess();
$daCustomProxy = $DAdaCustomProxy->GetdaCustomProxy($daCustomProxyPID, $ProjectPID);
	
$DAdaCustomProxyFunc = new daCustomProxyFuncDBAccess();
$DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da = new daCustomProxyFunc_leftouterjoin_dafunc_and_daDBAccess();
$daCustomProxyFuncList = $DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da->GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList($ProjectPID, $daCustomProxyPID);
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForProxyCustom("Proxy Target Function List to be called [Custom, Multi]", $ProjectPID, $daCustomProxyPID);
	// == END OF EDITABLE AREA FOR "Path on Top" ==
	
	$updatedSomething = false;
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				// == START OF EDITABLE AREA FOR "Update Sort Order" ==
				if($DAdaCustomProxyFunc->UpdatedaCustomProxyFuncFunctionListOrder($i + 1, $thisPID, $ProjectPID) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to update. something strange. Please ask administrator if this continues.</font></h3>
					<?php
					$needToLoad = false;
					
				} else {
					// Success
					if ($mtooldb->affected_rows > 0 ) {
						$updatedSomething = true;
					}
					update_custom_proxy_LastModifiedDT($daCustomProxyPID, $ProjectPID);
				}
				// == END OF EDITABLE AREA FOR "Update Sort Order" ==
			}
			if ($updatedSomething) {
				// == START OF EDITABLE AREA FOR "Updated Something" ==
				?>
			<h3><font color="red"><?php print getres("ACTION_UPDATED_PROXY_CUSTOM_FUNC_CHANGE_ORDER_SORT_ORDER"); ?></font></h3>
				<?php
				// == END OF EDITABLE AREA FOR "Updated Something" ==
			}
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
		$daCustomProxyFuncList = $DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da->GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList($ProjectPID, $daCustomProxyPID);
		// == END OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
	}
	if ($doReset != "") {
		// == START OF EDITABLE AREA FOR "Reset Sort Order" ==
		for($i = 0 ; $i < count($daCustomProxyFuncList); $i++) {
			$daCustomProxyFunc = $daCustomProxyFuncList[$i];
			
			if($DAdaCustomProxyFunc->UpdatedaCustomProxyFuncFunctionListOrder("Default(FunctionListOrder)", $daCustomProxyFunc->PID, $ProjectPID) === FALSE) {
				// Failed
				?>
				<h3><font color="red">Error! Failed to update. something strange. Please ask administrator if this continues.</font></h3>
				<?php
				$needToLoad = false;
				
			} else {
				// Success
				if ($mtooldb->affected_rows > 0 ) {
					$updatedSomething = true;
				}
				update_custom_proxy_LastModifiedDT($daCustomProxyPID, $ProjectPID);
			}
		}
		// == END OF EDITABLE AREA FOR "Reset Sort Order" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Reseted Sort Order" ==
			?>
		<h3><font color="red">Reset Sort Order</font></h3>
			<?php
			// == END OF EDITABLE AREA FOR "Reseted Sort Order" ==
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
		$daCustomProxyFuncList = $DAdaCustomProxyFunc_leftouterjoin_dafunc_and_da->GetdaCustomProxyFunc_leftouterjoin_dafunc_and_daList($ProjectPID, $daCustomProxyPID);
		// == END OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
	}
	
	if (count($daCustomProxyFuncList) > 0) {
		// == START OF EDITABLE AREA FOR "Main Table" ==
		$for_list = false;
		$for_sort = !$for_list;
		include_once("da_proxy_custom_func_table_include.php");
		// == END OF EDITABLE AREA FOR "Main Table" ==
		?>
		
		<form action="<?php print $_SERVER['SCRIPT_NAME']; ?>" method="post" id="orderupdateform"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
        <?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
		<input name="daCustomProxyPID" type="hidden" value="<?php print htmlspecialchars($daCustomProxyPID); ?>">
		<?php
		// == END OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
        <input name="NewSortOrder"  id="NewSortOrder" type="hidden" value="">
        <input name="submitbutton" type="button" id="submitbutton" value="UPDATE">
        <input name="doReset" type="submit" id="doReset" value="RESET">
		</form>
        

<script>
$(function() {
	$("#sortablebodyarea").sortable({
		cursor: 'move',
		opacity: 0.7,
		placeholder: 'ui-state-highlight',
	});
	$("#submitbutton").click(function() {
		var result = $("#sortablebodyarea").sortable("toArray").join(',');
		$("#NewSortOrder").val(result);
		// set_style_display("submitbutton", "none");
		// set_style_display("submitingarea", "inline");
		$("#orderupdateform").submit();
	});
});
</script>
        
		<?php
	} else {
		?>
<p>none</p>
		<?php
	}
	?>
    <br>
    <br>
    <br>
	<?php
	// == START OF EDITABLE AREA FOR "Bottom Links" ==
	?>
	<p align="right"><a href="da_proxy_custom_func_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxyPID); ?>&<?php print makeRandStr(8); ?>">Add functions to be called</a></p>
    <p><a href="da_proxy_custom_func.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&daCustomProxyPID=<?php print urlencode($daCustomProxyPID); ?>&<?php print makeRandStr(8); ?>">Back to Function list to be called</a></p>
    <p><a href="da_proxy_custom.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Proxy Target Setting [Multi, Custom] List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
