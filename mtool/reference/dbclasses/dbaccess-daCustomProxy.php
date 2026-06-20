<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DaCustomProxyBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DaCustomProxy.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DaCustomProxy.php` and extend `DaCustomProxyDBAccessBase` for project-specific customizations.

    class DaCustomProxyDBAccess extends DaCustomProxyDBAccessBase
    {
    }
}

?>
