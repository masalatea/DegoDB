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
<title><?php print getres("TITLE_PROJECT_GROUP_TEMPLATE_EDIT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_project_group.php");

$ADD = trim(GetParam("ADD"));
$insertToken = trim(GetParam("insertToken"));

$username = trim(GetParam("username"));
$custom_server_name = trim(GetParam("custom_server_name"));
$custom_server_ip = trim(GetParam("custom_server_ip"));
$custom_base_url = trim(GetParam("custom_base_url"));
$custom_virtual_host_name = trim(GetParam("custom_virtual_host_name"));

if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
}

printPathOnTopForProjectGroupSetting("Make Project Group Setting");

if ($ADD != "") {

	$PID = "";
	if ($insertToken != "") {
		// If already inserted, PID will be get. If not yet, keep empty
		$PID = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($insertToken);
	}
	if ($PID == "") {
		// Add
		$PID = create_project_group($username, ProjectGroupTemplateProjectGroupTypeEnum::$VPS, $custom_server_name, $custom_server_ip, $custom_base_url, $custom_virtual_host_name);
		
		if ($insertToken != "" && is_numeric($PID)) {
			if (SetPrimaryKeyValueForInsertTokenForThisHost($insertToken, $PID)) {
				// Success
				$insertToken = "";
				?>
                <p>New Project Group was created.</p>
                <?php
			} else {
				// Failed
				?>
				<h3><font color="red">Internal Error! Failed to complete Insert because of complete access token</font></h3>
				<?php
			}
		} else {
			// Failed
			?>
			<h3><font color="red">Internal Error! Failed to complete Insert. Something Strange</font></h3>
			<?php
		}
		?>
        
        <?php
		
	} else {
		?>
        <p>Already Created. (Did you reload page?)</p>
        <?php
	}
	
} else {
	$insertToken = CreateNewTokenForThisHost();
}

if ($insertToken != "" && $ADD == "") {
	?>
	<form action="projectgroup_make.php" method="post">
	<?php
			mtoolCommonFormInput("username", $username,
				array($LANG_ENGLISH=>"User Name", $LANG_JAPANESE=>"User Name"),
				array($LANG_ENGLISH=>"Please input User Name", $LANG_JAPANESE=>"User Nameを入力して下さい"),
				"text", "");
			mtoolCommonFormInput("custom_server_name", $custom_server_name,
				array($LANG_ENGLISH=>"Custom Server Name", $LANG_JAPANESE=>"Custom Server Name"),
				array($LANG_ENGLISH=>"Please input Custom Server Name. e.g. dbaassandbox", $LANG_JAPANESE=>"Custom Server Nameを入力して下さい。例: dbaassandbox"),
				"text", "");
			mtoolCommonFormInput("custom_server_ip", $custom_server_ip,
				array($LANG_ENGLISH=>"Custom Server IP", $LANG_JAPANESE=>"Custom Server IP"),
				array($LANG_ENGLISH=>"Please input Custom Server IP", $LANG_JAPANESE=>"Custom Server IPを入力して下さい"),
				"text", "");
			mtoolCommonFormInput("custom_base_url", $custom_base_url,
				array($LANG_ENGLISH=>"Custom Base URL", $LANG_JAPANESE=>"Custom Base URL"),
				array($LANG_ENGLISH=>"Please input Custom Base URL. e.g. https://dbaassandbox.matsuesoft.co.jp", $LANG_JAPANESE=>"Custom Base URLを入力して下さい。例: https://dbaassandbox.matsuesoft.co.jp"),
				"text", "");
			mtoolCommonFormInput("custom_virtual_host_name", $custom_virtual_host_name,
				array($LANG_ENGLISH=>"Virtual Host Name", $LANG_JAPANESE=>"Virtual Host Name"),
				array($LANG_ENGLISH=>"Please input Virtual Host Name.  e.g. dbaassandbox.matsuesoft.co.jp", $LANG_JAPANESE=>"Virtual Host Nameを入力して下さい。例: dbaassandbox.matsuesoft.co.jp"),
				"text", "");
	?>
			<div class="row">
				<label class="col-md-3 control-label" for="inputtext"></label>
				<div class="col-md-9"><input name="ADD" type="submit" value="Add">
				</div>
			</div>
			<input name="insertToken" type="hidden" value="<?php print htmlspecialchars($insertToken); ?>">
	</form>
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
