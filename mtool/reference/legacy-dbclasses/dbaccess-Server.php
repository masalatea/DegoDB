<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ServerDBAccess
{
	public function __construct() {
	}
	
	public function GetServerList($param_Server_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetServerList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetServerList ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where Server.SettingGroupPID = '" . $mtooldb->real_escape_string($param_Server_SettingGroupPID_where) . "' order by Server.LocalServerName,Server.IP,Server.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecificOrAnyCommonServerList($param_Server_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecificOrAnyCommonServerList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecificOrAnyCommonServerList ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where (Server.SettingGroupPID = '" . $mtooldb->real_escape_string($param_Server_SettingGroupPID_where) . "' or Server.IsCommonForAllSettingGroupForUpload = '" . $mtooldb->real_escape_string("1") . "' or Server.IsCommonForAllSettingGroupForDBServer = '" . $mtooldb->real_escape_string("1") . "' or Server.IsCommonForAllSettingGroupForDBClient = '" . $mtooldb->real_escape_string("1") . "') order by Server.LocalServerName,Server.IP,Server.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecificOrCommonUploadServerList($param_Server_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecificOrCommonUploadServerList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecificOrCommonUploadServerList ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where (Server.SettingGroupPID = '" . $mtooldb->real_escape_string($param_Server_SettingGroupPID_where) . "' or Server.IsCommonForAllSettingGroupForUpload = '" . $mtooldb->real_escape_string("1") . "') order by Server.IsCommonForAllSettingGroupForUpload desc,Server.LocalServerName,Server.IP,Server.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecificOrCommonDBServerList($param_Server_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecificOrCommonDBServerList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecificOrCommonDBServerList ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where (Server.SettingGroupPID = '" . $mtooldb->real_escape_string($param_Server_SettingGroupPID_where) . "' or Server.IsCommonForAllSettingGroupForDBServer = '" . $mtooldb->real_escape_string("1") . "') order by Server.IsCommonForAllSettingGroupForDBServer desc,Server.LocalServerName,Server.IP,Server.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecificOrCommonDBClientList($param_Server_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecificOrCommonDBClientList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecificOrCommonDBClientList ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where (Server.SettingGroupPID = '" . $mtooldb->real_escape_string($param_Server_SettingGroupPID_where) . "' or Server.IsCommonForAllSettingGroupForDBClient = '" . $mtooldb->real_escape_string("1") . "') order by Server.IsCommonForAllSettingGroupForDBClient desc,Server.LocalServerName,Server.IP,Server.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetServer($param_Server_PID_where, $param_Server_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetServer ==
		// == END OF EDITABLE AREA FOR FUNCTION GetServer ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where Server.PID = '" . $mtooldb->real_escape_string($param_Server_PID_where) . "' and Server.SettingGroupPID = '" . $mtooldb->real_escape_string($param_Server_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function GetServerOfAnySettingGroup($param_Server_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetServerOfAnySettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetServerOfAnySettingGroup ==
		
		$last_sql_command_for_mtooldb = "select Server.PID, Server.SettingGroupPID, Server.LocalServerName, Server.IP, Server.IsCommonForAllSettingGroupForUpload, Server.IsCommonForAllSettingGroupForDBServer, Server.IsCommonForAllSettingGroupForDBClient from Server where Server.PID = '" . $mtooldb->real_escape_string($param_Server_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ServerData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->LocalServerName = $thisline[2];
			$thisresult->IP = $thisline[3];
			$thisresult->IsCommonForAllSettingGroupForUpload = $thisline[4];
			$thisresult->IsCommonForAllSettingGroupForDBServer = $thisline[5];
			$thisresult->IsCommonForAllSettingGroupForDBClient = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertServer($ServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertServer ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertServer ==
		
		$last_sql_command_for_mtooldb = "insert into Server (SettingGroupPID, LocalServerName, IP, IsCommonForAllSettingGroupForUpload, IsCommonForAllSettingGroupForDBServer, IsCommonForAllSettingGroupForDBClient) values('" . $mtooldb->real_escape_string($ServerObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($ServerObj->LocalServerName) . "', '" . $mtooldb->real_escape_string($ServerObj->IP) . "', '" . $mtooldb->real_escape_string($ServerObj->IsCommonForAllSettingGroupForUpload) . "', '" . $mtooldb->real_escape_string($ServerObj->IsCommonForAllSettingGroupForDBServer) . "', '" . $mtooldb->real_escape_string($ServerObj->IsCommonForAllSettingGroupForDBClient) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateServer($ServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateServer ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateServer ==
		
		$last_sql_command_for_mtooldb = "update Server SET LocalServerName = '" . $mtooldb->real_escape_string($ServerObj->LocalServerName) . "', IP = '" . $mtooldb->real_escape_string($ServerObj->IP) . "', IsCommonForAllSettingGroupForUpload = '" . $mtooldb->real_escape_string($ServerObj->IsCommonForAllSettingGroupForUpload) . "', IsCommonForAllSettingGroupForDBServer = '" . $mtooldb->real_escape_string($ServerObj->IsCommonForAllSettingGroupForDBServer) . "', IsCommonForAllSettingGroupForDBClient = '" . $mtooldb->real_escape_string($ServerObj->IsCommonForAllSettingGroupForDBClient) . "' where Server.PID = '" . $mtooldb->real_escape_string($ServerObj->PID) . "' and Server.SettingGroupPID = '" . $mtooldb->real_escape_string($ServerObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateServerExceptCommonFlag($ServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateServerExceptCommonFlag ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateServerExceptCommonFlag ==
		
		$last_sql_command_for_mtooldb = "update Server SET LocalServerName = '" . $mtooldb->real_escape_string($ServerObj->LocalServerName) . "', IP = '" . $mtooldb->real_escape_string($ServerObj->IP) . "' where Server.PID = '" . $mtooldb->real_escape_string($ServerObj->PID) . "' and Server.SettingGroupPID = '" . $mtooldb->real_escape_string($ServerObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteServer($ServerObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteServer ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteServer ==
		
		$last_sql_command_for_mtooldb = "delete from Server where Server.PID = '" . $mtooldb->real_escape_string($ServerObj->PID) . "' and Server.SettingGroupPID = '" . $mtooldb->real_escape_string($ServerObj->SettingGroupPID) . "'";
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