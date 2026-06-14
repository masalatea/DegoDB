<?php

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = trim(GetParam("ProjectPID"));
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForDBTable("Change Order of Project's Source Output Setting", $ProjectPID, "", "");
	// == END OF EDITABLE AREA FOR "Path on Top" ==
	
	$updatedSomething = false;
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				// == START OF EDITABLE AREA FOR "Update Sort Order" ==
				if($DAProjectSourceOutput->UpdateProjectSourceOutputListOrder($i + 1, $thisPID, $ProjectPID) === FALSE) {
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
				}
				// == END OF EDITABLE AREA FOR "Update Sort Order" ==
			}
			if ($updatedSomething) {
				// == START OF EDITABLE AREA FOR "Updated Something" ==
				?>
			<h3><font color="red"><?php print getres("ACTION_UPDATED_PROJECT_SOURCE_OUTPUT_SORT_ORDER"); ?></font></h3>
				<?php
				// == END OF EDITABLE AREA FOR "Updated Something" ==
			}
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
		$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
	}
	if ($doReset != "") {
		// == START OF EDITABLE AREA FOR "Reset Sort Order" ==
		for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
			$ProjectSourceOutput = $ProjectSourceOutputList[$i];
			
			if($DAProjectSourceOutput->UpdateProjectSourceOutputListOrder("Default(ProjectSourceOutputListOrder)", $ProjectSourceOutput->PID, $ProjectPID) === FALSE) {
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
		$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($ProjectPID); 
		// == END OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
	}
	
	if (count($ProjectSourceOutputList) > 0) {
		// == START OF EDITABLE AREA FOR "Main Table" ==
		$forSort = true;
		$forEdit = false;
		include_once("project_source_output_table_include.php");
		// == END OF EDITABLE AREA FOR "Main Table" ==
		?>
		
		<form action="<?php print $_SERVER['SCRIPT_NAME']; ?>" method="post" id="orderupdateform"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
        <?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
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
	<p><a href="project_source_output.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Source Output Setting</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
