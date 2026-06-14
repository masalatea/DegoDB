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
<title><?php print getres("TITLE_LANGUAGE_RESOURCE_LIST"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");

$ProjectPID = GetParam("ProjectPID");
$LanguageResourceGroupPID = GetParam("LanguageResourceGroupPID");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($LanguageResourceGroupPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Language Resource Group PID</font></H3>
    <?php
	$NoError = false;
}

include_once("lang_res_check_project_source_output_setting_lib.php");
if ($NoError) {
	CheckProjectSourceOutputSettingForLanguageResource($ProjectPID);
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForLanguageResource("Language Resource List", $ProjectPID, $LanguageResourceGroupPID);
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	
	$DALanguageResourceGroup = new LanguageResourceGroupDBAccess();
	$LanguageResourceGroup = $DALanguageResourceGroup->GetLanguageResourceGroup($LanguageResourceGroupPID, $ProjectPID);
	
	if ($LanguageResourceGroup) {
        
        $DALanguageResourceGroupLang = new LanguageResourceGroupLangDBAccess();
        $LanguageResourceGroupLangList = $DALanguageResourceGroupLang->GetLanguageResourceGroupLangList($ProjectPID, $LanguageResourceGroupPID);
        
        $DALanguageResourceCaption = new LanguageResourceCaptionDBAccess();
        
		$DALanguageResource = new LanguageResourceDBAccess();
		$LanguageResourceList = GetLanguageResourceListWithAdditionalGroup($LanguageResourceGroupPID, $ProjectPID);
		if ($LanguageResourceList) {
			?>
            <table class="table">
                <thead>
                <tr bgcolor="#ECECEC" class="unsortable">
                  <th></th>
                  <th>Key</th>
					<?php
					for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
						$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
						?>
						<th><?php print htmlspecialchars($LanguageResourceGroupLang->LanguageResourceLangCaption); ?> </th>
						<?php
					}
					?>
                  <?php if ($IsDotNetUWP) { ?>
                  <th>Property for UWP</th>
                  <?php } ?>
                  <th>Fixed?</th>
                  <?php if ($DBWritePermission) { ?>
                  <th></th>
                  <th></th>
                  <th></th>
                  <th>Assigned Additional Group</th>
                  <?php } ?>
                </tr>
                </thead>
                <tbody id="sortablebodyarea">
            <?php
			for($i = 0 ; $i < count($LanguageResourceList); $i++) {
				$LanguageResource = $LanguageResourceList[$i];
				
				$is_base = false;
				if ($LanguageResource->LanguageResourceGroupPID == $LanguageResourceGroupPID) {
					$is_base = true;
				}
				
				$is_fixed = ($LanguageResource->IsResourceFixed == 1);
				
				?>
                    <tr>
                      <td><font size="-1"><?php print htmlspecialchars($LanguageResource->SortGroup); ?></font></td>
                      <td><?php
					  
					  $output_font_tag_end = false;
					  $output_span_tag_end = false;
					  if (strlen($LanguageResource->KeyName) > 20) {
						  ?>
                          <font size="-2">
                          <?php
						  $output_font_tag_end = true;
					  } else if (strlen($LanguageResource->KeyName) > 40) {
						  ?>
                          <span style="font-size:6px">
                          <?php
						  $output_span_tag_end = true;
					  }
					  
					   print htmlspecialchars($LanguageResource->KeyName);
					   
					   if ($output_span_tag_end) {
						   ?>
                           </span>
                           <?php
					   }
					   if ($output_font_tag_end) {
						   ?>
                           </font>
                           <?php
					   }
					    ?>
					  <?php if ($DBWritePermission) { ?>
                     <br>
                      <a href="lang_res_edit.php?PID=<?php print urlencode($LanguageResource->PID); ?>&ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
                      <?php } ?>
					    </td>
					<?php
					
					$LanguageResourceCaptionList = $DALanguageResourceCaption->GetLanguageResourceCaptionList($ProjectPID, $LanguageResource->PID, $LanguageResource->LanguageResourceGroupPID);
					
					for($g = 0 ; $g < count($LanguageResourceGroupLangList); $g++) {
						$LanguageResourceGroupLang = $LanguageResourceGroupLangList[$g];
						
						$thisCaptin = "";
						$thisAutoTranslatedCaptin = "";
						for($k = 0 ; $k < count($LanguageResourceCaptionList) ; $k++) {
							$LanguageResourceCaption = $LanguageResourceCaptionList[$k];
							
							if ($LanguageResourceCaption->LanguageResourceLangPID == $LanguageResourceGroupLang->LanguageResourceLangPID) {
								$thisCaptin = $LanguageResourceCaption->Caption;
								$thisAutoTranslatedCaptin = $LanguageResourceCaption->CaptionAutoTranslated;
								
								/*
								if ($LanguageResourceGroupLang->LanguageResourceLangCaption == "Japanese" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "English" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "Simplified Chinese" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "Korean" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "Spanish" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "Portuguese" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "Traditional Chinese" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "French" ||
									$LanguageResourceGroupLang->LanguageResourceLangCaption == "Hindi"
								) {

								} else{
									if ($thisAutoTranslatedCaptin == "") {
										$LanguageResourceCaption->CaptionAutoTranslated = $thisCaptin;
										$DALanguageResourceCaption->UpdateLanguageResourceCaption($LanguageResourceCaption);
										
										$thisAutoTranslatedCaptin = $LanguageResourceCaption->CaptionAutoTranslated;
									}
								}
								*/
								
								break;
							}
						}
						
						$bgcolor = "";
						if ($thisAutoTranslatedCaptin != "" && $thisCaptin == $thisAutoTranslatedCaptin) {
							$bgcolor = " bgcolor=\"gray\"";
						}
						?>
						<th<?php print $bgcolor; ?>><?php print htmlspecialchars($thisCaptin); ?> </th>
						<?php
					}
					?>
                      <?php if ($IsDotNetUWP) { ?>
                      <td><?php print htmlspecialchars($LanguageResource->GetUWPTargetPropertyWithDot()); ?></td>
                      <?php } ?>
                     <td<?php if ($is_fixed) { print ' bgcolor="#C0F5C4"'; } ?>><?php
						if ($is_fixed) {
							print "Yes";
						}
						 ?></td>
					  <?php if ($DBWritePermission) { ?>
                      <td>
                      <?php
					  if ($is_base) {
					  ?>
                      <a href="lang_res_move.php?PID=<?php print urlencode($LanguageResource->PID); ?>&ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Move</a>
                      <?php
					  }
					  ?>
                      </td>
                      <td>
                      <a href="lang_res_edit.php?SourceResourcePID=<?php print urlencode($LanguageResource->PID); ?>&ProjectPID=<?php print urlencode($ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($LanguageResourceGroupPID); ?>&duplicate=y&<?php print makeRandStr(8); ?>">Duplicate</a>
                      </td>
                      <td><a href="lang_res_assign_additional_group.php?LanguageResourcePID=<?php print urlencode($LanguageResource->PID); ?>&ProjectPID=<?php print urlencode($ProjectPID); ?>&BaseLanguageResourceGroupPID=<?php print urlencode($LanguageResource->LanguageResourceGroupPID); ?>&<?php print makeRandStr(8); ?>">Assign Additional Group</a></td>
                      <td nowrap><?php
                      
					  if ($is_base) {
						  $DALanguageResourceAdditionalGroupAssignment = new LanguageResourceAdditionalGroupAssignmentDBAccess();
						  $LanguageResourceAdditionalGroupAssignmentList = $DALanguageResourceAdditionalGroupAssignment->GetLanguageResourceAdditionalGroupAssignmentList($LanguageResource->PID, $ProjectPID);
						  for($j = 0 ; $j < count($LanguageResourceAdditionalGroupAssignmentList); $j++) {
							  $LanguageResourceAdditionalGroupAssignment = $LanguageResourceAdditionalGroupAssignmentList[$j];
							  
							  if ($j > 0) {
								  print "<br>\n";
							  }
							  print htmlspecialchars($LanguageResourceAdditionalGroupAssignment->LanguageResourceGroupName);
							  
						  }
					  } else {
						  ?>
                          This definition is based on Additional Group
                          <?php
					  }
					  ?></td>
                      <?php } ?>
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
	} else {
		?>
    	<p>Something Wrong. No Corresponding Project Source Output Setting</p>
		<?php
	}
	?>
	<?php if ($DBWritePermission) { ?>
    <p align="right"><a href="lang_res_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($LanguageResourceGroupPID); ?>&<?php print makeRandStr(8); ?>">Add New Languager Resource</a></p>
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
