<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-daCustomProxyBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-daCustomProxy.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-daCustomProxy.php` and extend `daCustomProxyDataBase` for project-specific customizations.

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
}
// Memo: daCustomProxyAuthTypeEnum must be same as dafuncSingleProxy_AuthTypeEnum


?>
