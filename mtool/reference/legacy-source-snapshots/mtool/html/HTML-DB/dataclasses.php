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

function InitializeClassDuplicateNameCheck($dataclasslist)
{
	$duplicatedDataClassHT = array();
	$dataclassNameInSourceHT = array();
	for($i = 0 ; $i < count($dataclasslist); $i++) {
		$dataclass = $dataclasslist[$i];
		
		$dataclassNameInSource = CreateDataClassName($dataclass->name);
		$key = strtoupper($dataclassNameInSource);
		if (array_key_exists($key, $dataclassNameInSourceHT)) {
			$duplicatedDataClassHT[$key] = true;
		}
		$dataclassNameInSourceHT[$key] = true;
	}
	return $duplicatedDataClassHT;
}
function CheckIfClassDuplicateName($duplicatedDataClassHT, $dataclass)
{
	$isDuplicated = false;
	$dataclassNameInSource = CreateDataClassName($dataclass->name);
	$key = strtoupper($dataclassNameInSource);
	if (array_key_exists($key, $duplicatedDataClassHT)) {
		$isDuplicated = true;
	}
	return $isDuplicated;
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_DATA_CLASS_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

include_once("/srv/legacy/www/mtool_lib/lib_mtool_dataclasse_fields_sync.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");

$ProjectPID = GetParam("ProjectPID");

$filterdataclassPID = trim(GetParam("filterdataclassPID"));

if (is_numeric($filterdataclassPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific Data Class</i></font></h3>
    <?php
}

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForDataClasses("Data Class List", $ProjectPID, "", "");
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	if (!$project) {
		die("Something strange. Project is not found\n");
	}
	
	$DAdataclass = new dataclassDBAccess();
	$dataclasslist = $DAdataclass->GetdataclassList($ProjectPID); 
	
	$ShowSourceLink = $project->Getoption_show_source();
	$ShowDetailOfProject = $project->Getoption_show_detail();
	$AllSourceInclude = $project->Getoption_all_source_include();
	
	if (count($dataclasslist) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Name
              <?php if ($DBWritePermission) { ?>
              <br>
			  [Data Class Name in Source]
              <?php } // if DBWritePermission ?>
              </th>
			  <th>Inherit Parent Data Class</th>
              <?php if ($DBWritePermission) { ?>
                  <th>Store Base Path</th>
                  <?php if (!$AllSourceInclude) { ?>
                  	  <th>Include in Autoload <font size="-1">(for PHP only, not for C#)</font></th>
                  <?php } ?>
              <?php } // if DBWritePermission ?>
			  <th></th>
              <?php if ($DBWritePermission) { ?>
                  <th></th>
                  <th></th>
                  <?php if ($ShowSourceLink) { ?>
                  <th>Source</th>
                  <?php } ?>
              <?php } // if DBWritePermission ?>
			  <th></th>
			  <th></th>
			</tr>
            </thead>
            <tbody>
		<?php
		
		$duplicatedDataClassHT = InitializeClassDuplicateNameCheck($dataclasslist);
		
		for($i = 0 ; $i < count($dataclasslist); $i++) {
			$dataclass = $dataclasslist[$i];
			
			// filter
			if (is_numeric($filterdataclassPID)) {
				if ($filterdataclassPID != $dataclass->PID) {
					continue;
				}
			}
			
			$isDuplicated = CheckIfClassDuplicateName($duplicatedDataClassHT, $dataclass);
			?>
			<tr>
			  <td><?php print htmlspecialchars($dataclass->name); ?>
              <?php if ($DBWritePermission) { ?>
                  <br>
                  <font size="-2">[<?php print htmlspecialchars(CreateDataClassName($dataclass->name)); ?>]</font>
                  <?php
                  if ($isDuplicated) {
                      ?>
                      <font color="red">WARNING! Name is duplicated. Please Check</font>
                      <?php
                  }
                  ?>
              <?php } // if DBWritePermission ?>
              </td>
			  <td><?php print htmlspecialchars($dataclass->InheritParentDataClassName); ?>
				<?php
				
                if ($DBWritePermission) {
					$IsSynghronizeTarget = false;
					$NotAllFieldExistForInheritClassFlag = false;
					$NotAllFieldExistAndOrderMatchedForInheritClassFlag = false;
					check_mtool_dataclass_field_sync_status($ProjectPID, $dataclass, $IsSynghronizeTarget, $NotAllFieldExistForInheritClassFlag, $NotAllFieldExistAndOrderMatchedForInheritClassFlag);
					
					if ($IsSynghronizeTarget) {
                        ?>
                        <br>
                        [<a href="dataclass_fields_sync_inherit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclass->PID); ?>&<?php print makeRandStr(8); ?>">Syncronize Field(s)</a>]
                        <?php
						
						if ($NotAllFieldExistForInheritClassFlag) {
                            ?>
                            <br>
                            <font color="red">Warning: Not all field exists</font>
                            <?php
						} else if ($NotAllFieldExistAndOrderMatchedForInheritClassFlag) {
                            ?>
                            <br>
                            <font color="red">Warning: Order is not matched (you can ignore)</font>
                            <?php
						}
					}
					
                    // if (trim($dataclass->InheritParentDataClassName) != "") {
                    //     $Parentdataclassfieldlist = NULL;
                    //     $dataclassfieldlist = NULL;
                    //     $ParentDataclass = $DAdataclass->GetdataclassByName($ProjectPID, trim($dataclass->InheritParentDataClassName));
                    //     if ($ParentDataclass != NULL) {
                    //         $DAdataclassfields = new dataclassfieldsDBAccess();
                    //         $Parentdataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $ParentDataclass->PID); 
                    //         $dataclassfieldlist = $DAdataclassfields->GetdataclassfieldsList($ProjectPID, $dataclass->PID);
                    //     }
                    //     if (!IsAllFieldExistForInheritClass($dataclassfieldlist, $Parentdataclassfieldlist)) {
                    //     } else if (!IsAllFieldExistAndOrderMatchedForInheritClass($dataclassfieldlist, $Parentdataclassfieldlist)) {
                    //     }
                    // }
                } // if DBWritePermission
                ?>
              </td>
              <?php if ($DBWritePermission) { ?>
                  <td><?php print htmlspecialchars($dataclass->StoreBasePath); ?></td>
                  <?php if (!$AllSourceInclude) { ?>
	                  <td><?php print htmlspecialchars($dataclass->GetIsAutoloadCaption()); ?></td>
                  <?php } ?>
              <?php } // if DBWritePermission ?>
			  <td><a href="dataclass_fields.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclass->PID); ?>&<?php print makeRandStr(8); ?>">View Field(s)</a></td>
              <?php if ($DBWritePermission) { ?>
                  <td><a href="dataclass_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclass->PID); ?>&<?php print makeRandStr(8); ?>">Edit Data Class Info</a></td>
                  <td><a href="dataclasses_change_order.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&DataClassPID=<?php print urlencode($dataclass->PID); ?>&<?php print makeRandStr(8); ?>">Change Field's Order</a></td>
                  <?php if ($ShowSourceLink) { ?>
                  <td>
                    <?php
                    $DABuildSourceCache = new BuildSourceCacheDBAccess();
                    $BuildSourceCacheByDataClassList = $DABuildSourceCache->GetBuildSourceCacheByDataClassList($ProjectPID, $dataclass->PID);
                    if ($BuildSourceCacheByDataClassList) {
                        for ($j = 0 ; $j < count($BuildSourceCacheByDataClassList) ; $j++) {
                            $BuildSourceCacheByDataClass = $BuildSourceCacheByDataClassList[$j];
                            
                            if ($j > 0) {
                                print "<br>";
                            }
                            ?>
                            <a href="dataclasses_source.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&PID=<?php print urlencode($BuildSourceCacheByDataClass->PID); ?>&<?php print makeRandStr(8); ?>"><font size="-2"><?php print htmlspecialchars($BuildSourceCacheByDataClass->Filename); ?></font></a>
                            <?php
                        }
                    }
                    ?>
                  </td>
                  <?php } ?>
              <?php } // if DBWritePermission ?>
              <td><?php PrintAddMinutesLinkFordataclass($ProjectPID, $dataclass->PID); ?></td>
              <td><?php PrintSearchMinutesLinkFordataclass($ProjectPID, $dataclass->PID); ?></td>
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
	<?php if ($DBWritePermission) { ?>
    <p align="right"><a href="dataclass_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New Data Class</a></p>
    <?php } // if DBWritePermission ?>
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
