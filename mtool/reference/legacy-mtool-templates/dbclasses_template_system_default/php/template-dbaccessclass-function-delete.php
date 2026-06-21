	public function __FUNCTION_NAME__(__PARAMS__)
	{
		global $__DB_OBJECT__, $last_sql_command_for___DB_OBJECT__;
		connect___DB_OBJECT___if_not_yet();
		reconnect___DB_OBJECT___if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ ==
		// == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ ==
		
		$last_sql_command_for___DB_OBJECT__ = "delete from __DELETE_TARGET_TABLE____WHERE__";
		$result = $__DB_OBJECT__->query($last_sql_command_for___DB_OBJECT__);
		if ($__DB_OBJECT__->errno != 0) {
			error_log("Error occured while executing SQL: " . $__DB_OBJECT__->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
