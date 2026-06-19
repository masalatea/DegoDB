<?php

declare(strict_types=1);

$pageTitle = 'Sample20 Content Publishing';
$pageBody = 'This sample turns a JSON-first content idea into public article DataClass, DBAccess, HTML, and OpenAPI artifacts. Draft articles stay out of the public surface.';

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
