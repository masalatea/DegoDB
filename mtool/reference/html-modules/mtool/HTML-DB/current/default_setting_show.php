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
<title><?php print getres("TITLE_SHOW_DEFAULT_SETTING"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
<h3>Show Default Template</h3>
<?php

include_once("default_setting_lib.php");

$TemplateType = GetParam("TemplateType");
$TemplateTargetType = GetParam("TemplateTargetType");
$ProgramLanguage = trim(GetParam("ProgramLanguage"));
$ClassType = GetParam("ClassType");

$target_dir = GetMtoolSettingDirForView($TemplateType, $TemplateTargetType, $ProgramLanguage, true, $ClassType);

if (is_dir($target_dir)) {
	?>
    <table class="table">
        <thead>
        <tr bgcolor="#ECECEC">
        <th>Filename
        </th>
        <th>Source
        </th>
          </tr>
      </thead>
      <tbody>
	<?php
	if ($handle = opendir($target_dir)) {
		while (false !== ($filenameonly = readdir($handle))) {
			if ($filenameonly == "." || $filenameonly == "..") {
				continue;
			}
			$fullfilepath = pathCombine($target_dir, $filenameonly);
			
			if (is_file($fullfilepath)) {
				?>
				<tr>
				<td><?php print htmlspecialchars($filenameonly); ?> 
				[<a href="default_setting_download.php?TemplateType=<?php print htmlspecialchars($TemplateType); ?>&TemplateTargetType=<?php print urlencode($TemplateTargetType); ?>&ProgramLanguage=<?php print urlencode($ProgramLanguage); ?>&ClassType=<?php print urlencode($ClassType); ?>&File=<?php print urlencode($filenameonly); ?>">Download</a>] </td>
				<td>
				<pre><?php
				print htmlspecialchars(file_get_contents($fullfilepath));
				?></pre>
				</td>
				</tr>
				<?php
			}
		}
	}
	?>
    </tbody>
    </table>
    <?php
	
} else {
	?>
    <p>Error! Target Dir is not found. Something Strange. Please contact to administrator if this continues.</p>
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
