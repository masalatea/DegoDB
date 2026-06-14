<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class SpecDBAccess
{
	public function __construct() {
	}
	
	public function GetSpecList($param_Spec_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecList ==
		
		$last_sql_command_for_mtooldb = "select Spec.ProjectPID, Spec.PID, Spec.name from Spec where Spec.ProjectPID = '" . $mtooldb->real_escape_string($param_Spec_ProjectPID_where) . "' order by Spec.name,Spec.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpecByOwnerOrUserSecurityList($param_ProjectUser_username_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpecByOwnerOrUserSecurityList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpecByOwnerOrUserSecurityList ==
		
		$last_sql_command_for_mtooldb = "select Project.name, Spec.ProjectPID, Spec.PID, Spec.name from Project join Spec join ProjectUser where ProjectUser.username = '" . $mtooldb->real_escape_string($param_ProjectUser_username_where) . "' and ProjectUser.ProjectPID = Project.PID and ProjectUser.ProjectPID = Spec.ProjectPID order by Spec.name,Spec.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecData();
			$thisresult->Projectname = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->PID = $thisline[2];
			$thisresult->name = $thisline[3];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetSpec($param_Spec_PID_where, $param_Spec_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetSpec ==
		// == END OF EDITABLE AREA FOR FUNCTION GetSpec ==
		
		$last_sql_command_for_mtooldb = "select Spec.ProjectPID, Spec.PID, Spec.name from Spec where Spec.PID = '" . $mtooldb->real_escape_string($param_Spec_PID_where) . "' and Spec.ProjectPID = '" . $mtooldb->real_escape_string($param_Spec_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new SpecData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->name = $thisline[2];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertSpec($SpecObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertSpec ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertSpec ==
		
		$last_sql_command_for_mtooldb = "insert into Spec (ProjectPID, name) values('" . $mtooldb->real_escape_string($SpecObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($SpecObj->name) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateSpec($SpecObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateSpec ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateSpec ==
		
		$last_sql_command_for_mtooldb = "update Spec SET name = '" . $mtooldb->real_escape_string($SpecObj->name) . "' where Spec.PID = '" . $mtooldb->real_escape_string($SpecObj->PID) . "' and Spec.ProjectPID = '" . $mtooldb->real_escape_string($SpecObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteSpec($SpecObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteSpec ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteSpec ==
		
		$last_sql_command_for_mtooldb = "delete from Spec where Spec.PID = '" . $mtooldb->real_escape_string($SpecObj->PID) . "' and Spec.ProjectPID = '" . $mtooldb->real_escape_string($SpecObj->ProjectPID) . "'";
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