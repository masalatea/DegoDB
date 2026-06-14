<?php

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
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
// == END OF EDITABLE AREA FOR "Check Data" ==

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$DAdafuncupdatedeletewhere = new dafuncupdatedeletewhereDBAccess();
$dafuncupdatedeletewherelist = $DAdafuncupdatedeletewhere->GetdafuncupdatedeletewhereList($ProjectPID, $DAPID, $DAFuncPID); 
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForDBAccessClass("Change Order of Update/Delete's Where", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	// == END OF EDITABLE AREA FOR "Path on Top" ==
	
	$updatedSomething = false;
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				// == START OF EDITABLE AREA FOR "Update Sort Order" ==
				if($DAdafuncupdatedeletewhere->UpdatedafuncupdatedeletewhereOrder($i + 1, $thisPID, $ProjectPID) === FALSE) {
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
					update_da_LastModifiedDT($DAPID, $ProjectPID);
				}
				// == END OF EDITABLE AREA FOR "Update Sort Order" ==
			}
			if ($updatedSomething) {
				// == START OF EDITABLE AREA FOR "Updated Something" ==
				?>
			<h3><font color="red"><?php print getres("ACTION_UPDATED_DA_FUNC_SORT_ORDER"); ?></font></h3>
				<?php
				// == END OF EDITABLE AREA FOR "Updated Something" ==
			}
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
		$dafuncupdatedeletewherelist = $DAdafuncupdatedeletewhere->GetdafuncupdatedeletewhereList($ProjectPID, $DAPID, $DAFuncPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
	}
	if ($doReset != "") {
		// == START OF EDITABLE AREA FOR "Reset Sort Order" ==
		for($i = 0 ; $i < count($dafuncupdatedeletewherelist); $i++) {
			$dafuncupdatedeletewhere = $dafuncupdatedeletewherelist[$i];
			
			if($DAdafuncupdatedeletewhere->UpdatedafuncupdatedeletewhereOrder("Default(WhereOrder)", $dafuncupdatedeletewhere->PID, $ProjectPID) === FALSE) {
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
				update_da_LastModifiedDT($DAPID, $ProjectPID);
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
		$dafuncupdatedeletewherelist = $DAdafuncupdatedeletewhere->GetdafuncupdatedeletewhereList($ProjectPID, $DAPID, $DAFuncPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
	}
	
	if (count($dafuncupdatedeletewherelist) > 0) {
		// == START OF EDITABLE AREA FOR "Main Table" ==
		$forSort = true;
		include_once("da_func_update_delete_where_table_include.php");
		// == END OF EDITABLE AREA FOR "Main Table" ==
		?>
		
		<form action="<?php print $_SERVER['SCRIPT_NAME']; ?>" method="post" id="orderupdateform"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
        <?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($DAPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($DAFuncPID); ?>">
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
    <p align="right"><a href="da_func_select_where_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($DAFuncPID); ?>&<?php print makeRandStr(8); ?>">Add New Select's Where</a></p>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
