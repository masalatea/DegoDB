<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$ProjectGroupTemplate = new ProjectGroupTemplateData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ProjectGroupTemplate->PID = trim(GetParam("PID"));
$ProjectGroupTemplate->ProjectGroupType = trim(GetParam("ProjectGroupType"));
$ProjectGroupTemplate->ProjectGroupNamePrefix = trim(GetParam("ProjectGroupNamePrefix"));
$ProjectGroupTemplate->SettingGroupPID = trim(GetParam("SettingGroupPID"));
$ProjectGroupTemplate->MainServerPID = trim(GetParam("MainServerPID"));
$ProjectGroupTemplate->ServerPID = trim(GetParam("ServerPID"));
$ProjectGroupTemplate->DropboxSettingPID = trim(GetParam("DropboxSettingPID"));
$ProjectGroupTemplate->ApacheHostSettingTemplatePID = trim(GetParam("ApacheHostSettingTemplatePID"));
$ProjectGroupTemplate->DropboxBaseDir = trim(GetParam("DropboxBaseDir"));
$ProjectGroupTemplate->LocalBaseDir = trim(GetParam("LocalBaseDir"));
$ProjectGroupTemplate->ProxyBaseURL = trim(GetParam("ProxyBaseURL"));
$ProjectGroupTemplate->UploaderURLSuffix = trim(GetParam("UploaderURLSuffix"));
$ProjectGroupTemplate->DBManagerURLSuffix = trim(GetParam("DBManagerURLSuffix"));
$ProjectGroupTemplate->proxy_header_of_access_control_allow_origin = trim(GetParam("proxy_header_of_access_control_allow_origin"));
$ProjectGroupTemplate->proxy_header_of_access_control_allow_headers = trim(GetParam("proxy_header_of_access_control_allow_headers"));
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

