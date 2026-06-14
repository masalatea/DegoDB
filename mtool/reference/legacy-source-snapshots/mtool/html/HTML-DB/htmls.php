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

function CheckIfUnassignedProjectSourceOutputPID($PID)
{
	global $UnassignedProjectSourceOutputPIDHT;
	return array_key_exists($PID, $UnassignedProjectSourceOutputPIDHT);
}
$UnassignedProjectSourceOutputPIDHT = array();

function CheckIfAllHTMLParameterIsSetBasedOnTemplate($ProjectPID, $htmlTemplatePID, $htmlPID, &$IsDuplicated)
{
	$IsDuplicated = false;
	
	$DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate = new htmlTemplateParameter_leftouterjoin_AnotherHtmlTemplateDBAccess();
	$htmlTemplateParameterList = $DAhtmlTemplateParameter_leftouterjoin_AnotherHtmlTemplate->GethtmlTemplateParameterListMostDeep($htmlTemplatePID);
	
	$DAhtmlParameter = new htmlParameterDBAccess();
	$htmlParameterList = $DAhtmlParameter->GethtmlParameterList($ProjectPID, $htmlPID);
	
	if (count($htmlTemplateParameterList) > 0) {
		$ParameterNameHT = array();
		for($i = 0 ; $i < count($htmlTemplateParameterList); $i++) {
			$htmlTemplateParameter = $htmlTemplateParameterList[$i];
			
			if ($htmlTemplateParameter->TargetValueType != htmlTemplateParameterTargetValueTypeEnum::$EACHHTML) {
				// Not a target
				continue;
			}
			if (array_key_exists($htmlTemplateParameter->ParameterName, $ParameterNameHT)) {
				// Same Parameter Name. Skip. Same parameter name will be the same value.
				continue;
			}
			$ParameterNameHT[$htmlTemplateParameter->ParameterName] = true;
			
			$isMatched = false;
			for($j = 0 ; $j < count($htmlParameterList); $j++) {
				$htmlParameter = $htmlParameterList[$j];
				if ($htmlParameter->ParameterName == $htmlTemplateParameter->ParameterName) {
					if ($isMatched) {
						// Something Strange. Duplicated.
						$IsDuplicated = true;
						return false;
						
					} else {
						$isMatched = true;
					}
				}
			}
			if (!$isMatched) {
				// No corresponding set-Parameter
				return false;
			}
		}
	}
	return true;
}
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_HTML_VIEW"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox_core.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_dropbox.php");

$ProjectPID = GetParam("ProjectPID");

$filterhtmlPID = trim(GetParam("filterhtmlPID"));

if (is_numeric($filterhtmlPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific HTML</i></font></h3>
    <?php
}

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">ERROR! Unknown Project PID</font></H3>
    <?php
	$NoError = false;
}

$DAProjectSourceOutput = new ProjectSourceOutputDBAccess();

