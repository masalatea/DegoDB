<?php
include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_beta.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_uploader.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_uploader_prepare.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_update_last_update_timestamp.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_proxy.php");

$UPDATE = (trim(GetParam("UPDATE")) != "");
$QUICK_BUILD = (GetParam("QUICK_BUILD") != "");
$BUILD_BETA = (GetParam("BUILD_BETA") != "");
$DETAILED_BUILD = (GetParam("DETAILED_BUILD") != "");
$OUTPUT_TO_TEMP_FOLDER = (GetParam("OUTPUT_TO_TEMP_FOLDER") != "");
$OUTPUT_AFTER_COPY_TO_TEMP_FOLDER = (GetParam("OUTPUT_AFTER_COPY_TO_TEMP_FOLDER") != "");
$OUTPUT_DEBUG_MESSAGE = (GetParam("OUTPUT_DEBUG_MESSAGE") != "");

$is_first = !$UPDATE;

$UpdateClasses = GetParam("UpdateClasses");
if (!is_array($UpdateClasses)) {
	$UpdateClasses = array();
}
$UpdateFunction = GetParam("UpdateFunction");
if (!is_array($UpdateFunction)) {
	$UpdateFunction = array();
}
$UpdateProxyServer = GetParam("UpdateProxyServer");
if (!is_array($UpdateProxyServer)) {
	$UpdateProxyServer = array();
}
$UpdateProxyClient = GetParam("UpdateProxyClient");
if (!is_array($UpdateProxyClient)) {
	$UpdateProxyClient = array();
}
$UpdateHtml = GetParam("UpdateHtml");
if (!is_array($UpdateHtml)) {
	$UpdateHtml = array();
}
$UpdateLanguageResource = GetParam("UpdateLanguageResource");
if (!is_array($UpdateLanguageResource)) {
	$UpdateLanguageResource = array();
}

$project = new ProjectData();
$project->PID = trim(GetParam("ProjectPID"));

