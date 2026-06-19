<?php

declare(strict_types=1);

$bookTitle = 'JSONから始める電子書籍CMS';
$bookSlug = 'json-first-ebook-cms';
$authorName = 'Sample Editor';
$genreName = 'Guide';
$summary = 'JSONを入口に、電子書籍サイトとapp向けAPIを組み立てる小さなreader sampleです。';
$epubUrl = '/assets/epub/json-first-mini-book/json-first-mini-book.epub';
$epubSha256 = '6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea';
$chapters = [
    ['title' => 'はじめに', 'slug' => 'intro', 'body' => 'JSONから始める電子書籍CMSの導入章です。'],
    ['title' => '公開APIを考える', 'slug' => 'public-api', 'body' => '読者向けには公開済み章だけをspine順で返します。'],
];

?><!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($bookTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        :root {
            color-scheme: light;
            --ink: #242424;
            --muted: #5e6470;
            --line: #d7d2c8;
            --paper: #fbfaf7;
            --panel: #ffffff;
            --accent: #1f6f78;
            --accent-soft: #e7f2f1;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.7;
        }
        header, main {
            max-width: 960px;
            margin: 0 auto;
            padding: 24px;
        }
        header {
            border-bottom: 1px solid var(--line);
        }
        h1 {
            margin: 0 0 8px;
            font-size: clamp(28px, 5vw, 46px);
            line-height: 1.15;
        }
        h2 {
            margin: 0 0 12px;
            font-size: 22px;
        }
        .meta, .summary, .checksum {
            color: var(--muted);
        }
        .reader-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(240px, 1fr);
            gap: 20px;
            align-items: start;
        }
        section, aside {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
        }
        .chapter-list {
            display: grid;
            gap: 12px;
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
        }
        .chapter-list a, .download {
            color: var(--accent);
            font-weight: 700;
            text-decoration: none;
        }
        .chapter {
            margin-top: 20px;
        }
        .download-box {
            background: var(--accent-soft);
        }
        .checksum {
            overflow-wrap: anywhere;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
        }
        @media (max-width: 720px) {
            header, main {
                padding: 18px;
            }
            .reader-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <p class="meta">Public Reader / <?php echo htmlspecialchars($genreName, ENT_QUOTES, 'UTF-8'); ?></p>
        <h1><?php echo htmlspecialchars($bookTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="meta">by <?php echo htmlspecialchars($authorName, ENT_QUOTES, 'UTF-8'); ?> · slug: <?php echo htmlspecialchars($bookSlug, ENT_QUOTES, 'UTF-8'); ?></p>
        <p class="summary"><?php echo htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'); ?></p>
    </header>
    <main class="reader-grid">
        <section>
            <h2>Chapters</h2>
            <ul class="chapter-list">
                <?php foreach ($chapters as $chapter): ?>
                    <li>
                        <a href="#chapter-<?php echo htmlspecialchars($chapter['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ($chapters as $chapter): ?>
                <article class="chapter" id="chapter-<?php echo htmlspecialchars($chapter['slug'], ENT_QUOTES, 'UTF-8'); ?>">
                    <h2><?php echo htmlspecialchars($chapter['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars($chapter['body'], ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
            <?php endforeach; ?>
        </section>
        <aside class="download-box">
            <h2>EPUB Download</h2>
            <p><a class="download" href="<?php echo htmlspecialchars($epubUrl, ENT_QUOTES, 'UTF-8'); ?>">json-first-mini-book.epub</a></p>
            <p class="meta">MIME: application/epub+zip<br>Size: 3125 bytes<br>Version: sample-fixture-v1</p>
            <p class="checksum"><?php echo htmlspecialchars($epubSha256, ENT_QUOTES, 'UTF-8'); ?></p>
        </aside>
    </main>
</body>
</html>
