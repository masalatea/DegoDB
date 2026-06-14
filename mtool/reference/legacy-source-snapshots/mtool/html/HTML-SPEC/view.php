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
<title><?php print getres("TITLE_VIEW_SPEC"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

InitializeOutputShortenedStringWithExpansion();

$ProjectPID = trim(GetParam("ProjectPID"));
$SpecPID = trim(GetParam("SpecPID"));

$NoError = true;
if (!is_numeric($ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

if ($NoError) {
	
	printPathOnTopForSpec("View Spec", $ProjectPID, $SpecPID, "");
	
	$DASpecContent = new SpecContentDBAccess();
	$SpecContentList = $DASpecContent->GetSpecContentList($ProjectPID, $SpecPID);
	
	if (count($SpecContentList) > 0) {
		?>
		<table class="table">
            <tbody>
		<?php
		
		$SectionNumberList = array();
		for($num = $OUTPUT_SECTION_NUMBER_START ; $num <= $CONTENT_DEPTH_MAX; $num++) {
			$SectionNumberList[$num] = 1;
		}
		
		for($i = 0 ; $i < count($SpecContentList); $i++) {
			$SpecContent = $SpecContentList[$i];
			
			$thisAlign = "left";
			$thisSectionNumber = "";
			if ($SpecContent->Depth <= 1) {
				// If 0: Undefined
				// If 1: Part number is not output.
				$thisAlign = "center";
			} else {
				for($num = $OUTPUT_SECTION_NUMBER_START ; $num <= $CONTENT_DEPTH_MAX; $num++) {
					if ($num <= $SpecContent->Depth) {
						if ($thisSectionNumber != "") {
							$thisSectionNumber .= ".";
						}
						$thisSectionNumber .= $SectionNumberList[$num];
						$SectionNumberList[$num]++;
						
						for($initnum = $num + 1 ; $initnum <= $CONTENT_DEPTH_MAX; $initnum++) {
							$SectionNumberList[$initnum] = 1;
						}
					}
				}
			}
			
			$thisFontSize = "";
			switch($SpecContent->Depth)
			{
				case "0":
					$thisFontSize = "32px";
					break;
				case "1":
					$thisFontSize = "32px";
					break;
				case "2":
					$thisFontSize = "28px";
					break;
				case "3":
					$thisFontSize = "24px";
					break;
				case "4":
					$thisFontSize = "18px";
					break;
				case "5":
					$thisFontSize = "16px";
					break;
				case "6":
					$thisFontSize = "12px";
					break;
				case "7":
					$thisFontSize = "11px";
					break;
			}
			
			?>
			<tr bgcolor="#ECECEC">
			  <td align="<?php print $thisAlign; ?>" style="font-size:<?php print $thisFontSize; ?>"><?php print $thisSectionNumber . " " . htmlspecialchars($SpecContent->Title); ?></td>
			</tr>
            <tr>
              <td><?php
              print nl2br(htmlspecialchars($SpecContent->Description));
			  // タグが使えると凝ってしまって時間を無駄にしてしまうこともあると思われるためタグは許可しない。
			  // タグを使いたいのは強調文字の場合がほとんどと思われるが、
			  // トピックを強調したいのであればサブのセクションを作ってもらえば良い、というスタンス
			  ?>
              </td>
            </tr>
            <tr>
              <td colspan="<?php print ($indent + $restCount); ?>"></td>
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
    <p><a href="contents.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&SpecPID=<?php print urlencode($SpecPID); ?>&<?php print makeRandStr(8); ?>">Back to Content List</a></p>
    <?php
	include_once("/srv/legacy/www/$WWWDOMAINNAME/spec/footer_back_link_include.php");
	print_footer_back_link($ProjectPID);
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

