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

class DASyncForImport
{
	public $dataclass;
	public $alreadyExist;
	
	public function Initialize($dataclass, $dalist, &$any_not_matched)
	{
		$this->dataclass = $dataclass;
		$this->alreadyExist = false;
		for($j = 0 ; $j < count($dalist); $j++) {
			$da = $dalist[$j];
			
			if ($this->dataclass->name == $da->name) {
				$this->alreadyExist = true;
				break;
			}
		}
		if (!$this->alreadyExist) {
			$any_not_matched = true;
		}
	}
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DA_SYNC_WITH_DATA_CLASS"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$ProjectPID = trim(GetParam("ProjectPID"));

$DataClassName = trim(GetParam("DataClassName"));
$DoSync = trim(GetParam("DoSync"));
$DoSyncAll = trim(GetParam("DoSyncAll"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	
	printPathOnTopForDBAccessClass("Sync with Data Class", $ProjectPID, "", "", "", "", "", "", "");
	
	$DAdataclass = new dataclassDBAccess();
	$dataclasslist = $DAdataclass->GetdataclassList($ProjectPID); 
	
	$DAda = new daDBAccess();
	$dalist = $DAda->GetdaList($ProjectPID); 
	
	if ($DoSync != "" || $DoSyncAll != "") {
		for($i = 0 ; $i < count($dataclasslist); $i++) {
			$dataclass = $dataclasslist[$i];
			
			if ($dataclass->name == $DataClassName || $DoSyncAll != "") {
				$daExisting = $DAda->GetdaByName($ProjectPID, $dataclass->name); 
				
				if ($daExisting == NULL) {
					// Insert
					$da = new daData();
					$da->ProjectPID = $ProjectPID;
					$da->name = $dataclass->name;
					$da->StoreBasePath = $dataclass->StoreBasePath;
					$da->IsAutoload = "1";
					
					if($DAda->Insertda($da) === FALSE) {
						// Failed
						?>
						<h3><font color="red">Error! Failed to insert. Please ask administrator if this continues.</font></h3>
						<?php
					} else {
						// Success
						$da->PID = $mtooldb->insert_id;
						?>
						<h3><font color="red">New DB Access Class was added: <?php print $da->name; ?></font></h3>
						<?php
					}
				}
			}
		}
		
		// Initialize Again
		$dalist = $DAda->GetdaList($ProjectPID); 
	}
	
	$any_not_matched = false;
	
	if (count($dataclasslist) > 0) {
		
		$DASyncForImportList = array();
		for($i = 0 ; $i < count($dataclasslist); $i++) {
			$dataclass = $dataclasslist[$i];
			
			$DASyncForImportData = new DASyncForImport();
			$DASyncForImportData->Initialize($dataclass, $dalist, $any_not_matched);
			
			array_push($DASyncForImportList, $DASyncForImportData);
		}
		if ($any_not_matched) {
			?>
            <div style="background-color:#00F; margin:2px; padding:10px">
            <form action="da_sync.php" method="get">
            <input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
            <input name="DoSyncAll" type="hidden" value="y">
            <input name="r" type="hidden" value="<?php print htmlspecialchars(makeRandStr(8)); ?>">
            <input name="DoImport" type="submit" value="Synchronize All">
            </form>
            </div>
			<?php
		} else {
			?>
            <p>Note: All corresponding Data Access Class is exist.</p>
            <?php
		}
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Data Class Name<br>
			  [Data Class Name in Source]</th>
			  <th>Already Exists?</th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		
		for($i = 0 ; $i < count($DASyncForImportList); $i++) {
			$DASyncForImportData = $DASyncForImportList[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($DASyncForImportData->dataclass->name); ?><br>
			  <font size="-2">[<?php print htmlspecialchars(CreateDataClassName($DASyncForImportData->dataclass->name)); ?>]</font></td>
              <td><?php
				if ($DASyncForImportData->alreadyExist) {
					?>
					Exist
					<?php
				} else {
					?>
					<font color="red">Not Exist</font>
					<?php
				}
              
              ?></td>
              <td>
				<?php
				if (!$DASyncForImportData->alreadyExist) {
					?>
					<a href="da_sync.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassName=<?php print urlencode($DASyncForImportData->dataclass->name); ?>&DoSync=y&<?php print makeRandStr(8); ?>">Synchronize for Each</a>
					<?php
                }
                ?>
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
    <p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
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

