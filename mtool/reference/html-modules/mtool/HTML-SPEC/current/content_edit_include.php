<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$SpecContent = new SpecContentData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$SpecContent->ProjectPID = trim(GetParam("ProjectPID"));
$SpecContent->SpecPID = trim(GetParam("SpecPID"));
$SpecContent->PID = trim(GetParam("ContentPID"));
$SpecContent->Depth = trim(GetParam("Depth"));
$SpecContent->Title = trim(GetParam("Title"));
$SpecContent->Description = trim(GetParam("Description"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($SpecContent->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($SpecContent->SpecPID)) {
	?>
    <H3><font color="red">Spec is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$SpecContent->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($SpecContent->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DASpecContent = new SpecContentDBAccess();
			$insertResult = $DASpecContent->InsertSpecContent($SpecContent);
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
				$SpecContent->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_CONTENT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $SpecContent->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($SpecContent->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $SpecContent->PID)) {
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
		
	} else if (is_numeric($SpecContent->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DASpecContent = new SpecContentDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DASpecContent->UpdateSpecContent($SpecContent);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_CONTENT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DASpecContent->DeleteSpecContent($SpecContent);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_CONTENT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$SpecContent = $DASpecContent->GetSpecContent($SpecContent->PID, $SpecContent->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! Content PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($SpecContent->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_CONTENT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_CONTENT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $SpecContent != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForSpec($HeaderCaption, $SpecContent->ProjectPID, $SpecContent->SpecPID, $SpecContent->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="content_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		$TEXTAREA_MARGIN_ROW_COUNT = 2;
		
		$selections = array();
		for($depth = 1 ; $depth <= $CONTENT_DEPTH_MAX ; $depth++) {
			array_push($selections,
				array("VALUE"=>$depth, "CAPTION"=>GetDepthCaptionCommon($depth))
				);
		}
		mtoolCommonFormSelect("Depth", $SpecContent->Depth,
			array($LANG_ENGLISH=>"Content's Depth", $LANG_JAPANESE=>"コンテントの深さ"),
			array($LANG_ENGLISH=>"Please select Content's Depth", $LANG_JAPANESE=>"コンテントの深さを選択して下さい"),
			$selections,
			array(), "");
		mtoolCommonFormInput("Title", $SpecContent->Title,
			array($LANG_ENGLISH=>"Content Title", $LANG_JAPANESE=>"タイトル名"),
			array($LANG_ENGLISH=>"Please input Content Title", $LANG_JAPANESE=>"タイトル名を入力して下さい"),
			"text", "");
		mtoolCommonFormTextAreaWithRowCount("Description", $SpecContent->Description,
			array($LANG_ENGLISH=>"Description", $LANG_JAPANESE=>"本文"),
			array($LANG_ENGLISH=>"Please input Description", $LANG_JAPANESE=>"本文を入力して下さい"),
			"", CountLineCount($SpecContent->Description) + $TEXTAREA_MARGIN_ROW_COUNT);
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($SpecContent->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($SpecContent->ProjectPID); ?>">
		<input name="SpecPID" type="hidden" value="<?php print htmlspecialchars($SpecContent->SpecPID); ?>">
		<input name="ContentPID" type="hidden" value="<?php print htmlspecialchars($SpecContent->PID); ?>">
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
    <p><a href="contents.php?ProjectPID=<?php print urlencode($SpecContent->ProjectPID); ?>&SpecPID=<?php print urlencode($SpecContent->SpecPID); ?>&<?php print makeRandStr(8); ?>">Back to Content List</a></p>
	<br>
	<br>
	<br>
	<?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/spec/footer_back_link_include.php");
	print_footer_back_link($SpecContent->ProjectPID);
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
