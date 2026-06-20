<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/data-DaCustomProxyBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('data-DaCustomProxy.php')) {
    // Generated wrapper entry for runtime data class.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/data-DaCustomProxy.php` and extend `DaCustomProxyDataBase` for project-specific customizations.

    class DaCustomProxyData extends DaCustomProxyDataBase
    {
	function IsLoginByLoginCookieToken()
	{
		switch($this->AuthType) {
			case DaCustomProxyAuthTypeEnum::$DEFAULT:
			case DaCustomProxyAuthTypeEnum::$PROJECTTOKEN:
			case DaCustomProxyAuthTypeEnum::$GETFUNC:
			case DaCustomProxyAuthTypeEnum::$PROJECTTOKENORGETFUNC:
			case DaCustomProxyAuthTypeEnum::$NOSECURITY:
			case DaCustomProxyAuthTypeEnum::$MANUAL:
				break;
			case DaCustomProxyAuthTypeEnum::$LOGINCOOKIETOKEN:
				return true;
			default:
				print "INTERNAL ERROR! Unknown Auth Type: " . $this->SingleProxy_AuthType . "\n";
		}
		return false;
	}
    }
}
// Memo: DaCustomProxyAuthTypeEnum must be same as DafuncSingleProxy_AuthTypeEnum


?>
