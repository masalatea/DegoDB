<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

// == START OF EDITABLE AREA FOR "Input Parameter" ==
$htmlTemplateParameter = new htmlTemplateParameterData();
$htmlTemplateParameter->htmlTemplatePID = trim(GetParam("htmlTemplatePID"));
$htmlTemplateParameter->PID = trim(GetParam("htmlTemplateParameterPID"));
$htmlTemplateParameter->ParameterName = trim(GetParam("ParameterName"));
$htmlTemplateParameter->TargetValueType = trim(GetParam("TargetValueType"));
$htmlTemplateParameter->TargetVariableOrClassObject = trim(GetParam("TargetVariableOrClassObject"));
$htmlTemplateParameter->TargetPropertyOfClassObject = trim(GetParam("TargetPropertyOfClassObject"));
$htmlTemplateParameter->AnotherTemplatePID = trim(GetParam("AnotherTemplatePID"));
$htmlTemplateParameter->TrimLastSpace = trim(GetParam("TrimLastSpace"));
$htmlTemplateParameter->TrimLastReturn = trim(GetParam("TrimLastReturn"));
$htmlTemplateParameter->DataType = trim(GetParam("DataType"));
// == END OF EDITABLE AREA FOR "Input Parameter" ==

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==

if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
}

if (!is_numeric($htmlTemplateParameter->htmlTemplatePID)) {
	?>
    <H3><font color="red">HTML Template is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==

$DAhtmlTemplate = new htmlTemplateDBAccess();
$htmlTemplate = $DAhtmlTemplate->GethtmlTemplate($htmlTemplateParameter->htmlTemplatePID);
if (!$htmlTemplate) {
	?>
    <H3><font color="red">No Corresponding HTML Template. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($htmlTemplateParameter->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			
			// print_r($htmlTemplateParameter);
			
			$DAhtmlTemplateParameter = new htmlTemplateParameterDBAccess();
			if($DAhtmlTemplateParameter->InserthtmlTemplateParameter($htmlTemplateParameter) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to insert</font></h3>
                <?php
			} else {
				// Success
				$htmlTemplateParameter->PID = $mtooldb->insert_id;
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_HTML_TEMPLATE_PARAMETER"); ?></font></h3>
                <?php
			}
			// == END OF EDITABLE AREA FOR "Insert Data" ==
		}
		
	} else if (is_numeric($htmlTemplateParameter->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAhtmlTemplateParameter = new htmlTemplateParameterDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			
			if($DAhtmlTemplateParameter->UpdatehtmlTemplateParameter($htmlTemplateParameter) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red"><?php print getres("ACTION_UPDATED_HTML_TEMPLATE_PARAMETER"); ?></font></h3>
                <?php
			}
			// == END OF EDITABLE AREA FOR "Update Data" ==
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			if($DAhtmlTemplateParameter->DeletehtmlTemplateParameter($htmlTemplateParameter) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to delete</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red"><?php print getres("ACTION_DELETED_HTML_TEMPLATE_PARAMETER"); ?></font></h3>
                <?php
				$needToLoad = false;
				$showForm = false;
			}
			// == END OF EDITABLE AREA FOR "Delete Data" ==
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$htmlTemplateParameter = $DAhtmlTemplateParameter->GethtmlTemplateParameter($htmlTemplateParameter->PID, $htmlTemplateParameter->htmlTemplatePID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! HTML Template Parameter PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($htmlTemplateParameter->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_HTML_TEMPLATE_PARAMETER");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_HTML_TEMPLATE_PARAMETER");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $htmlTemplateParameter != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForhtmlTemplate($HeaderCaption, $htmlTemplateParameter->htmlTemplatePID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="html_template_parameter_edit.php" method="post">
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("ParameterName", $htmlTemplateParameter->ParameterName,
			array($LANG_ENGLISH=>"Parameter Name", $LANG_JAPANESE=>"パラメータ名"),
			array($LANG_ENGLISH=>"Please input Parameter Name", $LANG_JAPANESE=>"パラメータ名を入力して下さい。"),
			"text", "");
		
		$TargetValueTypeSelections = array(
				array("VALUE"=>htmlTemplateParameterTargetValueTypeEnum::$CODE, "CAPTION"=>GethtmlTemplateParameterTargetValueTypeCaption(htmlTemplateParameterTargetValueTypeEnum::$CODE)),
				array("VALUE"=>htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE, "CAPTION"=>GethtmlTemplateParameterTargetValueTypeCaption(htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE))
				);
		if ($htmlTemplate->TargetType == htmlTemplateTargetTypeEnum::$HTML) {
			array_unshift($TargetValueTypeSelections,
				array("VALUE"=>htmlTemplateParameterTargetValueTypeEnum::$EACHHTML, "CAPTION"=>GethtmlTemplateParameterTargetValueTypeCaption(htmlTemplateParameterTargetValueTypeEnum::$EACHHTML))
			);
		}
		
		mtoolCommonFormSelect("TargetValueType", $htmlTemplateParameter->TargetValueType,
			array($LANG_ENGLISH=>"Value Type", $LANG_JAPANESE=>"値の種類"),
			array($LANG_ENGLISH=>"Please select Value Type", $LANG_JAPANESE=>"値の種類を選択して下さい"), 
			$TargetValueTypeSelections
			, array(
				array("VALUE"=>htmlTemplateParameterTargetValueTypeEnum::$EACHHTML, "SHOW"=>"DataTypeArea"),
				array("VALUE"=>htmlTemplateParameterTargetValueTypeEnum::$CODE, "SHOW"=>"TargetVariableOrClassObjectArea,TargetPropertyOfClassObjectArea"),
				array("VALUE"=>htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE, "SHOW"=>"AnotherTemplatePIDArea")
			), "limitParameterTypeArea");
		
		$DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate = new htmlTemplate_leftouterjoin_ParentHtmlTemplateDBAccess();
		$originalhtmlTemplateList = $DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate->GethtmlTemplateList();
		$htmlTemplateList = SorthtmlTemplateDataListByTree($originalhtmlTemplateList);
		
		$htmlTemplateSelections = array();
		for($i = 0 ; $i < count($htmlTemplateList) ; $i++) {
			$thishtmlTemplate = $htmlTemplateList[$i];
			
			if ($thishtmlTemplate->ParentHtmlTemplatePID == $htmlTemplateParameter->htmlTemplatePID) {
				$thisCaption = $thishtmlTemplate->name;
				
				if ($thishtmlTemplate->ParentHtmlTemplatename != "") {
					$thisCaption = $thisCaption . " (Parent: " . $thishtmlTemplate->ParentHtmlTemplatename . ")";
				}
				array_push($htmlTemplateSelections,
					array("VALUE"=>$thishtmlTemplate->PID, "CAPTION"=>$thisCaption)
					);
			}
		}
		mtoolCommonFormSelect("AnotherTemplatePID", $htmlTemplateParameter->AnotherTemplatePID,
			array($LANG_ENGLISH=>"Html Template", $LANG_JAPANESE=>"HTMLテンプレート"),
			array($LANG_ENGLISH=>"Please select Parent Html Template", $LANG_JAPANESE=>"HTMLテンプレートを選択して下さい"), 
			$htmlTemplateSelections
			, array(), "AnotherTemplatePIDArea");
		
		mtoolCommonFormInput("TargetVariableOrClassObject", $htmlTemplateParameter->TargetVariableOrClassObject,
			array($LANG_ENGLISH=>"Variable Name", $LANG_JAPANESE=>"変数名"),
			array($LANG_ENGLISH=>"Please input Variable Name (Don't include '\$')", $LANG_JAPANESE=>"変数名を入力して下さい。(\$は含めない)"),
			"text", "TargetVariableOrClassObjectArea");
		mtoolCommonFormInput("TargetPropertyOfClassObject", $htmlTemplateParameter->TargetPropertyOfClassObject,
			array($LANG_ENGLISH=>"Property Name", $LANG_JAPANESE=>"プロパティ名"),
			array($LANG_ENGLISH=>"Please input Property Name if Variable is Class Object", $LANG_JAPANESE=>"変数がオブジェクトの場合は参照するプロパティ名を入力して下さい。"),
			"text", "TargetPropertyOfClassObjectArea");
		mtoolCommonFormSelect("DataType", $htmlTemplateParameter->DataType,
			array($LANG_ENGLISH=>"Data Type", $LANG_JAPANESE=>"データ種類"),
			array($LANG_ENGLISH=>"Please select Data Type (for Each HTML)", $LANG_JAPANESE=>"データの種類を選択して下さい (for Each HTML)"), 
			array(
				array("VALUE"=>htmlTemplateParameterDataTypeEnum::$DEFAULT, "CAPTION"=>GethtmlTemplateParameterDataTypeCaption(htmlTemplateParameterDataTypeEnum::$DEFAULT)),
				array("VALUE"=>htmlTemplateParameterDataTypeEnum::$DATACLASSNAME, "CAPTION"=>GethtmlTemplateParameterDataTypeCaption(htmlTemplateParameterDataTypeEnum::$DATACLASSNAME)),
				array("VALUE"=>htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME, "CAPTION"=>GethtmlTemplateParameterDataTypeCaption(htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME))
			), array(
			), "DataTypeArea");
		mtoolCommonFormCheckBoxForBoolean("TrimLastSpace", $htmlTemplateParameter->TrimLastSpace,
			array($LANG_ENGLISH=>"Trim Last Spece", $LANG_JAPANESE=>"後空白文字Trim"),
			array($LANG_ENGLISH=>"Trim Last Spece after string", $LANG_JAPANESE=>"文字の後ろの空白文字を取り除きます"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("TrimLastReturn", $htmlTemplateParameter->TrimLastReturn,
			array($LANG_ENGLISH=>"Trim Last Return", $LANG_JAPANESE=>"後改行文字Trim"),
			array($LANG_ENGLISH=>"Trim Last Return after string", $LANG_JAPANESE=>"文字の後ろの改行文字を取り除きます"),
			"", "", true);
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($htmlTemplateParameter->PID != "") {
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
		<input name="htmlTemplatePID" type="hidden" value="<?php print htmlspecialchars($htmlTemplateParameter->htmlTemplatePID); ?>">
		<input name="htmlTemplateParameterPID" type="hidden" value="<?php print htmlspecialchars($htmlTemplateParameter->PID); ?>">
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
    <p><a href="html_template_parameters.php?htmlTemplatePID=<?php print urlencode($htmlTemplateParameter->htmlTemplatePID); ?>&<?php print makeRandStr(8); ?>">Back to HTML Template Parameter List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
