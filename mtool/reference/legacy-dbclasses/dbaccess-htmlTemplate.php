<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlTemplateDBAccess
{
	public function __construct() {
	}
	
	public function GethtmlTemplate($param_htmlTemplate_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlTemplate ==
		
		$last_sql_command_for_mtooldb = "select htmlTemplate.PID, htmlTemplate.TargetType, htmlTemplate.ParentHtmlTemplatePID, htmlTemplate.name, htmlTemplate.ProgramLanguage, htmlTemplate.FileName, htmlTemplate.Comment from htmlTemplate where htmlTemplate.PID = '" . $mtooldb->real_escape_string($param_htmlTemplate_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->TargetType = $thisline[1];
			$thisresult->ParentHtmlTemplatePID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->ProgramLanguage = $thisline[4];
			$thisresult->FileName = $thisline[5];
			$thisresult->Comment = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function GethtmlTemplateByName($param_htmlTemplate_TargetType_where, $param_htmlTemplate_name_where, $param_htmlTemplate_ProgramLanguage_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlTemplateByName ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlTemplateByName ==
		
		$last_sql_command_for_mtooldb = "select htmlTemplate.PID, htmlTemplate.TargetType, htmlTemplate.ParentHtmlTemplatePID, htmlTemplate.name, htmlTemplate.ProgramLanguage, htmlTemplate.FileName, htmlTemplate.Comment from htmlTemplate where htmlTemplate.TargetType = '" . $mtooldb->real_escape_string($param_htmlTemplate_TargetType_where) . "' and htmlTemplate.name = '" . $mtooldb->real_escape_string($param_htmlTemplate_name_where) . "' and htmlTemplate.ProgramLanguage = '" . $mtooldb->real_escape_string($param_htmlTemplate_ProgramLanguage_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->TargetType = $thisline[1];
			$thisresult->ParentHtmlTemplatePID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->ProgramLanguage = $thisline[4];
			$thisresult->FileName = $thisline[5];
			$thisresult->Comment = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InserthtmlTemplate($htmlTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InserthtmlTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION InserthtmlTemplate ==
		
		$last_sql_command_for_mtooldb = "insert into htmlTemplate (TargetType, ParentHtmlTemplatePID, name, ProgramLanguage, FileName, Comment) values('" . $mtooldb->real_escape_string($htmlTemplateObj->TargetType) . "', '" . $mtooldb->real_escape_string($htmlTemplateObj->ParentHtmlTemplatePID) . "', '" . $mtooldb->real_escape_string($htmlTemplateObj->name) . "', '" . $mtooldb->real_escape_string($htmlTemplateObj->ProgramLanguage) . "', '" . $mtooldb->real_escape_string($htmlTemplateObj->FileName) . "', '" . $mtooldb->real_escape_string($htmlTemplateObj->Comment) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdatehtmlTemplate($htmlTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdatehtmlTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdatehtmlTemplate ==
		
		$last_sql_command_for_mtooldb = "update htmlTemplate SET TargetType = '" . $mtooldb->real_escape_string($htmlTemplateObj->TargetType) . "', ParentHtmlTemplatePID = '" . $mtooldb->real_escape_string($htmlTemplateObj->ParentHtmlTemplatePID) . "', name = '" . $mtooldb->real_escape_string($htmlTemplateObj->name) . "', ProgramLanguage = '" . $mtooldb->real_escape_string($htmlTemplateObj->ProgramLanguage) . "', FileName = '" . $mtooldb->real_escape_string($htmlTemplateObj->FileName) . "', Comment = '" . $mtooldb->real_escape_string($htmlTemplateObj->Comment) . "' where htmlTemplate.PID = '" . $mtooldb->real_escape_string($htmlTemplateObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeletehtmlTemplate($htmlTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeletehtmlTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION DeletehtmlTemplate ==
		
		$last_sql_command_for_mtooldb = "delete from htmlTemplate where htmlTemplate.PID = '" . $mtooldb->real_escape_string($htmlTemplateObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	
	// == START OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
	// == END OF EDITABLE AREA FOR ADDITIONAL CLASS DEFINITION ==
}

// == START OF EDITABLE AREA FOR BOTTOM ==
// == END OF EDITABLE AREA FOR BOTTOM ==

?>