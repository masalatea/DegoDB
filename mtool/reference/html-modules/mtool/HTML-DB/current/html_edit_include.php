<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$html = new htmlData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$html->ProjectPID = trim(GetParam("ProjectPID"));
$html->PID = trim(GetParam("htmlPID"));
$html->name = trim(GetParam("name"));
$html->ProjectSourceOutputPID = trim(GetParam("ProjectSourceOutputPID"));
$html->htmlTemplatePID = trim(GetParam("htmlTemplatePID"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($html->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
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
		$html->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($html->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAhtml = new htmlDBAccess();
			$insertResult = $DAhtml->Inserthtml($html);
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
				$html->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_HTML"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $html->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($html->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $html->PID)) {
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
		
	} else if (is_numeric($html->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAhtml = new htmlDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAhtml->Updatehtml($html);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_HTML"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAhtml->Deletehtml($html);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_HTML"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$html = $DAhtml->Gethtml($html->PID, $html->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! HTML PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($html->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_HTML");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_HTML");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $html != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForHtml($HeaderCaption, $html->ProjectPID, $html->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="html_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("name", $html->name,
			array($LANG_ENGLISH=>"Name", $LANG_JAPANESE=>"名前"),
			array($LANG_ENGLISH=>"Please input Name (It is used for prefix for filename)", $LANG_JAPANESE=>"名前を入力して下さい。ファイル名の接頭辞になります。"),
			"text", "");
		
		$ProjectSourceOutputSelections = array();
		$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
		$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($html->ProjectPID); 
		for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
			$ProjectSourceOutput = $ProjectSourceOutputList[$i];
			
			switch($ProjectSourceOutput->ClassType)
			{
				case ProjectSourceOutputClassTypeEnum::$DBACCESS:
				case ProjectSourceOutputClassTypeEnum::$PROXYSERVER:
				case ProjectSourceOutputClassTypeEnum::$PROXYCLIENT:
				case ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER:
				case ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT:
					break;
				case ProjectSourceOutputClassTypeEnum::$HTML:
					array_push($ProjectSourceOutputSelections, array("VALUE"=>$ProjectSourceOutput->PID, "CAPTION"=>$ProjectSourceOutput->GetOneLineShortCaptionForHtml()));
					break;
				case ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE:
					break;
			}
		}
		
		mtoolCommonFormSelect("ProjectSourceOutputPID", $html->ProjectSourceOutputPID,
			array($LANG_ENGLISH=>"Source Output", $LANG_JAPANESE=>"プロジェクト ソース出力"),
			array($LANG_ENGLISH=>"Please select Source Output", $LANG_JAPANESE=>"プロジェクト ソース出力を選択して下さい"), 
			$ProjectSourceOutputSelections
			, array(), "");
		
		$htmlTemplateSelections = array();
		$DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate = new htmlTemplate_leftouterjoin_ParentHtmlTemplateDBAccess();
		$htmlTemplateList = $DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate->GethtmlTemplateByTargetTypeList(htmlTemplateTargetTypeEnum::$HTML);
		for($i = 0 ; $i < count($htmlTemplateList); $i++) {
			$htmlTemplate = $htmlTemplateList[$i];
			
			if ($htmlTemplate->ParentHtmlTemplatePID == 0) {
				// Only Top Level Item
				array_push($htmlTemplateSelections, array("VALUE"=>$htmlTemplate->PID, "CAPTION"=>$htmlTemplate->name));
			}
		}
		mtoolCommonFormSelect("htmlTemplatePID", $html->htmlTemplatePID,
			array($LANG_ENGLISH=>"HTML Template", $LANG_JAPANESE=>"HTMLテンプレート"),
			array($LANG_ENGLISH=>"Please select HTML Template", $LANG_JAPANESE=>"HTMLテンプレートを選択して下さい"), 
			$htmlTemplateSelections
			, array(), "");
		
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($html->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($html->ProjectPID); ?>">
		<input name="htmlPID" type="hidden" value="<?php print htmlspecialchars($html->PID); ?>">
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
    <p><a href="htmls.php?ProjectPID=<?php print urlencode($html->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Html List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
