	$already_inserted = false;
	$inserted_primary_key = "";
	if (property_exists($data, "InsertToken")) {
		$inserted_primary_key = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($data->InsertToken);
		if ($inserted_primary_key != "") {
			$already_inserted = true;
		}
	}
	if (!$already_inserted) {
		// == START OF EDITABLE AREA FOR CUSTOM ACTION AFTER CHECK INSERT TOKEN ==
		// == END OF EDITABLE AREA FOR CUSTOM ACTION AFTER CHECK INSERT TOKEN ==
