<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$htmlTemplate = new htmlTemplateData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$htmlTemplate->PID = trim(GetParam("htmlTemplatePID"));
$htmlTemplate->TargetType = trim(GetParam("TargetType"));
$htmlTemplate->ParentHtmlTemplatePID = trim(GetParam("ParentHtmlTemplatePID"));
$htmlTemplate->name = trim(GetParam("name"));
$htmlTemplate->ProgramLanguage = trim(GetParam("ProgramLanguage"));
$htmlTemplate->FileName = trim(GetParam("FileName"));
$htmlTemplate->Comment = trim(GetParam("Comment"));

$duplicate_htmlTemplatePID = trim(GetParam("duplicate_htmlTemplatePID"));
if (is_numeric($duplicate_htmlTemplatePID)) {
	$DAhtmlTemplate = new htmlTemplateDBAccess();
	$duplicate_htmlTemplate = $DAhtmlTemplate->GethtmlTemplate($duplicate_htmlTemplatePID);
	
	$htmlTemplate->PID = "";
	$htmlTemplate->TargetType = $duplicate_htmlTemplate->TargetType;
	$htmlTemplate->ParentHtmlTemplatePID = $duplicate_htmlTemplate->ParentHtmlTemplatePID;
	$htmlTemplate->name = $duplicate_htmlTemplate->name;
	$htmlTemplate->ProgramLanguage = $duplicate_htmlTemplate->ProgramLanguage;
	$htmlTemplate->FileName = $duplicate_htmlTemplate->FileName;
	$htmlTemplate->Comment = $duplicate_htmlTemplate->Comment;
}

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
		$htmlTemplate->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($htmlTemplate->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAhtmlTemplate = new htmlTemplateDBAccess();
			$insertResult = $DAhtmlTemplate->InserthtmlTemplate($htmlTemplate);
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
				$htmlTemplate->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_HTML_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $htmlTemplate->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($htmlTemplate->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $htmlTemplate->PID)) {
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
		
	} else if (is_numeric($htmlTemplate->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAhtmlTemplate = new htmlTemplateDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAhtmlTemplate->UpdatehtmlTemplate($htmlTemplate);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_HTML_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAhtmlTemplate->DeletehtmlTemplate($htmlTemplate);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_HTML_TEMPLATE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$htmlTemplate = $DAhtmlTemplate->GethtmlTemplate($htmlTemplate->PID);
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
	if ($htmlTemplate->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_HTML_TEMPLATE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_HTML_TEMPLATE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $htmlTemplate != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForhtmlTemplate($HeaderCaption, $htmlTemplate->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="html_template_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		mtoolCommonFormSelect("TargetType", $htmlTemplate->TargetType,
			array($LANG_ENGLISH=>"Target Type", $LANG_JAPANESE=>"ターゲット"),
			array($LANG_ENGLISH=>"Please select Target Type", $LANG_JAPANESE=>"ターゲットを選択して下さい"), 
			array(
				array("VALUE"=>htmlTemplateTargetTypeEnum::$HTML, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$HTML)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$DB, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$DB)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$PROXYSERVER, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$PROXYSERVER)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$PROXYCLIENT, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$PROXYCLIENT)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$UNITTEST, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$UNITTEST)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$UPLOADSETTING, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$UPLOADSETTING)),
				array("VALUE"=>htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE, "CAPTION"=>GethtmlTemplateTargetTypeCaption(htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE))
			), array(
			), "");
		
		$DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate = new htmlTemplate_leftouterjoin_ParentHtmlTemplateDBAccess();
		$originalhtmlTemplateList = $DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate->GethtmlTemplateList();
		$htmlTemplateList = SorthtmlTemplateDataListByTree($originalhtmlTemplateList);
		
		$htmlTemplateSelections = array();
		for($i = 0 ; $i < count($htmlTemplateList) ; $i++) {
			$thishtmlTemplate = $htmlTemplateList[$i];
			
			if ($thishtmlTemplate->PID != $htmlTemplate->PID) {
				$thisCaption = $thishtmlTemplate->name . " (" . GethtmlTemplateDataLanguageCaption($thishtmlTemplate->ProgramLanguage) . ")";
				
				if ($thishtmlTemplate->ParentHtmlTemplatename != "") {
					$thisCaption = $thisCaption . " (Parent: " . $thishtmlTemplate->ParentHtmlTemplatename . ")";
				}
				array_push($htmlTemplateSelections,
					array("VALUE"=>$thishtmlTemplate->PID, "CAPTION"=>$thisCaption)
					);
			}
		}
		
		mtoolCommonFormSelect("ParentHtmlTemplatePID", $htmlTemplate->ParentHtmlTemplatePID,
			array($LANG_ENGLISH=>"Parent Html Template", $LANG_JAPANESE=>"親HTMLテンプレート"),
			array($LANG_ENGLISH=>"Please select Parent Html Template (Regard as top if not select)", $LANG_JAPANESE=>"親HTMLテンプレートを選択して下さい (未選択はトップ)"), 
			$htmlTemplateSelections
			, array(), "");
		mtoolCommonFormInput("name", $htmlTemplate->name,
			array($LANG_ENGLISH=>"Name", $LANG_JAPANESE=>"名前"),
			array($LANG_ENGLISH=>"Please input Name", $LANG_JAPANESE=>"名前を入力して下さい。"),
			"text", "");
		mtoolCommonFormSelect("ProgramLanguage", $htmlTemplate->ProgramLanguage,
			array($LANG_ENGLISH=>"Program Language", $LANG_JAPANESE=>"プログラム言語"),
			array($LANG_ENGLISH=>"Please select Program Language", $LANG_JAPANESE=>"プログラム言語を選択して下さい"), 
			array(
				array("VALUE"=>htmlTemplateProgramLanguageEnum::$PHP, "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(htmlTemplateProgramLanguageEnum::$PHP)),
				array("VALUE"=>htmlTemplateProgramLanguageEnum::$CS, "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(htmlTemplateProgramLanguageEnum::$CS)),
				array("VALUE"=>htmlTemplateProgramLanguageEnum::$JAVA, "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(htmlTemplateProgramLanguageEnum::$JAVA)),
				array("VALUE"=>htmlTemplateProgramLanguageEnum::$OBJECTIVECH, "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(htmlTemplateProgramLanguageEnum::$OBJECTIVECH)),
				array("VALUE"=>htmlTemplateProgramLanguageEnum::$OBJECTIVECM, "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(htmlTemplateProgramLanguageEnum::$OBJECTIVECM)),
				array("VALUE"=>htmlTemplateProgramLanguageEnum::$SWIFT, "CAPTION"=>GetProjectSourceOutputProgramLanguageCaption(htmlTemplateProgramLanguageEnum::$SWIFT))
			), array(
			), "ProgramLanguageArea");
		mtoolCommonFormInput("FileName", $htmlTemplate->FileName,
			array($LANG_ENGLISH=>"File Name", $LANG_JAPANESE=>"ファイル名"),
			array($LANG_ENGLISH=>"Please input File Name", $LANG_JAPANESE=>"ファイル名を入力して下さい。"),
			"text", "");
		mtoolCommonFormInput("Comment", $htmlTemplate->Comment,
			array($LANG_ENGLISH=>"Comment", $LANG_JAPANESE=>"コメント"),
			array($LANG_ENGLISH=>"Please input Comment", $LANG_JAPANESE=>"コメントを入力して下さい。"),
			"text", "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($htmlTemplate->PID != "") {
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
		<input name="htmlTemplatePID" type="hidden" value="<?php print htmlspecialchars($htmlTemplate->PID); ?>">
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
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to HTML Template List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
