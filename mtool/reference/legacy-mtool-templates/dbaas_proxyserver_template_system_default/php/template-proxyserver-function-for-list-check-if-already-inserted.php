		if (property_exists($data->__LIST_OBJECT_NAME__[$index], "InsertToken")) {
			$inserted_primary_key = GetPrimaryKeyValueIfAlreadyInsertedForThisHost($data->__LIST_OBJECT_NAME__[$index]->InsertToken);
			if ($inserted_primary_key != "") {
				array_push($insert_id__RESULT_NO__, $inserted_primary_key);
				continue;
			}
			// == START OF EDITABLE AREA FOR CUSTOM ACTION AFTER CHECK INSERT TOKEN FOR STEP__STEP_NO__ IN LOOP ==
			// == END OF EDITABLE AREA FOR CUSTOM ACTION AFTER CHECK INSERT TOKEN FOR STEP__STEP_NO__ IN LOOP ==
		}
