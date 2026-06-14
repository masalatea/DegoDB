<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$project = new ProjectData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$project->PID = trim(GetParam("ProjectPID"));
$project->name = trim(GetParam("name"));
$project->StorageType = trim(GetParam("StorageType"));
$project->DropboxBaseFolderPID = trim(GetParam("DropboxBaseFolderPID"));
$project->DBType = trim(GetParam("DBType"));
$project->DBUserPID = trim(GetParam("DBUserPID"));
$project->SQLServerConnectionString = trim(GetParam("SQLServerConnectionString"));
$project->SettingDir = trim(GetParam("SettingDir"));
$project->DBManagerURL = trim(GetParam("DBManagerURL"));
$project->TokenForProxyAccess = trim(GetParam("TokenForProxyAccess"));
$project->proxy_header_of_access_control_allow_origin = trim(GetParam("proxy_header_of_access_control_allow_origin"));
$project->proxy_header_of_access_control_allow_headers = trim(GetParam("proxy_header_of_access_control_allow_headers"));
$project->option_automatically_create_simple_proxy = trim(GetParam("option_automatically_create_simple_proxy"));
if (!is_numeric($project->option_automatically_create_simple_proxy)) {
	$project->option_automatically_create_simple_proxy = 0;
}
$project->option_automatically_create_custom_proxy = trim(GetParam("option_automatically_create_custom_proxy"));
if (!is_numeric($project->option_automatically_create_custom_proxy)) {
	$project->option_automatically_create_custom_proxy = 0;
}
$project->option_show_proxy_link = trim(GetParam("option_show_proxy_link"));
if (!is_numeric($project->option_show_proxy_link)) {
	$project->option_show_proxy_link = 0;
}
$project->option_auto_upload_after_build = trim(GetParam("option_auto_upload_after_build"));
if (!is_numeric($project->option_auto_upload_after_build)) {
	$project->option_auto_upload_after_build = 0;
}
$project->option_show_source = trim(GetParam("option_show_source"));
if (!is_numeric($project->option_show_source)) {
	$project->option_show_source = 0;
}
$project->option_show_detail = trim(GetParam("option_show_detail"));
if (!is_numeric($project->option_show_detail)) {
	$project->option_show_detail = 0;
}
$project->option_show_recommended_column_warning = trim(GetParam("option_show_recommended_column_warning"));
if (!is_numeric($project->option_show_recommended_column_warning)) {
	$project->option_show_recommended_column_warning = 0;
}
$project->option_all_source_include = trim(GetParam("option_all_source_include"));
if (!is_numeric($project->option_all_source_include)) {
	$project->option_all_source_include = 0;
}
$project->option_user_can_change_da_func_order = trim(GetParam("option_user_can_change_da_func_order"));
if (!is_numeric($project->option_user_can_change_da_func_order)) {
	$project->option_user_can_change_da_func_order = 0;
}
$project->option_show_source_output_setting = trim(GetParam("option_show_source_output_setting"));
if (!is_numeric($project->option_show_source_output_setting)) {
	$project->option_show_source_output_setting = 0;
}
$project->option_restrict_proxy_server_to_single = trim(GetParam("option_restrict_proxy_server_to_single"));
if (!is_numeric($project->option_restrict_proxy_server_to_single)) {
	$project->option_restrict_proxy_server_to_single = 0;
}
$project->option_show_language_resource = trim(GetParam("option_show_language_resource"));
if (!is_numeric($project->option_show_language_resource)) {
	$project->option_show_language_resource = 0;
}
$project->option_build_dataclass_for_proxy_client_only_if_proxy_exist = trim(GetParam("option_build_dataclass_for_proxy_client_only_if_proxy_exist"));
if (!is_numeric($project->option_build_dataclass_for_proxy_client_only_if_proxy_exist)) {
	$project->option_build_dataclass_for_proxy_client_only_if_proxy_exist = 0;
}
$project->option_show_output_temp_folder_on_build = trim(GetParam("option_show_output_temp_folder_on_build"));
if (!is_numeric($project->option_show_output_temp_folder_on_build)) {
	$project->option_show_output_temp_folder_on_build = 0;
}
$project->option_default_output_temp_folder_on_build_is_true = trim(GetParam("option_default_output_temp_folder_on_build_is_true"));
if (!is_numeric($project->option_default_output_temp_folder_on_build_is_true)) {
	$project->option_default_output_temp_folder_on_build_is_true = 0;
}
$project->option_IsCompareOutputTarget = trim(GetParam("option_IsCompareOutputTarget"));
if (!is_numeric($project->option_IsCompareOutputTarget)) {
	$project->option_IsCompareOutputTarget = 0;
}

