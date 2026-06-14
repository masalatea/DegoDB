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
<title><?php print getres("TITLE_LANGUAGE_RESOURCE_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = GetParam("ProjectPID");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

include_once("lang_res_check_project_source_output_setting_lib.php");
if ($NoError) {
	CheckProjectSourceOutputSettingForLanguageResource($ProjectPID);
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForLanguageResource("Language Resource", $ProjectPID, "");
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	
	$DALanguageResourceGroup = new LanguageResourceGroupDBAccess();
	$LanguageResourceGroupList = $DALanguageResourceGroup->GetLanguageResourceGroupList($ProjectPID); 
	
	if (count($LanguageResourceGroupList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC" class="unsortable">
			  <th>Group Name</th>
              <th></th>
              <?php if ($DBWritePermission) { ?>
                <th></th>
              <?php } // if DBWritePermission ?>
			</tr>
            </thead>
            <tbody id="sortablebodyarea">
		<?php
		for($i = 0 ; $i < count($LanguageResourceGroupList); $i++) {
			$LanguageResourceGroup = $LanguageResourceGroupList[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($LanguageResourceGroup->Name); ?></td>
		      <td><a href="lang_res_list.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&LanguageResourceGroupPID=<?php print urlencode($LanguageResourceGroup->PID); ?>&<?php print makeRandStr(8); ?>">View Language Resource List</a></td>
              <?php if ($DBWritePermission) { ?>
		        <td><a href="lang_res_group_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&PID=<?php print urlencode($LanguageResourceGroup->PID); ?>&<?php print makeRandStr(8); ?>">Edit Group Info</a></td>
              <?php } // if DBWritePermission ?>
			</tr>
			<?php
		}
		?>
        	</tbody>
		</table>
        <?php
		if ($IsIncludeXcode) {
			switch($lang) {
				case $LANG_JAPANESE:
					?>
					<p>注: Xcode向けキー名は読み辛い場合がありますので通常のキー名に加えてXcode向けキー名を設定できるようにしてあります。可読可能なキー名の場合はキー名のみ設定し、読み辛いキー名の場合はキー名とXcode向けキー名の両方を設定することをお勧め致します。</p>
					<?php
					break;
				case $LANG_ENGLISH:
					?>
					<p>Note: Sometimes this key name is not readable. So, additional setting can be defined in addition to normal Key Nam which is readable. If readable, just input Key Name only. If not readable, both Key Name and Key Name for Xcode should input.</p>
					<?php
					break;
			}
		}
        
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
    <?php if ($DBWritePermission) { ?>
    <p align="right"><a href="lang_res_group_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add Group</a></p>
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
