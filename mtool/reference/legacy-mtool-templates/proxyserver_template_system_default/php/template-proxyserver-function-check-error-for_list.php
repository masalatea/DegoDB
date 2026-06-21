		if ($__DB_OBJECT__->errno != 0) {
	__ROLLBACK_TRANSACTION__
			print json_encode(array(
__RESULT_PARAM_FOR_LIST__
				"_status"=>"NGinServer",
				"Message"=>$__DB_OBJECT__->error
			));
			exit(0);
		}
