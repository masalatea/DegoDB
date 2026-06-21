		$inserted_primary_key = $__DB_OBJECT__->insert_id;
		if (property_exists($data, "InsertToken")) {
			SetPrimaryKeyValueForInsertTokenForThisHost($data->InsertToken, $inserted_primary_key);
		}
