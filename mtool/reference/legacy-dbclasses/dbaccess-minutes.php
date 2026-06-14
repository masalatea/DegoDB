<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class minutesDBAccess
{
	public function __construct() {
	}
	
	public function GetminutesList($param_minutes_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetminutesList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetminutesList ==
		
		$last_sql_command_for_mtooldb = "select minutes.ProjectPID, minutes.PID, minutes.AddedDateTime, minutes.UpdatedDateTime, minutes.Title, minutes.Overview, minutes.IsBrainstorming, minutes.Brainstorming, minutes.IsDecisionMaking, minutes.DecisionMaking, minutes.IsLearning, minutes.Learning, minutes.chattopicPID, minutes.ReqPID, minutes.SpecPID, minutes.SpecContentPID, minutes.TestGroupPID, minutes.TestPID, minutes.daPID, minutes.dafuncPID, minutes.dataclassPID, minutes.dbtablePID from minutes where minutes.ProjectPID = '" . $mtooldb->real_escape_string($param_minutes_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new minutesData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->AddedDateTime = $thisline[2];
			$thisresult->UpdatedDateTime = $thisline[3];
			$thisresult->Title = $thisline[4];
			$thisresult->Overview = $thisline[5];
			$thisresult->IsBrainstorming = $thisline[6];
			$thisresult->Brainstorming = $thisline[7];
			$thisresult->IsDecisionMaking = $thisline[8];
			$thisresult->DecisionMaking = $thisline[9];
			$thisresult->IsLearning = $thisline[10];
			$thisresult->Learning = $thisline[11];
			$thisresult->chattopicPID = $thisline[12];
			$thisresult->ReqPID = $thisline[13];
			$thisresult->SpecPID = $thisline[14];
			$thisresult->SpecContentPID = $thisline[15];
			$thisresult->TestGroupPID = $thisline[16];
			$thisresult->TestPID = $thisline[17];
			$thisresult->daPID = $thisline[18];
			$thisresult->dafuncPID = $thisline[19];
			$thisresult->dataclassPID = $thisline[20];
			$thisresult->dbtablePID = $thisline[21];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function Getminutes($param_minutes_PID_where, $param_minutes_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Getminutes ==
		// == END OF EDITABLE AREA FOR FUNCTION Getminutes ==
		
		$last_sql_command_for_mtooldb = "select minutes.ProjectPID, minutes.PID, minutes.AddedDateTime, minutes.UpdatedDateTime, minutes.Title, minutes.Overview, minutes.IsBrainstorming, minutes.Brainstorming, minutes.IsDecisionMaking, minutes.DecisionMaking, minutes.IsLearning, minutes.Learning, minutes.chattopicPID, minutes.ReqPID, minutes.SpecPID, minutes.SpecContentPID, minutes.TestGroupPID, minutes.TestPID, minutes.daPID, minutes.dafuncPID, minutes.dataclassPID, minutes.dbtablePID from minutes where minutes.PID = '" . $mtooldb->real_escape_string($param_minutes_PID_where) . "' and minutes.ProjectPID = '" . $mtooldb->real_escape_string($param_minutes_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new minutesData();
			$thisresult->ProjectPID = $thisline[0];
			$thisresult->PID = $thisline[1];
			$thisresult->AddedDateTime = $thisline[2];
			$thisresult->UpdatedDateTime = $thisline[3];
			$thisresult->Title = $thisline[4];
			$thisresult->Overview = $thisline[5];
			$thisresult->IsBrainstorming = $thisline[6];
			$thisresult->Brainstorming = $thisline[7];
			$thisresult->IsDecisionMaking = $thisline[8];
			$thisresult->DecisionMaking = $thisline[9];
			$thisresult->IsLearning = $thisline[10];
			$thisresult->Learning = $thisline[11];
			$thisresult->chattopicPID = $thisline[12];
			$thisresult->ReqPID = $thisline[13];
			$thisresult->SpecPID = $thisline[14];
			$thisresult->SpecContentPID = $thisline[15];
			$thisresult->TestGroupPID = $thisline[16];
			$thisresult->TestPID = $thisline[17];
			$thisresult->daPID = $thisline[18];
			$thisresult->dafuncPID = $thisline[19];
			$thisresult->dataclassPID = $thisline[20];
			$thisresult->dbtablePID = $thisline[21];
			return $thisresult;
		}
		return NULL;
	}
	public function Insertminutes($minutesObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Insertminutes ==
		// == END OF EDITABLE AREA FOR FUNCTION Insertminutes ==
		
		$last_sql_command_for_mtooldb = "insert into minutes (ProjectPID, AddedDateTime, UpdatedDateTime, Title, Overview, IsBrainstorming, Brainstorming, IsDecisionMaking, DecisionMaking, IsLearning, Learning, chattopicPID, ReqPID, SpecPID, SpecContentPID, TestGroupPID, TestPID, daPID, dafuncPID, dataclassPID, dbtablePID) values('" . $mtooldb->real_escape_string($minutesObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($minutesObj->AddedDateTime) . "', '" . $mtooldb->real_escape_string($minutesObj->UpdatedDateTime) . "', '" . $mtooldb->real_escape_string($minutesObj->Title) . "', '" . $mtooldb->real_escape_string($minutesObj->Overview) . "', '" . $mtooldb->real_escape_string($minutesObj->IsBrainstorming) . "', '" . $mtooldb->real_escape_string($minutesObj->Brainstorming) . "', '" . $mtooldb->real_escape_string($minutesObj->IsDecisionMaking) . "', '" . $mtooldb->real_escape_string($minutesObj->DecisionMaking) . "', '" . $mtooldb->real_escape_string($minutesObj->IsLearning) . "', '" . $mtooldb->real_escape_string($minutesObj->Learning) . "', '" . $mtooldb->real_escape_string($minutesObj->chattopicPID) . "', '" . $mtooldb->real_escape_string($minutesObj->ReqPID) . "', '" . $mtooldb->real_escape_string($minutesObj->SpecPID) . "', '" . $mtooldb->real_escape_string($minutesObj->SpecContentPID) . "', '" . $mtooldb->real_escape_string($minutesObj->TestGroupPID) . "', '" . $mtooldb->real_escape_string($minutesObj->TestPID) . "', '" . $mtooldb->real_escape_string($minutesObj->daPID) . "', '" . $mtooldb->real_escape_string($minutesObj->dafuncPID) . "', '" . $mtooldb->real_escape_string($minutesObj->dataclassPID) . "', '" . $mtooldb->real_escape_string($minutesObj->dbtablePID) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Updateminutes($minutesObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Updateminutes ==
		// == END OF EDITABLE AREA FOR FUNCTION Updateminutes ==
		
		$last_sql_command_for_mtooldb = "update minutes SET UpdatedDateTime = '" . $mtooldb->real_escape_string($minutesObj->UpdatedDateTime) . "', Title = '" . $mtooldb->real_escape_string($minutesObj->Title) . "', Overview = '" . $mtooldb->real_escape_string($minutesObj->Overview) . "', IsBrainstorming = '" . $mtooldb->real_escape_string($minutesObj->IsBrainstorming) . "', Brainstorming = '" . $mtooldb->real_escape_string($minutesObj->Brainstorming) . "', IsDecisionMaking = '" . $mtooldb->real_escape_string($minutesObj->IsDecisionMaking) . "', DecisionMaking = '" . $mtooldb->real_escape_string($minutesObj->DecisionMaking) . "', IsLearning = '" . $mtooldb->real_escape_string($minutesObj->IsLearning) . "', Learning = '" . $mtooldb->real_escape_string($minutesObj->Learning) . "', chattopicPID = '" . $mtooldb->real_escape_string($minutesObj->chattopicPID) . "', ReqPID = '" . $mtooldb->real_escape_string($minutesObj->ReqPID) . "', SpecPID = '" . $mtooldb->real_escape_string($minutesObj->SpecPID) . "', SpecContentPID = '" . $mtooldb->real_escape_string($minutesObj->SpecContentPID) . "', TestGroupPID = '" . $mtooldb->real_escape_string($minutesObj->TestGroupPID) . "', TestPID = '" . $mtooldb->real_escape_string($minutesObj->TestPID) . "', daPID = '" . $mtooldb->real_escape_string($minutesObj->daPID) . "', dafuncPID = '" . $mtooldb->real_escape_string($minutesObj->dafuncPID) . "', dataclassPID = '" . $mtooldb->real_escape_string($minutesObj->dataclassPID) . "', dbtablePID = '" . $mtooldb->real_escape_string($minutesObj->dbtablePID) . "' where minutes.PID = '" . $mtooldb->real_escape_string($minutesObj->PID) . "' and minutes.ProjectPID = '" . $mtooldb->real_escape_string($minutesObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function Deleteminutes($minutesObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION Deleteminutes ==
		// == END OF EDITABLE AREA FOR FUNCTION Deleteminutes ==
		
		$last_sql_command_for_mtooldb = "delete from minutes where minutes.PID = '" . $mtooldb->real_escape_string($minutesObj->PID) . "' and minutes.ProjectPID = '" . $mtooldb->real_escape_string($minutesObj->ProjectPID) . "'";
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