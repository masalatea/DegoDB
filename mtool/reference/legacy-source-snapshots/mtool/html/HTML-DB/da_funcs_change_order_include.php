<?php

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
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
// == END OF EDITABLE AREA FOR "Check Data" ==

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$DAdafunc = new dafuncDBAccess();
$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $DAPID); 
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForDBAccessClass("Change Order of Functions", $ProjectPID, $DAPID, "", "", "", "", "", "");
	// == END OF EDITABLE AREA FOR "Path on Top" ==
	
	$updatedSomething = false;
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				// == START OF EDITABLE AREA FOR "Update Sort Order" ==
				if($DAdafunc->UpdatedafuncFunctionListOrder($i + 1, $thisPID, $ProjectPID) === FALSE) {
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
		$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $DAPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
	}
	if ($doReset != "") {
		// == START OF EDITABLE AREA FOR "Reset Sort Order" ==
		for($i = 0 ; $i < count($dafunclist); $i++) {
			$dafunc = $dafunclist[$i];
			
			if($DAdafunc->UpdatedafuncFunctionListOrder("Default(FunctionListOrder)", $dafunc->PID, $ProjectPID) === FALSE) {
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
		$dafunclist = $DAdafunc->GetdafuncList($ProjectPID, $DAPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
	}
	
	if (count($dafunclist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
              <th></th>
			  <?php
				// == START OF EDITABLE AREA FOR "Table Header" ==
				?>
			  <th>Name<br>
<font size="-2">[DB Access Function Name in Source]</font></th>
			  <th>Action Type</th>
				<?php
				// == END OF EDITABLE AREA FOR "Table Header" ==
			  ?>
			</tr>
          </thead>
            <tbody id="sortablebodyarea">
		<?php

		// == START OF EDITABLE AREA FOR "Table Body" ==
		for($i = 0 ; $i < count($dafunclist); $i++) {
			$dafunc = $dafunclist[$i];
			
			?>
			<tr id="<?php print $dafunc->PID; ?>">
              <td><?php print ($i + 1); ?></td>
			  <td><?php print htmlspecialchars($dafunc->name); ?>
              <br>
			  <font size="-2">[<?php print htmlspecialchars(GetFunctionNameFromFunctionActionType($dafunc->name, $dafunc->ActionType)); 
			  ?>]</font></td>
			  <td><?php print htmlspecialchars(GetDAFuncActionTypeCaption($dafunc->ActionType)); ?></td>
			</tr>
			<?php
		}
		// == END OF EDITABLE AREA FOR "Table Body" ==
		?>
        	</tbody>
		</table>
		<form action="<?php print $_SERVER['SCRIPT_NAME']; ?>" method="post" id="orderupdateform"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
        <?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($DAPID); ?>">
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
    <p><a href="da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
    <p><a href="./da.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Class List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
