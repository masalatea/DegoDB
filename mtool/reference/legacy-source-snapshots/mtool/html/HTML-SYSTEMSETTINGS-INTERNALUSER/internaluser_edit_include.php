<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$DELETE = trim(GetParam("DELETE"));

// Array Parameter
$UserIsSystemAdminList = GetParam("UserIsSystemAdminList");
$UserNameList = GetParam("UserNameList");
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==

if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
}

// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
$DAInternalUser = new InternalUserDBAccess();
$AllInternalUserList = NULL;

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {

	// == START OF EDITABLE AREA FOR "Path on Top" ==
	printPathOnTopForInternalUserSetting("Edit Internal User");
	// == END OF EDITABLE AREA FOR "Path on Top" ==

	$buttonCaption = "";
	$HeaderCaption = "";
	
	$updatedSomething = false;
	
	$needToLoad = true;
	
	if ($UPDATE != "") {
		// == START OF EDITABLE AREA FOR "Update Data" ==
		
		$AllInternalUserList = $DAInternalUser->GetInternalUserList();
		
		$UserDataList = array();
		for ($i = 0 ; $i < count($UserNameList); $i++) {
			$thisUserName = trim($UserNameList[$i]);
			$thisIsSystemAdmin = (trim($UserIsSystemAdminList[$i]) == 1);
			
			if ($thisUserName != "") {
				$thisUserData = new InternalUserData();
				$thisUserData->username = $thisUserName;
				if ($thisIsSystemAdmin) {
					$thisUserData->IsSystemAdmin = 1;
				} else {
					$thisUserData->IsSystemAdmin = 0;
				}
				array_push($UserDataList, $thisUserData);
			}
		}
		
		// Add/Update
		for ($i = 0 ; $i < count($UserDataList); $i++) {
			$thisUserData = $UserDataList[$i];
			
			$needToAdd = true;
			$needToUpdate = false;
			for($j = 0 ; $j < count($AllInternalUserList); $j++) {
				$thisInternalUser = $AllInternalUserList[$j];
				
				if ($thisInternalUser->username == $thisUserData->username) {
					$needToAdd = false;
					$needToUpdate = true;
					break;
				}
			}
			if ($needToAdd) {
				if($DAInternalUser->InsertInternalUser($thisUserData) === FALSE) {
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
				if($DAInternalUser->UpdateInternalUser($thisUserData) === FALSE) {
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
		for($j = 0 ; $j < count($AllInternalUserList); $j++) {
			$thisInternalUser = $AllInternalUserList[$j];
			
			$needToDelete = true;
			for ($i = 0 ; $i < count($UserDataList); $i++) {
				$thisUserData = $UserDataList[$i];
				
				if ($thisInternalUser->username == $thisUserData->username) {
					$needToDelete = false;
					break;
				}
			}
			if ($needToDelete) {
				if($DAInternalUser->DeleteInternalUser($thisInternalUser) === FALSE) {
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
		$AllInternalUserList = $DAInternalUser->GetInternalUserList();
	
		$UserNameList = array();
		$UserIsSystemAdminList = array();
		for($i = 0 ; $i < count($AllInternalUserList); $i++) {
			$thisInternalUser = $AllInternalUserList[$i];
			$thisUsername = trim($thisInternalUser->username);
			
			if ($thisUsername != "") {
				array_push($UserNameList, $thisUsername);
				array_push($UserIsSystemAdminList, $thisInternalUser->IsSystemAdmin);
			}
		}
		// == END OF EDITABLE AREA FOR "Get Data" ==
	}
	
	// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	$buttonCaption = getres("ACTION_UPDATE");
	$HeaderCaption = getres("ACTION_UPDATE_INTERNAL_USER");
	// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	
	if ($showForm) {
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="internaluser_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		$BLANKAREACOUNT = 10;
		
		for ($i = 0 ; $i < count($UserNameList) + $BLANKAREACOUNT; $i++) {
			$thisUserName = "";
			$thisIsSystemAdmin = "";
			if (is_array($UserNameList) && $i < count($UserNameList)) {
				$thisUserName = trim($UserNameList[$i]);
			}
			if (is_array($UserIsSystemAdminList) && $i < count($UserIsSystemAdminList)) {
				$thisIsSystemAdmin = trim($UserIsSystemAdminList[$i]);
			}
			mtoolCommonFormCheckBoxAndMultiInput("", true, array($LANG_ENGLISH=>"User Name", $LANG_JAPANESE=>"ユーザ名"), 
				array(
					array(MtoolCommonFormCheckBoxAndMultiInputType::$CheckBox, 3, "UserIsSystemAdminList[]", $i, $thisIsSystemAdmin, array($LANG_ENGLISH=>"System Admin", $LANG_JAPANESE=>"システム管理者"), NULL, NULL, 1),
					array(MtoolCommonFormCheckBoxAndMultiInputType::$Text, 6, "UserNameList[]", $thisUserName, array($LANG_ENGLISH=>"Please input User Name", $LANG_JAPANESE=>"ユーザ名を入力して下さい"), "text", "")
				));
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
	<p><a href="./?<?php print makeRandStr(8); ?>">Back to Internal User List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
