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

function GetCorrespondingDBTableColumn($dataclassfield, $CorrespondingDBTableColumnList)
{
	$CorrespondingDBTableColumn = NULL;
	if ($CorrespondingDBTableColumnList != NULL) {
		for ( $j = 0 ; $j < count($CorrespondingDBTableColumnList); $j++) {
			$thisDBTableColumn = $CorrespondingDBTableColumnList[$j];
			
			if ($dataclassfield->name == $thisDBTableColumn->name) {
				$CorrespondingDBTableColumn = $thisDBTableColumn;
				break;
			}
		}
	}
	return $CorrespondingDBTableColumn;
}
function GetCorrespondingDAFuncUpdateDeleteWhere($dataclassfield, $dafuncupdatedeletewherelist)
{
	$CorrespondingDafuncupdatedeletewhereList = array();
	
	for ($j = 0 ; $j < count($dafuncupdatedeletewherelist); $j++) {
		$dafuncupdatedeletewhere = $dafuncupdatedeletewherelist[$j];
		
		if ($dataclassfield->name == $dafuncupdatedeletewhere->targetTableColumnName) {
			array_push($CorrespondingDafuncupdatedeletewhereList, $dafuncupdatedeletewhere);
		}
	}
	return $CorrespondingDafuncupdatedeletewhereList;
}

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DA_FUNC_UPDATE_DELETE_WHERE_INPUT_AID"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = trim(GetParam("ProjectPID"));
$DAPID = trim(GetParam("DAPID"));
$DAFuncPID = trim(GetParam("DAFuncPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Class PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($DAFuncPID)) {
	?>
    <H3><font color="red">ERROR! Unknown DB Access Function PID</font></H3>
    <?php
	$NoError = false;
}

$DAdafunc = new dafuncDBAccess();
$dafunc = NULL;


$DAdataclass = new dataclassDBAccess();
$dataclass = NULL;
$BaseDataClassName = "";

$DAdataclassfields = new dataclassfieldsDBAccess();
$DAdbtable = new dbtableDBAccess();
$DAdbtablecolumns = new dbtablecolumnsDBAccess();

if ($NoError) {
	$dafunc = $DAdafunc->Getdafunc($DAFuncPID, $ProjectPID);
	if ($dafunc == NULL) {
		?>
		<H3><font color="red">DB Access Function is not found. Please ask administrator if this continues.</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {
	printPathOnTopForDBAccessClass("Input Aid for Update/Delete's Where", $ProjectPID, $DAPID, $DAFuncPID, "", "", "", "", "");
	
	$BaseDataClassName = $dafunc->GetBaseDataClassName();
	if (trim($BaseDataClassName) == "") {
		?>
		<H3><font color="red">Stop process. Data Class Name can't be determined from Name/Class Base Name for Select Action</font></H3>
		<?php
		$NoError = false;
	}
}

if ($NoError) {	
	
	$dataclass = $DAdataclass->GetdataclassByName($ProjectPID, $BaseDataClassName);
	if ($dataclass == NULL) {
		?>
		<H3><font color="red">Stop process. Corresponding Data Class is not exist.</font></H3>
		<?php
		$NoError = false;
	}
}

InitializeOutputShortenedStringWithExpansion();

if ($NoError) {	
	?>
    <h3><font color="red">Corresponding Data Class exists: <?php print $dataclass->name; ?></font></h3>
	<?php
	
	$dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $dataclass->PID); 
	
	$DAdafuncupdatedeletewhere = new dafuncupdatedeletewhereDBAccess();
	$dafuncupdatedeletewherelist = $DAdafuncupdatedeletewhere->GetdafuncupdatedeletewhereList($ProjectPID, $DAPID, $DAFuncPID); 	
	
	$CorrespondingDBTable = $DAdbtable->GetdbtableByName($ProjectPID, $dataclass->name);
	$CorrespondingDBTableColumnList = NULL;
	if ($CorrespondingDBTable != NULL) {
		$CorrespondingDBTableColumnList = $DAdbtablecolumns->GetdbtablecolumnsList($ProjectPID, $CorrespondingDBTable->PID); 
	}
	
	if (count($dataclassfieldlist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th rowspan="2" bgcolor="#DDDDDD">Data Class's Field Name</th>
			  <th rowspan="2" bgcolor="#DDDDDD">Already Exists?</th>
			  <th colspan="6">Corresponding DB Table Info with Same Name</th>
			  <th rowspan="2" bgcolor="#DDDDDD">Input Aid (link)</th>
			</tr>
			<tr bgcolor="#ECECEC">
			  <th>DB Table Name</th>
			  <th>Data Type</th>
			  <th>Null</th>
			  <th>Key</th>
			  <th>Default</th>
			  <th>Extra</th>
			</tr>
          </thead>
            <tbody>
		<?php
		
		for($i = 0 ; $i < count($dataclassfieldlist); $i++) {
			$dataclassfield = $dataclassfieldlist[$i];
			
			$CorrespondingDBTableColumn = GetCorrespondingDBTableColumn($dataclassfield, $CorrespondingDBTableColumnList);
			$CorrespondingDafuncupdatedeletewhereList = GetCorrespondingDAFuncUpdateDeleteWhere($dataclassfield, $dafuncupdatedeletewherelist);
			
			?>
			<tr>
			  <td><?php print htmlspecialchars($dataclassfield->name); ?></td>
			  <td>
				<?php
				if (count($CorrespondingDafuncupdatedeletewhereList) > 0) {
					?>
					<font color="red">Exist</font>
					<?php
				} else {
					?>
					Not Exist
					<?php
				}
                ?>
              </td>
			  <td><?php
				if ($CorrespondingDBTableColumn != NULL) {
					print $CorrespondingDBTableColumn->name;
				} else {
					?>
                    <font color="red">Not Exist</font>
                    <?php
				}
			   ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { OutputShortenedStringWithExpansion($CorrespondingDBTableColumn->datatype, 20); } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->IsNull; } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->IsKey; } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->IsDefault; } ?></td>
			  <td><?php if ($CorrespondingDBTableColumn != NULL) { print $CorrespondingDBTableColumn->Extra; } ?></td>
              <td>
              <?php
			    for($j = 0 ; $j < count($CorrespondingDafuncupdatedeletewhereList); $j++) {
					$CorrespondingDafuncupdatedeletewhere = $CorrespondingDafuncupdatedeletewhereList[$j];
					
					?>
                    <a href="da_func_update_delete_where_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($CorrespondingDafuncupdatedeletewhere->daPID); ?>&DAFuncPID=<?php print urlencode($CorrespondingDafuncupdatedeletewhere->dafuncPID); ?>&PID=<?php print urlencode($CorrespondingDafuncupdatedeletewhere->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
                    <br>
                    <?php
				}
			  ?>
                <a href="da_func_update_delete_where_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&DAFuncPID=<?php print urlencode($DAFuncPID); ?>&PID=&targetTableColumnName=<?php print urlencode($dataclassfield->name); ?>&ParameterType=<?php print urlencode("argument"); ?>&<?php print makeRandStr(8); ?>">Add</a>
              </td>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
		<?php
		
	} else {
		?>
<p>none</p>
		<?php
	}
	
	?>
    <br>
    <br>
    <br>
    <p><a href="./da_funcs.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DAPID=<?php print urlencode($DAPID); ?>&<?php print makeRandStr(8); ?>">Back to DB Access Function List</a></p>
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
