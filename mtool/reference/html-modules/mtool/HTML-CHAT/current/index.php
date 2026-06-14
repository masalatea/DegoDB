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
<title><?php print getres("TITLE_TOP"); ?></title>
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

include_once("/srv/legacy/www/nodejs_lib/lib_create_whiteboard_token.php");
include_once("/srv/legacy/www/mtool_lib/lib_mtool_minutes.php");

$ProjectPID = GetParam("ProjectPID");		// Optional for top page. Mandatory for sub pages

$filterchattopicPID = trim(GetParam("filterchattopicPID"));
if (is_numeric($filterchattopicPID)) {
	?>
    <h3 align="right"><font color="#0000FF"><i>Now Filtering by specific Topic</i></font></h3>
    <?php
}

InitializeOutputShortenedStringWithExpansion();

$HeaderCaption = "Chat Topics for all Project";
if ($ProjectPID != "") {
	
	$DAProject = new ProjectDBAccess();
	$thisProjectObj = $DAProject->GetProject($ProjectPID);
	if ($thisProjectObj) {
		$HeaderCaption = "Chat Topics for Project: " . $thisProjectObj->name;
	} else {
		die("Fatal Error. Unknown Project");
	}
}
printPathOnTopForChat($HeaderCaption, "");

$DAWhiteboardRoom = new WhiteboardRoomDBAccess();
$UpdatedRoomList = $DAWhiteboardRoom->GetUpdatedRoomList(
	$matsuesoft_login_token_id
	);

$DAchattopic_and_Project = new chattopic_and_ProjectDBAccess();
$chattopicList = $DAchattopic_and_Project->GetchattopicList($matsuesoft_login_token_id);

