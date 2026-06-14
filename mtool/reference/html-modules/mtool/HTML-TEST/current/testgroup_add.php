<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_EMAIL_VERIFY_AFTER_LOGIN = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_TEST_GROUP_EDIT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = GetParam("ProjectPID");		// Optional

$DAProject = new ProjectDBAccess();
$projectlist = $DAProject->GetProjectbyOwnerOrUserSecurityList($matsuesoft_login_token_id); 
if (count($projectlist) > 0) {
	?>
    <h3>Select Project to add Test Group</h3>
    <form action="testgroup_edit.php" method="post" id="testgroupeditform">
    <?php
	if ($ProjectPID != "") {
		$DAProject = new ProjectDBAccess();
		$thisProjectObj = $DAProject->GetProject($ProjectPID);
		if ($thisProjectObj) {
			?>
            <p>Project: <?php print $thisProjectObj->name; ?></p>
            <?php
		} else {
			die("Fatal Error. Unknown Project");
		}
		?>
        <input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
        <?php
	} else {
		?>
        <select name="ProjectPID" id="ProjectPID">
            <option value="">Select Project</option>
        <?php
        for($i = 0 ; $i < count($projectlist); $i++) {
            $project = $projectlist[$i];
            
            if (!CheckIfPossibleToAccessByList($project->PID, $matsuesoft_login_token_id,
                    array(
                        ProjectUserSerurityEnum::$TESTTOOLREAD,
                        ProjectUserSerurityEnum::$TESTTOOLWRITE
                    )
                )) {
                continue;
            }
            ?>
            <option value="<?php print htmlspecialchars($project->PID); ?>"><?php print htmlspecialchars($project->name); ?></option>
            <?php
        }
        ?>
        </select>
    <?php
	}
	?>
    <input name="Next" id="nextbutton" type="button" value="Next">
    </form>
<script>
$(document).ready(function() {
    $('#nextbutton').click(function() {
		var projectPIDvalue = $("#ProjectPID").val();
		
		if (projectPIDvalue == "") {
			alert("Please select Project");
		} else {
	        $('#testgroupeditform').submit();
		}
    });
});
</script>

<?php
} else {
	?>
    <p>No Project to be accessed</p>
    <?php
}

?>
<br>
<br>
<br>
<?php
include_once("/srv/legacy/www/$WWWDOMAINNAME/test/footer_back_link_include.php");
print_footer_back_link($ProjectPID);
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
