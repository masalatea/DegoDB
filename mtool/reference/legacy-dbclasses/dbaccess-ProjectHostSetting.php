<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectHostSettingDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectHostSettingByProjectList($param_ProjectHostSetting_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectHostSettingByProjectList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectHostSettingByProjectList ==
		
		$last_sql_command_for_mtooldb = "select ProjectHostSetting.ProjectPID, ProjectHostSetting.PID, ProjectHostSetting.ApacheSettingPID, ProjectHostSetting.ApacheHostSettingPID, ApacheSetting.name, ApacheHostSetting.VirtualHostName, ApacheHostSettingTemplate.name, Server.LocalServerName from ProjectHostSetting LEFT OUTER JOIN ApacheSetting ON ProjectHostSetting.ApacheSettingPID = ApacheSetting.PID LEFT OUTER JOIN ApacheHostSetting ON ProjectHostSetting.ApacheHostSettingPID = ApacheHostSetting.PID and ProjectHostSetting.ApacheSettingPID = ApacheHostSetting.ApacheSettingPID LEFT OUTER JOIN ApacheHostSettingTemplate ON ApacheHostSetting.ApacheHostSettingTemplatePID = ApacheHostSettingTemplate.PID LEFT OUTER JOIN Server ON ApacheSetting.ServerPID = Server.PID where ProjectHostSetting.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectHostSetting_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectHostSettingData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->ApacheSettingPID = $thisline[2];
			$thisresult->ApacheHostSettingPID = $thisline[3];
			$thisresult->ApacheSettingname = $thisline[4];
			$thisresult->ApacheHostSettingVirtualHostName = $thisline[5];
			$thisresult->ApacheHostSettingTemplatename = $thisline[6];
			$thisresult->ServerLocalServerName = $thisline[7];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function InsertProjectHostSetting($ProjectHostSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectHostSetting ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectHostSetting (ProjectPID, ApacheSettingPID, ApacheHostSettingPID) values('" . $mtooldb->real_escape_string($ProjectHostSettingObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($ProjectHostSettingObj->ApacheSettingPID) . "', '" . $mtooldb->real_escape_string($ProjectHostSettingObj->ApacheHostSettingPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProjectHostSetting($ProjectHostSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProjectHostSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProjectHostSetting ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectHostSetting where ProjectHostSetting.PID = '" . $mtooldb->real_escape_string($ProjectHostSettingObj->PID) . "' and ProjectHostSetting.ProjectPID = '" . $mtooldb->real_escape_string($ProjectHostSettingObj->ProjectPID) . "'";
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