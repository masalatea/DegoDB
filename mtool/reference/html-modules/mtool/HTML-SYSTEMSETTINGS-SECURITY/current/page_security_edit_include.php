<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$EachPagePID = trim(GetParam("EachPagePID"));
$SERVER_NAME = trim(GetParam("SERVER_NAME"));
$SCRIPT_NAME = trim(GetParam("SCRIPT_NAME"));

// List
$SecurityTypeList = GetParam("SecurityTypeList");

$DAProjectSecurityForEachPageDetails = new ProjectSecurityForEachPageDetailsDBAccess();

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	?>
	<H3>Edit Page Security Setting</H3>
	<p>Server Name: <?php print htmlspecialchars($SERVER_NAME); ?></p>
	<p>Script Name: <?php print htmlspecialchars($SCRIPT_NAME); ?></p>
	<?php
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		
		$ProjectSecurityForEachPageDetailList = $DAProjectSecurityForEachPageDetails->GetProjectSecurityForEachPageDetailsList($SERVER_NAME, $SCRIPT_NAME);
		
		for($i = 0 ; $i < count($SecurityTypeList) ; $i++ ) {
			$SecurityType = $SecurityTypeList[$i];
			if ($SecurityType == "") {
				continue;
			}
			
			$IsAlreadyExist = false;
			if ($ProjectSecurityForEachPageDetailList) {
				for($j = 0 ; $j < count($ProjectSecurityForEachPageDetailList) ; $j++) {
					$ProjectSecurityForEachPageDetail = $ProjectSecurityForEachPageDetailList[$j];
					
					if ($SecurityType == $ProjectSecurityForEachPageDetail->SecurityType) {
						$IsAlreadyExist = true;
						break;
					}
				}
			}
			if (!$IsAlreadyExist) {
				$newdata = new ProjectSecurityForEachPageDetailsData();
				$newdata->EachPagePID = $EachPagePID;
				$newdata->PID = "";			// Auto Assign
				$newdata->SecurityType = $SecurityType;
				
				if ($DAProjectSecurityForEachPageDetails->InsertProjectSecurityForEachPageDetails($newdata)) {
					// Success
					if ($mtooldb->affected_rows > 0 ) {
						$updatedSomething = true;
					}
					
				} else {
					// Failed
					?>
					<h3><font color="red">Error! Failed to update</font></h3>
					<?php
					$needToLoad = false;
				}
			}
		}
		if ($ProjectSecurityForEachPageDetailList) {
			for($j = 0 ; $j < count($ProjectSecurityForEachPageDetailList) ; $j++) {
				$ProjectSecurityForEachPageDetail = $ProjectSecurityForEachPageDetailList[$j];
				
				$NeedToDelete = true;
				for($i = 0 ; $i < count($SecurityTypeList) ; $i++ ) {
					$SecurityType = $SecurityTypeList[$i];
					if ($SecurityType == "") {
						continue;
					}
					
					if ($SecurityType == $ProjectSecurityForEachPageDetail->SecurityType) {
						$NeedToDelete = false;
						break;
					}
				}
				if ($NeedToDelete) {
					if ($DAProjectSecurityForEachPageDetails->DeleteProjectSecurityForEachPageDetails($ProjectSecurityForEachPageDetail)) {
						// Success
						if ($mtooldb->affected_rows > 0 ) {
							$updatedSomething = true;
						}
					} else {
						// Failed
						?>
						<h3><font color="red">Error! Failed to update</font></h3>
						<?php
						$needToLoad = false;
					}
				}
			}
		}
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
			<h3><font color="red">Updated</font></h3>
			<?php
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		$ProjectSecurityForEachPageDetailList = $DAProjectSecurityForEachPageDetails->GetProjectSecurityForEachPageDetailsList($SERVER_NAME, $SCRIPT_NAME);
		
		$SecurityTypeList = array();
		for($j = 0 ; $j < count($ProjectSecurityForEachPageDetailList) ; $j++) {
			$ProjectSecurityForEachPageDetail = $ProjectSecurityForEachPageDetailList[$j];
			array_push($SecurityTypeList, $ProjectSecurityForEachPageDetail->SecurityType);
		}
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_UPDATE");
	$HeaderCaption = getres("ACTION_UPDATE_SECURITY_USER_DETAIL");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="page_security_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		$SecurityTypeSelectionList = GetAllSecurityTypeListOfProjectUser();
		
		$selectionList = array();
		for ($i = 0 ; $i < count($SecurityTypeSelectionList) ; $i++ ) {
			$thisSecurityTypeSelection = $SecurityTypeSelectionList[$i];
			array_push($selectionList,
					array("VALUE"=>$thisSecurityTypeSelection, "CAPTION"=>GetProjectUserSerurityCaption($thisSecurityTypeSelection))
				);
		}
		$MARGIN_SELECTION = 2;
		
		for ($i = 0 ; $i < count($SecurityTypeList) + $MARGIN_SELECTION ; $i++ ) {
			$SecurityType = "";
			if ($i < count($SecurityTypeList)) {
				$SecurityType = $SecurityTypeList[$i];
			}
			
			mtoolCommonFormSelect("SecurityTypeList[]", $SecurityType,
				array($LANG_ENGLISH=>"Security Type", $LANG_JAPANESE=>"セキュリティ種類"),
				array($LANG_ENGLISH=>"Please select Security Type", $LANG_JAPANESE=>"セキュリティ種類を選択して下さい"), 
				$selectionList
				, array(), "");
		}
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
		<input name="EachPagePID" type="hidden" value="<?php print htmlspecialchars($EachPagePID); ?>">
		<input name="SERVER_NAME" type="hidden" value="<?php print htmlspecialchars($SERVER_NAME); ?>">
		<input name="SCRIPT_NAME" type="hidden" value="<?php print htmlspecialchars($SCRIPT_NAME); ?>">
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
	<p><a href="page_security.php?<?php print makeRandStr(8); ?>">Back to Page Security List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
