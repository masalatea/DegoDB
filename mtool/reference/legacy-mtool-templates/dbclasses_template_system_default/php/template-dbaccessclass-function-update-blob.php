	public function __FUNCTION_NAME__(__PARAMS__)
	{
		global $__DB_OBJECT__, $last_sql_command_for___DB_OBJECT__;
		connect___DB_OBJECT___if_not_yet();
		reconnect___DB_OBJECT___if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ ==
		// == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ ==
		
		$last_sql_command_for___DB_OBJECT__ = "update __UPDATE_TARGET_TABLE__ SET __SET____WHERE__";
		
		$stmt = $__DB_OBJECT__->prepare($last_sql_command_for___DB_OBJECT__);
		$dummy_for_ref = NULL;
		$stmt->bind_param("b", $dummy_for_ref);
		$fp = fopen(__PARAM_FOR_FILE__, "r");
		while (!feof($fp)) {
			$stmt->send_long_data(0, fread($fp, 8192));
		}
		fclose($fp);
		$result = $stmt->execute();
		
		if ($__DB_OBJECT__->errno != 0) {
			error_log("Error occured while executing SQL: " . $__DB_OBJECT__->error . " in " . __FILE__ . " on line " . __LINE__);
		}
		return $result;
	}
