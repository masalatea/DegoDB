<?php

declare(strict_types=1);

$pageTitle = 'Sample18 Mini Task Board';
$pageBody = 'Instruction-driven demo that publishes TaskCard DataClass, DBAccess, HTML, and OpenAPI artifacts from one small project.';
$columns = ['Title', 'Status', 'Assigned To', 'Priority', 'Due Date'];
$rows = [
    ['Define first demo prompt', 'doing', 'Alice', '30', '2026-06-19'],
    ['Create TaskCard metadata', 'todo', 'Bob', '20', '2026-06-20'],
    ['Publish reference outputs', 'todo', 'Chris', '10', '2026-06-21'],
];

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body {
            color: #202124;
            font-family: Arial, sans-serif;
            margin: 2rem;
        }

        main {
            max-width: 880px;
        }

        table {
            border-collapse: collapse;
            margin-top: 1.5rem;
            width: 100%;
        }

        th,
        td {
            border-bottom: 1px solid #d7dce2;
            padding: 0.65rem 0.75rem;
            text-align: left;
        }

        th {
            background: #f3f6f8;
        }
    </style>
</head>
<body>
    <main>
        <h1><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p><?php echo htmlspecialchars($pageBody, ENT_QUOTES, 'UTF-8'); ?></p>
        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?php echo htmlspecialchars($column, ENT_QUOTES, 'UTF-8'); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>

