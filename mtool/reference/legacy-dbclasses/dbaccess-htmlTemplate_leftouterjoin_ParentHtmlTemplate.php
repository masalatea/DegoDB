<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class htmlTemplate_leftouterjoin_ParentHtmlTemplateDBAccess
{
	public function __construct() {
	}
	
	public function GethtmlTemplateList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlTemplateList ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlTemplateList ==
		
		$last_sql_command_for_mtooldb = "select htmlTemplate.PID, htmlTemplate.TargetType, htmlTemplate.ParentHtmlTemplatePID, htmlTemplate.name, htmlTemplate.ProgramLanguage, htmlTemplate.FileName, htmlTemplate.Comment, ParentHtmlTemplate.name from htmlTemplate LEFT OUTER JOIN htmlTemplate as ParentHtmlTemplate ON htmlTemplate.ParentHtmlTemplatePID = ParentHtmlTemplate.PID order by htmlTemplate.name,htmlTemplate.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlTemplate_leftouterjoin_ParentHtmlTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->TargetType = $thisline[1];
			$thisresult->ParentHtmlTemplatePID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->ProgramLanguage = $thisline[4];
			$thisresult->FileName = $thisline[5];
			$thisresult->Comment = $thisline[6];
			$thisresult->ParentHtmlTemplatename = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GethtmlTemplateByTargetTypeList($param_htmlTemplate_TargetType_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GethtmlTemplateByTargetTypeList ==
		// == END OF EDITABLE AREA FOR FUNCTION GethtmlTemplateByTargetTypeList ==
		
		$last_sql_command_for_mtooldb = "select htmlTemplate.PID, htmlTemplate.TargetType, htmlTemplate.ParentHtmlTemplatePID, htmlTemplate.name, htmlTemplate.ProgramLanguage, htmlTemplate.FileName, htmlTemplate.Comment, ParentHtmlTemplate.name from htmlTemplate LEFT OUTER JOIN htmlTemplate as ParentHtmlTemplate ON htmlTemplate.ParentHtmlTemplatePID = ParentHtmlTemplate.PID where htmlTemplate.TargetType = '" . $mtooldb->real_escape_string($param_htmlTemplate_TargetType_where) . "' order by htmlTemplate.name,htmlTemplate.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new htmlTemplate_leftouterjoin_ParentHtmlTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->TargetType = $thisline[1];
			$thisresult->ParentHtmlTemplatePID = $thisline[2];
			$thisresult->name = $thisline[3];
			$thisresult->ProgramLanguage = $thisline[4];
			$thisresult->FileName = $thisline[5];
			$thisresult->Comment = $thisline[6];
			$thisresult->ParentHtmlTemplatename = $thisline[7];
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