$NoError = true;
if (!is_numeric($project->PID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAProject = new ProjectDBAccess();
$project = $DAProject->GetProject($project->PID);
if ($project) {
	// OK
} else {
	?>
	<h3>ERROR. Unknown Project ID</h3>
	<?php
	$NoError = false;
}

$showForm = true;

if ($NoError) {
	
	printPathOnTopForDBTable("Build", $project->PID, "", "");
	
	$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();
	$ProjectSourceOutputList = $DAProjectSourceOutput->GetProjectSourceOutputList($project->PID); 
	
	if (count($ProjectSourceOutputList) > 0) {
		?>
        <div id="buildbuttonarea" style="display:<?php print "none"; ?>">
        <form id="buildstartform" action="<?php print $_SERVER['SCRIPT_NAME']; ?>" method="post">
        <?php
		$beta_output_exist = false;
		for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
			$ProjectSourceOutput = $ProjectSourceOutputList[$i];
			if ($ProjectSourceOutput->ReleaseTargetType == ProjectSourceOutputReleaseTargetTypeEnum::$BETA) {
				$beta_output_exist = true;
				break;
			}
		}
		if ($detailed_build_mode) {
			?>
			<table class="table">
				<tbody>
				<tr>
					<td><input name="startdetailedbuildbutton" id="startdetailedbuildbutton" value="Build" type="button"></td>
				</tr>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<table class="table">
				<tbody>
				<tr>
					<td>Main</td>
					<td><input name="startquickbuildbutton" id="startquickbuildbutton" value="Quick Build" type="button"> 
						<input name="startbuildbutton" id="startbuildbutton" value="Full Build" type="button"></td>
				</tr>
				<?php
				if ($beta_output_exist) {
				?>
				<tr>
					<td>Beta</td>
					<td><input name="startquickbuildbuttonforbeta" id="startquickbuildbuttonforbeta" value="Quick Build" type="button"> 
						<input name="startbuildbuttonforbeta" id="startbuildbuttonforbeta" value="Full Build" type="button"></td>
				</tr>
				<?php
				}
				?>
				</tbody>
			</table>
			<?php
		}
		?>
        <input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($project->PID); ?>">
        <input name="UPDATE" id="UPDATE" type="hidden" value="y">
        <input name="QUICK_BUILD" id="QUICK_BUILD" type="hidden" value="">
        <input name="BUILD_BETA" id="BUILD_BETA" type="hidden" value="">
        <input name="DETAILED_BUILD" id="DETAILED_BUILD" type="hidden" value="">
        <br>
		<?php
		
		$current_user_is_dropbox_base_folder_user = false;
		$DADropboxBaseFolderUser = new DropboxBaseFolderUserDBAccess();
		$DropboxBaseFolderUserList = $DADropboxBaseFolderUser->GetDropboxBaseFolderUserList($project->DropboxBaseFolderPID);
		if ($DropboxBaseFolderUserList) {
			for($i = 0 ; $i < count($DropboxBaseFolderUserList) ; $i++) {
				$DropboxBaseFolderUser = $DropboxBaseFolderUserList[$i];
				
				if (strtoupper($DropboxBaseFolderUser->username) == strtoupper($matsuesoft_login_token_id)) {
					$current_user_is_dropbox_base_folder_user = true;
					break;
				}
			}
		}
		
		$show_temp_output_folder_option = ($project->option_show_output_temp_folder_on_build == 1);
		$option_default_output_temp_folder_on_build_is_true = ($project->option_default_output_temp_folder_on_build_is_true == 1);
		
		$default_check_state_of_output_to_temp_folder = (($is_first && $option_default_output_temp_folder_on_build_is_true) || $OUTPUT_TO_TEMP_FOLDER);
		
		if ($show_temp_output_folder_option) {
			?>
			<p><label><input id="OUTPUT_TO_TEMP_FOLDER" name="OUTPUT_TO_TEMP_FOLDER" type="checkbox" value="t"<?php if ($default_check_state_of_output_to_temp_folder) { print " checked"; } ?>>Output to Temp Folder</label></p>
			<p id="OUTPUT_AFTER_COPY_TO_TEMP_FOLDER_AREA" <?php if (!$default_check_state_of_output_to_temp_folder) { print " style=\"display:none\""; } ?>><label><input id="OUTPUT_AFTER_COPY_TO_TEMP_FOLDER" name="OUTPUT_AFTER_COPY_TO_TEMP_FOLDER" type="checkbox" value="t"<?php if ($OUTPUT_AFTER_COPY_TO_TEMP_FOLDER) { print " checked"; } ?>>Copy to Temp Folder before Process</label></p>
            <?php
			if (!$current_user_is_dropbox_base_folder_user) {
				?>
                <p>WARNING: You are not a User of the DropBox Folder. The TEMP Output file might not see you.</p>
                <?php
			}
			?>
			<br>
			<?php
		}
		$default_check_state_of_output_debug_message = $OUTPUT_DEBUG_MESSAGE;
		?>
        <p><label><input id="OUTPUT_DEBUG_MESSAGE" name="OUTPUT_DEBUG_MESSAGE" type="checkbox" value="t"<?php if ($default_check_state_of_output_debug_message) { print " checked"; } ?>>Output Debug Message</label></p>
        
<script>
$(function() {
	$("#OUTPUT_TO_TEMP_FOLDER").click(function() {
		update_visible_option();
	});
	function update_visible_option()
	{
		if ($("#OUTPUT_TO_TEMP_FOLDER").prop("checked")) {
			$("#OUTPUT_AFTER_COPY_TO_TEMP_FOLDER_AREA").show();
		} else {
			$("#OUTPUT_AFTER_COPY_TO_TEMP_FOLDER_AREA").hide();
		}
	}
	update_visible_option();
});
</script>
		<?php
		if ($detailed_build_mode) {
			?>
			<br>
			<table class="table">
				<thead>
				<tr bgcolor="#ECECEC">
				  <th colspan="7">Update Target</th>
				  <th rowspan="2">Program Language</th>
				  <th rowspan="2">Class Type</th>
				  <th rowspan="2">Output Path</th>
				</tr>
				<tr bgcolor="#ECECEC">
				  <th>&nbsp;</th>
				  <th><span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllDataClass"); ?>Data Class</label></span></th>
				  <th><span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllDatabaseAccess"); ?>Database Access</label></span></th>
				  <th><span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllProxyServer"); ?>Proxy Server</label></span></th>
				  <th><span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllClient"); ?>Proxy Client</label></span></th>
				  <th><span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllHTML"); ?>HTML</label></span></th>
                  <th><span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllLanguageResource"); ?>Language Resource</label></span></th>
				</tr>
				</thead>
				<tbody>
			<?php
			
			$showcomment1 = false;
			for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
				$ProjectSourceOutput = $ProjectSourceOutputList[$i];
				?>
				<tr>
				  <td><?php mtool_output_checkbox_for_select_all_by_name("AllItemSelect" . $ProjectSourceOutput->PID); ?></td>
				  <td>
				  <?php
				  if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBACCESS ||
					 $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYCLIENT ||
					 $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT
					 )
				  {
					  ?>
					  <span class="checkbox"><label><input name="UpdateClasses[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForDataClass MtoolMultiCheckBox" . $ProjectSourceOutput->PID); ?> value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($is_first || in_array($ProjectSourceOutput->PID, $UpdateClasses)) { print " checked"; } ?>>Update Data Class</label></span>
					  <?php
				  } else if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER ||
							$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER) {
					  // Not a target
					  ?>
					  (*1)
					  <?php
					  $showcomment1 = true;
				  }
				  ?>
				  </td>
				  <td>
				  <?php
				  if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBACCESS) {
					  ?>
					  <span class="checkbox"><label><input name="UpdateFunction[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForDatabaseAccess MtoolMultiCheckBox" . $ProjectSourceOutput->PID); ?> value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($is_first || in_array($ProjectSourceOutput->PID, $UpdateFunction)) { print " checked"; } ?>>Update Database Access</label></span>
					  <?php
				  }
				  ?>
				  </td>
				  <td>
				  <?php
				  if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER ||
					 $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER) {
					  ?>
					  <span class="checkbox"><label><input name="UpdateProxyServer[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForProxyServer MtoolMultiCheckBox" . $ProjectSourceOutput->PID); ?> value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($is_first || in_array($ProjectSourceOutput->PID, $UpdateProxyServer)) { print " checked"; } ?>>Update Proxy Server</label></span>
					  <?php
				  }
				  ?>
				  </td>
				  <td>
				  <?php
				  if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYCLIENT ||
					 $ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT) {
					  ?>
					  <span class="checkbox"><label><input name="UpdateProxyClient[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForClient MtoolMultiCheckBox" . $ProjectSourceOutput->PID); ?> value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($is_first || in_array($ProjectSourceOutput->PID, $UpdateProxyClient)) { print " checked"; } ?>>Update Proxy Client</label></span>
					  <?php
				  }
				  ?>
				  </td>
				  <td>
				  <?php
				  if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$HTML) {
					  ?>
					  <span class="checkbox"><label><input name="UpdateHtml[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForHTML MtoolMultiCheckBox" . $ProjectSourceOutput->PID); ?> value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($is_first || in_array($ProjectSourceOutput->PID, $UpdateHtml)) { print " checked"; } ?>>Update HTML</label></span>
					  <?php
				  }
				  ?>
				  </td>
				  <td>
				  <?php
				  if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE) {
					  ?>
					  <span class="checkbox"><label><input name="UpdateLanguageResource[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForLanguageResource MtoolMultiCheckBox" . $ProjectSourceOutput->PID); ?> value="<?php print $ProjectSourceOutput->PID; ?>"<?php if ($is_first || in_array($ProjectSourceOutput->PID, $UpdateLanguageResource)) { print " checked"; } ?>>Update Language Resource</label></span>
					  <?php
				  }
				  ?>
				  </td>
				  <td><?php print htmlspecialchars(GetProjectSourceOutputProgramLanguageCaption($ProjectSourceOutput->ProgramLanguage)); ?></td>
				  <td><?php print htmlspecialchars(GetProjectSourceOutputClassTypeCaption($ProjectSourceOutput->ClassType)); ?></td>
				  <td><?php print htmlspecialchars(MakeDropboxFolderByProjectAndProjectSourceOutput($project->PID, $ProjectSourceOutput, $ProjectSourceOutput->SourceOutputDir)); ?></td>
				</tr>
				<?php
				mtool_output_script_tag_for_multi_checkbox_by_name("AllItemSelect" . $ProjectSourceOutput->PID, "MtoolMultiCheckBox" . $ProjectSourceOutput->PID);
			}
			?>
				</tbody>
			</table>
			<?php
            mtool_output_script_tag_for_multi_checkbox_by_name("AllDataClass", "MtoolMultiCheckBoxForDataClass");
            mtool_output_script_tag_for_multi_checkbox_by_name("AllDatabaseAccess", "MtoolMultiCheckBoxForDatabaseAccess");
            mtool_output_script_tag_for_multi_checkbox_by_name("AllProxyServer", "MtoolMultiCheckBoxForProxyServer");
            mtool_output_script_tag_for_multi_checkbox_by_name("AllClient", "MtoolMultiCheckBoxForClient");
            mtool_output_script_tag_for_multi_checkbox_by_name("AllHTML", "MtoolMultiCheckBoxForHTML");
            mtool_output_script_tag_for_multi_checkbox_by_name("AllLanguageResource", "MtoolMultiCheckBoxForLanguageResource");
			
			if ($showcomment1) {
				?>
    <p><font size="-1">(*1) Proxy Server doesn't include Data Class. Force disabled. Please make data class by DBAccess type and use by combination.</font></p>
				<?Php
			}
			
		} else {
			for($i = 0 ; $i < count($ProjectSourceOutputList); $i++) {
				$ProjectSourceOutput = $ProjectSourceOutputList[$i];
				
				$this_project_source_output_is_a_target_of_build = false;
				if ($DETAILED_BUILD) {
					$this_project_source_output_is_a_target_of_build = true;
				} else {
					if ($BUILD_BETA && $ProjectSourceOutput->ReleaseTargetType == ProjectSourceOutputReleaseTargetTypeEnum::$BETA) {
						$this_project_source_output_is_a_target_of_build = true;
					}
					if (!$BUILD_BETA && $ProjectSourceOutput->ReleaseTargetType != ProjectSourceOutputReleaseTargetTypeEnum::$BETA) {
						$this_project_source_output_is_a_target_of_build = true;
					}
				}
				
				if ($this_project_source_output_is_a_target_of_build) {
					if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBACCESS ||
						$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYCLIENT ||
						$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT)
					{
						array_push($UpdateClasses, $ProjectSourceOutput->PID);
					}
					if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBACCESS)
					{
						array_push($UpdateFunction, $ProjectSourceOutput->PID);
					}
					if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYSERVER ||
						$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYSERVER)
					{
						array_push($UpdateProxyServer, $ProjectSourceOutput->PID);
					}
					if ($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$PROXYCLIENT ||
						$ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$DBAASPROXYCLIENT)
					{
						array_push($UpdateProxyClient, $ProjectSourceOutput->PID);
					}
					if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$HTML)
					{
						array_push($UpdateHtml, $ProjectSourceOutput->PID);
					}
					if($ProjectSourceOutput->ClassType == ProjectSourceOutputClassTypeEnum::$LANGUAGERESOURCE)
					{
						array_push($UpdateLanguageResource, $ProjectSourceOutput->PID);
					}
				}
			}
		}
		?>
        </form>
        <br>
        <br>
        </div>
        
		<?php
	} else {
		?>
    <p>No Build Target. Please add Source Output Setting</p>
		<?php
	}
	?>
    <div id="submittingarea" style="display:none">
    <p>Requesting...</p>
    </div>
