<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class ProjectUserDBAccess
{
	public function __construct() {
	}
	
	public function GetProjectOwnerList($param_ProjectUser_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectOwnerList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectOwnerList ==
		
		$last_sql_command_for_mtooldb = "select ProjectUser.ProjectPID, ProjectUser.PID, ProjectUser.username, ProjectUser.IsOwner, ProjectUser.dbtoolRead, ProjectUser.dbtoolWrite, ProjectUser.htmlRead, ProjectUser.htmlWrite, ProjectUser.testtoolRead, ProjectUser.testtoolWrite, ProjectUser.spectoolRead, ProjectUser.spectoolWrite, ProjectUser.ReqRead, ProjectUser.ReqWrite, ProjectUser.ChatRead, ProjectUser.ChatWrite, ProjectUser.MinutesRead, ProjectUser.MinutesWrite, ProjectUser.UploadRead, ProjectUser.UploadWrite from ProjectUser where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectUser_ProjectPID_where) . "' and ProjectUser.IsOwner = '" . $mtooldb->real_escape_string("t") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectUserData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsOwner = $thisline[3];
			$thisresult->dbtoolRead = $thisline[4];
			$thisresult->dbtoolWrite = $thisline[5];
			$thisresult->htmlRead = $thisline[6];
			$thisresult->htmlWrite = $thisline[7];
			$thisresult->testtoolRead = $thisline[8];
			$thisresult->testtoolWrite = $thisline[9];
			$thisresult->spectoolRead = $thisline[10];
			$thisresult->spectoolWrite = $thisline[11];
			$thisresult->ReqRead = $thisline[12];
			$thisresult->ReqWrite = $thisline[13];
			$thisresult->ChatRead = $thisline[14];
			$thisresult->ChatWrite = $thisline[15];
			$thisresult->MinutesRead = $thisline[16];
			$thisresult->MinutesWrite = $thisline[17];
			$thisresult->UploadRead = $thisline[18];
			$thisresult->UploadWrite = $thisline[19];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectUserList($param_ProjectUser_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectUserList ==
		
		$last_sql_command_for_mtooldb = "select ProjectUser.ProjectPID, ProjectUser.PID, ProjectUser.username, ProjectUser.IsOwner, ProjectUser.dbtoolRead, ProjectUser.dbtoolWrite, ProjectUser.htmlRead, ProjectUser.htmlWrite, ProjectUser.testtoolRead, ProjectUser.testtoolWrite, ProjectUser.spectoolRead, ProjectUser.spectoolWrite, ProjectUser.ReqRead, ProjectUser.ReqWrite, ProjectUser.ChatRead, ProjectUser.ChatWrite, ProjectUser.MinutesRead, ProjectUser.MinutesWrite, ProjectUser.UploadRead, ProjectUser.UploadWrite from ProjectUser where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectUser_ProjectPID_where) . "' and ProjectUser.IsOwner = '" . $mtooldb->real_escape_string("f") . "' order by ProjectUser.username";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectUserData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsOwner = $thisline[3];
			$thisresult->dbtoolRead = $thisline[4];
			$thisresult->dbtoolWrite = $thisline[5];
			$thisresult->htmlRead = $thisline[6];
			$thisresult->htmlWrite = $thisline[7];
			$thisresult->testtoolRead = $thisline[8];
			$thisresult->testtoolWrite = $thisline[9];
			$thisresult->spectoolRead = $thisline[10];
			$thisresult->spectoolWrite = $thisline[11];
			$thisresult->ReqRead = $thisline[12];
			$thisresult->ReqWrite = $thisline[13];
			$thisresult->ChatRead = $thisline[14];
			$thisresult->ChatWrite = $thisline[15];
			$thisresult->MinutesRead = $thisline[16];
			$thisresult->MinutesWrite = $thisline[17];
			$thisresult->UploadRead = $thisline[18];
			$thisresult->UploadWrite = $thisline[19];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectOwnerOrUserList($param_ProjectUser_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectOwnerOrUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectOwnerOrUserList ==
		
		$last_sql_command_for_mtooldb = "select ProjectUser.ProjectPID, ProjectUser.PID, ProjectUser.username, ProjectUser.IsOwner, ProjectUser.dbtoolRead, ProjectUser.dbtoolWrite, ProjectUser.htmlRead, ProjectUser.htmlWrite, ProjectUser.testtoolRead, ProjectUser.testtoolWrite, ProjectUser.spectoolRead, ProjectUser.spectoolWrite, ProjectUser.ReqRead, ProjectUser.ReqWrite, ProjectUser.ChatRead, ProjectUser.ChatWrite, ProjectUser.MinutesRead, ProjectUser.MinutesWrite, ProjectUser.UploadRead, ProjectUser.UploadWrite from ProjectUser where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectUser_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectUserData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsOwner = $thisline[3];
			$thisresult->dbtoolRead = $thisline[4];
			$thisresult->dbtoolWrite = $thisline[5];
			$thisresult->htmlRead = $thisline[6];
			$thisresult->htmlWrite = $thisline[7];
			$thisresult->testtoolRead = $thisline[8];
			$thisresult->testtoolWrite = $thisline[9];
			$thisresult->spectoolRead = $thisline[10];
			$thisresult->spectoolWrite = $thisline[11];
			$thisresult->ReqRead = $thisline[12];
			$thisresult->ReqWrite = $thisline[13];
			$thisresult->ChatRead = $thisline[14];
			$thisresult->ChatWrite = $thisline[15];
			$thisresult->MinutesRead = $thisline[16];
			$thisresult->MinutesWrite = $thisline[17];
			$thisresult->UploadRead = $thisline[18];
			$thisresult->UploadWrite = $thisline[19];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectOwner($param_ProjectUser_ProjectPID_where, $param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectOwner ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectOwner ==
		
		$last_sql_command_for_mtooldb = "select ProjectUser.ProjectPID, ProjectUser.PID, ProjectUser.username, ProjectUser.IsOwner, ProjectUser.dbtoolRead, ProjectUser.dbtoolWrite, ProjectUser.htmlRead, ProjectUser.htmlWrite, ProjectUser.testtoolRead, ProjectUser.testtoolWrite, ProjectUser.spectoolRead, ProjectUser.spectoolWrite, ProjectUser.ReqRead, ProjectUser.ReqWrite, ProjectUser.ChatRead, ProjectUser.ChatWrite, ProjectUser.MinutesRead, ProjectUser.MinutesWrite, ProjectUser.UploadRead, ProjectUser.UploadWrite from ProjectUser where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectUser_ProjectPID_where) . "' and ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.IsOwner = '" . $mtooldb->real_escape_string("t") . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectUserData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsOwner = $thisline[3];
			$thisresult->dbtoolRead = $thisline[4];
			$thisresult->dbtoolWrite = $thisline[5];
			$thisresult->htmlRead = $thisline[6];
			$thisresult->htmlWrite = $thisline[7];
			$thisresult->testtoolRead = $thisline[8];
			$thisresult->testtoolWrite = $thisline[9];
			$thisresult->spectoolRead = $thisline[10];
			$thisresult->spectoolWrite = $thisline[11];
			$thisresult->ReqRead = $thisline[12];
			$thisresult->ReqWrite = $thisline[13];
			$thisresult->ChatRead = $thisline[14];
			$thisresult->ChatWrite = $thisline[15];
			$thisresult->MinutesRead = $thisline[16];
			$thisresult->MinutesWrite = $thisline[17];
			$thisresult->UploadRead = $thisline[18];
			$thisresult->UploadWrite = $thisline[19];
			return $thisresult;
		}
		return NULL;
	}
	public function GetProjectOwnerOfTheUserList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectOwnerOfTheUserList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectOwnerOfTheUserList ==
		
		$last_sql_command_for_mtooldb = "select ProjectUser.ProjectPID, ProjectUser.PID, ProjectUser.username, ProjectUser.IsOwner, ProjectUser.dbtoolRead, ProjectUser.dbtoolWrite, ProjectUser.htmlRead, ProjectUser.htmlWrite, ProjectUser.testtoolRead, ProjectUser.testtoolWrite, ProjectUser.spectoolRead, ProjectUser.spectoolWrite, ProjectUser.ReqRead, ProjectUser.ReqWrite, ProjectUser.ChatRead, ProjectUser.ChatWrite, ProjectUser.MinutesRead, ProjectUser.MinutesWrite, ProjectUser.UploadRead, ProjectUser.UploadWrite from ProjectUser join Project where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.IsOwner = '" . $mtooldb->real_escape_string("t") . "' and ProjectUser.ProjectPID = Project.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectUserData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsOwner = $thisline[3];
			$thisresult->dbtoolRead = $thisline[4];
			$thisresult->dbtoolWrite = $thisline[5];
			$thisresult->htmlRead = $thisline[6];
			$thisresult->htmlWrite = $thisline[7];
			$thisresult->testtoolRead = $thisline[8];
			$thisresult->testtoolWrite = $thisline[9];
			$thisresult->spectoolRead = $thisline[10];
			$thisresult->spectoolWrite = $thisline[11];
			$thisresult->ReqRead = $thisline[12];
			$thisresult->ReqWrite = $thisline[13];
			$thisresult->ChatRead = $thisline[14];
			$thisresult->ChatWrite = $thisline[15];
			$thisresult->MinutesRead = $thisline[16];
			$thisresult->MinutesWrite = $thisline[17];
			$thisresult->UploadRead = $thisline[18];
			$thisresult->UploadWrite = $thisline[19];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetProjectOwnerOrUser($param_ProjectUser_ProjectPID_where, $param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetProjectOwnerOrUser ==
		// == END OF EDITABLE AREA FOR FUNCTION GetProjectOwnerOrUser ==
		
		$last_sql_command_for_mtooldb = "select ProjectUser.ProjectPID, ProjectUser.PID, ProjectUser.username, ProjectUser.IsOwner, ProjectUser.dbtoolRead, ProjectUser.dbtoolWrite, ProjectUser.htmlRead, ProjectUser.htmlWrite, ProjectUser.testtoolRead, ProjectUser.testtoolWrite, ProjectUser.spectoolRead, ProjectUser.spectoolWrite, ProjectUser.ReqRead, ProjectUser.ReqWrite, ProjectUser.ChatRead, ProjectUser.ChatWrite, ProjectUser.MinutesRead, ProjectUser.MinutesWrite, ProjectUser.UploadRead, ProjectUser.UploadWrite from ProjectUser where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($param_ProjectUser_ProjectPID_where) . "' and ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new ProjectUserData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->username = $thisline[2];
			$thisresult->IsOwner = $thisline[3];
			$thisresult->dbtoolRead = $thisline[4];
			$thisresult->dbtoolWrite = $thisline[5];
			$thisresult->htmlRead = $thisline[6];
			$thisresult->htmlWrite = $thisline[7];
			$thisresult->testtoolRead = $thisline[8];
			$thisresult->testtoolWrite = $thisline[9];
			$thisresult->spectoolRead = $thisline[10];
			$thisresult->spectoolWrite = $thisline[11];
			$thisresult->ReqRead = $thisline[12];
			$thisresult->ReqWrite = $thisline[13];
			$thisresult->ChatRead = $thisline[14];
			$thisresult->ChatWrite = $thisline[15];
			$thisresult->MinutesRead = $thisline[16];
			$thisresult->MinutesWrite = $thisline[17];
			$thisresult->UploadRead = $thisline[18];
			$thisresult->UploadWrite = $thisline[19];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertProjectOwnerOrUser($ProjectUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertProjectOwnerOrUser ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertProjectOwnerOrUser ==
		
		$last_sql_command_for_mtooldb = "insert into ProjectUser (ProjectPID, username, IsOwner, dbtoolRead, dbtoolWrite, htmlRead, htmlWrite, testtoolRead, testtoolWrite, spectoolRead, spectoolWrite, ReqRead, ReqWrite, ChatRead, ChatWrite, MinutesRead, MinutesWrite, UploadRead, UploadWrite) values('" . $mtooldb->real_escape_string($ProjectUserObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->username) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->IsOwner) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->dbtoolRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->dbtoolWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->htmlRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->htmlWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->testtoolRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->testtoolWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->spectoolRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->spectoolWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->ReqRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->ReqWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->ChatRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->ChatWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->MinutesRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->MinutesWrite) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->UploadRead) . "', '" . $mtooldb->real_escape_string($ProjectUserObj->UploadWrite) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectOwnerOrUserBasicInfo($ProjectUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectOwnerOrUserBasicInfo ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectOwnerOrUserBasicInfo ==
		
		$last_sql_command_for_mtooldb = "update ProjectUser SET IsOwner = '" . $mtooldb->real_escape_string($ProjectUserObj->IsOwner) . "' where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($ProjectUserObj->ProjectPID) . "' and ProjectUser.username = '" . $mtooldb->real_escape_string($ProjectUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateProjectUserDetail($ProjectUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateProjectUserDetail ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateProjectUserDetail ==
		
		$last_sql_command_for_mtooldb = "update ProjectUser SET dbtoolRead = '" . $mtooldb->real_escape_string($ProjectUserObj->dbtoolRead) . "', dbtoolWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->dbtoolWrite) . "', htmlRead = '" . $mtooldb->real_escape_string($ProjectUserObj->htmlRead) . "', htmlWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->htmlWrite) . "', testtoolRead = '" . $mtooldb->real_escape_string($ProjectUserObj->testtoolRead) . "', testtoolWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->testtoolWrite) . "', spectoolRead = '" . $mtooldb->real_escape_string($ProjectUserObj->spectoolRead) . "', spectoolWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->spectoolWrite) . "', ReqRead = '" . $mtooldb->real_escape_string($ProjectUserObj->ReqRead) . "', ReqWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->ReqWrite) . "', ChatRead = '" . $mtooldb->real_escape_string($ProjectUserObj->ChatRead) . "', ChatWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->ChatWrite) . "', MinutesRead = '" . $mtooldb->real_escape_string($ProjectUserObj->MinutesRead) . "', MinutesWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->MinutesWrite) . "', UploadRead = '" . $mtooldb->real_escape_string($ProjectUserObj->UploadRead) . "', UploadWrite = '" . $mtooldb->real_escape_string($ProjectUserObj->UploadWrite) . "' where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($ProjectUserObj->ProjectPID) . "' and ProjectUser.username = '" . $mtooldb->real_escape_string($ProjectUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteProjectOwnerOrUser($ProjectUserObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteProjectOwnerOrUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteProjectOwnerOrUser ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectUser where ProjectUser.ProjectPID = '" . $mtooldb->real_escape_string($ProjectUserObj->ProjectPID) . "' and ProjectUser.username = '" . $mtooldb->real_escape_string($ProjectUserObj->username) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteUnmatchedProjectUser()
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteUnmatchedProjectUser ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteUnmatchedProjectUser ==
		
		$last_sql_command_for_mtooldb = "delete from ProjectUser where ProjectUser.ProjectPID not in (select PID from Project)";
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