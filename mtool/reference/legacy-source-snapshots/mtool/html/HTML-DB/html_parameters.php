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
<title><?php print getres("TITLE_HTML_PARAMETER_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$ProjectPID = GetParam("ProjectPID");
$htmlPID = GetParam("htmlPID");

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($htmlPID)) {
	?>
    <H3><font color="red">ERROR! Unknown HTML PID</font></H3>
    <?php
	$NoError = false;
}

$DAhtml = new htmlDBAccess();
$html = NULL;

if ($NoError) {
	$html = $DAhtml->Gethtml($htmlPID, $ProjectPID);
	if (!$html) {
		$NoError = false;
		?>
		<H3><font color="red">ERROR! No Corresponding HTML</font></H3>
		<?php
	}
}

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);
	
	printPathOnTopForHtml("HTML Parameter List", $ProjectPID, $htmlPID);
	
	$DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate = new htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDBAccess();
	$htmlTemplateParameterList = $DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate->GethtmlTemplateParameterListMostDeep($html->htmlTemplatePID);
	
	$DAhtmlParameter = new htmlParameterDBAccess();
	$htmlParameterList = $DAhtmlParameter->GethtmlParameterList($ProjectPID, $htmlPID);
	
	if (count($htmlTemplateParameterList) > 0) {
		
		$matchedParameterPIDList = array();
		?>
		<table class="table">
			<thead>
			<tr bgcolor="#ECECEC">
			  <th>Parameter Name</th>
			  <th>Data Type</th>
			  <th>Parameter Value</th>
			  <th></th>
			</tr>
			</thead>
			<tbody>
		<?php
		
		$ParameterNameListForWarningForDuplicateParameterNameButDataTypeIsNotMatched = array();
		
		$ParameterNameHT = array();
		$ParameterDataTypeHT = array();
		for($i = 0 ; $i < count($htmlTemplateParameterList); $i++) {
			$htmlTemplateParameter = $htmlTemplateParameterList[$i];
			
			if ($htmlTemplateParameter->TargetValueType != htmlTemplateParameterTargetValueTypeEnum::$EACHHTML) {
				continue;
			}
			if (array_key_exists($htmlTemplateParameter->ParameterName, $ParameterNameHT)) {
				// Same Parameter Name. Skip. Same parameter name will be the same value.
				
				if ($ParameterDataTypeHT[$htmlTemplateParameter->ParameterName] != $htmlTemplateParameter->DataType) {
					array_push($ParameterNameListForWarningForDuplicateParameterNameButDataTypeIsNotMatched, $htmlTemplateParameter->ParameterName);
				}
				continue;
			}
			$ParameterNameHT[$htmlTemplateParameter->ParameterName] = true;
			$ParameterDataTypeHT[$htmlTemplateParameter->ParameterName] = $htmlTemplateParameter->DataType;
			
			$correspondinghtmlParameter = NULL;
			$correspondinghtmlParameterPID = "";
			$duplicatedhtmlParameterPIDList = array();
			$alreadymatched = false;
			for($j = 0 ; $j < count($htmlParameterList); $j++) {
				$htmlParameter = $htmlParameterList[$j];
				
				if ($htmlParameter->ParameterName == $htmlTemplateParameter->ParameterName) {
					
					if ($alreadymatched) {
						// Something Strange. Duplicated.
						array_push($duplicatedhtmlParameterPIDList, $htmlParameter->PID);
						
					} else {
						$correspondinghtmlParameter = $htmlParameter;
						$correspondinghtmlParameterPID = $htmlParameter->PID;
						$alreadymatched = true;
					}
					array_push($matchedParameterPIDList, $htmlParameter->PID);
				}
			}
			
			$AdditionalParametersForEditLink = "";
			if ($correspondinghtmlParameter == NULL) {
				$AdditionalParametersForEditLink .= "&ParameterName=" . urlencode($htmlTemplateParameter->ParameterName);
			}
			$AdditionalParametersForEditLink .= "&DataType=" . urlencode($htmlTemplateParameter->DataType);
			?>
			<tr>
			  <td><?php print htmlspecialchars($htmlTemplateParameter->ParameterName); ?></td>
			  <td><?php print htmlspecialchars(GethtmlTemplateParameterDataTypeCaption($htmlTemplateParameter->DataType)); ?></td>
              <td><?php
			  if ($correspondinghtmlParameter != NULL) {
				  print htmlspecialchars($correspondinghtmlParameter->ParameterValue);
			  } else {
				  ?>
                  <font color="red">Not Yet Set</font>
                  <?php
			  }
			  ?></td>
			  <?php if ($DBWritePermission) { ?>
				  <td><a href="html_parameter_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&htmlPID=<?php print urlencode($htmlPID); ?>&htmlParameterPID=<?php print urlencode($correspondinghtmlParameterPID); ?><?php print $AdditionalParametersForEditLink; ?>&<?php print makeRandStr(8); ?>">Edit</a>
                  <?php
                  if (count($duplicatedhtmlParameterPIDList) > 0) {
					  ?>
                      <br>
                      <font color="red">Error! There is a duplicated items. Please delete or change Parameter Name of this:</font>
                      <?php
					  for($j = 0 ; $j < count($duplicatedhtmlParameterPIDList) ; $j++) {
						  $duplicatedhtmlParameterPID = $duplicatedhtmlParameterPIDList[$j];
						  ?>
                          <a href="html_parameter_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&htmlPID=<?php print urlencode($htmlPID); ?>&htmlParameterPID=<?php print urlencode($duplicatedhtmlParameterPID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
                          <?php
					  }
                  }
                  ?>
                  </td>
			  <?php } ?>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
	<?php
		if (count($ParameterNameListForWarningForDuplicateParameterNameButDataTypeIsNotMatched) > 0) {
			?>
            <h2><font color="red">Warning! Some Parameter Name's Data Type is not matched. Please check Template Setting.</font></h2>
            <?php
			for($j = 0 ; $j < count($ParameterNameListForWarningForDuplicateParameterNameButDataTypeIsNotMatched); $j++) {
				$thisParameterName = $ParameterNameListForWarningForDuplicateParameterNameButDataTypeIsNotMatched[$j];
				?>
                <p>Parameter Name: <?php print $thisParameterName; ?></p>
                <?php
			}
			?>
            <br>
            <br>
            <br>
            <?php
		}
		
		for($j = 0 ; $j < count($htmlParameterList); $j++) {
			$htmlParameter = $htmlParameterList[$j];
			
			if (!in_array($htmlParameter->PID, $matchedParameterPIDList)) {
				?>
                Warning: This is a item which not matched with any Parameter Name. This is not used. Please Check: 
                <a href="html_parameter_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&htmlPID=<?php print urlencode($htmlPID); ?>&htmlParameterPID=<?php print urlencode($htmlParameter->PID); ?>&<?php print makeRandStr(8); ?>">Edit</a>
                <br>
                <?php
			}
		}
	
        
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
    <br>
    <br>
    <br>
    <p><a href="htmls.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Back to HTML List</a></p>
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
