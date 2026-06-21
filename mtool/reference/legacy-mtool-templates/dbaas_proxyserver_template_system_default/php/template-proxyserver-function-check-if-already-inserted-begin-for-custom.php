	$already_inserted = false;
	if (property_exists($data->step__RESULT_NO__, "InsertToken")) {
		$inserted_primary_key = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($data->step__RESULT_NO__->InsertToken);
		if ($inserted_primary_key != "") {
			$insert_id__RESULT_NO__ = $inserted_primary_key;
			$already_inserted = true;
		}
	}
	if (!$already_inserted) {
