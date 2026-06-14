<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectPID = trim(GetParam("ProjectPID"));

// List
$ThisSettingList = GetParam("ThisSettingList");

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==

$DAProjectHostSetting = new ProjectHostSettingDBAccess();
$ProjectHostSettingList = NULL;

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForProjectHostAssignment(getres("TITLE_PROJECT_HOST_ASSIGNMENT_EDIT"), $ProjectPID);
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		
		$ProjectHostSettingList = $DAProjectHostSetting->GetProjectHostSettingByProjectList($ProjectPID);
		
		for($i = 0 ; $i < count($ThisSettingList) ; $i++ ) {
			$ThisSetting = $ThisSettingList[$i];
			if ($ThisSetting == "") {
				continue;
			}
			$thisApacheSettingPID = "";
			$thisApacheHostSettingPID = "";
			if (preg_match("/^(\d+)\-(\d+)$/", $ThisSetting, $matches)) {
				$thisApacheSettingPID     = $matches[1];
				$thisApacheHostSettingPID = $matches[2];
			} else {
				continue;
			}
			
			$IsAlreadyExist = false;
			if ($ProjectHostSettingList) {
				for($j = 0 ; $j < count($ProjectHostSettingList) ; $j++) {
					$ProjectHostSetting = $ProjectHostSettingList[$j];
					
					if ($thisApacheSettingPID     == $ProjectHostSetting->ApacheSettingPID &&
					    $thisApacheHostSettingPID == $ProjectHostSetting->ApacheHostSettingPID) {
						$IsAlreadyExist = true;
						break;
					}
				}
			}
			if (!$IsAlreadyExist) {
				$newdata = new ProjectHostSettingData();
				$newdata->ProjectPID = $ProjectPID;
				$newdata->PID = "";			// Auto Assign
				$newdata->ApacheSettingPID = $thisApacheSettingPID;
				$newdata->ApacheHostSettingPID = $thisApacheHostSettingPID;
				
				if ($DAProjectHostSetting->InsertProjectHostSetting($newdata)) {
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
		if ($ProjectHostSettingList) {
			for($j = 0 ; $j < count($ProjectHostSettingList) ; $j++) {
				$ProjectHostSetting = $ProjectHostSettingList[$j];
				
				$NeedToDelete = true;
				for($i = 0 ; $i < count($ThisSettingList) ; $i++ ) {
					$ThisSetting = $ThisSettingList[$i];
					if ($ThisSetting == "") {
						continue;
					}
					$thisApacheSettingPID = "";
					$thisApacheHostSettingPID = "";
					if (preg_match("/^(\d+)\-(\d+)$/", $ThisSetting, $matches)) {
						$thisApacheSettingPID     = $matches[1];
						$thisApacheHostSettingPID = $matches[2];
					} else {
						continue;
					}
					
					if ($thisApacheSettingPID     == $ProjectHostSetting->ApacheSettingPID &&
					    $thisApacheHostSettingPID == $ProjectHostSetting->ApacheHostSettingPID) {
						$NeedToDelete = false;
						break;
					}
				}
				if ($NeedToDelete) {
					if ($DAProjectHostSetting->DeleteProjectHostSetting($ProjectHostSetting)) {
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
		$ProjectHostSettingList = $DAProjectHostSetting->GetProjectHostSettingByProjectList($ProjectPID);
		
		$ThisSettingList = array();
		for($j = 0 ; $j < count($ProjectHostSettingList) ; $j++) {
			$ProjectHostSetting = $ProjectHostSettingList[$j];
			array_push($ThisSettingList, $ProjectHostSetting->ApacheSettingPID . "-" . $ProjectHostSetting->ApacheHostSettingPID);
		}
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_UPDATE");
	$HeaderCaption = getres("ACTION_UPDATE_HOST_ASSIGNMENT");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="project_host_assignment_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		$DAApacheHostSetting = new ApacheHostSettingDBAccess();
		$AllApacheHostSettingList = $DAApacheHostSetting->GetAllList();
		
		$selectionList = array();
		for ($i = 0 ; $i < count($AllApacheHostSettingList) ; $i++ ) {
			$ApacheHostSetting = $AllApacheHostSettingList[$i];
			array_push($selectionList,
					array("VALUE"=>$ApacheHostSetting->ApacheSettingPID . "-" . $ApacheHostSetting->PID, "CAPTION"=>$ApacheHostSetting->ServerLocalServerName . ": " . $ApacheHostSetting->VirtualHostName . " : " . $ApacheHostSetting->ApacheHostSettingTemplatename)
				);
		}
		$MARGIN_SELECTION = 5;
		
		$ProjectHostSettingList = $DAProjectHostSetting->GetProjectHostSettingByProjectList($ProjectPID);
		for ($i = 0 ; $i < count($ProjectHostSettingList) + $MARGIN_SELECTION ; $i++ ) {
			$ThisSetting = "";
			if ($i < count($ProjectHostSettingList)) {
				$ThisSetting = $ProjectHostSettingList[$i]->ApacheSettingPID . "-" . $ProjectHostSettingList[$i]->ApacheHostSettingPID;
			}
			
			mtoolCommonFormSelect("ThisSettingList[]", $ThisSetting,
				array($LANG_ENGLISH=>"Host Setting", $LANG_JAPANESE=>"Host設定"),
				array($LANG_ENGLISH=>"Please select Host Setting", $LANG_JAPANESE=>"Host設定を選択して下さい"), 
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
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
	<p><a href="project_host_assignment.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Host Assignment List</a> / <a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
