<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DaCustomProxyFuncBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DaCustomProxyFunc.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DaCustomProxyFunc.php` and extend `DaCustomProxyFuncDBAccessBase` for project-specific customizations.

    class DaCustomProxyFuncDBAccess extends DaCustomProxyFuncDBAccessBase
    {
    }
}

?>
