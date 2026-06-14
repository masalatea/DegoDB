<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$ApacheHostSettingTemplate = new ApacheHostSettingTemplateData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$ApacheHostSettingTemplate->PID = trim(GetParam("PID"));
$ApacheHostSettingTemplate->name = trim(GetParam("name"));
$ApacheHostSettingTemplate->FilenameFormat = trim(GetParam("FilenameFormat"));
$ApacheHostSettingTemplate->Template = trim(GetParam("Template"));
$ApacheHostSettingTemplate->AccessLogFilenameFormat = trim(GetParam("AccessLogFilenameFormat"));
$ApacheHostSettingTemplate->ErrorLogFilenameFormat = trim(GetParam("ErrorLogFilenameFormat"));
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
		$ApacheHostSettingTemplate->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($ApacheHostSettingTemplate->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAApacheHostSettingTemplate = new ApacheHostSettingTemplateDBAccess();
			$insertResult = $DAApacheHostSettingTemplate->InsertApacheHostSettingTemplate($ApacheHostSettingTemplate);
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
				$ApacheHostSettingTemplate->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_APACHE_HOST_SETTING_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $ApacheHostSettingTemplate->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($ApacheHostSettingTemplate->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $ApacheHostSettingTemplate->PID)) {
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
		
	} else if (is_numeric($ApacheHostSettingTemplate->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAApacheHostSettingTemplate = new ApacheHostSettingTemplateDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAApacheHostSettingTemplate->UpdateApacheHostSettingTemplate($ApacheHostSettingTemplate);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_APACHE_HOST_SETTING_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAApacheHostSettingTemplate->DeleteApacheHostSettingTemplate($ApacheHostSettingTemplate);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_APACHE_HOST_SETTING_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$ApacheHostSettingTemplate = $DAApacheHostSettingTemplate->GetApacheHostSettingTemplate($ApacheHostSettingTemplate->PID);
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
	if ($ApacheHostSettingTemplate->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_APACHE_HOST_SETTING_TEMPLATE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_APACHE_HOST_SETTING_TEMPLATE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $ApacheHostSettingTemplate != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForApacheTemplateSetting($HeaderCaption, $ApacheHostSettingTemplate->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="apache_host_template_setting_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		mtoolCommonFormInput("name", $ApacheHostSettingTemplate->name,
			array($LANG_ENGLISH=>"Name", $LANG_JAPANESE=>"名前"),
			array($LANG_ENGLISH=>"Please input Name", $LANG_JAPANESE=>"名前を入力して下さい。"),
			"text", "");
		mtoolCommonFormInput("FilenameFormat", $ApacheHostSettingTemplate->FilenameFormat,
			array($LANG_ENGLISH=>"Filename Format", $LANG_JAPANESE=>"ファイル名フォーマット"),
			array($LANG_ENGLISH=>"Please input Filename Format", $LANG_JAPANESE=>"ファイル名フォーマットを入力して下さい。"),
			"text", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __HOST__ Host Name", $LANG_JAPANESE=>"置換文字列: __HOST__ Host Name")
		), "", "");
		mtoolCommonFormTextArea("Template", $ApacheHostSettingTemplate->Template,
			array($LANG_ENGLISH=>"Template", $LANG_JAPANESE=>"テンプレート"),
			array($LANG_ENGLISH=>"Please input Template", $LANG_JAPANESE=>"テンプレートを入力して下さい"),
			"");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __EMAIL__ Email", $LANG_JAPANESE=>"置換文字列: __EMAIL__ Email")
		), "", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __HOST__ Host Name", $LANG_JAPANESE=>"置換文字列: __HOST__ Host Name")
		), "", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __DOCUMENT_ROOT_SUFFIX__ Document Root's Suffix", $LANG_JAPANESE=>"置換文字列: __DOCUMENT_ROOT_SUFFIX__ Document Rootの接尾語")
		), "", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __ACCESS_LOG_FILENAME__ Access Log File Name", $LANG_JAPANESE=>"置換文字列: __ACCESS_LOG_FILENAME__ アクセスログファイル名")
		), "", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __ERROR_LOG_FILENAME__ Error Log File Name", $LANG_JAPANESE=>"置換文字列: __ERROR_LOG_FILENAME__ エラーログファイル名")
		), "", "");
		mtoolCommonFormInput("AccessLogFilenameFormat", $ApacheHostSettingTemplate->AccessLogFilenameFormat,
			array($LANG_ENGLISH=>"Access Log Filename Format", $LANG_JAPANESE=>"アクセスログファイル名フォーマット"),
			array($LANG_ENGLISH=>"Please input Access Log Filename Format", $LANG_JAPANESE=>"アクセスログファイル名フォーマットを入力して下さい。"),
			"text", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __HOST__ Host Name", $LANG_JAPANESE=>"置換文字列: __HOST__ Host Name")
		), "", "");
		mtoolCommonFormInput("ErrorLogFilenameFormat", $ApacheHostSettingTemplate->ErrorLogFilenameFormat,
			array($LANG_ENGLISH=>"Error Log Filename Format", $LANG_JAPANESE=>"エラーログファイル名フォーマット"),
			array($LANG_ENGLISH=>"Please input Error Log Filename Format", $LANG_JAPANESE=>"エラーログファイル名フォーマットを入力して下さい。"),
			"text", "");
		mtoolCommonFormCommentForEachLanguage(array(), array(
			array($LANG_ENGLISH=>"Replacement String: __HOST__ Host Name", $LANG_JAPANESE=>"置換文字列: __HOST__ Host Name")
		), "", "");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($ApacheHostSettingTemplate->PID != "") {
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
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($ApacheHostSettingTemplate->PID); ?>">
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
    <p>
    <a href="./apache_host_template_setting.php?SettingGroupPID=<?php print urlencode($SettingGroupPID); ?>&<?php print makeRandStr(8); ?>">Back to Apache Host Template List</a>
    </p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
