<?PHP

// THIS CLASS WAS AUTOMATICALLY CREATED BY MTOOL. DON'T EDIT EXCEPT THE EDITABLE AREA.
// [JP] このクラスは自動生成されています。編集可能領域以外は変更しないで下さい。

// == START OF EDITABLE AREA FOR ABOVE ==
// == END OF EDITABLE AREA FOR ABOVE ==

class LanguageResourceDBAccess
{
	public function __construct() {
	}
	
	public function GetLanguageResourceList($param_LanguageResource_LanguageResourceGroupPID_where, $param_LanguageResource_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResource.PID, LanguageResource.ProjectPID, LanguageResource.LanguageResourceGroupPID, LanguageResource.KeyForUpdate, LanguageResource.SortGroup, LanguageResource.KeyName, LanguageResource.KeyNameForXcode, LanguageResource.UWPTargetProperty, LanguageResource.IsResourceFixed, LanguageResource.UseDefaultIfCaptionIsBlank from LanguageResource where LanguageResource.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($param_LanguageResource_LanguageResourceGroupPID_where) . "' and LanguageResource.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResource_ProjectPID_where) . "' order by LanguageResource.ProjectPID,LanguageResource.LanguageResourceGroupPID,LanguageResource.SortGroup,LanguageResource.KeyName,LanguageResource.PID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourceGroupPID = $thisline[2];
			$thisresult->KeyForUpdate = $thisline[3];
			$thisresult->SortGroup = $thisline[4];
			$thisresult->KeyName = $thisline[5];
			$thisresult->KeyNameForXcode = $thisline[6];
			$thisresult->UWPTargetProperty = $thisline[7];
			$thisresult->IsResourceFixed = $thisline[8];
			$thisresult->UseDefaultIfCaptionIsBlank = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLanguageResourceOfAdditionalGroupList($param_LanguageResourceAdditionalGroupAssignment_LanguageResourceGroupPID_where, $param_LanguageResourceAdditionalGroupAssignment_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		$result = array();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceOfAdditionalGroupList ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceOfAdditionalGroupList ==
		
		$last_sql_command_for_mtooldb = "select LanguageResource.PID, LanguageResource.ProjectPID, LanguageResource.LanguageResourceGroupPID, LanguageResource.KeyForUpdate, LanguageResource.SortGroup, LanguageResource.KeyName, LanguageResource.KeyNameForXcode, LanguageResource.UWPTargetProperty, LanguageResource.IsResourceFixed, LanguageResource.UseDefaultIfCaptionIsBlank from LanguageResource join LanguageResourceAdditionalGroupAssignment where LanguageResourceAdditionalGroupAssignment.LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($param_LanguageResourceAdditionalGroupAssignment_LanguageResourceGroupPID_where) . "' and LanguageResourceAdditionalGroupAssignment.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResourceAdditionalGroupAssignment_ProjectPID_where) . "' and LanguageResourceAdditionalGroupAssignment.LanguageResourcePID = LanguageResource.PID and LanguageResourceAdditionalGroupAssignment.ProjectPID = LanguageResource.ProjectPID";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourceGroupPID = $thisline[2];
			$thisresult->KeyForUpdate = $thisline[3];
			$thisresult->SortGroup = $thisline[4];
			$thisresult->KeyName = $thisline[5];
			$thisresult->KeyNameForXcode = $thisline[6];
			$thisresult->UWPTargetProperty = $thisline[7];
			$thisresult->IsResourceFixed = $thisline[8];
			$thisresult->UseDefaultIfCaptionIsBlank = $thisline[9];
			array_push($result, $thisresult);
		}
		return $result;
	}
	public function GetLanguageResource($param_LanguageResource_PID_where, $param_LanguageResource_ProjectPID_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResource ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResource ==
		
		$last_sql_command_for_mtooldb = "select LanguageResource.PID, LanguageResource.ProjectPID, LanguageResource.LanguageResourceGroupPID, LanguageResource.KeyForUpdate, LanguageResource.SortGroup, LanguageResource.KeyName, LanguageResource.KeyNameForXcode, LanguageResource.UWPTargetProperty, LanguageResource.IsResourceFixed, LanguageResource.UseDefaultIfCaptionIsBlank from LanguageResource where LanguageResource.PID = '" . $mtooldb->real_escape_string($param_LanguageResource_PID_where) . "' and LanguageResource.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResource_ProjectPID_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourceGroupPID = $thisline[2];
			$thisresult->KeyForUpdate = $thisline[3];
			$thisresult->SortGroup = $thisline[4];
			$thisresult->KeyName = $thisline[5];
			$thisresult->KeyNameForXcode = $thisline[6];
			$thisresult->UWPTargetProperty = $thisline[7];
			$thisresult->IsResourceFixed = $thisline[8];
			$thisresult->UseDefaultIfCaptionIsBlank = $thisline[9];
			return $thisresult;
		}
		return NULL;
	}
	public function GetLanguageResourceByKeyName($param_LanguageResource_ProjectPID_where, $param_LanguageResource_KeyName_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION GetLanguageResourceByKeyName ==
		// == END OF EDITABLE AREA FOR FUNCTION GetLanguageResourceByKeyName ==
		
		$last_sql_command_for_mtooldb = "select LanguageResource.PID, LanguageResource.ProjectPID, LanguageResource.LanguageResourceGroupPID, LanguageResource.KeyForUpdate, LanguageResource.SortGroup, LanguageResource.KeyName, LanguageResource.KeyNameForXcode, LanguageResource.UWPTargetProperty, LanguageResource.IsResourceFixed, LanguageResource.UseDefaultIfCaptionIsBlank from LanguageResource where LanguageResource.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResource_ProjectPID_where) . "' and LanguageResource.KeyName = '" . $mtooldb->real_escape_string($param_LanguageResource_KeyName_where) . "'";
		$ret = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new LanguageResourceData();
			$thisresult->PID = $thisline[0];
			$thisresult->ProjectPID = $thisline[1];
			$thisresult->LanguageResourceGroupPID = $thisline[2];
			$thisresult->KeyForUpdate = $thisline[3];
			$thisresult->SortGroup = $thisline[4];
			$thisresult->KeyName = $thisline[5];
			$thisresult->KeyNameForXcode = $thisline[6];
			$thisresult->UWPTargetProperty = $thisline[7];
			$thisresult->IsResourceFixed = $thisline[8];
			$thisresult->UseDefaultIfCaptionIsBlank = $thisline[9];
			return $thisresult;
		}
		return NULL;
	}
	public function InsertLanguageResource($LanguageResourceObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION InsertLanguageResource ==
		// == END OF EDITABLE AREA FOR FUNCTION InsertLanguageResource ==
		
		$last_sql_command_for_mtooldb = "insert into LanguageResource (ProjectPID, LanguageResourceGroupPID, KeyForUpdate, SortGroup, KeyName, KeyNameForXcode, UWPTargetProperty, IsResourceFixed, UseDefaultIfCaptionIsBlank) values('" . $mtooldb->real_escape_string($LanguageResourceObj->ProjectPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->LanguageResourceGroupPID) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->KeyForUpdate) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->SortGroup) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->KeyName) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->KeyNameForXcode) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->UWPTargetProperty) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->IsResourceFixed) . "', '" . $mtooldb->real_escape_string($LanguageResourceObj->UseDefaultIfCaptionIsBlank) . "')";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLanguageResource($LanguageResourceObj, $param_LanguageResource_PID_where, $param_LanguageResource_ProjectPID_where, $param_LanguageResource_KeyForUpdate_where)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLanguageResource ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLanguageResource ==
		
		$last_sql_command_for_mtooldb = "update LanguageResource SET KeyForUpdate = '" . $mtooldb->real_escape_string($LanguageResourceObj->KeyForUpdate) . "', SortGroup = '" . $mtooldb->real_escape_string($LanguageResourceObj->SortGroup) . "', KeyName = '" . $mtooldb->real_escape_string($LanguageResourceObj->KeyName) . "', KeyNameForXcode = '" . $mtooldb->real_escape_string($LanguageResourceObj->KeyNameForXcode) . "', UWPTargetProperty = '" . $mtooldb->real_escape_string($LanguageResourceObj->UWPTargetProperty) . "', IsResourceFixed = '" . $mtooldb->real_escape_string($LanguageResourceObj->IsResourceFixed) . "', UseDefaultIfCaptionIsBlank = '" . $mtooldb->real_escape_string($LanguageResourceObj->UseDefaultIfCaptionIsBlank) . "' where LanguageResource.PID = '" . $mtooldb->real_escape_string($param_LanguageResource_PID_where) . "' and LanguageResource.ProjectPID = '" . $mtooldb->real_escape_string($param_LanguageResource_ProjectPID_where) . "' and LanguageResource.KeyForUpdate = '" . $mtooldb->real_escape_string($param_LanguageResource_KeyForUpdate_where) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function UpdateLanguageGroup($LanguageResourceObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION UpdateLanguageGroup ==
		// == END OF EDITABLE AREA FOR FUNCTION UpdateLanguageGroup ==
		
		$last_sql_command_for_mtooldb = "update LanguageResource SET LanguageResourceGroupPID = '" . $mtooldb->real_escape_string($LanguageResourceObj->LanguageResourceGroupPID) . "' where LanguageResource.PID = '" . $mtooldb->real_escape_string($LanguageResourceObj->PID) . "' and LanguageResource.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceObj->ProjectPID) . "'";
		$result = $mtooldb->query($last_sql_command_for_mtooldb);
		if ($mtooldb->errno != 0) {
			error_log("Error occured while executing SQL: " . $mtooldb->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
	public function DeleteLanguageResource($LanguageResourceObj)
	{
		global $mtooldb, $last_sql_command_for_mtooldb;
		connect_mtooldb_if_not_yet();
		reconnect_mtooldb_if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION DeleteLanguageResource ==
		// == END OF EDITABLE AREA FOR FUNCTION DeleteLanguageResource ==
		
		$last_sql_command_for_mtooldb = "delete from LanguageResource where LanguageResource.PID = '" . $mtooldb->real_escape_string($LanguageResourceObj->PID) . "' and LanguageResource.ProjectPID = '" . $mtooldb->real_escape_string($LanguageResourceObj->ProjectPID) . "'";
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

function GetLanguageResourceListWithAdditionalGroup($LanguageResourceGroupPID, $ProjectPID)
{
	$DALanguageResource = new LanguageResourceDBAccess();
	$LanguageResourceList = $DALanguageResource->GetLanguageResourceList($LanguageResourceGroupPID, $ProjectPID);
	if ($LanguageResourceList) {
		$AdditionalLanguageResourceList = $DALanguageResource->GetLanguageResourceOfAdditionalGroupList($LanguageResourceGroupPID, $ProjectPID);
		if ($AdditionalLanguageResourceList) {
			for($i = 0 ; $i < count($AdditionalLanguageResourceList); $i++) {
				$AdditionalLanguageResource = $AdditionalLanguageResourceList[$i];
				
				$already_exit = false;
				for($j = 0 ; $j < count($LanguageResourceList); $j++) {
					$LanguageResource = $LanguageResourceList[$j];
					
					if ($LanguageResource->PID == $AdditionalLanguageResource->PID) {
						$already_exit = true;
						break;
					}
				}
				if (!$already_exit) {
					array_push($LanguageResourceList, $AdditionalLanguageResource);
				}
			}
		}
	}
	return $LanguageResourceList;
}

// == END OF EDITABLE AREA FOR BOTTOM ==

?>