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

function show_project_row($project, $is_main_target)
{
	global $is_first;
	global $CheckTargetProjectPIDList;
	?>
	<tr>
		<td><?php print htmlspecialchars($project->name);
			if ($is_main_target) {
				?>
				<br>
				<font color="red">(Main Target)</font>
				<?php
			}
			?></td>
		<td>
		<?php
			$DACompareOutput = new CompareOutputDBAccess();
			$CompareOutputList = $DACompareOutput->GetCompareOutputList($project->PID);
			if (count($CompareOutputList) > 0) {
				?>
				<span class="checkbox"><label><input name="CheckTargetProjectPIDList[]" type="checkbox" <?php mtool_output_class_tag_for_each_checkbox_by_name("MtoolMultiCheckBoxForCheckTarget"); ?> value="<?php print $project->PID; ?>"<?php if (($is_main_target && $is_first) || in_array($project->PID, $CheckTargetProjectPIDList)) { print " checked"; } ?>>Compare</label></span>
				<?php
			}
			?>
		</td>
	</tr>
	<?php
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_PROJECT_SOURCE_COMPARE_EXECUTE"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_compare_output.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_ignore.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

$FORCE_CHECK_UPDATED_DAY_THRESHOLD = 3;

$START_CHECK = (trim(GetParam("START_CHECK")) != "");
$ProjectPID = trim(GetParam("ProjectPID"));
$QUICK_CHECK = trim(GetParam("QUICK_CHECK"));

$CheckTargetProjectPIDList = GetParam("CheckTargetProjectPIDList");
if (!is_array($CheckTargetProjectPIDList)) {
	$CheckTargetProjectPIDList = array();
}

$is_first = !$START_CHECK;

if ($is_first) {
	$QUICK_CHECK = "y";
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
	$IsMtoolProjectOwner = CheckIfMtoolProjectOwner($ProjectPID, $matsuesoft_login_token_id);
	
	printPathOnTopForSourceCompare("Compare Output", $ProjectPID);
	
	$DAProject = new ProjectDBAccess();
	$projectlist = $DAProject->GetProjectbyOwnerOrUserSecurityList($matsuesoft_login_token_id); 
	
	if (count($projectlist) > 0) {
		?>
<script>
$(document).ready(function() {
    $('#START_CHECK').click(function() {
		
		$("#comppareoutputform").hide();
		$("#result_area").empty();
		
		$('#buildingactivity_upper').activity(true);
		$('#buildingactivity_bottom').activity(true);
		
		$.each($("input[name='CheckTargetProjectPIDList[]']:checked"), function() {
			var CheckTargetProjectPID = parseInt($(this).val());
			CheckTargetProjectPIDList.push(CheckTargetProjectPID);
		});
    });
	setInterval(function(){
		if (!RequestingNow) {
			if (CheckTargetProjectPIDList.length > 0) {
				RequestingNow = true;
				
				var QUICK_CHECK = "";
				if ($("#QUICK_CHECK").prop('checked')) {
					QUICK_CHECK = "1";
				}
				var CheckTargetProjectPID = CheckTargetProjectPIDList.pop();
				
				jQuery.ajax(
					"compare_output_do_ajax.php",{
						type: "POST",
						dataType: 'html',
						data: {
							"ProjectPID": "<?php print htmlspecialchars($ProjectPID); ?>",
							"CheckTargetProjectPIDList": CheckTargetProjectPID,
							"QUICK_CHECK": QUICK_CHECK,
							"QUICK_CHECK_THRESHOLD_DAY": $("#QUICK_CHECK_THRESHOLD_DAY").val(),
						},
						success: function(result){
							$("#result_area").append(result);
						},
						error : function() {
							alert("Internal Error!");
						},
						complete: function() {
							RequestingNow = false;
							
							if (CheckTargetProjectPIDList.length == 0) {
								$('#buildingactivity_upper').activity(false);
								$('#buildingactivity_bottom').activity(false);
								$("#comppareoutputform").show();
							}
						}
					}
				);
			}
		}
		
	}, 1000);
	var CheckTargetProjectPIDList = [];
	var RequestingNow = false;
});
</script>
		<div id="buildingactivity_upper"></div>
        <div id="comppareoutputform">
       <br>
        <p>
			<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
			<input name="START_CHECK" id="START_CHECK" value="Start Check" type="button">
		</p>
		<p>
		<span class="checkbox"><label><input name="QUICK_CHECK" type="checkbox" id="QUICK_CHECK"<?php if ($QUICK_CHECK != "") { print " checked"; } ?>> Quick Check</label>  Threshold: <input name="QUICK_CHECK_THRESHOLD_DAY" id="QUICK_CHECK_THRESHOLD_DAY" type="text" value="<?php print htmlspecialchars($FORCE_CHECK_UPDATED_DAY_THRESHOLD); ?>" size="3"> Day(s)
        </p>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Name</th>
			  <th>Target? <span class="checkbox"><label><?php mtool_output_checkbox_for_select_all_by_name("AllCheckTarget"); ?>Check Target</label></th>
			  <th></th>
			</tr>
			</thead>
			<tbody>
		<?php
		
		for($i = 0 ; $i < count($projectlist); $i++) {
			$project = $projectlist[$i];
			
			$is_main_target = false;
			if ($project->PID == $ProjectPID) {
				$is_main_target = true;
			}
			show_project_row($project, $is_main_target);
		}
		?>
			</tbody>
	    </table>
        </div>
		<?php
		mtool_output_script_tag_for_multi_checkbox_by_name("AllCheckTarget", "MtoolMultiCheckBoxForCheckTarget");

	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
	<div id="result_area"></div>
	<div id="buildingactivity_bottom"></div>
<script>
$(function() {
	$("#comppareoutputform").show();
	$('#buildingactivity_upper').activity(false);
	$('#buildingactivity_bottom').activity(false);
});
</script>
		<?php
	}
	
	?>
	<br>
	<br>
	<br>
	<p><a href="./?<?php print makeRandStr(8); ?>">Back to Project List</a></p>
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
