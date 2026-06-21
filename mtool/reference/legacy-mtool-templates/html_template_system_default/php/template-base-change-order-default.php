<?php

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
// == END OF EDITABLE AREA FOR "Check Data" ==

// == START OF EDITABLE AREA FOR "Initialize Common" ==
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	// == START OF EDITABLE AREA FOR "Path on Top" ==
	// == END OF EDITABLE AREA FOR "Path on Top" ==
	
	$updatedSomething = false;
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				// == START OF EDITABLE AREA FOR "Update Sort Order" ==
				// == END OF EDITABLE AREA FOR "Update Sort Order" ==
			}
			if ($updatedSomething) {
				// == START OF EDITABLE AREA FOR "Updated Something" ==
				?>
				<?php
				// == END OF EDITABLE AREA FOR "Updated Something" ==
			}
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
		// == END OF EDITABLE AREA FOR "Initialize Again after Updating Sort Order" ==
	}
	if ($doReset != "") {
		// == START OF EDITABLE AREA FOR "Reset Sort Order" ==
		// == END OF EDITABLE AREA FOR "Reset Sort Order" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Reseted Sort Order" ==
			?>
			<?php
			// == END OF EDITABLE AREA FOR "Reseted Sort Order" ==
		}
		// == START OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
		// == END OF EDITABLE AREA FOR "Initialize Again after Reseting Sort Order" ==
	}
	
	if (count($__LIST_OBJECT_NAME__) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
              <th></th>
			  <?php
				// == START OF EDITABLE AREA FOR "Table Header" ==
				?>
				<?php
				// == END OF EDITABLE AREA FOR "Table Header" ==
			  ?>
			</tr>
          </thead>
            <tbody id="sortablebodyarea">
		<?php

		// == START OF EDITABLE AREA FOR "Table Body" ==
		// == END OF EDITABLE AREA FOR "Table Body" ==
		?>
        	</tbody>
		</table>
		<form action="<?php print $_SERVER['SCRIPT_NAME']; ?>" method="post" id="orderupdateform"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
        <?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
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
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
