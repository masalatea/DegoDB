<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_OWNER = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content

include_once("/srv/legacy/www/mtool_lib/lib_form.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_build.php");

function OutputProjectUserListDetail($ProjectUserList)
{
	if (count($ProjectUserList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th rowspan="2">User Name</th>
              <th colspan="2">Chat Tool</th>
              <th colspan="2">Req Tool</th>
              <th colspan="2">Spec Tool</th>
              <th colspan="2">DB Tool</th>
              <th colspan="2">Html</th>
              <th colspan="2">Test Tool</th>
              <th colspan="2">Minutes Tool</th>
              <th colspan="2">Upload Tool</th>
              <th rowspan="2"></th>
			</tr>
			<tr bgcolor="#ECECEC">
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
              <th>Read</th>
              <th>Write</th>
			</tr>
          </thead>
            <tbody>
		<?php

		for($i = 0 ; $i < count($ProjectUserList); $i++) {
			$ProjectUser = $ProjectUserList[$i];
			?>
			<tr>
			  <td><?php print htmlspecialchars($ProjectUser->username); ?></td>
              <?php
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->ChatRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->ChatWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->ReqRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->ReqWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->spectoolRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->spectoolWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->dbtoolRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->dbtoolWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->htmlRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->htmlWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->testtoolRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->testtoolWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->MinutesRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->MinutesWrite);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->UploadRead);
			  OutputProjectUserListDetailOutputOneLine($ProjectUser->UploadWrite);
			  ?>
			  <td><a href="project_security_detail_edit.php?ProjectPID=<?php print urlencode($ProjectUser->ProjectPID); ?>&username=<?php print urlencode($ProjectUser->username); ?>&<?php print makeRandStr(8); ?>">Edit Detail Security</a></td>
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
}
function OutputProjectUserListDetailOutputOneLine($DBValue)
{
	$bgcolor = "";
	if ($DBValue == "1") {
		$bgcolor = ' bgcolor="#FFCC66"';
	}
	print "<td" . $bgcolor . ">" . GetYesOrNoBasedOnMySQLBooleanValue($DBValue) . "</td>";
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_PROJECT_SECURITY_DETAIL_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if ($NoError) {
	
	$DAProjectUser = new ProjectUserDBAccess();
	$ProjectOwnerList = $DAProjectUser->GetProjectOwnerList($ProjectPID);
	$ProjectUserList = $DAProjectUser->GetProjectUserList($ProjectPID);
	
	?>
    <h3>Project User List</h3>
    <?php
	OutputProjectUserListDetail($ProjectUserList);
	?>
    
    <h3>Project Owner List</h3>
    <?php
	OutputProjectUserListDetail($ProjectOwnerList);
	?>
    <br>
    <br>
    <br>

    <p align="right"><a href="project_security_user_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Edit User List</a></p>
    <br>
    <br>
    <br>
    <p><a href="./project_security.php?<?php print makeRandStr(8); ?>">Back to Project Security List</a></p>
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
