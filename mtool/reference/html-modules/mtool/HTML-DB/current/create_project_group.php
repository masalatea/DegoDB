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
<title><?php print getres("TITLE_CREATE_PROJECT_GROUP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_project_group.php");

$DO_CREATE = GetParam("DO_CREATE");

printPathOnTopForDBAccessClass("Create Sandbox Project", "", "", "", "", "", "", "", "");

if (check_if_project_group_is_created_for_this_mtool_user($matsuesoft_login_token_id, ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX) &&
    check_if_project_group_is_created_for_this_mtool_user($matsuesoft_login_token_id, ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER)) {
	?>
	<h3>Sandbox Project was already created.</h3>
	<?php
} else {
	
	if ($DO_CREATE != "") {
		?>
		<h3>Creating Sandbox Project</h3>
		<?php
		create_project_group($matsuesoft_login_token_id, ProjectGroupTemplateProjectGroupTypeEnum::$SANDBOX, NULL, NULL, NULL, NULL);
		?>
		<h3>Creating Project</h3>
		<?php
		create_project_group($matsuesoft_login_token_id, ProjectGroupTemplateProjectGroupTypeEnum::$SHAREDSERVER, NULL, NULL, NULL, NULL);
	} else {
		
		switch($lang) {
			case $LANG_JAPANESE:
				?>
                <h3>プロジェクトを作成します。</h3>
                <ul>
                  <li>実験用のSandboxプロジェクト</li>
                  <li>運用用DBプロジェクト</li>
                </ul>
                <p><a href="https://www.matsuesoft.co.jp/degodb/">説明ページ</a>を参照になり、プランおよび料金に同意してから作成して下さい。<br>
                Sandboxは開発＆実験用途に限り無料。通常プランも100MB以下は無料ですのでお気軽にお試し頂けます。</p>
				<?php
				break;
			case $LANG_ENGLISH:
				?>
				<h3>Create Project</h3>
				<ul>
                  <li>Sandbox Project for test</li>
                  <li>DB Project for normal operation</li>
                </ul>
                <p>Please create after reading and agree with <a href="https://www.matsuesoft.com/degodb/">Explanation</a> about the plan and fee.<br>
                Sandbox is free for development and testing purposes only. Normal plan is also free if storage usage is less than 100MB. You can try freely.</p>
				<?php
				break;
		}
		?>
        
        <form action="create_project_group.php" method="post">
        <input name="DO_CREATE" type="submit" value="Create Project">
        </form>
        <?php
	}
}
?>
<p>&nbsp;</p>
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
