<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$htmlParameter = new htmlParameterData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$htmlParameter->ProjectPID = trim(GetParam("ProjectPID"));
$htmlParameter->htmlPID = trim(GetParam("htmlPID"));
$htmlParameter->PID = trim(GetParam("htmlParameterPID"));
$htmlParameter->ParameterName = trim(GetParam("ParameterName"));
$htmlParameter->ParameterValue = trim(GetParam("ParameterValue"));

$DataType = trim(GetParam("DataType"));
$DataClassPID = trim(GetParam("DataClassPID"));
$DAPID = trim(GetParam("DAPID"));

$DAdataclass = new dataclassDBAccess();
$DAda = new daDBAccess();

switch($DataType)
{
	case htmlTemplateParameterDataTypeEnum::$DEFAULT:
		break;
	case htmlTemplateParameterDataTypeEnum::$DATACLASSNAME:
		if (is_numeric($DataClassPID)) {
			$dataclass = $DAdataclass->Getdataclass($DataClassPID, $htmlParameter->ProjectPID);
			if ($dataclass) {
				$htmlParameter->ParameterValue = CreateDataClassName($dataclass->name);
			}
		}
		break;
	case htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME:
		if (is_numeric($DAPID)) {
			$da = $DAda->GetdaList($htmlParameter->ProjectPID);
			if ($da) {
				$htmlParameter->ParameterValue = CreateDatabaseAccessClassName($da->name);
			}
		}
		break;
}

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($htmlParameter->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==

$DAhtml = new htmlDBAccess();
$html = NULL;

if ($NoError) {
	$html = $DAhtml->Gethtml($htmlParameter->htmlPID, $htmlParameter->ProjectPID);
	if (!$html) {
		$NoError = false;
		?>
		<H3><font color="red">ERROR! No Corresponding HTML</font></H3>
		<?php
	}
}
// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$htmlParameter->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($htmlParameter->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAhtmlParameter = new htmlParameterDBAccess();
			$insertResult = $DAhtmlParameter->InserthtmlParameter($htmlParameter);
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
				$htmlParameter->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_HTML_PARAMETER"); ?></font></h3>
                <?php
				$DAhtml = new htmlDBAccess();
				$DAhtml->UpdateLastModifiedDT($htmlParameter->htmlPID, $htmlParameter->ProjectPID);
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $htmlParameter->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($htmlParameter->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $htmlParameter->PID)) {
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
		
	} else if (is_numeric($htmlParameter->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAhtmlParameter = new htmlParameterDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAhtmlParameter->UpdatehtmlParameter($htmlParameter);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_HTML_PARAMETER"); ?></font></h3>
                <?php
				$DAhtml = new htmlDBAccess();
				$DAhtml->UpdateLastModifiedDT($htmlParameter->htmlPID, $htmlParameter->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAhtmlParameter->DeletehtmlParameter($htmlParameter);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_HTML_PARAMETER"); ?></font></h3>
                <?php
				$DAhtml = new htmlDBAccess();
				$DAhtml->UpdateLastModifiedDT($htmlParameter->htmlPID, $htmlParameter->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$htmlParameter = $DAhtmlParameter->GethtmlParameter($htmlParameter->PID, $htmlParameter->ProjectPID);
			
			switch($DataType)
			{
				case htmlTemplateParameterDataTypeEnum::$DEFAULT:
					break;
				case htmlTemplateParameterDataTypeEnum::$DATACLASSNAME:
					$dataclasslist = $DAdataclass->GetdataclassList($htmlParameter->ProjectPID); 
					for($i = 0 ; $i < count($dataclasslist); $i++) {
						$thisdataclass = $dataclasslist[$i];
						if (CreateDataClassName($thisdataclass->name) == $htmlParameter->ParameterValue) {
							$DataClassPID = $thisdataclass->PID;
						}
					}
					break;
				case htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME:
					$dalist = $DAda->GetdaList($htmlParameter->ProjectPID); 
					for($i = 0 ; $i < count($dalist); $i++) {
						$thisda = $dalist[$i];
						if (CreateDatabaseAccessClassName($thisda->name) == $htmlParameter->ParameterValue) {
							$DAPID = $thisda->PID;
						}
					}
					break;
			}
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! HTML Parameter PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($htmlParameter->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_HTML_PARAMETER");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_HTML_PARAMETER");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $htmlParameter != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForHtml($HeaderCaption, $htmlParameter->ProjectPID, $htmlParameter->htmlPID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="html_parameter_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("ParameterName", $htmlParameter->ParameterName,
			array($LANG_ENGLISH=>"Parameter Name", $LANG_JAPANESE=>"パラメータ名"),
			array($LANG_ENGLISH=>"Please input Parameter Name", $LANG_JAPANESE=>"パラメータ名を入力して下さい。"),
			"text", "");
		
		switch($DataType)
		{
			case htmlTemplateParameterDataTypeEnum::$DEFAULT:
				mtoolCommonFormInput("ParameterValue", $htmlParameter->ParameterValue,
					array($LANG_ENGLISH=>"Parameter Value", $LANG_JAPANESE=>"パラメータ値"),
					array($LANG_ENGLISH=>"Please input Parameter Value", $LANG_JAPANESE=>"パラメータ値を入力して下さい。"),
					"text", "");
				break;
			case htmlTemplateParameterDataTypeEnum::$DATACLASSNAME:
				$dataclasslist = $DAdataclass->GetdataclassList($htmlParameter->ProjectPID); 
				$dataclassSelections = array();
				for($i = 0 ; $i < count($dataclasslist); $i++) {
					$thisdataclass = $dataclasslist[$i];
					array_push($dataclassSelections,
							array("VALUE"=>$thisdataclass->PID, "CAPTION"=>CreateDataClassName($thisdataclass->name))
						);
				}
				mtoolCommonFormSelect("DataClassPID", $DataClassPID,
					array($LANG_ENGLISH=>"Data Class", $LANG_JAPANESE=>"データクラス"),
					array($LANG_ENGLISH=>"Please select Data Class", $LANG_JAPANESE=>"データクラスを選択して下さい"),
					$dataclassSelections, array(), "");
				break;
			case htmlTemplateParameterDataTypeEnum::$DBACCESSCLASSNAME:
				$dalist = $DAda->GetdaList($htmlParameter->ProjectPID); 
				$dbaccessclassSelections = array();
				for($i = 0 ; $i < count($dalist); $i++) {
					$thisda = $dalist[$i];
					array_push($dbaccessclassSelections,
							array("VALUE"=>$thisda->PID, "CAPTION"=>CreateDatabaseAccessClassName($thisda->name))
						);
				}
				mtoolCommonFormSelect("DAPID", $DAPID,
					array($LANG_ENGLISH=>"DB Access Class", $LANG_JAPANESE=>"データアクセスクラス"),
					array($LANG_ENGLISH=>"Please select DB Access Class", $LANG_JAPANESE=>"データアクセスクラスを選択して下さい"),
					$dbaccessclassSelections, array(), "");
				break;
		}
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($htmlParameter->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($htmlParameter->ProjectPID); ?>">
		<input name="htmlPID" type="hidden" value="<?php print htmlspecialchars($htmlParameter->htmlPID); ?>">
		<input name="htmlParameterPID" type="hidden" value="<?php print htmlspecialchars($htmlParameter->PID); ?>">
		<input name="DataType" type="hidden" value="<?php print htmlspecialchars($DataType); ?>">
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
    <p><a href="html_parameters.php?ProjectPID=<?php print urlencode($htmlParameter->ProjectPID); ?>&htmlPID=<?php print urlencode($htmlParameter->htmlPID); ?>&<?php print makeRandStr(8); ?>">Back to Html Parameter List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
