<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-DropboxUploadTokenBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-DropboxUploadToken.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-DropboxUploadToken.php` and extend `DropboxUploadTokenDBAccessBase` for project-specific customizations.

    class DropboxUploadTokenDBAccessLegacy extends DropboxUploadTokenDBAccessBase
    {
    }
}

?>
