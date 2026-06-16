<?php

declare(strict_types=1);

$pageTitle = 'Sample11 HTML Template Output';
$pageBody = 'This page is a minimal html-module-catalog output generated from curated current metadata.';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body>
    <main>
        <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($pageBody, ENT_QUOTES, 'UTF-8'); ?></p>
    </main>
</body>
</html>
