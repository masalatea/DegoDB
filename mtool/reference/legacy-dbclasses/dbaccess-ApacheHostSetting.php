<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ApacheHostSettingDBAccess
{
	public function __construct() {
	}
	
	public function GetApacheHostSettingList($param_ApacheHostSetting_ApacheSettingPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetApacheHostSettingList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetApacheHostSettingList ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSetting.PID, ApacheHostSetting.ApacheSettingPID, ApacheHostSetting.ApacheHostSettingTemplatePID, ApacheHostSetting.CategoryName, ApacheHostSetting.VirtualHostName, ApacheHostSetting.DocumentRootSuffix, ApacheHostSetting.Email, ApacheHostSetting.MonitorLog, ApacheHostSettingTemplate.name, Server.LocalServerName from ApacheHostSetting LEFT OUTER JOIN ApacheHostSettingTemplate ON ApacheHostSetting.ApacheHostSettingTemplatePID = ApacheHostSettingTemplate.PID join ApacheSetting LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID where ApacheHostSetting.ApacheSettingPID = '" . $mtooldb->real_escape_string($param_ApacheHostSetting_ApacheSettingPID_where) . "' and ApacheHostSetting.ApacheSettingPID = ApacheSetting.PID order by ApacheHostSetting.CategoryName,ApacheHostSetting.VirtualHostName,ApacheHostSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->ApacheSettingPID = $thisline[1];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->VirtualHostName = $thisline[4];
			$thisresult->DocumentRootSuffix = $thisline[5];
			$thisresult->Email = $thisline[6];
			$thisresult->MonitorLog = $thisline[7];
			$thisresult->ApacheHostSettingTemplatename = $thisline[8];
			$thisresult->ServerLocalServerName = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetMonitorLogList($param_ApacheHostSetting_ApacheSettingPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetMonitorLogList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetMonitorLogList ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSetting.PID, ApacheHostSetting.ApacheSettingPID, ApacheHostSetting.ApacheHostSettingTemplatePID, ApacheHostSetting.CategoryName, ApacheHostSetting.VirtualHostName, ApacheHostSetting.DocumentRootSuffix, ApacheHostSetting.Email, ApacheHostSetting.MonitorLog, ApacheHostSettingTemplate.name, Server.LocalServerName from ApacheHostSetting LEFT OUTER JOIN ApacheHostSettingTemplate ON ApacheHostSetting.ApacheHostSettingTemplatePID = ApacheHostSettingTemplate.PID join ApacheSetting LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID where ApacheHostSetting.ApacheSettingPID = '" . $mtooldb->real_escape_string($param_ApacheHostSetting_ApacheSettingPID_where) . "' and ApacheHostSetting.MonitorLog = '" . $mtooldb->real_escape_string("1") . "' and ApacheHostSetting.ApacheSettingPID = ApacheSetting.PID order by ApacheHostSetting.CategoryName,ApacheHostSetting.VirtualHostName,ApacheHostSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->ApacheSettingPID = $thisline[1];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->VirtualHostName = $thisline[4];
			$thisresult->DocumentRootSuffix = $thisline[5];
			$thisresult->Email = $thisline[6];
			$thisresult->MonitorLog = $thisline[7];
			$thisresult->ApacheHostSettingTemplatename = $thisline[8];
			$thisresult->ServerLocalServerName = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetMonitorLogByProjectUserList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetMonitorLogByProjectUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetMonitorLogByProjectUserList ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSetting.PID, ApacheHostSetting.ApacheSettingPID, ApacheHostSetting.ApacheHostSettingTemplatePID, ApacheHostSetting.CategoryName, ApacheHostSetting.VirtualHostName, ApacheHostSetting.DocumentRootSuffix, ApacheHostSetting.Email, ApacheHostSetting.MonitorLog, ApacheHostSettingTemplate.name, Server.LocalServerName from ApacheHostSetting LEFT OUTER JOIN ApacheHostSettingTemplate ON ApacheHostSetting.ApacheHostSettingTemplatePID = ApacheHostSettingTemplate.PID join ProjectUser join ProjectHostSetting join ApacheSetting LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.ProjectPID = ProjectHostSetting.ProjectPID and ProjectHostSetting.ApacheHostSettingPID = ApacheHostSetting.PID and ProjectHostSetting.ApacheSettingPID = ApacheHostSetting.ApacheSettingPID and ApacheHostSetting.MonitorLog = '" . $mtooldb->real_escape_string("1") . "' and ApacheHostSetting.ApacheSettingPID = ApacheSetting.PID order by ApacheHostSetting.CategoryName,ApacheHostSetting.VirtualHostName,ApacheHostSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->ApacheSettingPID = $thisline[1];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->VirtualHostName = $thisline[4];
			$thisresult->DocumentRootSuffix = $thisline[5];
			$thisresult->Email = $thisline[6];
			$thisresult->MonitorLog = $thisline[7];
			$thisresult->ApacheHostSettingTemplatename = $thisline[8];
			$thisresult->ServerLocalServerName = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetAllList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetAllList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetAllList ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSetting.PID, ApacheHostSetting.ApacheSettingPID, ApacheHostSetting.ApacheHostSettingTemplatePID, ApacheHostSetting.CategoryName, ApacheHostSetting.VirtualHostName, ApacheHostSetting.DocumentRootSuffix, ApacheHostSetting.Email, ApacheHostSetting.MonitorLog, ApacheHostSettingTemplate.name, Server.LocalServerName from ApacheHostSetting LEFT OUTER JOIN ApacheHostSettingTemplate ON ApacheHostSetting.ApacheHostSettingTemplatePID = ApacheHostSettingTemplate.PID join ApacheSetting LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID where ApacheHostSetting.ApacheSettingPID = ApacheSetting.PID order by ApacheHostSetting.CategoryName,ApacheHostSetting.VirtualHostName,ApacheHostSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->ApacheSettingPID = $thisline[1];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->VirtualHostName = $thisline[4];
			$thisresult->DocumentRootSuffix = $thisline[5];
			$thisresult->Email = $thisline[6];
			$thisresult->MonitorLog = $thisline[7];
			$thisresult->ApacheHostSettingTemplatename = $thisline[8];
			$thisresult->ServerLocalServerName = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetApacheHostSetting($param_ApacheHostSetting_PID_where, $param_ApacheHostSetting_ApacheSettingPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetApacheHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION GetApacheHostSetting ==
		
		$last_sql_command_for_mtooldb = "select ApacheHostSetting.PID, ApacheHostSetting.ApacheSettingPID, ApacheHostSetting.ApacheHostSettingTemplatePID, ApacheHostSetting.CategoryName, ApacheHostSetting.VirtualHostName, ApacheHostSetting.DocumentRootSuffix, ApacheHostSetting.Email, ApacheHostSetting.MonitorLog, Server.LocalServerName from ApacheHostSetting LEFT OUTER JOIN ApacheSetting ON ApacheHostSetting.ApacheSettingPID = ApacheSetting.PID LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID where ApacheHostSetting.PID = '" . $mtooldb->real_escape_string($param_ApacheHostSetting_PID_where) . "' and ApacheHostSetting.ApacheSettingPID = '" . $mtooldb->real_escape_string($param_ApacheHostSetting_ApacheSettingPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ApacheHostSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->ApacheSettingPID = $thisline[1];
			$thisresult->ApacheHostSettingTemplatePID = $thisline[2];
			$thisresult->CategoryName = $thisline[3];
			$thisresult->VirtualHostName = $thisline[4];
			$thisresult->DocumentRootSuffix = $thisline[5];
			$thisresult->Email = $thisline[6];
			$thisresult->MonitorLog = $thisline[7];
			$thisresult->ServerLocalServerName = $thisline[8];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertApacheHostSetting($ApacheHostSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertApacheHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertApacheHostSetting ==
		
		$last_sql_command_for_mtooldb = "insert into ApacheHostSetting (ApacheSettingPID, ApacheHostSettingTemplatePID, CategoryName, VirtualHostName, DocumentRootSuffix, Email, MonitorLog) values('" . $mtooldb->real_escape_string($ApacheHostSettingObj->ApacheSettingPID) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingObj->ApacheHostSettingTemplatePID) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingObj->CategoryName) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingObj->VirtualHostName) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingObj->DocumentRootSuffix) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingObj->Email) . "', '" . $mtooldb->real_escape_string($ApacheHostSettingObj->MonitorLog) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateApacheHostSetting($ApacheHostSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateApacheHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateApacheHostSetting ==
		
		$last_sql_command_for_mtooldb = "update ApacheHostSetting SET ApacheSettingPID = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->ApacheSettingPID) . "', ApacheHostSettingTemplatePID = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->ApacheHostSettingTemplatePID) . "', CategoryName = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->CategoryName) . "', VirtualHostName = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->VirtualHostName) . "', DocumentRootSuffix = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->DocumentRootSuffix) . "', Email = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->Email) . "', MonitorLog = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->MonitorLog) . "' where ApacheHostSetting.PID = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->PID) . "' and ApacheHostSetting.ApacheSettingPID = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->ApacheSettingPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteApacheHostSetting($ApacheHostSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteApacheHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteApacheHostSetting ==
		
		$last_sql_command_for_mtooldb = "delete from ApacheHostSetting where ApacheHostSetting.PID = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->PID) . "' and ApacheHostSetting.ApacheSettingPID = '" . $mtooldb->real_escape_string($ApacheHostSettingObj->ApacheSettingPID) . "'";
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