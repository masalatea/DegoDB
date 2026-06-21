		if (!$__SINGLE_RESULT_FOR_LIST__) {
	__ROLLBACK_TRANSACTION__
			print json_encode(array(
__RESULT_PARAM_FOR_LIST__
				"_status"=>"NGinServer",
				"Message"=>"Something wrong"
			));
			exit(0);
		}