<script>
$(document).ready(function() {
    $('#startbuildbutton').click(function() {
		submit_buildstartform();
    });
    $('#startquickbuildbutton').click(function() {
		$("#QUICK_BUILD").val("y");
		submit_buildstartform();
    });
	
    $('#startbuildbuttonforbeta').click(function() {
		$("#BUILD_BETA").val("y");
		submit_buildstartform();
    });
    $('#startquickbuildbuttonforbeta').click(function() {
		$("#BUILD_BETA").val("y");
		$("#QUICK_BUILD").val("y");
		submit_buildstartform();
    });
	
    $('#startdetailedbuildbutton').click(function() {
		$("#DETAILED_BUILD").val("y");
		submit_buildstartform();
    });
	
	function submit_buildstartform()
	{
		set_style_display("buildbuttonarea", "none");
		set_style_display("submittingarea", "inline");
		set_style_display("donebuildheader", "none");
		set_style_display("builddetailmessagearea", "none");
        $('#buildstartform').submit();
	}
});
</script>
    <?php
	
	if ($UPDATE) {
		
		?>
		<h3 id="buildingheader">Building...</h3>
		<br>
		<div id="builddetailmessagearea">
		<div id="buildingactivity_upper"></div>
		<pre id="buildresult"><?php 
		
		$showDetails = true;
		
		$BuildTokenString = PrepareBuildProjectSource($project, $UpdateClasses, $UpdateFunction, $UpdateProxyServer, $UpdateProxyClient, $UpdateHtml, $UpdateLanguageResource, $OUTPUT_TO_TEMP_FOLDER, $OUTPUT_AFTER_COPY_TO_TEMP_FOLDER, $QUICK_BUILD, $OUTPUT_DEBUG_MESSAGE);
		if (!$BuildTokenString) {
			print "Failed to Prepare for Build\n";
		}
		?></pre>
		<div id="buildingactivity_bottom"></div>
		<div id="donebuildheader" style="display:none">
		   <h3>Done Build</h3>
		   <p>&nbsp;</p>
		</div>
		<?php
		if ($BuildTokenString) {
			?>
<script>
$(function() {
	function StartBuildByToken()
	{
		$('#buildingactivity_upper').activity(true);
		
		jQuery.ajax(
			"build_project_ajax_check_if_completed.php",{
				type: "POST",
				dataType: 'json',
				data: {
					"ProjectPID": "<?php print htmlspecialchars($project->PID); ?>",
					"BuildTokenString": "<?php print htmlspecialchars($BuildTokenString); ?>"
				},
				success: function(json){
					if (json._status == "OK") {
						if (json.IsCompleted) {
							StartUploadIfNeeded();
							
						} else {
							jQuery.ajax(
								"build_project_ajax.php",{
									type: "POST",
									dataType: 'html',
									data: {
										"ProjectPID": "<?php print htmlspecialchars($project->PID); ?>",
										"BuildTokenString": "<?php print htmlspecialchars($BuildTokenString); ?>"
									},
									success: function(result){
										$('#buildingactivity_bottom').activity(true);
										$("#buildresult").append(result);
										
										setTimeout(function(){
											StartBuildByToken();
										},10);
									},
									error : function() {
										alert("Internal Error!");
									},
									complete: function() {
									}
								}
							);
						}
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
	StartBuildByToken();
	
	<?php
	
	$output_StartUpload = false;
	
	if ($project->Getoption_auto_upload_after_build())
	{
		if ($DETAILED_BUILD) {
			// No upload when Detailed Build
			
		} else {
			$LocalServerName = "";
			$reload = true;
			
			$DAProjectGroup = new ProjectGroupDBAccess();
			$ProjectGroup = $DAProjectGroup->GetProjectGroupByUserAndProject($matsuesoft_login_token_id, $project->PID);
			if ($ProjectGroup) {
				
				if ($ProjectGroup->CreateSettingGroup == 1 && is_numeric($ProjectGroup->SettingGroupPID) && $ProjectGroup->SettingGroupPID > 0 &&
					$ProjectGroup->CreateServer == 1 && is_numeric($ProjectGroup->ServerPID) && $ProjectGroup->ServerPID > 0 &&
					$ProjectGroup->CreateUploadGroup == 1 && is_numeric($ProjectGroup->UploadGroupPID) && $ProjectGroup->UploadGroupPID > 0 &&
					$ProjectGroup->CreateUploadServer == 1 && is_numeric($ProjectGroup->UploadServerPID) && $ProjectGroup->UploadServerPID > 0 &&
					$ProjectGroup->CreateUploadServerPath == 1 && is_numeric($ProjectGroup->UploadServerPathPID) && $ProjectGroup->UploadServerPathPID > 0 &&
					$ProjectGroup->CreateUploadServerPathForBeta == 1 && is_numeric($ProjectGroup->UploadServerPathPIDForBeta) && $ProjectGroup->UploadServerPathPIDForBeta > 0 &&
					$ProjectGroup->CreateProject == 1 && is_numeric($ProjectGroup->ProjectPID) && $ProjectGroup->ProjectPID > 0
					)
				{
					$DAServer = new ServerDBAccess();
					$Server = $DAServer->GetServerOfAnySettingGroup($ProjectGroup->ServerPID);
					if ($Server) {
						$LocalServerName = $Server->LocalServerName;
						
						$DAUploadServer = new UploadServerDBAccess();
						$UploadServer = $DAUploadServer->GetUploadServer($ProjectGroup->UploadServerPID, $ProjectGroup->SettingGroupPID);
						if ($UploadServer) {
							$DAUploadServerPath = new UploadServerPathDBAccess();
							$UploadServerPath = NULL;
							if (!$BUILD_BETA) {
								$UploadServerPath = $DAUploadServerPath->GetUploadServerPath($ProjectGroup->UploadServerPathPID);
							} else {
								$UploadServerPath = $DAUploadServerPath->GetUploadServerPath($ProjectGroup->UploadServerPathPIDForBeta);
							}
							if ($UploadServerPath) {
								$DropboxDownloadDataList = MakeDropboxDownloadCacheForMtool($LocalServerName, "", $ProjectGroup->UploadGroupPID, $reload, $UploadServerPath->DropboxPath);
								MakeTokenForDropboxDownload($DropboxDownloadDataList, false);
								?>
	var upload_target_token_list = [
								<?php
								$is_first_array = true;
								if (count($DropboxDownloadDataList) > 0)
								{
									for($i = 0 ; $i < count($DropboxDownloadDataList) ; $i++) {
										$DropboxDownloadData = $DropboxDownloadDataList[$i];
										
										for($j = 0 ; $j < count($DropboxDownloadData->AllDirectoryOnServerList); $j++) {
											$AllDirectoryOnServer = $DropboxDownloadData->AllDirectoryOnServerList[$j];
											
											if (!$is_first_array) {
												print ", ";
											}
											print "'" . htmlspecialchars(base64_encode($AllDirectoryOnServer->TokenForThisDirOnly)) . "'";
											$is_first_array = false;
										}
									}
								}
								$output_StartUpload = true;
								?>
	];
	function StartUpload()
	{
		if (upload_target_token_list.length <= 0) {
			CleanupBuild();
			
			return;
		}
		var Token = upload_target_token_list.shift();
		
		$("#buildresult").append("Uploading...<br>");
		
		$.ajax({
			type: "POST",
			url: "<?php print pathCombine($UploadServer->UploaderURL, "upload_do.php"); ?>",
			data: {
				"matsuesoft_login_token": Cookies.get('matsuesoft_login_token'),
				"Token": Token,
				"UploadWithinDays": 90
			},
			crossDomain: true,
			dataType : "text",
		success: function(result)
		{
			$("#buildresult").append(result);
			
			StartUpload();
		},
		error: function(XMLHttpRequest, textStatus, errorThrown)
		{
			$("#buildresult").append("Error occured while upload");
		}
		});
	}
							<?php
							}
						}
					}
				}
			}
		}
	}
	?>
	function StartUploadIfNeeded()
	{
		<?php
		if ($output_StartUpload) {
		?>
		StartUpload();
		<?php
		} else {
		?>
		CleanupBuild();
		<?php
		}
		?>
	}
	function CleanupBuild()
	{
		$('#buildingactivity_upper').activity(false);
		$('#buildingactivity_bottom').activity(false);
		
		set_style_display("buildingheader", "none");
		set_style_display("donebuildheader", "inline");
		set_style_display("buildbuttonarea", "inline");
	}
});

</script>
                
			<?php
            // UpdateProjectSource($project->PID, $BuildTokenString);
        }
        ?>
        <p>Please check if there is a Error.</p>
        </div>
        <?php
		
	} else {
		?>
		<script>
        set_style_display("buildbuttonarea", "inline");
        </script>
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
