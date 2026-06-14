<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$LanguageResource = new LanguageResourceData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$LanguageResource->ProjectPID = trim(GetParam("ProjectPID"));
$LanguageResource->PID = trim(GetParam("PID"));
$LanguageResource->KeyForUpdate = GetParam("KeyForUpdate");
$LanguageResource->SortGroup = trim(GetParam("SortGroup"));
$LanguageResource->KeyName = trim(GetParam("KeyName"));
$LanguageResource->KeyNameForXcode = trim(GetParam("KeyNameForXcode"));
$LanguageResource->LanguageResourceGroupPID = trim(GetParam("LanguageResourceGroupPID"));
$LanguageResource->UWPTargetProperty = GetParam("UWPTargetProperty");
$LanguageResource->IsResourceFixed = GetParam("IsResourceFixed");
$LanguageResource->UseDefaultIfCaptionIsBlank = GetParam("UseDefaultIfCaptionIsBlank");

$PID_BY_KEYNAME = trim(GetParam("PID_BY_KEYNAME"));
if ($PID_BY_KEYNAME != "") {
	$DALanguageResource = new LanguageResourceDBAccess();
	$LanguageResourceByKeyName = $DALanguageResource->GetLanguageResourceByKeyName($LanguageResource->ProjectPID, $PID_BY_KEYNAME);
	if ($LanguageResourceByKeyName) {
		$LanguageResource->PID = $LanguageResourceByKeyName->PID;
	}
}

$SourceResourcePID = trim(GetParam("SourceResourcePID"));
$duplicate = (trim(GetParam("duplicate")) != "");
if ($duplicate && is_numeric($SourceResourcePID)) {
	$DALanguageResource = new LanguageResourceDBAccess();
	$LanguageResourceSource = $DALanguageResource->GetLanguageResource($SourceResourcePID, $LanguageResource->ProjectPID);
	
	if ($LanguageResourceSource) {
		$LanguageResource->SortGroup = $LanguageResourceSource->SortGroup;
		$LanguageResource->KeyName = $LanguageResourceSource->KeyName;
		$LanguageResource->KeyNameForXcode = $LanguageResourceSource->KeyNameForXcode;
	}
}