if ($NoError) {
	$DBWritePermission = CheckIfPossibleToAccess($ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$DBTOOLWRITE);

	printPathOnTopForHtml("HTML List", $ProjectPID, "");
	
	$DAProject = new ProjectDBAccess();
	$project = $DAProject->GetProject($ProjectPID);
	
	$DAhtml_leftouterjoin_htmlTemplate = new html_leftouterjoin_htmlTemplateDBAccess();
	$htmlList = $DAhtml_leftouterjoin_htmlTemplate->GethtmlList($ProjectPID);
	
	if (count($htmlList) > 0) {
		
		$TargetProjectSourceOutputList = array();
		for($i = 0 ; $i < count($htmlList); $i++) {
			$html = $htmlList[$i];
			
			// filter
			if (is_numeric($filterhtmlPID)) {
				if ($filterhtmlPID != $html->PID) {
					continue;
				}
			}
			
			$isAlreadyExist = false;
			for($j = 0 ; $j < count($TargetProjectSourceOutputList) ; $j++) {
				$TargetProjectSourceOutput = $TargetProjectSourceOutputList[$j];
				
				if ($html->ProjectSourceOutputPID == $TargetProjectSourceOutput->PID) {
					$isAlreadyExist = true;
					break;
				}
			}
			if (!$isAlreadyExist) {
				$projectSourceOutput = $DAProjectSourceOutput->GetProjectSourceOutput($html->ProjectSourceOutputPID, $ProjectPID);
				if ($projectSourceOutput != NULL) {
					array_push($TargetProjectSourceOutputList, $projectSourceOutput);
				} else {
					$UnassignedProjectSourceOutputPIDHT[$html->ProjectSourceOutputPID] = true;
				}
			}
		}
		$UNASSIGNED_PROJECT_SOURCE_OUTPUT = -99999;
		if (count($UnassignedProjectSourceOutputPIDHT) > 0) {
			$UnassignedProjectSourceOutput =  new ProjectSourceOutputData();
			$UnassignedProjectSourceOutput->PID = $UNASSIGNED_PROJECT_SOURCE_OUTPUT;
			array_push($TargetProjectSourceOutputList, $UnassignedProjectSourceOutput);
		}
		
		for($j = 0 ; $j < count($TargetProjectSourceOutputList); $j++) {
			$TargetProjectSourceOutput = $TargetProjectSourceOutputList[$j];
			
			if ($TargetProjectSourceOutput->PID != $UNASSIGNED_PROJECT_SOURCE_OUTPUT) {
				?>
				<h3>Project Source Output: <?php print htmlspecialchars(MakeDropboxFolderByName($project->DropboxBaseFolderName, $TargetProjectSourceOutput->SourceOutputDir)); ?></h3>
                <?php
			} else {
				?>
				<h3>Project Source Output is not assigned yet</h3>
                <?php
			}
            ?>
            
			<table class="table">
				<thead>
				<tr bgcolor="#ECECEC">
				  <th>Name</th>
				  <th>Template Name</th>
				  <?php if ($DBWritePermission) { ?>
                      <th>All Parameter Set?</th>
                      <th></th>
                      <th></th>
				  <?php } // DBWritePermission ?>
				</tr>
				</thead>
				<tbody>
			<?php
			for($i = 0 ; $i < count($htmlList); $i++) {
				$html = $htmlList[$i];
				
				// filter
				if (is_numeric($filterhtmlPID)) {
					if ($filterhtmlPID != $html->PID) {
						continue;
					}
				}
				if (
				    ($html->ProjectSourceOutputPID == $TargetProjectSourceOutput->PID)
				     || 
					($TargetProjectSourceOutput->PID == $UNASSIGNED_PROJECT_SOURCE_OUTPUT &&
					 CheckIfUnassignedProjectSourceOutputPID($html->ProjectSourceOutputPID))
				    ) {
					?>
					<tr>
					  <td><?php print htmlspecialchars($html->name); ?></td>
					  <td><?php print htmlspecialchars($html->htmlTemplatename);
					  	
						if (trim($html->htmlTemplateComment) != "") {
							print "<br>";
							print htmlspecialchars($html->htmlTemplateComment);
						}
					  ?></td>
					  <?php if ($DBWritePermission) { ?>
                          <td><?php
						  
						  $IsDuplicated = false;
                          if (CheckIfAllHTMLParameterIsSetBasedOnTemplate($ProjectPID, $html->htmlTemplatePID, $html->PID, $IsDuplicated)) {
							  ?>
							  All Paramete is set
							  <?php
						  } else {
							  if ($IsDuplicated) {
								  ?>
								  <font color="red">Warning! Parameter setting is duplicated</font>
								  <?php
							  } else {
								  ?>
								  <font color="red">Warning! Not All Parameter is set</font>
								  <?php
							  }
						  }
						  
						  ?></td>
						  <td><a href="html_parameters.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&htmlPID=<?php print urlencode($html->PID); ?>&<?php print makeRandStr(8); ?>">Parameter List</a></td>
						  <td><a href="html_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&htmlPID=<?php print urlencode($html->PID); ?>&<?php print makeRandStr(8); ?>">Edit Basic Info</a></td>
					  <?php } // DBWritePermission ?>
					</tr>
					<?php
				}
			}
			?>
        	</tbody>
		</table>
        <?php
		}
        
	} else {
		?>
    <p>none</p>
		<?php
	}
	?>
	<?php if ($DBWritePermission) { ?>
    <p align="right"><a href="html_edit.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New HTML</a></p>
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
