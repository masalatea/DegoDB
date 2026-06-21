function CheckSecurityByGetFunc($data)
{
	$SecurityCheckResult = false;
	$DA__CLASS_BASE_NAME_FOR_CHECKING_TOKEN__ = new __CLASS_BASE_NAME_FOR_CHECKING_TOKEN__DBAccess();
	$result = $DA__CLASS_BASE_NAME_FOR_CHECKING_TOKEN__->__FUNCTION_NAME_FOR_CHECKING_TOKEN__(
__REQUEST_PARAMS_FOR_CHECKING_TOKEN__
		);
	if ($result) {
		$SecurityCheckResult = true;
	}
	return $SecurityCheckResult;
}
