<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-daCustomProxyFuncBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-daCustomProxyFunc.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-daCustomProxyFunc.php` and extend `daCustomProxyFuncDBAccessBase` for project-specific customizations.

    class daCustomProxyFuncDBAccess extends daCustomProxyFuncDBAccessBase
    {
    }
}

?>
