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
<title><?php print getres("TITLE_CHAT_TOPIC_ATTACHMENT_EDIT"); ?> - <?php print getres("TITLE_TOP"); ?></title>
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

$UPDATE = trim(GetParam("UPDATE"));
$DELETE = trim(GetParam("DELETE"));

$chattopicAttachment = new chattopicAttachmentData();
$chattopicAttachment->ProjectPID = trim(GetParam("ProjectPID"));
$chattopicAttachment->chattopicPID = trim(GetParam("chattopicPID"));
$chattopicAttachment->PID = trim(GetParam("chattopicAttachmentPID"));
$chattopicAttachment->name = trim(GetParam("name"));

$NoError = true;
if (!is_numeric($chattopicAttachment->ProjectPID)) {
	?>
    <H3><font color="red">Project is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}
if (!is_numeric($chattopicAttachment->chattopicPID)) {
	?>
    <H3><font color="red">Topic is not specified. Something Strange. Please start from top page or ask administrator if this continues.</font></H3>
    <?php
	$NoError = false;
}

$showForm = true;

if ($NoError) {
	$buttonCaption = "";
	$HeaderCaption = "";
	
	if ($chattopicAttachment->PID == "") {
		// Add
		if ($UPDATE != "") {
			
			// print_r($chattopicAttachment);
			
			$DAchattopicAttachment = new chattopicAttachmentDBAccess();
			if($DAchattopicAttachment->InsertchattopicAttachment($chattopicAttachment) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to insert</font></h3>
                <?php
			} else {
				// Success
				$chattopicAttachment->PID = $mtooldb->insert_id;
				?>
                <h3><font color="red"><?php print getres("ACTION_ADDED_CHAT_TOPIC_ATTACHMENT"); ?></font></h3>
                <?php
				
				// Add Into Notification Queue
				$room = CreateRoomNameFromChatTopicPID($chattopicAttachment->ProjectPID, $chattopicAttachment->chattopicPID);
				if ($room != "") {
					$token = create_whiteboard_token_for_chat($room, $matsuesoft_login_token_id);
					if ($token != ""){ 
						if (AddWhiteboardNotification($room, "AddedAttachment", json_encode(array(
								'token' => $token,
								'ProjectPID'  => $chattopicAttachment->ProjectPID,
								'chattopicPID' => $chattopicAttachment->chattopicPID,
								'chattopicAttachmentPID' => $chattopicAttachment->PID,
								'name' => $chattopicAttachment->name
						)))) {
							// Success
							NotifyToWhiteboardServerForNewNotification();
						} else {
							?>
							<h3><font color="red">Error! Failed to Notify to Whiteboard</font></h3>
							<?php
						}
					}
				}
			}
		}
		
	} else if (is_numeric($chattopicAttachment->PID)) {
		// Select/Update
		$needToLoad = true;
		$DAchattopicAttachment = new chattopicAttachmentDBAccess();
		
		$chattopicAttachmentOld = $DAchattopicAttachment->GetchattopicAttachment($chattopicAttachment->PID, $chattopicAttachment->ProjectPID);
		$originalName = "";
		if ($chattopicAttachmentOld != "") {
			$originalName = $chattopicAttachmentOld->name;
		}
		
		if ($UPDATE != "") {
			
			if($DAchattopicAttachment->UpdatechattopicAttachment($chattopicAttachment) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to update</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red"><?php print getres("ACTION_UPDATED_CHAT_TOPIC_ATTACHMENT"); ?></font></h3>
                <?php
				
				// No Notification for Update because it is confusing with "name change" and "drawing something on whiteboard".
				// This notification is only for name change but user misunderstand whiteboard has change
				// Actually there is no notification when whiteboard change. So, it is better not to nofify in this case.
			}
		}
		if ($DELETE != "") {
			
			if($DAchattopicAttachment->DeletechattopicAttachment($chattopicAttachment) === FALSE) {
				// Failed
				?>
                <h3><font color="red">Error! Failed to delete</font></h3>
                <?php
				$needToLoad = false;
				
			} else {
				// Success
				?>
                <h3><font color="red"><?php print getres("ACTION_DELETED_CHAT_TOPIC_ATTACHMENT"); ?></font></h3>
                <?php
				$needToLoad = false;
				$showForm = false;
				
				// No Notification for Delete because it is not so important. (it may be added later)
			}
		}

		if ($needToLoad) {
			$chattopicAttachment = $DAchattopicAttachment->GetchattopicAttachment($chattopicAttachment->PID, $chattopicAttachment->ProjectPID);
		}
		
	} else {
		?>
		<h4>FATAL ERROR! Topic PID is something strange.</h4>
		<?php
		die();
	}
	if ($chattopicAttachment->PID == "") {
		// Add
		$buttonCaption = getres("ACTION_ADD");
		$HeaderCaption = getres("ACTION_ADD_CHAT_TOPIC_ATTACHMENT");
		
	} else {
		// Select/Update
		$buttonCaption = getres("ACTION_UPDATE");
		$HeaderCaption = getres("ACTION_UPDATE_CHAT_TOPIC_ATTACHMENT");
	}
	
	if ($showForm && $chattopicAttachment != NULL) {
		
		// printPathOnTopForDataClasses($HeaderCaption, $chattopicAttachment->ProjectPID, $chattopicAttachment->PID, "");
		
		?>
		
		<form action="chattopic_attachment_edit.php" method="post">
		
        <?php
		mtoolCommonFormInput("name", $chattopicAttachment->name,
			array($LANG_ENGLISH=>"Attachment Name", $LANG_JAPANESE=>"添付名"),
			array($LANG_ENGLISH=>"Please input Attachment Name", $LANG_JAPANESE=>"添付名を入力して下さい"),
			"text", "");
		?>
		
		<div class="row">
		  <label class="col-md-3 control-label" for="inputtext"></label>
		  <div class="col-md-9"><input name="UPDATE" type="submit" value="<?php print $buttonCaption; ?>">
          
			<?php
            if ($chattopicAttachment->PID != "") {
				?>
				<p align="right">
				<input name="DELETE" type="submit" value="<?php print htmlspecialchars(getres("ACTION_DELETE")); ?>" onClick="return confirm('<?php print htmlspecialchars(getres("ACTION_DELETE_CONFIRM")); ?>');">
				</p>
				<?php
            }
            ?>
          </div>
		</div>
		<input name="ProjectPID" type="hidden" value="<?php print htmlspecialchars($chattopicAttachment->ProjectPID); ?>">
		<input name="chattopicPID" type="hidden" value="<?php print htmlspecialchars($chattopicAttachment->chattopicPID); ?>">
		<input name="chattopicAttachmentPID" type="hidden" value="<?php print htmlspecialchars($chattopicAttachment->PID); ?>">
		</form>
        
		<?php
	}
	?>
	<br>
	<br>
	<br>
    <p><a href="/?<?php print makeRandStr(8); ?>">Back to Topic List</a></p>
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
