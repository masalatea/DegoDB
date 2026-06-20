<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DaCustomProxySourceOutputTargetBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DaCustomProxySourceOutputTarget.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DaCustomProxySourceOutputTarget.php` and extend `DaCustomProxySourceOutputTargetDBAccessBase` for project-specific customizations.

    class DaCustomProxySourceOutputTargetDBAccess extends DaCustomProxySourceOutputTargetDBAccessBase
    {
    }
}

?>
