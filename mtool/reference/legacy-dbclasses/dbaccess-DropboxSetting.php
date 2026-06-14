<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class DropboxSettingDBAccess
{
	public function __construct() {
	}
	
	public function GetDropboxSettingList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxSettingList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxSettingList ==
		
		$last_sql_command_for_mtooldb = "select DropboxSetting.PID, DropboxSetting.name, DropboxSetting.IsPublic, DropboxSetting.DropboxAppKey, DropboxSetting.DropboxAppSecret, DropboxSetting.AccessToken, DropboxSetting.Oauth2RedirectUrl from DropboxSetting order by DropboxSetting.name,DropboxSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->IsPublic = $thisline[2];
			$thisresult->DropboxAppKey = $thisline[3];
			$thisresult->DropboxAppSecret = $thisline[4];
			$thisresult->AccessToken = $thisline[5];
			$thisresult->Oauth2RedirectUrl = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetPublicDropboxSettingList()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetPublicDropboxSettingList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetPublicDropboxSettingList ==
		
		$last_sql_command_for_mtooldb = "select DropboxSetting.PID, DropboxSetting.name, DropboxSetting.IsPublic, DropboxSetting.DropboxAppKey, DropboxSetting.DropboxAppSecret, DropboxSetting.AccessToken, DropboxSetting.Oauth2RedirectUrl from DropboxSetting";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->IsPublic = $thisline[2];
			$thisresult->DropboxAppKey = $thisline[3];
			$thisresult->DropboxAppSecret = $thisline[4];
			$thisresult->AccessToken = $thisline[5];
			$thisresult->Oauth2RedirectUrl = $thisline[6];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetDropboxSetting($param_DropboxSetting_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxSetting ==
		
		$last_sql_command_for_mtooldb = "select DropboxSetting.PID, DropboxSetting.name, DropboxSetting.IsPublic, DropboxSetting.DropboxAppKey, DropboxSetting.DropboxAppSecret, DropboxSetting.AccessToken, DropboxSetting.Oauth2RedirectUrl from DropboxSetting where DropboxSetting.PID = '" . $mtooldb->real_escape_string($param_DropboxSetting_PID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->IsPublic = $thisline[2];
			$thisresult->DropboxAppKey = $thisline[3];
			$thisresult->DropboxAppSecret = $thisline[4];
			$thisresult->AccessToken = $thisline[5];
			$thisresult->Oauth2RedirectUrl = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function GetDropboxSettingByDropboxBaseFolderPID($param_DropboxBaseFolder_PID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetDropboxSettingByDropboxBaseFolderPID ==
		// == END OF EDITABLE AREA FOR FUNCTION GetDropboxSettingByDropboxBaseFolderPID ==
		
		$last_sql_command_for_mtooldb = "select DropboxSetting.PID, DropboxSetting.name, DropboxSetting.IsPublic, DropboxSetting.DropboxAppKey, DropboxSetting.DropboxAppSecret, DropboxSetting.AccessToken, DropboxSetting.Oauth2RedirectUrl from DropboxSetting join DropboxBaseFolder where DropboxBaseFolder.PID = '" . $mtooldb->real_escape_string($param_DropboxBaseFolder_PID_where) . "' and DropboxBaseFolder.DropboxSettingPID = DropboxSetting.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new DropboxSettingData();
			$thisresult->PID = $thisline[0];
			$thisresult->name = $thisline[1];
			$thisresult->IsPublic = $thisline[2];
			$thisresult->DropboxAppKey = $thisline[3];
			$thisresult->DropboxAppSecret = $thisline[4];
			$thisresult->AccessToken = $thisline[5];
			$thisresult->Oauth2RedirectUrl = $thisline[6];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertDropboxSetting($DropboxSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertDropboxSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertDropboxSetting ==
		
		$last_sql_command_for_mtooldb = "insert into DropboxSetting (name, IsPublic, DropboxAppKey, DropboxAppSecret, AccessToken, Oauth2RedirectUrl) values('" . $mtooldb->real_escape_string($DropboxSettingObj->name) . "', '" . $mtooldb->real_escape_string($DropboxSettingObj->IsPublic) . "', '" . $mtooldb->real_escape_string($DropboxSettingObj->DropboxAppKey) . "', '" . $mtooldb->real_escape_string($DropboxSettingObj->DropboxAppSecret) . "', '" . $mtooldb->real_escape_string($DropboxSettingObj->AccessToken) . "', '" . $mtooldb->real_escape_string($DropboxSettingObj->Oauth2RedirectUrl) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateDropboxSetting($DropboxSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateDropboxSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateDropboxSetting ==
		
		$last_sql_command_for_mtooldb = "update DropboxSetting SET name = '" . $mtooldb->real_escape_string($DropboxSettingObj->name) . "', IsPublic = '" . $mtooldb->real_escape_string($DropboxSettingObj->IsPublic) . "', DropboxAppKey = '" . $mtooldb->real_escape_string($DropboxSettingObj->DropboxAppKey) . "', DropboxAppSecret = '" . $mtooldb->real_escape_string($DropboxSettingObj->DropboxAppSecret) . "', Oauth2RedirectUrl = '" . $mtooldb->real_escape_string($DropboxSettingObj->Oauth2RedirectUrl) . "' where DropboxSetting.PID = '" . $mtooldb->real_escape_string($DropboxSettingObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateAccessToken($DropboxSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateAccessToken ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateAccessToken ==
		
		$last_sql_command_for_mtooldb = "update DropboxSetting SET AccessToken = '" . $mtooldb->real_escape_string($DropboxSettingObj->AccessToken) . "' where DropboxSetting.PID = '" . $mtooldb->real_escape_string($DropboxSettingObj->PID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteDropboxSetting($DropboxSettingObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteDropboxSetting ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteDropboxSetting ==
		
		$last_sql_command_for_mtooldb = "delete from DropboxSetting where DropboxSetting.PID = '" . $mtooldb->real_escape_string($DropboxSettingObj->PID) . "'";
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