<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$daCustomProxy = new daCustomProxyData();
// == START OF EDITABLE AREA FOR "Input Parameter" ==
$daCustomProxy->ProjectPID = trim(GetParam("ProjectPID"));
$daCustomProxy->PID = trim(GetParam("daCustomProxyPID"));
$daCustomProxy->basename = trim(GetParam("basename"));
$daCustomProxy->name = trim(GetParam("name"));
$daCustomProxy->InTransaction = trim(GetParam("InTransaction"));
$daCustomProxy->AuthType = GetParam("AuthType");
$daCustomProxy->SingleGetFuncPID = GetParam("SingleGetFuncPID");
$daCustomProxy->ContinueEvenIfFailedToInsert = trim(GetParam("ContinueEvenIfFailedToInsert"));

// == END OF EDITABLE AREA FOR "Input Parameter" ==

$insertToken = trim(GetParam("insertToken"));

$NoError = true;

// == START OF EDITABLE AREA FOR "Check Data" ==
if (!is_numeric($daCustomProxy->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
// == END OF EDITABLE AREA FOR "Check Data" ==

$showForm = true;

// == START OF EDITABLE AREA FOR "Initialize Common" ==
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_proxy.php");

function UpdateCustomProxyTargetSourceOutput()
{
	global $mtooldb;
	global $daCustomProxy;
	
	// Array
	$TargetProjectSourceOutputPIDList = GetParam("TargetProjectSourceOutputPIDList");
	
	$DAProjectSourceOutputData = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutputData->GetProjectSourceOutputList($daCustomProxy->ProjectPID);
	
	$DAdaCustomProxySourceOutputTarget = new daCustomProxySourceOutputTargetDBAccess();
	$daCustomProxySourceOutputTargetList = $DAdaCustomProxySourceOutputTarget->GetdaCustomProxySourceOutputTargetList($daCustomProxy->ProjectPID, $daCustomProxy->PID);
	
	for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
		$ProjectSourceOutput = $ProjectSourceOutputList[$j];
		
		$IsChecked = false;
		if (is_array($TargetProjectSourceOutputPIDList)) {
			for ($k = 0 ; $k < count($TargetProjectSourceOutputPIDList) ; $k++) {
				$TargetProjectSourceOutputPID = $TargetProjectSourceOutputPIDList[$k];
				
				if ($TargetProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					$IsChecked = true;
					
					// print "Checked: " . $ProjectSourceOutput->SourceOutputDir . "<br>\n";
					break;
				}
			}
		}
		
		if ($IsChecked) {
			// Checked for this Source Output
			$NeedToInsert = true;
			for ( $k = 0 ; $k < count($daCustomProxySourceOutputTargetList) ; $k++) {
				$daCustomProxySourceOutputTarget = $daCustomProxySourceOutputTargetList[$k];
				
				if ($daCustomProxySourceOutputTarget->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					$NeedToInsert = false;
					break;
				}
			}
			if ($NeedToInsert) {
				$thisTargetObj = new daCustomProxySourceOutputTargetData();
				$thisTargetObj->ProjectPID = $daCustomProxy->ProjectPID;
				$thisTargetObj->daCustomProxyPID = $daCustomProxy->PID;
				$thisTargetObj->ProjectSourceOutputPID = $ProjectSourceOutput->PID;				
				
				if ($DAdaCustomProxySourceOutputTarget->InsertdaCustomProxySourceOutputTarget($thisTargetObj)) {
					// Success
					if (mysqli_affected_rows($mtooldb) > 0) {
						?>
						<h3><font color="red">Added Output Target</font> </h3>
						<?php
					}
					update_custom_proxy_LastModifiedDT($daCustomProxy->PID, $daCustomProxy->ProjectPID);
					
				} else {
					?>
					<h3><font color="red">Error! Failed to add Output Target</font></h3>
					<?php
				}
			}
		} else {
			// Not Checked for this Source Output
			$NeedToDelete = false;
			for ( $k = 0 ; $k < count($daCustomProxySourceOutputTargetList) ; $k++) {
				$daCustomProxySourceOutputTarget = $daCustomProxySourceOutputTargetList[$k];
				
				if ($daCustomProxySourceOutputTarget->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
					if ($DAdaCustomProxySourceOutputTarget->DeletedaCustomProxySourceOutputTarget($daCustomProxySourceOutputTarget)) {
						// Success
						if (mysqli_affected_rows($mtooldb) > 0) {
							?>
							<h3><font color="red">Deleted Output Target</font> </h3>
							<?php
						}
						update_custom_proxy_LastModifiedDT($daCustomProxy->PID, $daCustomProxy->ProjectPID);
						
					} else {
						?>
						<h3><font color="red">Error! Failed to delete Output Target</font></h3>
						<?php
					}
					break;
				}
			}
		}
	}
}

if ($NoError) {
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($daCustomProxy->ProjectPID);
	if (!$project) {
		die("Something wrong. No Project");
	}
}

// == END OF EDITABLE AREA FOR "Initialize Common" ==

if ($NoError) {
	
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$daCustomProxy->PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	
	if ($daCustomProxy->PID == "") {
		// Add
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Insert Data" ==
			$DAdaCustomProxy = new daCustomProxyDBAccess();
			$insertResult = $DAdaCustomProxy->InsertdaCustomProxy($daCustomProxy);
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
				$daCustomProxy->PID = $mtooldb->insert_id;
				// == START OF EDITABLE AREA FOR "Insert Data - Success" ==
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_PROXY_CUSTOM"); ?></font></h3>
                <?php
				
				if (!$project->Getoption_automatically_create_custom_proxy()) {
					UpdateCustomProxyTargetSourceOutput();
				}
				synchronize_mtool_custom_proxy_if_automatic($daCustomProxy->ProjectPID);
				update_custom_proxy_LastModifiedDT($daCustomProxy->PID, $daCustomProxy->ProjectPID);
				
				// == END OF EDITABLE AREA FOR "Insert Data - Success" ==
			}
			
			// Please set $daCustomProxy->PID in above Editable Area to perform this.
			if ($insertToken != "" && is_numeric($daCustomProxy->PID)) {
				if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $daCustomProxy->PID)) {
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
		
	} else if (is_numeric($daCustomProxy->PID)) {
		// Select/Update
		$needToLoad = true;
		
		// == START OF EDITABLE AREA FOR "Common for Select/Update" ==
		$DAdaCustomProxy = new daCustomProxyDBAccess();
		// == END OF EDITABLE AREA FOR "Common for Select/Update" ==
		
		if ($UPDATE != "") {
			// == START OF EDITABLE AREA FOR "Update Data" ==
			$updateResult = $DAdaCustomProxy->UpdatedaCustomProxy($daCustomProxy);
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
                <h3><font color="red"><?php print getres("ACTION_UPDATED_PROXY_CUSTOM"); ?></font></h3>
                <?php
				if (!$project->Getoption_automatically_create_custom_proxy()) {
					UpdateCustomProxyTargetSourceOutput();
				}
				synchronize_mtool_custom_proxy_if_automatic($daCustomProxy->ProjectPID);
				update_custom_proxy_LastModifiedDT($daCustomProxy->PID, $daCustomProxy->ProjectPID);
				// == END OF EDITABLE AREA FOR "Update Data - Success" ==
			}
		}
		
		if ($DELETE != "") {
			// == START OF EDITABLE AREA FOR "Delete Data" ==
			$deleteResult = $DAdaCustomProxy->DeletedaCustomProxy($daCustomProxy);
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
                <h3><font color="red"><?php print getres("ACTION_DELETED_PROXY_CUSTOM"); ?></font></h3>
                <?php
				update_custom_proxy_LastModifiedDT($daCustomProxy->PID, $daCustomProxy->ProjectPID);
				// == END OF EDITABLE AREA FOR "Delete Data - Success" ==
				$needToLoad = false;
				$showForm = false;
			}
		}
		
		if ($needToLoad) {
			// == START OF EDITABLE AREA FOR "Get Data" ==
			$daCustomProxy = $DAdaCustomProxy->GetdaCustomProxy($daCustomProxy->PID, $daCustomProxy->ProjectPID);
			// == END OF EDITABLE AREA FOR "Get Data" ==
		}
		
	} else {
		// == START OF EDITABLE AREA FOR "Error when PID is strange" ==
		?>
		<h4>FATAL ERROR! DB Access Class PID is something strange.</h4>
		<?php
		// == END OF EDITABLE AREA FOR "Error when PID is strange" ==
		die();
	}
	if ($daCustomProxy->PID == "") {
		// Add
		// == START OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_PROXY_CUSTOM");
		
		// Set Default
		$daCustomProxy->InTransaction = 1;
		$daCustomProxy->ContinueEvenIfFailedToInsert = 0;
		// == END OF EDITABLE AREA FOR "Initialize Caption for Add" ==
		
		$insertToken = CreateNewTokenForThisHost();
		
	} else {
		// Select/Update
		// == START OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_PROXY_CUSTOM");
		// == END OF EDITABLE AREA FOR "Initialize Caption for Select/Update" ==
	}
	
	if ($showForm && $daCustomProxy != NULL) {
		
		// == START OF EDITABLE AREA FOR "Path on Top" ==
		printPathOnTopForProxyCustom("Proxy Target Setting [Custom, Multi]", $daCustomProxy->ProjectPID, $daCustomProxy->PID);
		// == END OF EDITABLE AREA FOR "Path on Top" ==
		
		// == START OF EDITABLE AREA FOR "Script for Form" ==
		?>
		<?php
		// == END OF EDITABLE AREA FOR "Script for Form" ==
		?>
		
		<form action="da_proxy_custom_edit.php" method="post"<?php if (isset($IsMultipart) && $IsMultipart) { print " enctype=\"multipart/form-data\""; } ?>>
		
		<?php
		// == START OF EDITABLE AREA FOR "Input Form" ==
		mtoolCommonFormInput("basename", $daCustomProxy->basename,
			array($LANG_ENGLISH=>"Base Name", $LANG_JAPANESE=>"基本名"),
			array($LANG_ENGLISH=>"Please input Base Name (for url. recommend alphabet and number)", $LANG_JAPANESE=>"基本名を入力して下さい(URLの一部になります. 英数字推奨)"),
			"text", "");
		mtoolCommonFormInput("name", $daCustomProxy->name,
			array($LANG_ENGLISH=>"Custom Proxy Name", $LANG_JAPANESE=>"カスタムProxy名"),
			array($LANG_ENGLISH=>"Please input Custom Proxy Name (for url. recommend alphabet and number)", $LANG_JAPANESE=>"カスタムProxy名を入力して下さい(URLの一部になります. 英数字推奨)"),
			"text", "");
		mtoolCommonFormCheckBoxForBoolean("InTransaction", $daCustomProxy->InTransaction,
			array($LANG_ENGLISH=>"Enable Transaction", $LANG_JAPANESE=>"トランザクション有効"),
			array($LANG_ENGLISH=>"Perform action(s) in Transaction", $LANG_JAPANESE=>"トランザクション中に実行する"),
			"", "", true);
		mtoolCommonFormCheckBoxForBoolean("ContinueEvenIfFailedToInsert", $daCustomProxy->ContinueEvenIfFailedToInsert,
			array($LANG_ENGLISH=>"Continue even if failed to Insert", $LANG_JAPANESE=>"データ挿入失敗しても継続する"),
			array($LANG_ENGLISH=>"Please input option to continue even if failed to Insert", $LANG_JAPANESE=>"データ挿入失敗しても継続する"),
			"", "", true);
		?>
        <div class="clearfix"></div>
        <?php
		if (!$project->Getoption_automatically_create_custom_proxy()) {
		?>
        <div class="row">
            <label class="col-md-3 control-label" for="inputtext">Source Output Target</label>
            <div class="col-md-9">
                <?php
				  	$DAdaCustomProxySourceOutputTarget = new daCustomProxySourceOutputTargetDBAccess();
					$daCustomProxySourceOutputTargetList = $DAdaCustomProxySourceOutputTarget->GetdaCustomProxySourceOutputTargetList($daCustomProxy->ProjectPID, $daCustomProxy->PID);
					
					// $isFirstLine = true;
					
					$DAProjectSourceOutputData = new ProjectSourceOutputDBAccess();
					$ProjectSourceOutputList = $DAProjectSourceOutputData->GetProjectSourceOutputList($daCustomProxy->ProjectPID);
					for($j = 0 ; $j < count($ProjectSourceOutputList) ; $j++) {
						$ProjectSourceOutput = $ProjectSourceOutputList[$j];
						
						if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYCLIENT ||
							$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER ||
							$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER ||
							$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT) {
							
							$isSelected = false;
							for($k = 0 ; $k < count($daCustomProxySourceOutputTargetList) ; $k++) {
								$daCustomProxySourceOutputTarget = $daCustomProxySourceOutputTargetList[$k];
								
								if ($daCustomProxySourceOutputTarget->ProjectSourceOutputPID == $ProjectSourceOutput->PID) {
									$isSelected = true;
									break;
								}
							}
							// if (!$isFirstLine) {
							// 	print "<br>";
							// }
							?>
		                	<span class="checkbox">
		                	<label>
			                <input name="TargetProjectSourceOutputPIDList[]" type="checkbox" value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($isSelected) { print " checked"; } ?> /> <?php
								print htmlspecialchars(MakeDropboxFolderByProjectAndProjectSourceOutput($daCustomProxy->ProjectPID, $ProjectSourceOutput, $ProjectSourceOutput->SourceOutputDir)) . " " . htmlspecialchars($ProjectSourceOutput->TargtServerPSOProxyBaseURL);
							?>
			                </label>
							</span>
							<?php
							// $isFirstLine = false;
						}
					}
                ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <?php
		}
		$ProjectPID = $daCustomProxy->ProjectPID;
		$thisPID = $daCustomProxy->PID;
		$AuthType = $daCustomProxy->AuthType;
		$SingleGetFuncPID = $daCustomProxy->SingleGetFuncPID;
		$FormKeyNameForAuthType = "AuthType";
		$FormKeyNameForSingleGetFuncPID = "SingleGetFuncPID";
		include_once("proxy_auth_common_include.php");
		// == END OF EDITABLE AREA FOR "Input Form" ==
		?>
		
		<div class="row">
			<label class="col-md-3 control-label" for="inputtext"></label>
			<div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
			
			<?php
			if ($daCustomProxy->PID != "") {
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
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($daCustomProxy->ProjectPID); ?>">
		<input name="daCustomProxyPID" type="hidden" value="<?php print htmlspecialchars($daCustomProxy->PID); ?>">
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
    <p><a href="da_proxy_custom.php?ProjectPID=<?php print urlencode($daCustomProxy->ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to Proxy Target Setting [Multi, Custom] List</a></p>
	<?php
	// == END OF EDITABLE AREA FOR "Bottom Links" ==
}
?>
