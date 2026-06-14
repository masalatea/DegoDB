<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DropboxOauth2StatusHashBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DropboxOauth2StatusHash.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DropboxOauth2StatusHash.php` and extend `DropboxOauth2StatusHashDBAccessBase` for project-specific customizations.

    class DropboxOauth2StatusHashDBAccess extends DropboxOauth2StatusHashDBAccessBase
    {
    }
}

?>
