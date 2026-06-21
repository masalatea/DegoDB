<?php
include_once($MTOOL_LIB . "/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
			<?php
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="__NAME__.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			</div>
		</div>
		<?php
		// == START OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Hidden Parameters" ==
		?>
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
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
