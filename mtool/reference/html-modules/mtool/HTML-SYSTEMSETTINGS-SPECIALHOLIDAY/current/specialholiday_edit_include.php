<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$SpecialHoliday = new SpecialHolidayData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$SpecialHoliday->PID = trim(GetParam("PID"));
$SpecialHoliday->year = trim(GetParam("year"));
$SpecialHoliday->month = trim(GetParam("month"));
$SpecialHoliday->day = trim(GetParam("day"));
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

$now_t = time();
$now_y = date("Y", $now_t);
$now_m = date("n", $now_t);
$now_d = date("j", $now_t);

function GetSpecialHolidaySelectionList($from, $upto)
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
		$SpecialHoliday->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($SpecialHoliday->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DASpecialHoliday = new SpecialHolidayDBAccess();
			$insertResult = $DASpecialHoliday->InsertSpecialHoliday($SpecialHoliday);
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
				$SpecialHoliday->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_SPECIAL_HOLIDAY"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $SpecialHoliday->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($SpecialHoliday->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $SpecialHoliday->PID)) {
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
		
	} else if (is_numeric($SpecialHoliday->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DASpecialHoliday = new SpecialHolidayDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DASpecialHoliday->UpdateSpecialHoliday($SpecialHoliday);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_SPECIAL_HOLIDAY"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DASpecialHoliday->DeleteSpecialHoliday($SpecialHoliday);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_SPECIAL_HOLIDAY"); ?></font></h3>
                <?php
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$SpecialHoliday = $DASpecialHoliday->GetSpecialHoliday($SpecialHoliday->PID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! SpecialHoliday PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($SpecialHoliday->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_SPECIAL_HOLIDAY");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_SPECIAL_HOLIDAY");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $SpecialHoliday != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForSpecialHolidaySetting($HeaderCaption);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="specialholiday_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		
		$start_year = -1;
		if ($SpecialHoliday->PID == "") {
			// Add
			$start_year = $now_y;
		} else {
			// Select/Update
			$start_year = 2016;
		}
		mtoolCommonFormSelect("year", $SpecialHoliday->year,
			array($LANG_ENGLISH=>"Year", $LANG_JAPANESE=>"年"),
			array($LANG_ENGLISH=>"Please select Year", $LANG_JAPANESE=>"年を選択して下さい"), 
			GetSpecialHolidaySelectionList($start_year, $now_y + 3)
			, array(), "");
		mtoolCommonFormSelect("month", $SpecialHoliday->month,
			array($LANG_ENGLISH=>"Year", $LANG_JAPANESE=>"月"),
			array($LANG_ENGLISH=>"Please select Year", $LANG_JAPANESE=>"月を選択して下さい"), 
			GetSpecialHolidaySelectionList(1, 12)
			, array(), "");
		mtoolCommonFormSelect("day", $SpecialHoliday->day,
			array($LANG_ENGLISH=>"Day", $LANG_JAPANESE=>"日"),
			array($LANG_ENGLISH=>"Please select Day", $LANG_JAPANESE=>"日を選択して下さい"), 
			GetSpecialHolidaySelectionList(1, 31)
			, array(), "");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($SpecialHoliday->PID != "") {
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
		<input name="PID" type="hidden" value="<?php print htmlspecialchars($SpecialHoliday->PID); ?>">
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
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to Special Holiday List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
