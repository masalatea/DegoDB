<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_USER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DA_FUNC_SORT_ORDER_EDIT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
// End Template Content

// Start Template Content: HTML_HEAD_BOTTOM
// End Template Content

// Start Template Content: HTML_BODY_MAIN_JUMBOTRON
// End Template Content

// Start Template Content: HTML_BODY_MAIN_UPPER
// End Template Content

// Start Template Content: HTML_BODY_MAIN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_SIMPLE
<?php

include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$dafunc = new dafuncData();
$dafunc->ProjectPID = trim(GetParam("ProjectPID"));
$dafunc->daPID = trim(GetParam("DAPID"));
$dafunc->PID = trim(GetParam("DAFuncPID"));

$dafunc->SortOrderColumns = GetParam("SortOrderColumns");		// Array Parameter
if (is_array($dafunc->SortOrderColumns)) {
	$result = "";
	for ($i = 0 ;$i < count($dafunc->SortOrderColumns); $i++) {
		$thisVal = trim($dafunc->SortOrderColumns[$i]);
		if ($thisVal != "") {
			if ($result != "") {
				$result .= ",";
			}
			$result .= $thisVal;
		}
	}
	$dafunc->SortOrderColumns = $result;
}

$NoError = true;
if (!is_numeric($dafunc->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafunc->daPID)) {
	?>
    <H3><font color="red">DB Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($dafunc->PID)) {
	?>
    <H3><font color="red">DB Function Access Class is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

$showForm = true;

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if (is_numeric($dafunc->PID)) {
		// Select/Update
		$needToLoad = true;
		$DAdafunc = new dafuncDBAccess();
		if ($UPDATE != "") {
			
			if($DAdafunc->UpdatedafuncSortOrderColumns($dafunc) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red">Updated Sort Order</font></h3>
                <?php
				update_da_LastModifiedDT($dafunc->daPID, $dafunc->ProjectPID);
			}
		}

		if ($needToLoad) {
			$dafunc = $DAdafunc->Getdafunc($dafunc->PID, $dafunc->ProjectPID);
		}
		
	} else {
		?>
		<h4>FATAL ERROR! DB Access Function PID is something strange.</h4>
		<?php
		die();
	}
	if ($dafunc->PID == "") {
		// Add
		
	} else {
		// Select/Update
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_DA_FUNC_SORT_ORDER");
	}
	
	if ($showForm && $dafunc != NULL) {
		
		printPathOnTopForDBAccessClass($HeaderCaption, $dafunc->ProjectPID, $dafunc->daPID, $dafunc->PID, "", "", "", "", "");
		
		$DAdbtable = new dbtableDBAccess();
		$DAdafuncselecttargetfields = new dafuncselecttargetfieldsDBAccess();
		$DAdafuncselectwhere = new dafuncselectwhereDBAccess();
		$DAdbtablecolumns = new dbtablecolumnsDBAccess();

		$thisBaseDataClassName = $dafunc->GetBaseDataClassName();
		$dbtable = NULL;
		if ($thisBaseDataClassName != "") {
			$dbtable = $DAdbtable->GetdbtableByName($dafunc->ProjectPID, $thisBaseDataClassName);
		}
		$dafuncselecttargetfieldlist = $DAdafuncselecttargetfields->GetdafuncselecttargetfieldsList($dafunc->ProjectPID, $dafunc->daPID, $dafunc->PID); 
		$dafuncselectwherelist = $DAdafuncselectwhere->GetdafuncselectwhereList($dafunc->ProjectPID, $dafunc->daPID, $dafunc->PID); 
		$alltablelistinproject = $DAdbtable->GetdbtableList($dafunc->ProjectPID);
		
		$DAdataclass = new dataclassDBAccess();
		$dataclasslist = $DAdataclass->GetdataclassList($dafunc->ProjectPID); 
		
		$RelatedDBTableNameList = GetRelatedDBTableList($alltablelistinproject, $dbtable, $dafuncselecttargetfieldlist, $dafuncselectwherelist, $dafunc, $dataclasslist);
		
		$selectionArray = array();
		
		for ($i = 0 ; $i < count($alltablelistinproject); $i++) {
			$thisTable = $alltablelistinproject[$i];
			
			if (CheckIfAlreadyExistTableNameOnlyInRelatedDBTableResult($RelatedDBTableNameList, $thisTable->name)) {
				$dbtablecolumnList = $DAdbtablecolumns->GetdbtablecolumnsList($dafunc->ProjectPID, $thisTable->PID);
				if ($dbtablecolumnList != NULL) {
					for ($j = 0 ; $j < count($dbtablecolumnList) ; $j++) {
						$dbtablecolumn = $dbtablecolumnList[$j];
						array_push($selectionArray, array(
							"VALUE"  =>$thisTable->name . "." . $dbtablecolumn->name,
							"CAPTION"=>$thisTable->name . "." . $dbtablecolumn->name . " (default:asc)"
							));
						array_push($selectionArray, array(
							"VALUE"  =>$thisTable->name . "." . $dbtablecolumn->name . " desc",
							"CAPTION"=>$thisTable->name . "." . $dbtablecolumn->name . " (desc)"
							));
					}
				}
			}
		}
		
		?>
		
		<form action="da_func_sort_order_edit.php" method="post">
		
        <?php
		
		$notMatchedValues = array();
		
		$BLANKNUM = 3;
		
		$SortOrderColumnsList = preg_split("/,+/", $dafunc->SortOrderColumns);
		for($i = 0 ; $i < count($SortOrderColumnsList) + $BLANKNUM ; $i++) {
			$thisVal = "";
			if ($i < count($SortOrderColumnsList)) {
				$thisVal = trim($SortOrderColumnsList[$i]);
				if ($thisVal != "") {
					$matchExist = false;
					for ($j = 0 ; $j < count($selectionArray) ; $j++) {
						$mtoolIfSelect = $selectionArray[$j]["VALUE"];
						
						if ($thisVal == $mtoolIfSelect) {
							$matchExist = true;
							break;
						}
					}
					if (!$matchExist) {
						array_push($notMatchedValues, $thisVal);
					}
				}
			}
			mtoolCommonFormSelect("SortOrderColumns[]", $thisVal,
				array($LANG_ENGLISH=>"Sort Order", $LANG_JAPANESE=>"Sort Order (Priority" . ($i+1) . ")"),
				array($LANG_ENGLISH=>"Please select Sort Order", $LANG_JAPANESE=>"Sort Orderを選択して下さい. 優先度" . ($i+1)),
				$selectionArray
				, array(), "");
		}
		?>
		
		<div class="row">
		  <label class="col-md-3 control-label" for="inputtext"></label>
		  <div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
          
			<?php
            if ($dafunc->PID != "") {
				?>
				<p align="right">
				<input name="DELETE" type="submit" value="<?php print htmlspecialchars(getres("ACTION_DELETE")); ?>" onClick="return confirm('<?php print htmlspecialchars(getres("ACTION_DELETE_CONFIRM")); ?>');">
				</p>
				<?php
            }
            ?>
          </div>
		</div>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($dafunc->ProjectPID); ?>">
		<input name="DAPID" type="hidden" value="<?php print htmlspecialchars($dafunc->daPID); ?>">
		<input name="DAFuncPID" type="hidden" value="<?php print htmlspecialchars($dafunc->PID); ?>">
		</form>
        
		<?php
		if (count($notMatchedValues) > 0) {
			?>
            <br>
            <br>
            <br>
            <h3><font color="red">Warning: Following items will be deleted when update</font></h3>
            <?php
			for($i = 0 ; $i < count($notMatchedValues); $i++) {
				$thisVal = $notMatchedValues[$i];
				?>
                <h4><font color="red"><?php print htmlspecialchars($thisVal); ?></font></h4>
                <?php
			}
			?>
            <?php
		}
	}
	?>
    <br>
    <br>
    <br>
    <p><a href="da_funcs.php?ProjectPID=<?php print urlencode($dafunc->ProjectPID); ?>&DAPID=<?php print urlencode($dafunc->daPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
    <?php
}
?>
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_JP
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_EN
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_ZH
// End Template Content

// Start Template Content: HTML_BODY_MAIN_LANG_KO
// End Template Content

// Start Template Content: HTML_BODY_MAIN_BOTTOM
// End Template Content

// Start Template Content: HTML_BOTTOM
// End Template Content
