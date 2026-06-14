<?php

date_default_timezone_set('Asia/Tokyo');

// print $token . "<br>\n";
// print $room . "<br>\n";
// print $userid . "<br>\n";
// print $username . "<br>\n";

if ( !CheckIfRoomNameIsValid($room) ||
     !preg_match("/^\w+$/", $token) ||
     !CheckIfValidUserIDFormat($userid)
    ) {
	print "Something wrong with room or user.";
	exit;
}

?>
<script>

(function($) {
  'use strict';
	
	window.onload = function()
	{
		addsystemmessage("Initializing(初期化中です)...");
		addsystemmessage("Connecting to Server. Waiting for a response. (サーバの応答を待っています. 応答があるまでお待ち下さい)");
		
		clearTimeout(reload_timer_id);
		FRBUF_Initialize();
		
		initialize_colorpicker();
		initialize_penpicker();
		initialize_modebutton();
		initialize_paint(GetRandomString(32));
		initialize_chat('<?php print $username; ?>');
		initialize_drawtext();
		initialize_redraw_timer();

		initialize_socket(
			"<?php print $token; ?>",
			"<?php print $room; ?>",
			"<?php print $userid; ?>"
		);
		
		<?php
		if ($student_token_for_teacher != "") {
		?>
		set_style_display("showstudenturlbuttonid", "inline");
		set_style_display("showbookdialogbuttonid", "inline");
		initialize_student_url_dialog("<?php print "http://whiteboard.dokotera.com/open_room.php?token=" . $student_token_for_teacher; ?>");
		initialize_bookpage_loader("<?php print $token; ?>");
		<?php
		} else {
		?>
		set_style_display("showstudenturlbuttonid", "inline");
		initialize_student_url_dialog("<?php print "http://whiteboard.dokotera.com/open_room.php?token=" . $token; ?>");
		<?php
		}
		?>
	};
})(jQuery);

reload_timer_id = setTimeout("reload_page()", 30000);
function reload_timer_id() {
	location.reload();
}
</script>

<div id="menucontainerid" onSelectStart = "return false;" style = "-moz-user-select: none; -khtml-user-select: none; user-select: none; z-index: 0;">
</div>
<div id="messageareaid">
    <div id="internalmessageareaid" class="scrollbar_area">
    </div>
</div>
<div id="messageinputareaid" class="mainfooter" style="vertical-align:bottom; overflow:hidden">
    <div style="float:left; overflow:hidden" id="messagelabelarea">Message:</div>
    <div style="float:left; overflow:hidden" id="usercommentidarea">
        <input name="comment" id="usercommentid" type="text" value="" size="80" />
    </div>
    <div style="float:left; overflow:hidden" id="usermultilinecommentidarea">
        <textarea name="comment" cols="80" rows="3" id="usermultilinecommentid" style="display:none"></textarea>
    </div>
    <div style="float:left; overflow:hidden"><span id="singletomultiiconid" class="ui-icon ui-icon-arrowthick-1-n"></span></div>
    <div style="float:left; overflow:hidden">
        <input type="button" id="usercommentbuttonid" value="Send Comment" />
    </div>
</div>

<style>


</style>
<script type="text/javascript">
$(function(){
});
</script>
<div id="messageeditconfirmdialogid" style="display:none">
  <div id="messageeditconfirmdialogmessageid" style="margin-top:10px; margin-left:15px"> </div>
</div>


