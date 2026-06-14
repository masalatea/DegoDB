<?php

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = trim(GetParam("ProjectPID"));
$DataClassPID = trim(GetParam("DataClassPID"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DataClassPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Data Class PID</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$DAdataclassfields = new dataclassfieldsDBAccess();
$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $DataClassPID); 
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForDataClasses("Change Order of Data Class's Field", $ProjectPID, $DataClassPID, "");
	// == END OF EDITABLE AREA FOR "Path on Top" ==
	
	$updatedSomething = false;
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				// == START OF EDITABLE AREA FOR "Update Sort Order" ==
				if($DAdataclassfields->UpdatedataclassfieldsFieldListOrder($i + 1001, $thisPID, $ProjectPID) === FALSE) {
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
					update_dataclass_LastModifiedDT($DataClassPID, $ProjectPID);
				}
				// == END OF EDITABLE AREA FOR "Update Sort Order" ==
			}
			if ($updatedSomething) {
				// == START OF EDITABLE AREA FOR "Updated Something" ==
				?>
			<h3><font color="red"><?php print getres("ACTION_UPDATED_DATA_CLASS_SORT_ORDER"); ?></font></h3>
				<?php
				// == END OF EDITABLE AREA FOR "Updated Something" ==
			}
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
		$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $DataClassPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
	}
	if ($doReset != "") {
		// == START OF EDITABLE AREA FOR "Reset Sort Order" ==
		for($i = 0 ; $i < count($dataclassfieldlist); $i++) {
			$dataclassfield = $dataclassfieldlist[$i];
			
			if($DAdataclassfields->UpdatedataclassfieldsFieldListOrder("Default(FieldListOrder)", $dataclassfield->PID, $ProjectPID) === FALSE) {
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
				update_dataclass_LastModifiedDT($DataClassPID, $ProjectPID);
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
		$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $DataClassPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
	}
	
	if (count($dataclassfieldlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
              <th></th>
			  <?php
				// == START OF EDITABLE AREA FOR "Table Header" ==
				?>
			  <th>Name</th>
			  <th>Data Type<br>
<font size="-2">(For C#, not for PHP)</font></th>
				<?php
				// == END OF EDITABLE AREA FOR "Table Header" ==
			  ?>
			</tr>
          </thead>
            <tbody id="sortablebodyarea">
		<?php

		// == START OF EDITABLE AREA FOR "Table Body" ==
		for($i = 0 ; $i < count($dataclassfieldlist); $i++) {
			$dataclassfield = $dataclassfieldlist[$i];
			?>
			<tr id="<?php print $dataclassfield->PID; ?>">
			  <td><?php print htmlspecialchars($dataclassfield->name); ?></td>
			  <td><?php OutputShortenedStringWithExpansion($dataclassfield->datatype, 20); ?></td>
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
		<input name="DataClassPID" type="hidden" value="<?php print htmlspecialchars($DataClassPID); ?>">
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
    <p><a href="dataclass_fields.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($DataClassPID); ?>&<?php print makeRandStr(8); ?>">Back to Data Class Field List</a></p>
    <p><a href="./dataclasses.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Data Class List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
