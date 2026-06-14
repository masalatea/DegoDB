<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ApacheHostSettingTemplateDBAccess
{
	public function __construct() {
	}
	
	public function GetApacheHostSettingTemplateList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetApacheHostSettingTemplateList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetApacheHostSettingTemplateList ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSettingTemplate.PID, ApacheHostSettingTemplate.name, ApacheHostSettingTemplate.FilenameFormat, ApacheHostSettingTemplate.Template, ApacheHostSettingTemplate.AccessLogFilenameFormat, ApacheHostSettingTemplate.ErrorLogFilenameFormat from ApacheHostSettingTemplate order by ApacheHostSettingTemplate.name,ApacheHostSettingTemplate.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->FilenameFormat = $thisline[2];
			$thisresult->Template = $thisline[3];
			$thisresult->AccessLogFilenameFormat = $thisline[4];
			$thisresult->ErrorLogFilenameFormat = $thisline[5];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetApacheHostSettingTemplate($param_ApacheHostSettingTemplate_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetApacheHostSettingTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION GetApacheHostSettingTemplate ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSettingTemplate.PID, ApacheHostSettingTemplate.name, ApacheHostSettingTemplate.FilenameFormat, ApacheHostSettingTemplate.Template, ApacheHostSettingTemplate.AccessLogFilenameFormat, ApacheHostSettingTemplate.ErrorLogFilenameFormat from ApacheHostSettingTemplate where ApacheHostSettingTemplate.PID = '" . $mtooldb->real_escape_string($param_ApacheHostSettingTemplate_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingTemplateData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->FilenameFormat = $thisline[2];
			$thisresult->Template = $thisline[3];
			$thisresult->AccessLogFilenameFormat = $thisline[4];
			$thisresult->ErrorLogFilenameFormat = $thisline[5];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertApacheHostSettingTemplate($ApacheHostSettingTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertApacheHostSettingTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertApacheHostSettingTemplate ==
		
		$last_sql_command_for_mtooldb = "insert into ApacheHostSettingTemplate (name, FilenameFormat, Template, AccessLogFilenameFormat, ErrorLogFilenameFormat) values('" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->name) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->FilenameFormat) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->Template) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->AccessLogFilenameFormat) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->ErrorLogFilenameFormat) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateApacheHostSettingTemplate($ApacheHostSettingTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateApacheHostSettingTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateApacheHostSettingTemplate ==
		
		$last_sql_command_for_mtooldb = "update ApacheHostSettingTemplate SET name = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->name) . "', FilenameFormat = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->FilenameFormat) . "', Template = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->Template) . "', AccessLogFilenameFormat = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->AccessLogFilenameFormat) . "', ErrorLogFilenameFormat = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->ErrorLogFilenameFormat) . "' where ApacheHostSettingTemplate.PID = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteApacheHostSettingTemplate($ApacheHostSettingTemplateObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteApacheHostSettingTemplate ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteApacheHostSettingTemplate ==
		
		$last_sql_command_for_mtooldb = "delete from ApacheHostSettingTemplate where ApacheHostSettingTemplate.PID = '" . $mtooldb->real_escape_string($ApacheHostSettingTemplateObj->PID) . "'";
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