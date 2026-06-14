<?php

// Generated wrapper entry migrated from a legacy data class.
// Keep custom properties, helper methods, and top-level helpers here.

require_once __DIR__ . '/base/data-daCustomProxyBase.php';

class daCustomProxyData extends daCustomProxyDataBase
{
	function IsLoginByLoginCookieToken()
	{
		switch($this->AuthType) {
			case daCustomProxyAuthTypeEnum::$DEFAULT:
			case daCustomProxyAuthTypeEnum::$PROJECTTOKEN:
			case daCustomProxyAuthTypeEnum::$GETFUNC:
			case daCustomProxyAuthTypeEnum::$PROJECTTOKENORGETFUNC:
			case daCustomProxyAuthTypeEnum::$NOSECURITY:
			case daCustomProxyAuthTypeEnum::$MANUAL:
				break;
			case daCustomProxyAuthTypeEnum::$LOGINCOOKIETOKEN:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Auth Type: " . $this->SingleProxy_AuthType . "\n";
		}
		return false;
	}
}
// Memo: daCustomProxyAuthTypeEnum must be same as dafuncSingleProxy_AuthTypeEnum

?>