<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$DropboxSetting = new DropboxSettingData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$DropboxSetting->PID = trim(GetParam("PID"));
$DropboxSetting->name = trim(GetParam("name"));
$DropboxSetting->IsPublic = trim(GetParam("IsPublic"));
if (!is_numeric($DropboxSetting->IsPublic)) {
	$DropboxSetting->IsPublic = 0;
}
$DropboxSetting->DropboxAppKey = trim(GetParam("DropboxAppKey"));
$DropboxSetting->DropboxAppSecret = trim(GetParam("DropboxAppSecret"));
$DropboxSetting->Oauth2RedirectUrl = trim(GetParam("Oauth2RedirectUrl"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
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
		$DropboxSetting->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($DropboxSetting->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DADropboxSetting = new DropboxSettingDBAccess();
			$insertResult = $DADropboxSetting->InsertDropboxSetting($DropboxSetting);
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
				$DropboxSetting->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_DROPBOX_SETTING"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $DropboxSetting->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($DropboxSetting->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $DropboxSetting->PID)) {
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
		
	} else if (is_numeric($DropboxSetting->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DADropboxSetting = new DropboxSettingDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DADropboxSetting->UpdateDropboxSetting($DropboxSetting);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_DROPBOX_SETTING"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DADropboxSetting->DeleteDropboxSetting($DropboxSetting);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_DROPBOX_SETTING"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$DropboxSetting = $DADropboxSetting->GetDropboxSetting($DropboxSetting->PID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! HTML Template PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($DropboxSetting->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_DROPBOX_SETTING");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DROPBOX_SETTING");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $DropboxSetting != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDropboxSetting($HeaderCaption);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="dropbox_setting_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		mtoolCommonFormInput("name", $DropboxSetting->name,
			array($LANG_ENGLISH=>"Name", $LANG_JAPANESE=>"名前"),
			array($LANG_ENGLISH=>"Please input Name", $LANG_JAPANESE=>"名前を入力して下さい。"),
			"text", "");
		mtoolCommonFormInput("DropboxAppKey", $DropboxSetting->DropboxAppKey,
			array($LANG_ENGLISH=>"App Key", $LANG_JAPANESE=>"App Key"),
			array($LANG_ENGLISH=>"Please input App Key", $LANG_JAPANESE=>"App Keyを入力して下さい。"),
			"text", "");
		mtoolCommonFormInput("DropboxAppSecret", $DropboxSetting->DropboxAppSecret,
			array($LANG_ENGLISH=>"App Secret", $LANG_JAPANESE=>"App Secret"),
			array($LANG_ENGLISH=>"Please input App Secret", $LANG_JAPANESE=>"App Secretを入力して下さい。"),
			"password", "");
		mtoolCommonFormInput("Oauth2RedirectUrl", $DropboxSetting->Oauth2RedirectUrl,
			array($LANG_ENGLISH=>"Redirect URL", $LANG_JAPANESE=>"Redirect URL"),
			array($LANG_ENGLISH=>"Please input Redirect URL", $LANG_JAPANESE=>"Redirect URLを入力して下さい。"),
			"text", "");
		mtoolCommonFormCheckBoxForValue("IsPublic", $DropboxSetting->IsPublic,
			array($LANG_ENGLISH=>"Public?", $LANG_JAPANESE=>"一般向け?"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($DropboxSetting->PID != "") {
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
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($DropboxSetting->PID); ?>">
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
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to Dropbox Setting List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
