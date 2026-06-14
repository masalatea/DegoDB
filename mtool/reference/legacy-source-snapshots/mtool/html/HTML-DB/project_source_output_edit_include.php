<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$projectSourceOutput = new ProjectSourceOutputData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$projectSourceOutput->ProjectPID = trim(GetParam("ProjectPID"));
$projectSourceOutput->PID = trim(GetParam("ProjectSourceOutputPID"));
$projectSourceOutput->ProgramLanguage = trim(GetParam("ProgramLanguage"));
$projectSourceOutput->CustomFileExtention = trim(GetParam("CustomFileExtention"));
$projectSourceOutput->ClassType = trim(GetParam("ClassType"));
$projectSourceOutput->ReleaseTargetType = trim(GetParam("ReleaseTargetType"));
$projectSourceOutput->SourceTemplateDir = trim(GetParam("SourceTemplateDir"));
$projectSourceOutput->DropboxBaseFolderPID = trim(GetParam("DropboxBaseFolderPID"));
$projectSourceOutput->SourceOutputDir = trim(GetParam("SourceOutputDir"));
$projectSourceOutput->SourceTempOutputDir = trim(GetParam("SourceTempOutputDir"));
$projectSourceOutput->ProxyBaseURL = trim(GetParam("ProxyBaseURL"));
$projectSourceOutput->UnitTestTemplateDir = trim(GetParam("UnitTestTemplateDir"));
$projectSourceOutput->UnitTestOutputDir = trim(GetParam("UnitTestOutputDir"));
$projectSourceOutput->AutoloadFilenameSuffix = trim(GetParam("AutoloadFilenameSuffix"));
$projectSourceOutput->TargetServerProjectSourceOutputPID = trim(GetParam("TargetServerProjectSourceOutputPID"));
$projectSourceOutput->SourceTextCharCode = trim(GetParam("SourceTextCharCode"));
$projectSourceOutput->CSNameSpace = trim(GetParam("CSNameSpace"));
$projectSourceOutput->JavaPackageName = trim(GetParam("JavaPackageName"));
$projectSourceOutput->AutoLoadFilePathForPHP = trim(GetParam("AutoLoadFilePathForPHP"));
$projectSourceOutput->JavaFunctionType = trim(GetParam("JavaFunctionType"));
$projectSourceOutput->DotNetLanguageResourceType = trim(GetParam("DotNetLanguageResourceType"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($projectSourceOutput->ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAProject = new ProjectDBAccess();
$project = NULL;
if ($NoError) {
	$project = $DAProject->GetProject($projectSourceOutput->ProjectPID);
	if ($project == NULL) {
		?>
		<H3><font color="red">Unknown Project</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	if ($UPDATE != "") {
		// Add
		switch($projectSourceOutput->ClassType)
		{
			case ProjectSourceOutputClassTypeEnum::$DBACCESS:
			case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
			case ProjectSourceOutputClassTypeEnum::$HTML:
				break;
			case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
			case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
				if ($project && $project->option_restrict_proxy_server_to_single == 1) {
					// Check if duplicate of Proxy Server
					$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
					$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($projectSourceOutput->ProjectPID);
					if ($ProjectSourceOutputList) {
						$include_proxy_server = false;
						for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
							$thisProjectSourceOutput = $ProjectSourceOutputList[$i];
							
							if ($thisProjectSourceOutput->PID != $projectSourceOutput->PID &&
							    $thisProjectSourceOutput->ReleaseTargetType == $projectSourceOutput->ReleaseTargetType) {
								switch($thisProjectSourceOutput->ClassType)
								{
									case ProjectSourceOutputClassTypeEnum::$DBACCESS:
									case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
									case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
									case ProjectSourceOutputClassTypeEnum::$HTML:
										break;
									case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
									case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
										$include_proxy_server = true;
										break;
									case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
										break;
									default:
										die("Unknown Class Type:" . $thisProjectSourceOutput->ClassType);
								}
							}
						}
						if ($include_proxy_server) {
							?>
							<H3><font color="red">Warning! Another Proxy Server exists for this Release Target Type. Only one Proxy Server should be defined for each Release Target Type.</font></H3>
							<?php
						}
					}
				}
				break;
			case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
				break;
			default:
				break;		// OK. Not selected
		}
	}
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_base_folder_edit.php");

InitializeOutputShortenedStringWithExpansion();

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$projectSourceOutput->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($projectSourceOutput->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
			$insertResult = $DAProjectSourceOutput->InsertProjectSourceOutput($projectSourceOutput);
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
				$projectSourceOutput->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_PROJECT_SOURCE_OUTPUT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $projectSourceOutput->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($projectSourceOutput->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $projectSourceOutput->PID)) {
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
		
	} else if (is_numeric($projectSourceOutput->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAProjectSourceOutput->UpdateProjectSourceOutput($projectSourceOutput);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_PROJECT_SOURCE_OUTPUT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAProjectSourceOutput->DeleteProjectSourceOutput($projectSourceOutput);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_PROJECT_SOURCE_OUTPUT"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$projectSourceOutput = $DAProjectSourceOutput->GetProjectSourceOutput($projectSourceOutput->PID, $projectSourceOutput->ProjectPID);
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
	if ($projectSourceOutput->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_PROJECT_SOURCE_OUTPUT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_PROJECT_SOURCE_OUTPUT");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $projectSourceOutput != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForDBTable($HeaderCaption, $projectSourceOutput->ProjectPID, "", "");
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
<script>
var CheckAutoloadFilenameSuffixAreaVisible = function()
{
	var ProgramLanguageSelectedValue = $('#ProgramLanguage').val();
	
	switch (ProgramLanguageSelectedValue) {
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$PHP; ?>":
			var ClassTypeSelectedValue = $('#ClassType').val();
			switch (ClassTypeSelectedValue) {
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBACCESS; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT; ?>":
					return true;
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYSERVER; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$HTML; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE; ?>":
					break;
			}
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$CS; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$JAVA; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$SWIFT; ?>":
			break;
	}
	return false;
};
var CheckAutoLoadFilePathForPHPAreaVisible = function()
{
	var ProgramLanguageSelectedValue = $('#ProgramLanguage').val();
	
	switch (ProgramLanguageSelectedValue) {
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$PHP; ?>":
			var ClassTypeSelectedValue = $('#ClassType').val();
			switch (ClassTypeSelectedValue) {
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBACCESS; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYSERVER; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$HTML; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE; ?>":
					break;
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER; ?>":
					return true;
			}
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$CS; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$JAVA; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$SWIFT; ?>":
			break;
	}
	return false;
};

var CheckCSNameSpaceAreaVisible = function()
{
	var ProgramLanguageSelectedValue = $('#ProgramLanguage').val();
	
	switch (ProgramLanguageSelectedValue) {
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$PHP; ?>":
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$CS; ?>":
			var ClassTypeSelectedValue = $('#ClassType').val();
			switch (ClassTypeSelectedValue) {
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBACCESS; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYSERVER; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$HTML; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER; ?>":
					return true;
				case "<?php print ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE; ?>":
					var DotNetLanguageResourceTypeSelectedValue = $('#DotNetLanguageResourceType').val();
					switch (DotNetLanguageResourceTypeSelectedValue) {
						case "<?php print ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT; ?>":
						case "<?php print ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP; ?>":
							break;
						case "<?php print ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE; ?>":
							return true;
					}
					break;
			}
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$JAVA; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$SWIFT; ?>":
			break;
	}
	return false;
};

var CheckCSLangAreaVisible = function()
{
	var ProgramLanguageSelectedValue = $('#ProgramLanguage').val();
	
	switch (ProgramLanguageSelectedValue) {
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$PHP; ?>":
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$CS; ?>":
			var ClassTypeSelectedValue = $('#ClassType').val();
			switch (ClassTypeSelectedValue) {
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBACCESS; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYSERVER; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$HTML; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER; ?>":
					break;
				case "<?php print ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE; ?>":
					return true;
			}
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$JAVA; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$SWIFT; ?>":
			break;
	}
	return false;
};

var CheckJavaSourceSettingAreaVisible = function()
{
	var ProgramLanguageSelectedValue = $('#ProgramLanguage').val();
	
	switch (ProgramLanguageSelectedValue) {
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$PHP; ?>":
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$CS; ?>":
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$JAVA; ?>":
			var ClassTypeSelectedValue = $('#ClassType').val();
			switch (ClassTypeSelectedValue) {
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBACCESS; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$PROXYSERVER; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$HTML; ?>":
				case "<?php print ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER; ?>":
					return true;
				case "<?php print ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE; ?>":
					break;
			}
			break;
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM; ?>":
		case "<?php print ProjectSourceOutputProgramLanguageEnum::$SWIFT; ?>":
			break;
	}
	return false;
};

</script>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="project_source_output_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormSelect("ClassType", $projectSourceOutput->ClassType,
			array($LANG_ENGLISH=>"Class Type", $LANG_JAPANESE=>"クラス種類"),
			array($LANG_ENGLISH=>"Please select Class Type", $LANG_JAPANESE=>"クラス種類を選択して下さい"), 
			array(
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$DBACCESS, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$DBACCESS)),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$PROXYSERVER, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$PROXYSERVER)),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$PROXYCLIENT, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$PROXYCLIENT)),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER)),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT)),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$HTML, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$HTML)),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE, "CAPTION"=>GetProjectSourceOutputClassTypeCaption(ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE))
			), array(
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$DBACCESS . "," . ProjectSourceOutputClassTypeEnum::$PROXYSERVER . "," . ProjectSourceOutputClassTypeEnum::$PROXYCLIENT, "SHOW"=>"UnitTestTemplateDirArea"),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$DBACCESS . "," . ProjectSourceOutputClassTypeEnum::$PROXYSERVER . "," . ProjectSourceOutputClassTypeEnum::$PROXYCLIENT, "SHOW"=>"UnitTestOutputDirArea"),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$PROXYCLIENT . "," . ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT, "SHOW"=>"TargetServerProjectSourceOutputArea"),
				array("VALUE"=>ProjectSourceOutputClassTypeEnum::$PROXYSERVER . "," . ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER, "SHOW"=>"ProxyBaseURLArea"),
				array("VALUE"=>"", "SHOW"=>"CSNameSpaceArea", "CUSTOMFUNCTION"=>"CheckCSNameSpaceAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"DotNetLanguageResourceTypeArea", "CUSTOMFUNCTION"=>"CheckCSLangAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"JavaPackageNameArea,JavaFunctionTypeArea", "CUSTOMFUNCTION"=>"CheckJavaSourceSettingAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"AutoloadFilenameSuffixArea", "CUSTOMFUNCTION"=>"CheckAutoloadFilenameSuffixAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"AutoLoadFilePathForPHPArea,AutoLoadFilePathForPHPCommentArea", "CUSTOMFUNCTION"=>"CheckAutoLoadFilePathForPHPAreaVisible")
			), "");
		mtoolCommonFormSelect("ProgramLanguage", $projectSourceOutput->ProgramLanguage,
			array($LANG_ENGLISH=>"Program Language", $LANG_JAPANESE=>"プログラム言語"),
			array($LANG_ENGLISH=>"Please select Program Language", $LANG_JAPANESE=>"プログラム言語を選択して下さい"), 
			array(
				array("VALUE"=>ProjectSourceOutputProgramLanguageEnum::$PHP,    "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(ProjectSourceOutputProgramLanguageEnum::$PHP)),
				array("VALUE"=>ProjectSourceOutputProgramLanguageEnum::$CS,     "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(ProjectSourceOutputProgramLanguageEnum::$CS)),
				array("VALUE"=>ProjectSourceOutputProgramLanguageEnum::$JAVA,   "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(ProjectSourceOutputProgramLanguageEnum::$JAVA)),
				array("VALUE"=>ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH,   "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECH)),
				array("VALUE"=>ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM,   "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(ProjectSourceOutputProgramLanguageEnum::$OBJECTIVECM)),
				array("VALUE"=>ProjectSourceOutputProgramLanguageEnum::$SWIFT,   "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(ProjectSourceOutputProgramLanguageEnum::$SWIFT))
			), array(
				array("VALUE"=>"", "SHOW"=>"CSNameSpaceArea", "CUSTOMFUNCTION"=>"CheckCSNameSpaceAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"DotNetLanguageResourceTypeArea", "CUSTOMFUNCTION"=>"CheckCSLangAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"JavaPackageNameArea,JavaFunctionTypeArea", "CUSTOMFUNCTION"=>"CheckJavaSourceSettingAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"AutoloadFilenameSuffixArea", "CUSTOMFUNCTION"=>"CheckAutoloadFilenameSuffixAreaVisible"),
				array("VALUE"=>"", "SHOW"=>"AutoLoadFilePathForPHPArea,AutoLoadFilePathForPHPCommentArea", "CUSTOMFUNCTION"=>"CheckAutoLoadFilePathForPHPAreaVisible")
			), "ProgramLanguageArea");
		mtoolCommonFormInput("SourceTemplateDir", $projectSourceOutput->SourceTemplateDir,
			array($LANG_ENGLISH=>"Template Dir", $LANG_JAPANESE=>"テンプレートのパス"),
			array($LANG_ENGLISH=>"Please input Template Dir (on Storege such as DropBox) Default is used if blank. Dropbox Base Folder is based on Project Setting.", $LANG_JAPANESE=>"テンプレートのパスを入力して下さい。格納先(DropBox等)のパス。空の場合はデフォルトのテンプレートを使用します。Dropbox Base FolderはProject設定が使用されます"),
			"text", "TemplateBaseDirArea");
		mtoolCommonFormSelect("DropboxBaseFolderPID", $projectSourceOutput->DropboxBaseFolderPID,
			array($LANG_ENGLISH=>"Dropbox Base Folder", $LANG_JAPANESE=>"Dropbox Base Folder設定"),
			array($LANG_ENGLISH=>"Please select Dropbox Base Folder (if Blank, Project Setting is used)", $LANG_JAPANESE=>"Dropbox Base Folder設定を選択して下さい (未選択の場合はProject設定を使用)"), 
			GetDropboxBaseFolderSelectionListForEditAnySettingGroup($matsuesoft_login_token_id)
			, array(), "DropboxBaseFolderArea");
		mtoolCommonFormInput("SourceOutputDir", $projectSourceOutput->SourceOutputDir,
			array($LANG_ENGLISH=>"Output Path", $LANG_JAPANESE=>"出力パス"),
			array($LANG_ENGLISH=>"Please input Output Path (on Storege such as DropBox)", $LANG_JAPANESE=>"出力パスを入力して下さい。格納先(DropBox等)のパス"),
			"text", "");
		
		if ($project->option_show_output_temp_folder_on_build == 1) {
			mtoolCommonFormInput("SourceTempOutputDir", $projectSourceOutput->SourceTempOutputDir,
				array($LANG_ENGLISH=>"Temp Output Path", $LANG_JAPANESE=>"一時出力パス"),
				array($LANG_ENGLISH=>"Please input Temp Output Path (on Storege such as DropBox) If blank, temp dir is Output Path + \"" . $SOURCE_TEMP_OUTPUT_SUFFIX . "\"", $LANG_JAPANESE=>"一時出力パスを入力して下さい。格納先(DropBox等)のパス。空の場合は \"" . $SOURCE_TEMP_OUTPUT_SUFFIX . "\" が出力パスの末尾に付きます"),
				"text", "");
		} else {
			?>
			<input name="SourceTempOutputDir" type="hidden" value="<?php print htmlspecialchars($projectSourceOutput->SourceTempOutputDir); ?>">
            <?php
		}
		mtoolCommonFormInput("ProxyBaseURL", $projectSourceOutput->ProxyBaseURL,
			array($LANG_ENGLISH=>"Proxy Base URL", $LANG_JAPANESE=>"Proxy基本URL"),
			array($LANG_ENGLISH=>"Please input Proxy Base URL", $LANG_JAPANESE=>"Proxy基本URLを入力して下さい。"),
			"text", "ProxyBaseURLArea");
		mtoolCommonFormInput("UnitTestTemplateDir", $projectSourceOutput->UnitTestTemplateDir,
			array($LANG_ENGLISH=>"Unit Test's Template Dir", $LANG_JAPANESE=>"Unit Testテンプレートのパス"),
			array($LANG_ENGLISH=>"Please input Unit Test's TemplateDir (on Storege such as DropBox)", $LANG_JAPANESE=>"Unit Testテンプレートのパスを入力して下さい。格納先(DropBox等)のパス。"),
			"text", "UnitTestTemplateDirArea");
		mtoolCommonFormInput("UnitTestOutputDir", $projectSourceOutput->UnitTestOutputDir,
			array($LANG_ENGLISH=>"Unit Test's Work Dir", $LANG_JAPANESE=>"Unit Test作業用パス"),
			array($LANG_ENGLISH=>"Please input Unit Test's Work Dir (on Storege such as DropBox)", $LANG_JAPANESE=>"Unit Test作業用パスを入力して下さい。格納先(DropBox等)のパス。"),
			"text", "UnitTestOutputDirArea");
		mtoolCommonFormInput("AutoloadFilenameSuffix", $projectSourceOutput->AutoloadFilenameSuffix,
			array($LANG_ENGLISH=>"Autoload Filename Suffix", $LANG_JAPANESE=>"Autoload用ファイル名接尾辞"),
			array($LANG_ENGLISH=>"Please input Autoload Filename Suffix(For PHP. Not for C#) [Optional]", $LANG_JAPANESE=>"Autoload用ファイル名接尾辞(PHP用。C#では未使用) [Optional]"),
			"text", "AutoloadFilenameSuffixArea");
		
		$ProjectSourceOutputSelections = array();
		$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
		$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($projectSourceOutput->ProjectPID); 
		for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
			$thisProjectSourceOutput = $ProjectSourceOutputList[$i];
			
			switch($thisProjectSourceOutput->ClassType)
			{
				case ProjectSourceOutputClassTypeEnum::$DBACCESS:
					break;
				case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
				case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
					array_push($ProjectSourceOutputSelections, array("VALUE"=>$thisProjectSourceOutput->PID, "CAPTION"=>$thisProjectSourceOutput->GetOneLineShortCaptionForHtml()));
				case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
				case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
					break;
				case ProjectSourceOutputClassTypeEnum::$HTML:
				case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
					break;
			}
		}
		
		mtoolCommonFormSelect("TargetServerProjectSourceOutputPID", $projectSourceOutput->TargetServerProjectSourceOutputPID,
			array($LANG_ENGLISH=>"Target Server's Source Output", $LANG_JAPANESE=>"ターゲットサーバのプロジェクト ソース出力"),
			array($LANG_ENGLISH=>"Please select Target Server's Source Output", $LANG_JAPANESE=>"ターゲットサーバのプロジェクト ソース出力を選択して下さい"), 
			$ProjectSourceOutputSelections
			, array(), "TargetServerProjectSourceOutputArea");
		mtoolCommonFormSelect("ReleaseTargetType", $projectSourceOutput->ReleaseTargetType,
			array($LANG_ENGLISH=>"Release Target Type", $LANG_JAPANESE=>"Releaseターゲット種類"),
			array($LANG_ENGLISH=>"Please select Release Target Type", $LANG_JAPANESE=>"Releaseターゲット種類を選択して下さい"), 
			array(
				array("VALUE"=>ProjectSourceOutputReleaseTargetTypeEnum::$RELEASE, "CAPTION"=>GetProjectSourceOutputReleaseTargetTypeCaption(ProjectSourceOutputReleaseTargetTypeEnum::$RELEASE)),
				array("VALUE"=>ProjectSourceOutputReleaseTargetTypeEnum::$BETA,    "CAPTION"=>GetProjectSourceOutputReleaseTargetTypeCaption(ProjectSourceOutputReleaseTargetTypeEnum::$BETA)),
			), array(
			), "");
		mtoolCommonFormInput("SourceTextCharCode", $projectSourceOutput->SourceTextCharCode,
			array($LANG_ENGLISH=>"Source's Char Code", $LANG_JAPANESE=>"ソースファイルの文字コード"),
			array($LANG_ENGLISH=>"Please input Source's Char Code", $LANG_JAPANESE=>"ソースファイルの文字コードを入力して下さい。空白の場合はデフォルト(UTF-8)"),
			"text", "");
		?>
        <div class="clearfix"></div>
        <div class="row">
            <label class="col-md-3 control-label" for="inputtext"></label>
            <div class="col-md-9">
              <?php
			  $fyi_header_string = "[FYI] Supported Char Codes: ";
			  OutputShortenedStringWithExpansionWithHeader($fyi_header_string, implode(", ", mb_list_encodings()), 5);
			  ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
		
		mtoolCommonFormInput("JavaPackageName", $projectSourceOutput->JavaPackageName,
			array($LANG_ENGLISH=>"Java's Package Name", $LANG_JAPANESE=>"Javaのパッケージ名"),
			array($LANG_ENGLISH=>"Please input Java's Package Name", $LANG_JAPANESE=>"Javaのパッケージ名を入力して下さい。"),
			"text", "JavaPackageNameArea");
		mtoolCommonFormInput("CSNameSpace", $projectSourceOutput->CSNameSpace,
			array($LANG_ENGLISH=>"C#'s Name Space", $LANG_JAPANESE=>"C#のネームスペース"),
			array($LANG_ENGLISH=>"Please input C#'s Name Space", $LANG_JAPANESE=>"C#のネームスペースを入力して下さい。"),
			"text", "CSNameSpaceArea");
		
		mtoolCommonFormInput("AutoLoadFilePathForPHP", $projectSourceOutput->AutoLoadFilePathForPHP,
			array($LANG_ENGLISH=>"Autoload Filename for PHP for DBaaS", $LANG_JAPANESE=>"Autoload用ファイル名(DBaaS用)"),
			array($LANG_ENGLISH=>"Please input Autoload Filename (Relative or Full Path) for PHP for DBaaS. Automatically set default if blank", $LANG_JAPANESE=>"Autoload用ファイル名(相対あるいはフルパス)を入力して下さい(DBaaS用) 空白の場合はデフォルトが設定されます"),
			"text", "AutoLoadFilePathForPHPArea");
		
		$autoloadfilename_default = GetProjectSourceOutputDefaultAutoloadFilename($projectSourceOutput->ProjectPID, $projectSourceOutput->SourceOutputDir);
		if ($autoloadfilename_default == "") {
			$this_captin_array = array(
				$LANG_ENGLISH=>"Empty (Not Found Database Access type of Source Output Setting for Source Output Dir: " . $projectSourceOutput->SourceOutputDir . ")",
				$LANG_JAPANESE=>"なし (Class TypeがDatabase Accessで出力パスが " . $projectSourceOutput->SourceOutputDir . " に一致するSource Output Settingが見つかりません)"
			);
			$autoloadfilename_default = $this_captin_array[$lang];
		}
		mtoolCommonFormComment(array($LANG_ENGLISH=>"[FYI] Default Autoload File Path", $LANG_JAPANESE=>"[参考] Autoload用ファイル名(DBaaS用)・デフォルト"), $autoloadfilename_default, "", "AutoLoadFilePathForPHPCommentArea");
		
		mtoolCommonFormSelect("JavaFunctionType", $projectSourceOutput->JavaFunctionType,
			array($LANG_ENGLISH=>"Java Function Type", $LANG_JAPANESE=>"Java関数種類"),
			array($LANG_ENGLISH=>"Please select Java Function Type", $LANG_JAPANESE=>"Java関数種類を選択して下さい"), 
			array(
				array("VALUE"=>ProjectSourceOutputJavaFunctionTypeEnum::$DEFAULT,                    "CAPTION"=>GetProjectSourceOutputJavaFunctionTypeCaption(ProjectSourceOutputJavaFunctionTypeEnum::$DEFAULT)),
				array("VALUE"=>ProjectSourceOutputJavaFunctionTypeEnum::$BOTH,                       "CAPTION"=>GetProjectSourceOutputJavaFunctionTypeCaption(ProjectSourceOutputJavaFunctionTypeEnum::$BOTH)),
				array("VALUE"=>ProjectSourceOutputJavaFunctionTypeEnum::$ANDROIDASYNCTASKLOADERONLY, "CAPTION"=>GetProjectSourceOutputJavaFunctionTypeCaption(ProjectSourceOutputJavaFunctionTypeEnum::$ANDROIDASYNCTASKLOADERONLY)),
				array("VALUE"=>ProjectSourceOutputJavaFunctionTypeEnum::$DIRECTONLY,                 "CAPTION"=>GetProjectSourceOutputJavaFunctionTypeCaption(ProjectSourceOutputJavaFunctionTypeEnum::$DIRECTONLY))
			), array(
			), "JavaFunctionTypeArea");
		mtoolCommonFormSelect("DotNetLanguageResourceType", $projectSourceOutput->DotNetLanguageResourceType,
			array($LANG_ENGLISH=>".Net Language Resource Type", $LANG_JAPANESE=>".NET言語リソース種類"),
			array($LANG_ENGLISH=>"Please select .Net Language Resource Type", $LANG_JAPANESE=>".NET言語リソース種類を選択して下さい"), 
			array(
				array("VALUE"=>ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT,                    "CAPTION"=>GetProjectSourceOutputDotNetLanguageResourceTypeCaption(ProjectSourceOutputDotNetLanguageResourceTypeEnum::$DEFAULT)),
				array("VALUE"=>ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP,                       "CAPTION"=>GetProjectSourceOutputDotNetLanguageResourceTypeCaption(ProjectSourceOutputDotNetLanguageResourceTypeEnum::$UWP)),
				array("VALUE"=>ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE,                       "CAPTION"=>GetProjectSourceOutputDotNetLanguageResourceTypeCaption(ProjectSourceOutputDotNetLanguageResourceTypeEnum::$BYCODE))
			), array(
				array("VALUE"=>"", "SHOW"=>"CSNameSpaceArea", "CUSTOMFUNCTION"=>"CheckCSNameSpaceAreaVisible"),
			), "DotNetLanguageResourceTypeArea");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($projectSourceOutput->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($projectSourceOutput->ProjectPID); ?>">
		<input name="ProjectSourceOutputPID" type="hidden" value="<?php print htmlspecialchars($projectSourceOutput->PID); ?>">
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
	<p><a href="project_source_output.php?ProjectPID=<?php print urlencode($projectSourceOutput->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Source Output Setting</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
