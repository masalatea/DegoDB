	public function __FUNCTION_NAME__(__PARAMS__)
	{
		global $__DB_OBJECT__, $last_sql_command_for___DB_OBJECT__;
		connect___DB_OBJECT___if_not_yet();
		reconnect___DB_OBJECT___if_necessary();
		
		// == START OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ ==
		// == END OF EDITABLE AREA FOR FUNCTION __FUNCTION_NAME__ ==
		
		$last_sql_command_for___DB_OBJECT__ = "select __SELECT_COLUMNS__ from __SELECT_FROM____WHERE____GROUP_BY____HAVING__";
		$ret = $__DB_OBJECT__->query($last_sql_command_for___DB_OBJECT__);
		if ($__DB_OBJECT__->errno != 0) {
			error_log("Error occured while executing SQL: " . $__DB_OBJECT__->error . " in " . __FILE__ . " on line " . __LINE__);
			return $ret;
		}
		while($thisline=$ret->fetch_row()) {
			$thisresult = new __DATA_CLASS_NAME__();
__STORE_DATA_CODE__
			return $thisresult;
		}
		return NULL;
	}
