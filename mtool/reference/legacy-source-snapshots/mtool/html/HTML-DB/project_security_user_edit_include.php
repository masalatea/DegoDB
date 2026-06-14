<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$DELETE = trim(GetParam("DELETE"));
$ProjectPID = trim(GetParam("ProjectPID"));

// Array Parameter
$UserNameList = GetParam("UserNameList");
$UserIsOwnerList = GetParam("UserIsOwnerList");
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

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("project_user_default_permission_lib.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_project.php");

$DAProjectUser = new ProjectUserDBAccess();
$CurrentProjectUserList = NULL;
$ProjectAllOwnerAndUserList = NULL;	

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForDBTable("Edit Security User", $ProjectPID, "", "");
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		
		$CurrentProjectUserList = $DAProjectUser->GetProjectOwnerOrUserList($ProjectPID);
		$ProjectAllOwnerAndUserList = $DAProjectUser->GetProjectOwnerOrUserList($ProjectPID);
		
		$UserDataList = array();
		for ($i = 0 ; $i < count($UserNameList); $i++) {
			$thisUserName = trim($UserNameList[$i]);
			$thisIsOwner = (trim($UserIsOwnerList[$i]) == "t");
			
			if ($thisUserName != "") {
				$thisUserData = new ProjectUserData();
				$thisUserData->ProjectPID = $ProjectPID;
				$thisUserData->username = $thisUserName;
				if ($thisIsOwner) {
					$thisUserData->IsOwner = "t";
				} else {
					$thisUserData->IsOwner = "f";
				}
				array_push($UserDataList, $thisUserData);
			}
		}
		
		// Add/Update
		for ($i = 0 ; $i < count($UserDataList); $i++) {
			$thisUserData = $UserDataList[$i];
			
			$needToAdd = true;
			$needToUpdate = false;
			for($j = 0 ; $j < count($ProjectAllOwnerAndUserList); $j++) {
				$thisProjectUser = $ProjectAllOwnerAndUserList[$j];
				
				if ($thisProjectUser->username == $thisUserData->username) {
					$needToAdd = false;
					$needToUpdate = true;
					break;
				}
			}
			if ($needToAdd) {
				set_default_user_permission($thisUserData);
				if($DAProjectUser->InsertProjectOwnerOrUser($thisUserData) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to add user info. Please ask administrator if this continues.</font></h3>
					<?php
					$needToLoad = false;
					
				} else {
					// Success
					if ($mtooldb->affected_rows > 0 ) {
						$updatedSomething = true;
					}
				}
			}
			if ($needToUpdate) {
				// Update
				if($DAProjectUser->UpdateProjectOwnerOrUserBasicInfo($thisUserData) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to update user info. Please ask administrator if this continues.</font></h3>
					<?php
					$needToLoad = false;
					
				} else {
					// Success
					if ($mtooldb->affected_rows > 0 ) {
						$updatedSomething = true;
					}
				}
			}
		}
		
		// Delete
		for($j = 0 ; $j < count($ProjectAllOwnerAndUserList); $j++) {
			$thisProjectUser = $ProjectAllOwnerAndUserList[$j];
			
			$needToDelete = true;
			for ($i = 0 ; $i < count($UserDataList); $i++) {
				$thisUserData = $UserDataList[$i];
				
				if ($thisProjectUser->username == $thisUserData->username) {
					$needToDelete = false;
					break;
				}
			}
			if ($needToDelete) {
				if($DAProjectUser->DeleteProjectOwnerOrUser($thisProjectUser) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to delete owner info. Please ask administrator if this continues.</font></h3>
					<?php
					$needToLoad = false;
					
				} else {
					// Success
					if ($mtooldb->affected_rows > 0 ) {
						$updatedSomething = true;
					}
				}
			}
		}
		// == END OF EDITABLE AREA FOR "Update Data" ==
		
		if ($updatedSomething) {
			// == START OF EDITABLE AREA FOR "Updated Something" ==
			?>
			<h3><font color="red">User was updated</font></h3>
			<?php
			// == END OF EDITABLE AREA FOR "Updated Something" ==
		}
	}
	if ($needToLoad) {
		// == START OF EDITABLE AREA FOR "Get Data" ==
		$CurrentProjectUserList = $DAProjectUser->GetProjectOwnerOrUserList($ProjectPID);
	
		$UserNameList = array();
		$UserEmailList = array();
		$UserFileShareModeList = array();
		for($i = 0 ; $i < count($CurrentProjectUserList); $i++) {
			$thisProjectUser = $CurrentProjectUserList[$i];
			$thisUsername = trim($thisProjectUser->username);
			
			if ($thisUsername != "") {
				array_push($UserNameList, $thisUsername);
			}
		}
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_UPDATE");
	$HeaderCaption = getres("ACTION_UPDATE_PROJECT_SECURITY_USER");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="project_security_user_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		$ProjectAllOwnerAndUserList = $DAProjectUser->GetProjectOwnerOrUserList($ProjectPID);
		
		$BLANKAREACOUNT = 10;
		
		for ($i = 0 ; $i < count($UserNameList) + $BLANKAREACOUNT; $i++) {
			$thisUserName = "";
			$thisIsOwnerValue = "";
			if (is_array($UserNameList) && $i < count($UserNameList)) {
				$thisUserName = trim($UserNameList[$i]);
				
				for($j = 0 ; $j < count($ProjectAllOwnerAndUserList) ; $j++) {
					$ProjectAllOwnerAndUser = $ProjectAllOwnerAndUserList[$j];
					
					if ($thisUserName == $ProjectAllOwnerAndUser->username) {
						$thisIsOwnerValue = $ProjectAllOwnerAndUser->IsOwner;
						break;
					}
				}
			}
			mtoolCommonFormCheckBoxAndMultiInput("", true, array($LANG_ENGLISH=>"User Name", $LANG_JAPANESE=>"ユーザ名"), 
				array(
					array(MtoolCommonFormCheckBoxAndMultiInputType::$Text, 6, "UserNameList[]", $thisUserName, array($LANG_ENGLISH=>"Please input User Name", $LANG_JAPANESE=>"ユーザ名を入力して下さい"), "text", ""),
					array(MtoolCommonFormCheckBoxAndMultiInputType::$CheckBox, 3, "UserIsOwnerList[]", $i, $thisIsOwnerValue, array($LANG_ENGLISH=>"Admin", $LANG_JAPANESE=>"管理者"), NULL, NULL, "t")
				)
			);
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
	<p><a href="project_security_detail.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Security List</a> / <a href="./project_security.php?<?php print makeRandStr(8); ?>">Back to Project Security List</a> / <a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>

	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
