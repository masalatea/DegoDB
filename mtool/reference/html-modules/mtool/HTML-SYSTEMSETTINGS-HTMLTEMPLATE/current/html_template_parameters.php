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
<title><?php print getres("TITLE_HTML_TEMPLATE_PARAMETER_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$htmlTemplatePID = trim(GetParam("htmlTemplatePID"));

$filterhtmlTemplateParameterPID = trim(GetParam("filterhtmlTemplateParameterPID"));

if (is_numeric($filterhtmlTemplateParameterPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific HTML Template Parameter</i></font></h3>
    <?php
}

$NoError = true;

if (!is_numeric($htmlTemplatePID)) {
	?>
    <H3><font color="red">HTML Template is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {

	printPathOnTopForHtmlTemplate("HTML Template Parameter List", $htmlTemplatePID);
	
	$DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate = new htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDBAccess();
	$htmlTemplateParameterList = $DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate->GethtmlTemplateParameterList($htmlTemplatePID);
	
	if (count($htmlTemplateParameterList) > 0) {
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th rowspan="2">Parameter Name</th>
			  <th colspan="4">Target Value</th>
			  <th rowspan="2">Trim Last Space</th>
			  <th rowspan="2">Trim Last Return</th>
			  <th rowspan="2"></th>
			</tr>
			<tr bgcolor="#ECECEC">
			  <th>Type</th>
			  <th>Data Type</th>
			  <th>Code</th>
              <th>Template</th>
			</tr>
			</thead>
			<tbody>
		<?php
		for($i = 0 ; $i < count($htmlTemplateParameterList); $i++) {
			$htmlTemplateParameter = $htmlTemplateParameterList[$i];
			
			// filter
			if (is_numeric($filterhtmlTemplateParameterPID)) {
				if ($filterhtmlTemplateParameterPID != $htmlTemplateParameter->PID) {
					continue;
				}
			}
			?>
			<tr>
			  <td><?php print htmlspecialchars($htmlTemplateParameter->ParameterName); ?></td>
			  <td><?php print htmlspecialchars(GethtmlTemplateParameterTargetValueTypeCaption($htmlTemplateParameter->TargetValueType)); ?></td>
              <td><?php 
				switch($htmlTemplateParameter->TargetValueType)
				{
					case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
						print htmlspecialchars(GethtmlTemplateParameterDataTypeCaption($htmlTemplateParameter->DataType));
						break;
					case htmlTemplateParameterTargetValueTypeEnum::$CODE:
						break;
					case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
						break;
				}
			   ?></td>
			  <td><?php
				switch($htmlTemplateParameter->TargetValueType)
				{
					case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
						break;
					case htmlTemplateParameterTargetValueTypeEnum::$CODE:
						print htmlspecialchars($htmlTemplateParameter->GetTargetVariable());
						break;
					case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
						break;
				}
			  ?></td>
			  <td><?php
				switch($htmlTemplateParameter->TargetValueType)
				{
					case htmlTemplateParameterTargetValueTypeEnum::$EACHHTML:
						break;
					case htmlTemplateParameterTargetValueTypeEnum::$CODE:
						break;
					case htmlTemplateParameterTargetValueTypeEnum::$ANOTHERTEMPLATE:
						print htmlspecialchars($htmlTemplateParameter->htmlTemplatename);
						break;
				}
			  ?></td>
			  <td><?php
			  if ($htmlTemplateParameter->TrimLastSpace == 1) {
				  print "Yes";
			  } else {
				  print "No";
			  } ?></td>
			  <td><?php
			  if ($htmlTemplateParameter->TrimLastReturn == 1) {
				  print "Yes";
			  } else {
				  print "No";
			  } ?></td>
              <td><a href="html_template_parameter_edit.php?htmlTemplatePID=<?php print urlencode($htmlTemplatePID); ?>&htmlTemplateParameterPID=<?php print urlencode($htmlTemplateParameter->PID); ?>&<?php print makeRandStr(8); ?>">Edit Basic Info</a></td>
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
	?>
    <p align="right"><a href="html_template_parameter_edit.php?htmlTemplatePID=<?php print urlencode($htmlTemplatePID); ?>&<?php print makeRandStr(8); ?>">Add New HTML Template Parameter</a></p>
    <br>
    <br>
    <br>
	<p><a href="./?<?php print makeRandStr(8); ?>">Back to HTML Template Setting List</a></p>
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
