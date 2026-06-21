	function __FUNCTION_NAME__ByProxy($param /* __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams class object */)
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => "__BASE_URL____REQUEST_URL__",
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
				),
			CURLOPT_POSTFIELDS => json_encode(array(
					// == START OF EDITABLE AREA FOR JSON PARAMETER IN __FUNCTION_NAME__ByDBConnectionOrProxy ==
					// == END OF EDITABLE AREA FOR JSON PARAMETER IN __FUNCTION_NAME__ByDBConnectionOrProxy ==
__REQUEST_PARAMS_FOR_JQUERY__
				)),
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($http_code != 200) {
			// Error Occured
			error_log("Error! http code: " . $http_code . " while requesting __BASE_URL____REQUEST_URL__");
		} else {
			// Success
			$json_response = json_decode($response);
			if ($json_response->_status == "OK") {
				if (property_exists($json_response, "Result")) {
					return $json_response->Result;
				} else {
					return $json_response;
				}
			} else {
				error_log("Error! Result: " . $json_response->_status . " : " . $json_response->Message);
			}
		}
		return false;
	}

