<?php

declare(strict_types=1);

$mtooldb = null;
$last_sql_command_for_mtooldb = '';
$time_for_reconnect_mtooldb_if_necessary = time();

require_once __DIR__ . '/_support/mtool_runtime_db.php';
require_once __DIR__ . '/data-EbookEditorChapter.php';
require_once __DIR__ . '/dbaccess-EbookEditorChapter.php';
