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
<title><?php print getres("TITLE_HTML_TEMPLATE_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

if (!CheckIfMtoolInternalSystemAdministrator($matsuesoft_login_token_id)) {
	die("This Page is Administrator Only.");
}

$filterhtmlTemplatePID = trim(GetParam("filterhtmlTemplatePID"));

if (is_numeric($filterhtmlTemplatePID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific HTML Template</i></font></h3>
    <?php
}

// function cmphtmlTemplateList($a, $b)
// {
// 	if ($a->PID == $b->ParentHtmlTemplatePID) {
// 		return -1;
// 	}
// 	if ($a->ParentHtmlTemplatePID == $b->PID) {
// 		return 1;
// 	}
// 	$result = ($a->ParentHtmlTemplatePID != $b->ParentHtmlTemplatePID);
// 	if ($result != 0) {
// 		return $result;
// 	}
// 	$result = strcmp($a->name, $b->name);
// 	if ($result != 0) {
// 		return $result;
// 	}
// 	return ($a->PID - $b->PID);
// }

$NoError = true;

if ($NoError) {

	printPathOnTopForHtmlTemplate("HTML Template List", "");
	
	$htmlTemplateTargetTypeList = GetAllhtmlTemplateTargetType();
	array_push($htmlTemplateTargetTypeList, "");
	
	for($i = 0 ; $i < count($htmlTemplateTargetTypeList); $i++) {
		$htmlTemplateTargetType = $htmlTemplateTargetTypeList[$i];
		
		$DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate = new htmlTemplate_leftouterjoin_ParentHtmlTemplateDBAccess();
		$originalhtmlTemplateList = $DAhtmlTemplate_leftouterjoin_ParentHtmlTemplate->GethtmlTemplateByTargetTypeList($htmlTemplateTargetType);
		$htmlTemplateList = SorthtmlTemplateDataListByTree($originalhtmlTemplateList);
		
		if (count($htmlTemplateList) > 0) {
			
			if ($htmlTemplateTargetType != "") {
				?>
				<h3>Target: <?php print GethtmlTemplateTargetTypeCaption($htmlTemplateTargetType); ?></h3>
				<?php
			} else {
				?>
				<h3>Others</h3>
				<?php
			}
			
			?>
			<table class="table">
				<thead>
				<tr bgcolor="#ECECEC">
				  <th>Parent</th>
				  <th>Name</th>
				  <th>Program Language</th>
				  <th>File Name</th>
				  <th>Comment</th>
				  <th></th>
				  <th></th>
				  <th></th>
				</tr>
				</thead>
				<tbody>
			<?php
			for($j = 0 ; $j < count($htmlTemplateList); $j++) {
				$htmlTemplate = $htmlTemplateList[$j];
				
				// filter
				if (is_numeric($filterhtmlTemplatePID)) {
					if ($filterhtmlTemplatePID != $htmlTemplate->PID) {
						continue;
					}
				}
				?>
				<tr>
				  <td><?php print htmlspecialchars($htmlTemplate->ParentHtmlTemplatename); ?></td>
				  <td><?php print htmlspecialchars($htmlTemplate->name); ?></td>
				  <td><?php print htmlspecialchars(GethtmlTemplateDataLanguageCaption($htmlTemplate->ProgramLanguage)); ?></td>
				  <td><?php print htmlspecialchars($htmlTemplate->FileName); ?></td>
				  <td><?php print htmlspecialchars($htmlTemplate->Comment); ?></td>
				  <td>
                  <?php
					switch($htmlTemplateTargetType)
					{
						case htmlTemplateTargetTypeEnum::$HTML:
							?>
							<a href="html_template_parameters.php?htmlTemplatePID=<?php print urlencode($htmlTemplate->PID); ?>&<?php print makeRandStr(8); ?>">Parameter List</a>
							<?php
							break;
						case "":
						case htmlTemplateTargetTypeEnum::$DB:
						case htmlTemplateTargetTypeEnum::$PROXYSERVER:
						case htmlTemplateTargetTypeEnum::$PROXYCLIENT:
						case htmlTemplateTargetTypeEnum::$DBAASPROXYSERVER:
						case htmlTemplateTargetTypeEnum::$DBAASPROXYCLIENT:
						case htmlTemplateTargetTypeEnum::$UNITTEST:
						case htmlTemplateTargetTypeEnum::$UPLOADSETTING:
						case htmlTemplateTargetTypeEnum::$LANGUAGERESOURCE:
							break;
					}
				  ?>
                  </td>
				  <td><a href="html_template_edit.php?htmlTemplatePID=<?php print urlencode($htmlTemplate->PID); ?>&<?php print makeRandStr(8); ?>">Edit Basic Info</a></td>
				  <td><a href="html_template_edit.php?duplicate_htmlTemplatePID=<?php print urlencode($htmlTemplate->PID); ?>&<?php print makeRandStr(8); ?>">Duplicate</a></td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
		<?php
			
		} else {
			// Nothing
		}
	}
	?>
    <p align="right"><a href="html_template_edit.php?<?php print makeRandStr(8); ?>">Add New HTML Template</a></p>
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