$NewKeyForUpdate = trim(GetParam("NewKeyForUpdate"));

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($LanguageResource->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");

include_once("lang_res_check_project_source_output_setting_lib.php");
if ($NoError) {
	CheckProjectSourceOutputSettingForLanguageResource($LanguageResource->ProjectPID);
}

// Load Data if Group PID is blank
if (is_numeric($LanguageResource->PID) && is_numeric($LanguageResource->ProjectPID) && trim($LanguageResource->LanguageResourceGroupPID) == "") {
	$DALanguageResource = new LanguageResourceDBAccess();
	$LanguageResource = $DALanguageResource->GetLanguageResource($LanguageResource->PID, $LanguageResource->ProjectPID);
}

$DALanguageResourceGroupLang = new LanguageResourceGroupLangDBAccess();
$LanguageResourceGroupLangList = $DALanguageResourceGroupLang->GetLanguageResourceGroupLangList($LanguageResource->ProjectPID, $LanguageResource->LanguageResourceGroupPID);

$DALanguageResourceCaption = new LanguageResourceCaptionDBAccess();

function UpdateLanguageResourceCaption()
{
	global $DALanguageResourceGroupLang;
	global $LanguageResourceGroupLangList;
	global $DALanguageResourceCaption;
	global $LanguageResource;
	
	$LanguageResourceCaptionList = $DALanguageResourceCaption->GetLanguageResourceCaptionList($LanguageResource->ProjectPID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID);
	
	for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
		$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
		
		$LanguageResourceCaptionObj = new LanguageResourceCaptionData();
		$LanguageResourceCaptionObj->ProjectPID = $LanguageResource->ProjectPID;
		$LanguageResourceCaptionObj->LanguageResourcePID = $LanguageResource->PID;
		$LanguageResourceCaptionObj->LanguageResourceGroupPID = $LanguageResource->LanguageResourceGroupPID;
		$LanguageResourceCaptionObj->LanguageResourceLangPID = $LanguageResourceGroupLang->LanguageResourceLangPID;
		$LanguageResourceCaptionObj->Caption = GetParam("Caption" . $LanguageResourceGroupLang->LanguageResourceLangPID);
		$LanguageResourceCaptionObj->CaptionAutoTranslated = GetParam("Caption" . $LanguageResourceGroupLang->LanguageResourceLangPID . "AutoTranslated");
		
		$ExitCurrentData = false;
		for($k = 0 ; $k < count($LanguageResourceCaptionList) ; $k++) {
			$LanguageResourceCaption = $LanguageResourceCaptionList[$k];
			
			if ($LanguageResourceCaption->LanguageResourceLangPID == $LanguageResourceGroupLang->LanguageResourceLangPID) {
				$ExitCurrentData = true;
				break;
			}
		}
		if (!$ExitCurrentData) {
			// Insert
			$DALanguageResourceCaption->InsertLanguageResourceCaption($LanguageResourceCaptionObj);
		} else {
			// Update
			$DALanguageResourceCaption->UpdateLanguageResourceCaption($LanguageResourceCaptionObj);
		}
	}
}

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$LanguageResource->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($LanguageResource->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$LanguageResource->KeyForUpdate = $NewKeyForUpdate;
			
			$DALanguageResource = new LanguageResourceDBAccess();
			$insertResult = $DALanguageResource->InsertLanguageResource($LanguageResource);
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
				$LanguageResource->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				UpdateLanguageResourceCaption();
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_LANGUAGE_RESOURCE"); ?></font></h3>
                <?php
				update_language_resource_LastModifiedDT($LanguageResource->LanguageResourceGroupPID, $LanguageResource->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $LanguageResource->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($LanguageResource->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $LanguageResource->PID)) {
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
		
	} else if (is_numeric($LanguageResource->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DALanguageResource = new LanguageResourceDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$param_LanguageResource_KeyForUpdate_where = $LanguageResource->KeyForUpdate;
			$LanguageResource->KeyForUpdate = $NewKeyForUpdate;
			
			$updateResult = $DALanguageResource->UpdateLanguageResource($LanguageResource, $LanguageResource->PID, $LanguageResource->ProjectPID, $param_LanguageResource_KeyForUpdate_where);
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
				UpdateLanguageResourceCaption();
				
				$UpdatedLanguageResource = $DALanguageResource->GetLanguageResource($LanguageResource->PID, $LanguageResource->ProjectPID);
				if ($UpdatedLanguageResource && $UpdatedLanguageResource->KeyForUpdate == $LanguageResource->KeyForUpdate) {
					?>
					<h3><font color="red"><?php print getres("ACTION_UPDATED_LANGUAGE_RESOURCE"); ?></font></h3>
					<?php
					update_language_resource_LastModifiedDT($LanguageResource->LanguageResourceGroupPID, $LanguageResource->ProjectPID);
					
				} else {
					?>
					<h1><font color="red">Confrict Error! Failed to update. Someone may update this Resource. Please reopen and update again.</font></h1>
					<?php
					$needToLoad = false;
				}
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DALanguageResource->DeleteLanguageResource($LanguageResource);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_LANGUAGE_RESOURCE"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$LanguageResource = $DALanguageResource->GetLanguageResource($LanguageResource->PID, $LanguageResource->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! LanguageResource PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($LanguageResource->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_LANGUAGE_RESOURCE");
		
		$LanguageResource->UseDefaultIfCaptionIsBlank = 1;
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_LANGUAGE_RESOURCE");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $LanguageResource != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForLanguageResource($HeaderCaption, $LanguageResource->ProjectPID, $LanguageResource->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="lang_res_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		if ($duplicate) {
			include_once("lang_res_select_resource_group_lib.php");
		}
		
		mtoolCommonFormInput("SortGroup", $LanguageResource->SortGroup,
			array($LANG_ENGLISH=>"Sort String", $LANG_JAPANESE=>"ソート用文字列"),
			array($LANG_ENGLISH=>"Please input Sort String (for sort order)", $LANG_JAPANESE=>"ソート用文字列を入力して下さい。(出力順番決定用)"),
			"text", "");
		mtoolCommonFormInput("KeyName", $LanguageResource->KeyName,
			array($LANG_ENGLISH=>"Key Name", $LANG_JAPANESE=>"キー名"),
			array($LANG_ENGLISH=>"Please input Key Name", $LANG_JAPANESE=>"キー名を入力して下さい。"),
			"text", "");
		
		if ($IsIncludeXcode) {
			mtoolCommonFormInput("KeyNameForXcode", $LanguageResource->KeyNameForXcode,
				array($LANG_ENGLISH=>"Key Name for Xcode", $LANG_JAPANESE=>"Xcode向けキー名"),
				array($LANG_ENGLISH=>"Please input Key Name for Xcode (If empty, Key Name is used)", $LANG_JAPANESE=>"Xcode向けキー名を入力して下さい。(空の場合はキー名が使用されます)"),
				"text", "");
		} else {
			?>
			<input name="KeyNameForXcode" type="hidden" value="<?php print htmlspecialchars($LanguageResource->KeyNameForXcode); ?>">
			<?php
		}
		
		$LanguageResourceCaptionList = $DALanguageResourceCaption->GetLanguageResourceCaptionList($LanguageResource->ProjectPID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID);
		
		for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
			$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
			
			$thisCaptin = "";
			$thisAutoTranslatedCaptin = "";
			for($k = 0 ; $k < count($LanguageResourceCaptionList) ; $k++) {
				$LanguageResourceCaption = $LanguageResourceCaptionList[$k];
				
				if ($LanguageResourceCaption->LanguageResourceLangPID == $LanguageResourceGroupLang->LanguageResourceLangPID) {
					$thisCaptin = $LanguageResourceCaption->Caption;
					$thisAutoTranslatedCaptin = $LanguageResourceCaption->CaptionAutoTranslated;
					break;
				}
			}
			mtoolCommonFormInput("Caption" . $LanguageResourceGroupLang->LanguageResourceLangPID, $thisCaptin,
				array($LANG_ENGLISH=>$LanguageResourceGroupLang->LanguageResourceLangCaption, $LANG_JAPANESE=>$LanguageResourceGroupLang->LanguageResourceLangCaption),
				array($LANG_ENGLISH=>"Please input Text", $LANG_JAPANESE=>"文字列を入力して下さい。"),
				"text", "");
			
			$this_id_name = "Caption" . $LanguageResourceGroupLang->LanguageResourceLangPID;
			?>
			<input name="<?php print $this_id_name; ?>AutoTranslated" id="<?php print $this_id_name; ?>AutoTranslated" type="hidden" value="<?php print htmlspecialchars($thisAutoTranslatedCaptin); ?>">
			<?php
		}
		
		if ($IsDotNetUWP) {
			mtoolCommonFormInput("UWPTargetProperty", $LanguageResource->UWPTargetProperty,
				array($LANG_ENGLISH=>"Property for UWP", $LANG_JAPANESE=>"UWP用プロパティ名"),
				array($LANG_ENGLISH=>"Please input Property for UWP (Automatically add \".\"(Dot) before property)", $LANG_JAPANESE=>"UWP用プロパティ名を入力して下さい。（プロパティ名の前に\".\"(ドット)が自動的に付きます）"),
				"text", "");
		} else {
			?>
			<input name="UWPTargetProperty" type="hidden" value="<?php print htmlspecialchars($LanguageResource->UWPTargetProperty); ?>">
			<?php
		}
		mtoolCommonFormCheckBoxForValue("IsResourceFixed", $LanguageResource->IsResourceFixed,
			array($LANG_ENGLISH=>"Fixed?", $LANG_JAPANESE=>"修正完了?"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		mtoolCommonFormCheckBoxForValue("UseDefaultIfCaptionIsBlank", $LanguageResource->UseDefaultIfCaptionIsBlank,
			array($LANG_ENGLISH=>"Use Default if Blank?", $LANG_JAPANESE=>"空文字列の場合はデフォルトを使う?"),
			array($LANG_ENGLISH=>"Yes", $LANG_JAPANESE=>"はい"),
			"", "", true, "1");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($LanguageResource->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($LanguageResource->ProjectPID); ?>">
        <?php
		if (!$duplicate) {
			?>
			<input name="LanguageResourceGroupPID" type="hidden" value="<?php print htmlspecialchars($LanguageResource->LanguageResourceGroupPID); ?>">
			<?php
		}
		?>
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($LanguageResource->PID); ?>">
		<input name="KeyForUpdate" type="hidden" value="<?php print htmlspecialchars($LanguageResource->KeyForUpdate); ?>">
		<input name="NewKeyForUpdate" type="hidden" value="<?php print htmlspecialchars(makeRandStr(128)); ?>">
		
    <div class="row">
      <div class="col-md-3">Translate Source
      </div>
      <div class="col-md-9">
      <textarea name="TranslateSourceValue" cols="80" rows="5" id="TranslateSourceValue"></textarea>
      <input name="TranslateSourceLang" type="hidden" id="TranslateSourceLang" value="">
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">
         Auto Translation by Google
      </div>
      <div class="col-md-9">
       <input type="button" id="AutoTranslateForAllBlankTarget" value="Auto Translate by Google for all Blank Value" style="display: none">
      </div>
    </div>	
		<?php
		for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
			$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
			?>
    <div class="row">
      <div class="col-md-3">
        <input type="button" value="Set Source Text for Translation" class="SetSourceText" CaptionTarget="Caption<?php print $LanguageResourceGroupLang->LanguageResourceLangPID; ?>" TranslateSourceLang="<?php print $LanguageResourceGroupLang->LanguageResourceLangLangForGoogle; ?>">
       <?php print $LanguageResourceGroupLang->LanguageResourceLangCaption; ?> 
      </div>
      <div class="col-md-9">
       <input type="button" class="AutoTranslate" value="Auto Translate by Google" CaptionTarget="Caption<?php print $LanguageResourceGroupLang->LanguageResourceLangPID; ?>" TargetLang="<?php print $LanguageResourceGroupLang->LanguageResourceLangLangForGoogle; ?>" style="display: none">
      </div>
    </div>
			<?php
		}
		?>
<script>
$(function() {
	$(".SetSourceText").click(function() {
		var SourceName = $(this).attr("CaptionTarget");
		$("#TranslateSourceValue").val($("#" + SourceName).val());
		$("#TranslateSourceLang").val($(this).attr("TranslateSourceLang"));
		$(".AutoTranslate").show();
		$("#AutoTranslateForAllBlankTarget").show();
	})
	function DoTranslateByGoogle(TargetName, TargetLang) {
		var SourceText = $("#TranslateSourceValue").val();
		var SourceLang = $("#TranslateSourceLang").val();
		
		jQuery.ajax(
			"lang_res_auto_translate_ajax.php",{
				type: "POST",
				dataType: 'json',
				data: {
					"ProjectPID": "<?php print htmlspecialchars($LanguageResource->ProjectPID); ?>",
					"SourceText": SourceText,
					"SourceLang": SourceLang,
					"TargetLang": TargetLang
				},
				success: function(json){
					if (json._status == "OK") {
						if ($("#" + TargetName).val() != json.TranslatedText) {
							$("#" + TargetName).val(json.TranslatedText);
							$("#" + TargetName + "AutoTranslated").val(json.TranslatedText);
							uncheck_is_resouce_fixed_checkbox();
						}
					} else {
						alert("Failed to Translate");
					}
				},
				error : function() {
					alert("Internal Error while checking Completed Status.");
				},
				complete: function() {
				}
			}
		);
	}
	$('#AutoTranslateForAllBlankTarget').click(function() {
		<?php
		for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
			$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
?>
			var CaptionTarget = "Caption<?php print $LanguageResourceGroupLang->LanguageResourceLangPID; ?>";
			var TargetLang="<?php print $LanguageResourceGroupLang->LanguageResourceLangLangForGoogle; ?>";
			if ($("#" + CaptionTarget).val() == "") {
				DoTranslateByGoogle(CaptionTarget, TargetLang);
			}
<?php
		}
		?>
	});
	$('.AutoTranslate').click(function() {
		var TargetName = $(this).attr("CaptionTarget");
		var TargetLang = $(this).attr("TargetLang");
		
		DoTranslateByGoogle(TargetName, TargetLang);
	});
});
</script>		
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
<script>

function uncheck_is_resouce_fixed_checkbox()
{
	$("#IsResourceFixed").prop('checked',false);
}
$(function() {
	$('#SortGroup').on('input', function() {
		uncheck_is_resouce_fixed_checkbox();
	});
	$('#KeyName').on('input', function() {
		uncheck_is_resouce_fixed_checkbox();
	});
	$('#KeyNameForXcode').on('input', function() {
		uncheck_is_resouce_fixed_checkbox();
	});
<?php
for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
	$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
	?>
	$('#Caption<?php print $LanguageResourceGroupLang->LanguageResourceLangPID; ?>').on('input', function() {
		uncheck_is_resouce_fixed_checkbox();
	});
	<?php
}
?>
	$('#UWPTargetProperty').on('input', function() {
		uncheck_is_resouce_fixed_checkbox();
	});
});
</script>
	<?php
	if ($LanguageResource->PID != "") {
	?>
    <p align="right"><a href="lang_res_move.php?PID=<?php print urlencode($LanguageResource->PID); ?>&ProjectPID=<?php print urlencode($LanguageResource->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Move</a></p>
   <p align="right"><a href="lang_res_edit.php?SourceResourcePID=<?php print urlencode($LanguageResource->PID); ?>&ProjectPID=<?php print urlencode($LanguageResource->ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($LanguageResource->LanguageResourceGroupPID); ?>&duplicate=y&<?php print makeRandStr(8); ?>">Duplicate</a></p>
    <?php
	}
	?>
	<br>
	<br>
	<br>
    <p><a href="lang_res_list.php?ProjectPID=<?php print urlencode($LanguageResource->ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($LanguageResource->LanguageResourceGroupPID); ?>&<?php print makeRandStr(8); ?>">Back to Language Resource List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