function GetProjectGroupTemplateSelectionList($from, $upto)
{
	$result = array();
	for($num = $from ; $num <= $upto; $num++) {
		array_push($result, array("VALUE"=>$num, "CAPTION"=>$num));
	}
	return $result;
}

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$ProjectGroupTemplate->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($ProjectGroupTemplate->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAProjectGroupTemplate = new ProjectGroupTemplateDBAccess();
			$insertResult = $DAProjectGroupTemplate->InsertProjectGroupTemplate($ProjectGroupTemplate);
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
				$ProjectGroupTemplate->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_PROJECT_GROUP_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $ProjectGroupTemplate->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($ProjectGroupTemplate->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $ProjectGroupTemplate->PID)) {
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
		
	} else if (is_numeric($ProjectGroupTemplate->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAProjectGroupTemplate = new ProjectGroupTemplateDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAProjectGroupTemplate->UpdateProjectGroupTemplate($ProjectGroupTemplate);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_PROJECT_GROUP_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAProjectGroupTemplate->DeleteProjectGroupTemplate($ProjectGroupTemplate);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_PROJECT_GROUP_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$ProjectGroupTemplate = $DAProjectGroupTemplate->GetProjectGroupTemplate($ProjectGroupTemplate->PID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! ProjectGroupTemplate PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($ProjectGroupTemplate->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_PROJECT_GROUP_TEMPLATE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_PROJECT_GROUP_TEMPLATE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $ProjectGroupTemplate != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForProjectGroupTemplateSetting($HeaderCaption);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="projectgroup_template_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormSelect("ProjectGroupType", $ProjectGroupTemplate->ProjectGroupType,
			array($LANG_ENGLISH=>"Project Group Type", $LANG_JAPANESE=>"プロジェクトグループ種類"),
			array($LANG_ENGLISH=>"Please select Project Group Type", $LANG_JAPANESE=>"プロジェクトグループ種類を選択して下さい"), 
			array(
				array("VALUE"=>ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX, "CAPTION"=>GetProjectGroupTemplateProjectGroupTypeCaption(ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX)),
				array("VALUE"=>ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER, "CAPTION"=>GetProjectGroupTemplateProjectGroupTypeCaption(ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER)),
				array("VALUE"=>ProjectGroupTemplateProjectGroupTypeEnum::$VPS, "CAPTION"=>GetProjectGroupTemplateProjectGroupTypeCaption(ProjectGroupTemplateProjectGroupTypeEnum::$VPS))
			) , array(), "");
		mtoolCommonFormInput("ProjectGroupNamePrefix", $ProjectGroupTemplate->ProjectGroupNamePrefix,
			array($LANG_ENGLISH=>"Project Group Name Prefix", $LANG_JAPANESE=>"プロジェクトグループ名・接頭語"),
			array($LANG_ENGLISH=>"Please input Project Group Name Prefix", $LANG_JAPANESE=>"プロジェクトグループ名・接頭語を入力して下さい"),
			"text", "");
		mtoolCommonFormInput("SettingGroupPID", $ProjectGroupTemplate->SettingGroupPID,
			array($LANG_ENGLISH=>"Setting Group's PID", $LANG_JAPANESE=>"設定グループPID"),
			array($LANG_ENGLISH=>"Please input Setting Group's PID", $LANG_JAPANESE=>"設定グループPIDを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("MainServerPID", $ProjectGroupTemplate->MainServerPID,
			array($LANG_ENGLISH=>"Main Server's PID", $LANG_JAPANESE=>"主サーバのPID"),
			array($LANG_ENGLISH=>"Please input Main Server's PID", $LANG_JAPANESE=>"主サーバのPIDを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("ServerPID", $ProjectGroupTemplate->ServerPID,
			array($LANG_ENGLISH=>"Server's PID", $LANG_JAPANESE=>"サーバのPID"),
			array($LANG_ENGLISH=>"Please input Server's PID", $LANG_JAPANESE=>"サーバのPIDを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("DropboxSettingPID", $ProjectGroupTemplate->DropboxSettingPID,
			array($LANG_ENGLISH=>"Dropbox Setting PID", $LANG_JAPANESE=>"Dropbox設定PID"),
			array($LANG_ENGLISH=>"Please input Dropbox Setting PID", $LANG_JAPANESE=>"Dropbox設定PIDを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("ApacheHostSettingTemplatePID", $ProjectGroupTemplate->ApacheHostSettingTemplatePID,
			array($LANG_ENGLISH=>"Apache Host Setting Template's PID", $LANG_JAPANESE=>"Apacheホスト設定テンプレートのPID"),
			array($LANG_ENGLISH=>"Please input Apache Host Setting Template's PID", $LANG_JAPANESE=>"Apacheホスト設定テンプレートのPIDを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("DropboxBaseDir", $ProjectGroupTemplate->DropboxBaseDir,
			array($LANG_ENGLISH=>"Dropbox Base Dir", $LANG_JAPANESE=>"Dropbox Base Dir"),
			array($LANG_ENGLISH=>"Please input Dropbox Base Dir", $LANG_JAPANESE=>"Dropbox Base Dirを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("LocalBaseDir", $ProjectGroupTemplate->LocalBaseDir,
			array($LANG_ENGLISH=>"Local Base Dir", $LANG_JAPANESE=>"Local Base Dir"),
			array($LANG_ENGLISH=>"Please input Local Base Dir", $LANG_JAPANESE=>"Local Base Dirを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("ProxyBaseURL", $ProjectGroupTemplate->ProxyBaseURL,
			array($LANG_ENGLISH=>"Proxy Base URL", $LANG_JAPANESE=>"Proxy Base URL"),
			array($LANG_ENGLISH=>"Please input Proxy Base URL", $LANG_JAPANESE=>"Proxy Base URLを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("UploaderURLSuffix", $ProjectGroupTemplate->UploaderURLSuffix,
			array($LANG_ENGLISH=>"Uploader URL Suffix", $LANG_JAPANESE=>"Uploader URL Suffix"),
			array($LANG_ENGLISH=>"Please input Uploader URL Suffix", $LANG_JAPANESE=>"Uploader URL Suffixを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("DBManagerURLSuffix", $ProjectGroupTemplate->DBManagerURLSuffix,
			array($LANG_ENGLISH=>"DB Manager URL Suffix", $LANG_JAPANESE=>"DB Manager URL Suffix"),
			array($LANG_ENGLISH=>"Please input DB Manager URL Suffix", $LANG_JAPANESE=>"DB Manager URL Suffixを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("proxy_header_of_access_control_allow_origin", $ProjectGroupTemplate->proxy_header_of_access_control_allow_origin,
			array($LANG_ENGLISH=>"Access Control Allow Origin Header for Proxy Access", $LANG_JAPANESE=>"Proxy用Header: Access Control Allow Origin"),
			array($LANG_ENGLISH=>"Please input Access Control Allow Origin Header for Proxy Access.  e.g. * or http://www.hogehoge.com", $LANG_JAPANESE=>"Proxy用Header: Access Control Allow Originを入力して下さい。例: * あるいは http://www.hogehoge.com"),
			"text", "");
		mtoolCommonFormInput("proxy_header_of_access_control_allow_headers", $ProjectGroupTemplate->proxy_header_of_access_control_allow_headers,
			array($LANG_ENGLISH=>"Access Control Allow Headers for Proxy Access", $LANG_JAPANESE=>"Proxy用Header: Access Control Allow Headers"),
			array($LANG_ENGLISH=>"Please input Access Control Allow Headers for Proxy Access (Access-Control-Allow-Origin). e.g. Origin, X-Requested-With, Content-Type, Accept", $LANG_JAPANESE=>"Proxy用Header Access Control Allow Headers(Access-Control-Allow-Origin)を入力して下さい。例: Origin, X-Requested-With, Content-Type, Accept"),
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($ProjectGroupTemplate->PID != "") {
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
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($ProjectGroupTemplate->PID); ?>">
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
    <p><a href="./projectgroup_templates.php?<?php print makeRandStr(8); ?>">Back to Project Group Template List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
