	function __FUNCTION_NAME__ByDBConnectionOrProxy($param /* __CLASS_BASE_NAME__Proxy__FUNCTION_NAME__RequestParams class object */)
	{
		global $__DB_OBJECT__;
		if (isset($__DB_OBJECT__) && $__DB_OBJECT__) {
			// == START OF EDITABLE AREA FOR DB CALL IN __FUNCTION_NAME__ByDBConnectionOrProxy ==
			// == END OF EDITABLE AREA FOR DB CALL IN __FUNCTION_NAME__ByDBConnectionOrProxy ==
__DB_CALL__
		}
		return $this->__FUNCTION_NAME__ByProxy($param);
	}
