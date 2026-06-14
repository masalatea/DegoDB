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
<title><?php print getres("TITLE_CONTENT_CHANGE_ORDER"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
$SpecPID = trim(GetParam("SpecPID"));

$NewSortOrder = trim(GetParam("NewSortOrder"));
$doReset = trim(GetParam("doReset"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($SpecPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Spec PID</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	printPathOnTopForSpec("Change Order of Content(s)", $ProjectPID, $SpecPID, "");
	
	$DASpecContent = new SpecContentDBAccess();
	$SpecContentList = $DASpecContent->GetSpecContentList($ProjectPID, $SpecPID); 
	
	if ($NewSortOrder != "") {
		$NewSortOrderList = preg_split("/,+/", $NewSortOrder);
		
		if (count($NewSortOrder) >0) {
			for ($i = 0 ; $i < count($NewSortOrderList) ;$i++) {
				$thisPID = $NewSortOrderList[$i];
				
				if($DASpecContent->UpdateSpecContentOrder($i + 1, $thisPID, $ProjectPID) === FALSE) {
					// Failed
					?>
					<h3><font color="red">Error! Failed to update. something strange. Please ask administrator if this continues.</font></h3>
					<?php
					$needToLoad = false;
					
				} else {
					// Success
				}
			}
			?>
			<h3><font color="red"><?php print getres("ACTION_UPDATED_CONTENT_SORT_ORDER"); ?></font></h3>
			<?php
		}
		
		// Initialize Again
		$SpecContentList = $DASpecContent->GetSpecContentList($ProjectPID, $SpecPID); 
	}
	if ($doReset != "") {
		for($i = 0 ; $i < count($SpecContentList); $i++) {
			$SpecContent = $SpecContentList[$i];
			
			if($DASpecContent->UpdateSpecContentOrder("Default(ContentOrder)", $SpecContent->PID, $ProjectPID) === FALSE) {
				// Failed
				?>
				<h3><font color="red">Error! Failed to update. something strange. Please ask administrator if this continues.</font></h3>
				<?php
				$needToLoad = false;
				
			} else {
				// Success
			}
		}
		?>
		<h3><font color="red">Reset Sort Order</font></h3>
		<?php
		
		// Initialize Again
		$SpecContentList = $DASpecContent->GetSpecContentList($ProjectPID, $SpecPID); 
	}
	
	if (count($SpecContentList) > 0) {
		
		$forSort = true;
		include_once("contents_table_include.php");
		
		?>
        
		<form action="contents_change_order.php" method="post" id="orderupdateform">
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($ProjectPID); ?>">
		<input name="SpecPID" type="hidden" value="<?php print htmlspecialchars($SpecPID); ?>">
        <input name="NewSortOrder"  id="NewSortOrder" type="hidden" value="">
        <input name="submitbutton" type="button" id="submitbutton" value="UPDATE">
        <input name="doReset" type="submit" id="doReset" value="RESET">
		</form>

<script>
$(function() {
	$("#functionlistbodyarea").sortable({
		cursor: 'move',
		opacity: 0.7,
		placeholder: 'ui-state-highlight',
	});
	$("#submitbutton").click(function() {
		var result = $("#functionlistbodyarea").sortable("toArray").join(',');
		$("#NewSortOrder").val(result);
		// set_style_display("submitbutton", "none");
		// set_style_display("submitingarea", "inline");
		$("#orderupdateform").submit();
	});
});
</script>
        
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
    <p><a href="contents.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&SpecPID=<?php print urlencode($SpecPID); ?>&<?php print makeRandStr(8); ?>">Back to Content List</a></p>
    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/spec/footer_back_link_include.php");
	print_footer_back_link($ProjectPID);
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
