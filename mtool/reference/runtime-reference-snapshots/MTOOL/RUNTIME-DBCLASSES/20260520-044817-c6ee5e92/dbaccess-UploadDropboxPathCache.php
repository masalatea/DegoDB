<?php

require_once __DIR__ . '/_runtime_loader.php';
require_once __DIR__ . '/base/dbaccess-UploadDropboxPathCacheBase.php';

if (!mtool_runtime_bundle_load_custom_wrapper('dbaccess-UploadDropboxPathCache.php')) {
    // Generated wrapper entry for runtime DB Access.
    // Override `mtool/extensions/MTOOL/RUNTIME-DBCLASSES/dbaccess-UploadDropboxPathCache.php` and extend `UploadDropboxPathCacheDBAccessBase` for project-specific customizations.

    class UploadDropboxPathCacheDBAccess extends UploadDropboxPathCacheDBAccessBase
    {
    }
}

?>
