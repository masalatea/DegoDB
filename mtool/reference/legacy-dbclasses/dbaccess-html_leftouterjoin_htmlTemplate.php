<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class html_leftouterjoin_htmlTemplateDBAccess
{
	public function __construct() {
	}
	
	public function GethtmlList($param_html_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlList ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlList ==
		
		$last_sql_command_for_mtooldb = "select html.ProjectPID, html.PID, html.name, html.ProjectSourceOutputPID, html.htmlTemplatePID, htmlTemplate.name, htmlTemplate.ProgramLanguage, htmlTemplate.FileName, htmlTemplate.Comment from html LEFT OUTER JOIN htmlTemplate ON html.htmlTemplatePID = htmlTemplate.PID where html.ProjectPID = '" . $mtooldb->real_escape_string($param_html_ProjectPID_where) . "' order by html.ProjectSourceOutputPID,html.name,html.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new html_leftouterjoin_htmlTemplateData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			$thisresult->ProjectSourceOutputPID = $thisline[3];
			$thisresult->htmlTemplatePID = $thisline[4];
			$thisresult->htmlTemplatename = $thisline[5];
			$thisresult->htmlTemplateProgramLanguage = $thisline[6];
			$thisresult->htmlTemplateFileName = $thisline[7];
			$thisresult->htmlTemplateComment = $thisline[8];
			array_push($result, $thisresult);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>