<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DropboxBaseFolderDBAccess
{
	public function __construct() {
	}
	
	public function GetAllDropboxBaseFolderList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetAllDropboxBaseFolderList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetAllDropboxBaseFolderList ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDropboxBaseFolderBySettingGroupList($param_DropboxBaseFolder_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderBySettingGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderBySettingGroupList ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDropboxBaseFolderByUserList($param_SettingGroupUser_username_where, $param_DropboxBaseFolder_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderByUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderByUserList ==
		
		$last_sql_command_for_mtooldb = "select distinct DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID join SettingGroupUser join SettingGroup where SettingGroupUser.username = '" . $mtooldb->real_escape_string($param_SettingGroupUser_username_where) . "' and SettingGroupUser.SettingGroupPID = SettingGroup.PID and SettingGroup.PID = DropboxBaseFolder.SettingGroupPID and DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDropboxBaseFolderByUserForAllSettingGroupList($param_SettingGroupUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderByUserForAllSettingGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderByUserForAllSettingGroupList ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID join SettingGroupUser join SettingGroup where SettingGroupUser.username = '" . $mtooldb->real_escape_string($param_SettingGroupUser_username_where) . "' and SettingGroupUser.SettingGroupPID = SettingGroup.PID and SettingGroup.PID = DropboxBaseFolder.SettingGroupPID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDropboxBaseFolder($param_DropboxBaseFolder_PID_where, $param_DropboxBaseFolder_SettingGroupPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolder ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolder ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_PID_where) . "' and DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_SettingGroupPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function GetDropboxBaseFolderByUserForAllSettingGroup($param_DropboxBaseFolder_PID_where, $param_SettingGroupUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderByUserForAllSettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderByUserForAllSettingGroup ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID join SettingGroupUser join SettingGroup where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_PID_where) . "' and SettingGroupUser.username = '" . $mtooldb->real_escape_string($param_SettingGroupUser_username_where) . "' and SettingGroupUser.SettingGroupPID = SettingGroup.PID and SettingGroup.PID = DropboxBaseFolder.SettingGroupPID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function GetDropboxBaseFolderForAnySettingGroup($param_DropboxBaseFolder_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderForAnySettingGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxBaseFolderForAnySettingGroup ==
		
		$last_sql_command_for_mtooldb = "select DropboxBaseFolder.PID, DropboxBaseFolder.SettingGroupPID, DropboxBaseFolder.Name, DropboxBaseFolder.DropboxSettingPID, DropboxBaseFolder.IsLocked, DropboxSetting.name, DropboxSetting.AccessToken from DropboxBaseFolder LEFT OUTER JOIN DropboxSetting ON DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxBaseFolderData();
			$thisresult->PID = $thisline[0];
			$thisresult->SettingGroupPID = $thisline[1];
			$thisresult->Name = $thisline[2];
			$thisresult->DropboxSettingPID = $thisline[3];
			$thisresult->IsLocked = $thisline[4];
			$thisresult->DropboxSettingname = $thisline[5];
			$thisresult->DropboxSettingAccessToken = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDropboxBaseFolder($DropboxBaseFolderObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDropboxBaseFolder ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDropboxBaseFolder ==
		
		$last_sql_command_for_mtooldb = "insert into DropboxBaseFolder (SettingGroupPID, Name, DropboxSettingPID) values('" . $mtooldb->real_escape_string($DropboxBaseFolderObj->SettingGroupPID) . "', '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->Name) . "', '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->DropboxSettingPID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDropboxBaseFolder($DropboxBaseFolderObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolder ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolder ==
		
		$last_sql_command_for_mtooldb = "update DropboxBaseFolder SET Name = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->Name) . "', DropboxSettingPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->DropboxSettingPID) . "' where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->PID) . "' and DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDropboxBaseFolderExceptName($DropboxBaseFolderObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolderExceptName ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDropboxBaseFolderExceptName ==
		
		$last_sql_command_for_mtooldb = "update DropboxBaseFolder SET DropboxSettingPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->DropboxSettingPID) . "' where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->PID) . "' and DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLockedFlag($DropboxBaseFolderObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLockedFlag ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLockedFlag ==
		
		$last_sql_command_for_mtooldb = "update DropboxBaseFolder SET IsLocked = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->IsLocked) . "' where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->PID) . "' and DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->SettingGroupPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDropboxBaseFolder($DropboxBaseFolderObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDropboxBaseFolder ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDropboxBaseFolder ==
		
		$last_sql_command_for_mtooldb = "delete from DropboxBaseFolder where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->PID) . "' and DropboxBaseFolder.SettingGroupPID = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->SettingGroupPID) . "' and (DropboxBaseFolder.IsLocked = '" . $mtooldb->real_escape_string($DropboxBaseFolderObj->IsLocked) . "')";
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