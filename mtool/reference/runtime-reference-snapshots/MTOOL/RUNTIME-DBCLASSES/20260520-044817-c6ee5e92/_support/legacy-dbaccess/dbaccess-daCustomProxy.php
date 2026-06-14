<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-daCustomProxyBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-daCustomProxy.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-daCustomProxy.php` and extend `daCustomProxyDBAccessBase` for project-specific customizations.

    class daCustomProxyDBAccessLegacy extends daCustomProxyDBAccessBase
    {
    }
}

?>
