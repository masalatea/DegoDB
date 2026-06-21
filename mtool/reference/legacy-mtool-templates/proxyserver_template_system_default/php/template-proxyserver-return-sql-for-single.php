	$request_header_list = apache_request_headers();
	if (array_key_exists('Matsuesoft-SQL-Output', $request_header_list)) {
		if (preg_match("/Yes/i", $request_header_list['Matsuesoft-SQL-Output'])) {
			$json_result["SQL [FYI][For Debug Only]"] = $last_sql_command_for___DB_OBJECT__;
		}
	}