$NOVALUECHANGE = "****NOVALUECHANGE****";

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_project.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_edit.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbuser_edit.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dbconnection_edit.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_base_folder_edit.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$project->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($project->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAProject = new ProjectDBAccess();
			$insertResult = $DAProject->InsertProject($project);
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
				$project->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				$newUserData = new ProjectUserData();
				$newUserData->ProjectPID = $project->PID;
				$newUserData->username = $matsuesoft_login_token_id;
				$newUserData->IsOwner = 't';
				set_default_user_permission($newUserData);
				
				$DAProjectUser = new ProjectUserDBAccess();
				if($DAProjectUser->InsertProjectOwnerOrUser($newUserData) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to add owner info. Please ask administrator if this continues.</font></h3>
					<?php
					
				} else {
					// Success
					?>
					<h3><font color="red"><?php print getres("ACTION_ADDED_PROJECT"); ?></font></h3>
					<?php
				}
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $project->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($project->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $project->PID)) {
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
		
	} else if (is_numeric($project->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAProject = new ProjectDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			if (CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
				$updateResult = $DAProject->UpdateProjectForAdmin($project);
			} else {
				$updateResult = $DAProject->UpdateProject($project);
			}
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
				<h3><font color="red"><?php print getres("ACTION_UPDATED_PROJECT"); ?></font></h3>
				<?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAProject->DeleteProject($project->PID);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_PROJECT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$project = $DAProject->GetProject($project->PID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! Project PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($project->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_PROJECT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_PROJECT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $project != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBTable($HeaderCaption, $project->PID, "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="project_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $project->name,
			array($LANG_ENGLISH=>"Name", $LANG_JAPANESE=>"名前"),
			array($LANG_ENGLISH=>"Please input Project Name", $LANG_JAPANESE=>"名前を入力して下さい"),
			"text", "");
		mtoolCommonFormSelect("StorageType", $project->StorageType,
			array($LANG_ENGLISH=>"Storage Type", $LANG_JAPANESE=>"格納先種類"),
			array($LANG_ENGLISH=>"Please select Storage Type", $LANG_JAPANESE=>"格納先種類を選択して下さい"), 
			array(
				array("VALUE"=>ProjectStorageTypeEnum::$DROPBOX, "CAPTION"=>GetProjectStorageTypeCaption(ProjectStorageTypeEnum::$DROPBOX))
			), array(
				array("VALUE"=>ProjectStorageTypeEnum::$DROPBOX, "SHOW"=>"DropboxBaseFolderArea")
			), "");
		
		$label_for_DropboxBaseFolderPID = array($LANG_ENGLISH=>"Dropbox Base Folder as a Default", $LANG_JAPANESE=>"デフォルトのDropbox Base Folder設定");
		$label_for_DBType = array($LANG_ENGLISH=>"DB Type", $LANG_JAPANESE=>"DB種類");
		$label_for_DBUserPID = array($LANG_ENGLISH=>"DB & User", $LANG_JAPANESE=>"接続先DBとユーザ");
		$label_for_SQLServerConnectionString = array($LANG_ENGLISH=>"SQL Server's Connection String", $LANG_JAPANESE=>"SQL Serverの接続文字列");
		if (CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
			mtoolCommonFormSelect("DropboxBaseFolderPID", $project->DropboxBaseFolderPID,
				$label_for_DropboxBaseFolderPID,
				array($LANG_ENGLISH=>"Please select Dropbox Base Folder as a Default", $LANG_JAPANESE=>"デフォルトのDropbox Base Folder設定を選択して下さい"), 
				GetDropboxBaseFolderSelectionListForEditAnySettingGroup($matsuesoft_login_token_id)
				, array(), "DropboxBaseFolderArea");
			mtoolCommonFormSelect("DBType", $project->DBType,
				$label_for_DBType,
				array($LANG_ENGLISH=>"Please select DB Type", $LANG_JAPANESE=>"DB種類を選択して下さい"), 
				array(
					array("VALUE"=>ProjectDBTypeEnum::$MYSQLONCLOUD, "CAPTION"=>GetProjectDBTypeCaption(ProjectDBTypeEnum::$MYSQLONCLOUD)),
					array("VALUE"=>ProjectDBTypeEnum::$SQLSERVER, "CAPTION"=>GetProjectDBTypeCaption(ProjectDBTypeEnum::$SQLSERVER))
				), array(
					array("VALUE"=>ProjectDBTypeEnum::$MYSQLONCLOUD, "SHOW"=>"DBUserPIDArea"),
					array("VALUE"=>ProjectDBTypeEnum::$SQLSERVER, "SHOW"=>"SQLServerConnectionStringArea")
				), "");
			mtoolCommonFormSelect("DBUserPID", $project->DBUserPID,
				$label_for_DBUserPID,
				array($LANG_ENGLISH=>"Please select DB & User", $LANG_JAPANESE=>"接続先DBとユーザを選択して下さい"), 
				GetDBUserSelectionListForEdit($matsuesoft_login_token_id)
				, array(), "DBUserPIDArea");
			if ($project->DBType == ProjectDBTypeEnum::$SQLSERVER) {
				mtoolCommonFormInput("SQLServerConnectionString", $project->SQLServerConnectionString,
					$label_for_SQLServerConnectionString,
					array($LANG_ENGLISH=>"Please input SQL Server's Connection String", $LANG_JAPANESE=>"SQL Serverの接続文字列を入力して下さい。"),
					"text", "SQLServerConnectionStringArea");
			}
		} else {
			// Other User
			
			$DropboxBaseFolderNameCaption = "";
			$DADropboxBaseFolder = new DropboxBaseFolderDBAccess();
			$DropboxBaseFolder = $DADropboxBaseFolder->GetDropboxBaseFolderForAnySettingGroup($project->DropboxBaseFolderPID);
			if ($DropboxBaseFolder) {
				$DropboxBaseFolderNameCaption = $DropboxBaseFolder->Name;
			}
			mtoolCommonFormComment($label_for_DropboxBaseFolderPID, $DropboxBaseFolderNameCaption, "", "");
			
			mtoolCommonFormComment($label_for_DBType, GetProjectDBTypeCaption($project->DBType), "", "");
			
			if($project->DBType == ProjectDBTypeEnum::$MYSQLONCLOUD) {
				$DBNameAndUserNameCaption = "";
				$DADBUser = new DBUserDBAccess();
				$DBUser = $DADBUser->GetDBUserForAnySettingGroup($project->DBUserPID);
				if ($DBUser) {
					$DBNameAndUserNameCaption = "DB Name: " . $DBUser->DBConnectionDBName . " User: " . $DBUser->User;
				}
				mtoolCommonFormComment($label_for_DBUserPID, $DBNameAndUserNameCaption, "", "");
			}
			if ($project->DBType == ProjectDBTypeEnum::$SQLSERVER) {
				mtoolCommonFormComment($label_for_SQLServerConnectionString, $project->SQLServerConnectionString, "", "");
			}
		}
		mtoolCommonFormInput("SettingDir", $project->SettingDir,
			array($LANG_ENGLISH=>"Setting Dir (on Dropbox)", $LANG_JAPANESE=>"設定保存先のパス(Dropbox上のパス)"),
			array($LANG_ENGLISH=>"Please input Setting Dir (on Storege such as DropBox. Use default if blank)", $LANG_JAPANESE=>"設定保存先のパスを入力して下さい。格納先(DropBox等)のパス。空の場合は規定が使用されます。"),
			"text", "");
		mtoolCommonFormInput("DBManagerURL", $project->DBManagerURL,
			array($LANG_ENGLISH=>"DB Manager URL", $LANG_JAPANESE=>"DBマネージャURL"),
			array($LANG_ENGLISH=>"Please input DB Manager URL", $LANG_JAPANESE=>"DBマネージャURLを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("TokenForProxyAccess", $project->TokenForProxyAccess,
			array($LANG_ENGLISH=>"Token for Proxy Access", $LANG_JAPANESE=>"Proxy用TOKEN"),
			array($LANG_ENGLISH=>"Please input Token for Proxy Access", $LANG_JAPANESE=>"Proxy用TOKENを入力して下さい"),
			"text", "");
		mtoolCommonFormInput("proxy_header_of_access_control_allow_origin", $project->proxy_header_of_access_control_allow_origin,
			array($LANG_ENGLISH=>"Access Control Allow Origin Header for Proxy Access", $LANG_JAPANESE=>"Proxy用Header: Access Control Allow Origin"),
			array($LANG_ENGLISH=>"Please input Access Control Allow Origin Header for Proxy Access (Access-Control-Allow-Origin).  e.g. * or https://www.hogehoge.com (Default if Empty: $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_ORIGIN)", $LANG_JAPANESE=>"Proxy用Header: Access Control Allow Origin(Access-Control-Allow-Origin)を入力して下さい。例: * あるいは https://www.hogehoge.com (未設定の場合のデフォルト: $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_ORIGIN)"),
			"text", "");
		mtoolCommonFormInput("proxy_header_of_access_control_allow_headers", $project->proxy_header_of_access_control_allow_headers,
			array($LANG_ENGLISH=>"Access Control Allow Headers for Proxy Access", $LANG_JAPANESE=>"Proxy用Header: Access Control Allow Headers"),
			array($LANG_ENGLISH=>"Please input Access Control Allow Headers for Proxy Access (Access-Control-Allow-Headers). e.g. Origin, X-Requested-With, Content-Type, Accept (Default if Empty: $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_HEADERS)", $LANG_JAPANESE=>"Proxy用Header Access Control Allow Headers(Access-Control-Allow-Headers)を入力して下さい。例: Origin, X-Requested-With, Content-Type, Accept (未設定の場合のデフォルト: $DEFAULT_PROXY_HEADER_OF_ACCESS_CONTROL_ALLOW_HEADERS)"),
			"text", "");
		mtoolCommonFormCheckBoxForValue("option_automatically_create_simple_proxy", $project->option_automatically_create_simple_proxy,
			array($LANG_ENGLISH=>"[OPTION] Create Simple Proxy Automatically when create DB func", $LANG_JAPANESE=>"[オプション] 関数追加時 単一対象Proxy自動追加"),
			array($LANG_ENGLISH=>"Set automatically", $LANG_JAPANESE=>"自動追加する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_automatically_create_custom_proxy", $project->option_automatically_create_custom_proxy,
			array($LANG_ENGLISH=>"[OPTION] Assign Custom Proxy Automatically when create Custom Proxy", $LANG_JAPANESE=>"[オプション] カスタムProxy追加時 カスタムProxy自動アサイン"),
			array($LANG_ENGLISH=>"Assign automatically", $LANG_JAPANESE=>"自動アサインする"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_proxy_link", $project->option_show_proxy_link,
			array($LANG_ENGLISH=>"[OPTION] Show Proxy Setting", $LANG_JAPANESE=>"[オプション] Proxy設定を表示"),
			array($LANG_ENGLISH=>"Show", $LANG_JAPANESE=>"表示"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_auto_upload_after_build", $project->option_auto_upload_after_build,
			array($LANG_ENGLISH=>"[OPTION] Upload automatically after build (only same target folder)", $LANG_JAPANESE=>"[オプション] ビルド後に自動アップロード (ビルド対象フォルダのみ)"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_source", $project->option_show_source,
			array($LANG_ENGLISH=>"[OPTION] Show Source", $LANG_JAPANESE=>"[オプション] ソース表示"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_detail", $project->option_show_detail,
			array($LANG_ENGLISH=>"[OPTION] Show Detailed Information", $LANG_JAPANESE=>"[オプション] 詳細情報表示"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_recommended_column_warning", $project->option_show_recommended_column_warning,
			array($LANG_ENGLISH=>"[OPTION] Show warning for recommended column", $LANG_JAPANESE=>"[オプション] 推奨カラム構成に関する警告を表示"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_all_source_include", $project->option_all_source_include,
			array($LANG_ENGLISH=>"[OPTION] Include all Source", $LANG_JAPANESE=>"[オプション] 全ソースをInclude"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_user_can_change_da_func_order", $project->option_user_can_change_da_func_order,
			array($LANG_ENGLISH=>"[OPTION] User can change DA Func Order", $LANG_JAPANESE=>"[オプション] データアクセス関数の並び順変更可能"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"可能"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_source_output_setting", $project->option_show_source_output_setting,
			array($LANG_ENGLISH=>"[OPTION] Show Source Output Setting", $LANG_JAPANESE=>"[オプション] Source Output Setting設定を表示する"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_restrict_proxy_server_to_single", $project->option_restrict_proxy_server_to_single,
			array($LANG_ENGLISH=>"[OPTION] Restrict Proxy Server to Single on Source Output Setting", $LANG_JAPANESE=>"[オプション] Source Output SettingでProxy Serverを１つに制限"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_language_resource", $project->option_show_language_resource,
			array($LANG_ENGLISH=>"[OPTION] Show Language Resource Setting", $LANG_JAPANESE=>"[オプション] 言語リソース設定を表示"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"する"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_build_dataclass_for_proxy_client_only_if_proxy_exist", $project->option_build_dataclass_for_proxy_client_only_if_proxy_exist,
			array($LANG_ENGLISH=>"[OPTION] Build Data Class for Proxy Client only if Proxy exist", $LANG_JAPANESE=>"[オプション] Proxyが存在する場合のみProxyクライアント向けデータクラスのソースをビルドする"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_show_output_temp_folder_on_build", $project->option_show_output_temp_folder_on_build,
			array($LANG_ENGLISH=>"[OPTION] Show OUTPUT TEMP FOLDER Option on Build", $LANG_JAPANESE=>"[オプション] ビルド時にTempフォルダに出力するオプションを表示する"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_default_output_temp_folder_on_build_is_true", $project->option_default_output_temp_folder_on_build_is_true,
			array($LANG_ENGLISH=>"[OPTION] Default OUTPUT TEMP FOLDER Option is TRUE", $LANG_JAPANESE=>"[オプション] ビルド時にTempフォルダに出力するオプションのデフォルトをONにする"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("option_IsCompareOutputTarget", $project->option_IsCompareOutputTarget,
			array($LANG_ENGLISH=>"[OPTION] use Compare Output", $LANG_JAPANESE=>"[オプション] 出力比較を使用する"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		?>
        <div class="clearfix"></div>
        <div class="row">
            <label class="col-md-3 control-label" for="inputtext"></label>
            <div class="col-md-9" align="right">
            	<a href="default_setting_show.php?TemplateType=common">Show Template Default</a>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($project->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($project->PID); ?>">
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
	<p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
