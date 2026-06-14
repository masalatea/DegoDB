<?php
$ORIGINAL_FILE = __FILE__;
$HTML_TEMPLATE_FILE = "HTMLTemplate_default.php";
include_once("/srv/legacy/www/mtool_lib/template_include.php");

// Start Template Content: MTOOL_VARIABLES
$MTOOL_NO_GOOGLE_ANALYTICS = true;
$MTOOL_JQUERY_PERIODICAL_UPDATER = true;
$MTOOL_CHECK_WHITEBOARD_UPDATE = true;
$MTOOL_BODY_FLUID_STYLE = true;
$MTOOL_NEED_LOGIN_ONLY_BY_ADMINISTRATOR = true;
// End Template Content

// Start Template Content: MTOOL_HTTP_HEADER
// End Template Content
?>

// Start Template Content: HTML_HEAD
<title><?php print getres("TITLE_PAGE_SECURITY"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$DAProjectSecurityForEachPage = new ProjectSecurityForEachPageDBAccess();
$ProjectSecurityForEachPageList = $DAProjectSecurityForEachPage->GetProjectSecurityForEachPageList();

$SecurityTypeSelectionList = GetAllSecurityTypeListOfProjectUser();

if (count($ProjectSecurityForEachPageList) > 0) {
	?>
	<table class="table">
		<thead>
		<tr bgcolor="#ECECEC">
		  <th rowspan="3">Server Name</th>
		  <th rowspan="3">Script Name</th>
		  <th colspan="14">Security Type</th>
		  <th rowspan="3"></th>
		</tr>
		<tr bgcolor="#ECECEC">
		<?php
		for ($i = 0 ; $i < count($SecurityTypeSelectionList); $i++) {
			$SecurityTypeSelection = $SecurityTypeSelectionList[$i];
			
			$colspantag = "";
			if (($i + 1 < count($SecurityTypeSelectionList)) && 
				 $SecurityTypeSelectionList[$i] == $SecurityTypeSelectionList[$i + 1])
			{
				$colspantag = ' colspan="2"';
				$i++;		// 先に進める
			}
			?>
			  <th<?php print $colspantag; ?>><?php print GetCategoryOfProjectUserSerurityCaption($SecurityTypeSelection); ?> </th>
			<?php
		}
		?>
		</tr>
		<tr bgcolor="#ECECEC">
		<?php
		for ($i = 0 ; $i < count($SecurityTypeSelectionList); $i++) {
			$SecurityTypeSelection = $SecurityTypeSelectionList[$i];
			?>
			  <th><?php print GetActionTypeOfProjectUserSerurityCaption($SecurityTypeSelection); ?> </th>
			<?php
		}
		?>
		</tr>
	  </thead>
		<tbody>
	<?php

	$DAProjectSecurityForEachPageDetails = new ProjectSecurityForEachPageDetailsDBAccess();
	
	for($i = 0 ; $i < count($ProjectSecurityForEachPageList); $i++) {
		$ProjectSecurityForEachPage = $ProjectSecurityForEachPageList[$i];
		?>
		<tr>
		  <td><?php print htmlspecialchars($ProjectSecurityForEachPage->SERVER_NAME); ?></td>
		  <td><?php print htmlspecialchars($ProjectSecurityForEachPage->SCRIPT_NAME); ?></td>
		  <?php
			$ProjectSecurityForEachPageDetailList = $DAProjectSecurityForEachPageDetails->GetProjectSecurityForEachPageDetailsList($ProjectSecurityForEachPage->SERVER_NAME, $ProjectSecurityForEachPage->SCRIPT_NAME);
			
			for ($j = 0 ; $j < count($SecurityTypeSelectionList); $j++) {
				$SecurityTypeSelection = $SecurityTypeSelectionList[$j];
				
				$IsExistInSetting = false;
				if ($ProjectSecurityForEachPageDetailList) {
					for($k = 0 ; $k < count($ProjectSecurityForEachPageDetailList) ; $k++) {
						$ProjectSecurityForEachPageDetail = $ProjectSecurityForEachPageDetailList[$k];
						
						if ($SecurityTypeSelection == $ProjectSecurityForEachPageDetail->SecurityType) {
							$IsExistInSetting = true;
							break;
						}
					}
				}
				$thisSecurityTypeSelectionCaption = "";
				$bgcolor = "";
				if ($IsExistInSetting) {
					$thisSecurityTypeSelectionCaption = "Yes";
					$bgcolor = ' bgcolor="#FFCC66"';
				} else {
					$thisSecurityTypeSelectionCaption = "No";
				}
				?>
				  <td<?php print $bgcolor; ?>><?php print $thisSecurityTypeSelectionCaption; ?></td>
				<?php
			}
			
		  ?>
		  <td><a href="page_security_edit.php?EachPagePID=<?php print urlencode($ProjectSecurityForEachPage->PID); ?>&SERVER_NAME=<?php print urlencode($ProjectSecurityForEachPage->SERVER_NAME); ?>&SCRIPT_NAME=<?php print urlencode($ProjectSecurityForEachPage->SCRIPT_NAME); ?>&<?php print makeRandStr(8); ?>">Edit Detail Security</a></td>
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

<br>
<br>
<br>
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
