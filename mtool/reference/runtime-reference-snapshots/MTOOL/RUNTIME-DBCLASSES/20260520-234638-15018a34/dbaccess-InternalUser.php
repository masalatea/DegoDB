<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-InternalUserBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-InternalUser.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-InternalUser.php` and extend `InternalUserDBAccessBase` for project-specific customizations.

    class InternalUserDBAccess extends InternalUserDBAccessBase
    {
    }
}

?>