if (count($chattopicList) > 0) {
	?>
<script>
$(function() {
	$(".openchat").click(function(){
		var url = $(this).attr("href");
		window.open(url, "_blank", "menubar=no,status=no,location=no,directories=no");
		return false;
	});
});
</script>
	<table class="table">
		<thead>
		<tr bgcolor="#ECECEC">
		  <th>Project</th>
		  <th>Topic</th>
		  <th>Attachment</th>
		  <th>Is OLD?</th>
		  <th>Member</th>
		  <th></th>
		  <th></th>
          <th></th>
          <th></th>
          <th></th>
		</tr>
        </thead>
        <tbody>
	<?php
	
	$DAchattopicAttachment = new chattopicAttachmentDBAccess();
	
	for($i = 0 ; $i < count($chattopicList); $i++) {
		$chattopic = $chattopicList[$i];
		
		if ($ProjectPID == "" || $ProjectPID == $chattopic->ProjectPID) {
			// OK
		} else {
			// Skip
			continue;
		}
		
		// Security Check
		if (!CheckIfPossibleToAccess($chattopic->ProjectPID, $matsuesoft_login_token_id, ProjectUserSerurityEnum::$CHATREAD)) {
			continue;
		}
		
		// filter
		if (is_numeric($filterchattopicPID)) {
			if ($filterchattopicPID != $chattopic->PID) {
				continue;
			}
		}
		
		$room = CreateRoomNameFromChatTopicPID($chattopic->ProjectPID, $chattopic->PID);
		
		$there_is_unread_message = false;
		if ($UpdatedRoomList) {
			for ($j = 0 ; $j < count($UpdatedRoomList) ; $j++) {
				$UpdatedRoom = $UpdatedRoomList[$j];
				
				if ($UpdatedRoom->room == $room) {
					$there_is_unread_message = true;
					break;
				}
			}
		}
		
		?>
		<tr>
		  <td><?php print htmlspecialchars($chattopic->Projectname); ?></td>
		  <td><?php print htmlspecialchars($chattopic->name); ?>
          <br>
          <a href="chat.php?ProjectPID=<?php print urlencode($chattopic->ProjectPID); ?>&chattopicPID=<?php print urlencode($chattopic->PID); ?>&<?php print makeRandStr(8); ?>">Open Chat Room</a> [<a class="openchat" href="chat.php?ProjectPID=<?php print urlencode($chattopic->ProjectPID); ?>&chattopicPID=<?php print urlencode($chattopic->PID); ?>&<?php print makeRandStr(8); ?>">Open by Popup Window</a>]
          <?php
		  if ($there_is_unread_message) {
			  ?>
              <br>
              <span class="ui-icon ui-icon-comment"></span> <font color="red">New Message Exists</font>
              <?php
		  }
		  ?>
          </td>
          <td><?php
          $chattopicAttachmentList = $DAchattopicAttachment->GetchattopicAttachmentList($chattopic->ProjectPID, $chattopic->PID);
		  for($j = 0 ; $j < count($chattopicAttachmentList); $j++) {
			  $chattopicAttachment = $chattopicAttachmentList[$j];
			  if ($j > 0) {
				  print "<br>";
			  }
			  ?>
              <a href="whiteboard_open_for_attachment.php?ProjectPID=<?php print urlencode($chattopic->ProjectPID); ?>&chattopicPID=<?php print urlencode($chattopic->PID); ?>&chattopicAttachmentPID=<?php print urlencode($chattopicAttachment->PID); ?>&<?php print makeRandStr(8); ?>"><?php print htmlspecialchars($chattopicAttachment->name); ?> (Open Whiteboard)</a> [<a href="chattopic_attachment_edit.php?ProjectPID=<?php print urlencode($chattopic->ProjectPID); ?>&chattopicPID=<?php print urlencode($chattopic->PID); ?>&chattopicAttachmentPID=<?php print urlencode($chattopicAttachment->PID); ?>&<?php print makeRandStr(8); ?>">Edit Basic Info</a>]
              <?php
		  }
		  ?></td>
		  <td><?php
		  if ($chattopic->IsOld == "1") {
			  print "Old";
		  }
		  ?></td>
		  <td><?php
		  
			$DAWhiteboardRoomMember = new WhiteboardRoomMemberDBAccess();
			$WhiteboardRoomMemberList = $DAWhiteboardRoomMember->GetWhiteboardRoomMemberList($room);
			if ($WhiteboardRoomMemberList) {
				?>
                <table class="table">
                    <thead>
                    </thead>
                    <tbody>
						<?php
                        for($j = 0 ; $j < count($WhiteboardRoomMemberList) ; $j++) {
                            $WhiteboardRoomMember = $WhiteboardRoomMemberList[$j];
							
							$inactive = ($WhiteboardRoomMember->IsActive == 0);
                            ?>
                            <tr>
                              <td><?php if ($inactive) { print "<s>"; } print htmlspecialchars($WhiteboardRoomMember->username); if ($inactive) { print "</s>"; } ?></td>
                              <td><?php if ($inactive) { print "<s>"; } print "ID: " . htmlspecialchars($WhiteboardRoomMember->userid); if ($inactive) { print "</s>"; } ?></td>
                              <td><?php if($inactive) {
							  	  print "Inactive";
							  }?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <?php
			}
		  
		  ?></td>
          <td><a href="chattopic_attachment_edit.php?ProjectPID=<?php print urlencode($chattopic->ProjectPID); ?>&chattopicPID=<?php print urlencode($chattopic->PID); ?>&<?php print makeRandStr(8); ?>">Add Attachment</a></td>
		  <td><a href="chattopic_edit.php?ProjectPID=<?php print urlencode($chattopic->ProjectPID); ?>&chattopicPID=<?php print urlencode($chattopic->PID); ?>&<?php print makeRandStr(8); ?>">Edit Topic Info</a></td>
          <td><?php PrintAddMinutesLinkForChat($chattopic->ProjectPID, $chattopic->PID); ?></td>
          <td><?php PrintSearchMinutesLinkForChat($chattopic->ProjectPID, $chattopic->PID); ?></td>
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
<p align="right"><a href="chattopic_add.php?ProjectPID=<?php print urlencode($ProjectPID); ?>&<?php print makeRandStr(8); ?>">Add New Topic</a></p>

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
