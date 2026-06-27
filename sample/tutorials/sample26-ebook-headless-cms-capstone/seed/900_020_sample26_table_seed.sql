DROP TABLE IF EXISTS ebook_cms_chapter;
DROP TABLE IF EXISTS ebook_cms_book;

CREATE TABLE ebook_cms_book (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    author_name VARCHAR(120) NOT NULL,
    genre_name VARCHAR(80) NOT NULL,
    status VARCHAR(40) NOT NULL,
    cover_image_url VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    epub_download_url VARCHAR(255) NOT NULL,
    epub_mime_type VARCHAR(120) NOT NULL,
    epub_sha256 VARCHAR(64) NOT NULL,
    published_at DATETIME NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_ebook_cms_book_slug (slug)
);

CREATE TABLE ebook_cms_chapter (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ebook_cms_book_id INT NOT NULL,
    book_slug VARCHAR(120) NOT NULL,
    chapter_title VARCHAR(160) NOT NULL,
    chapter_slug VARCHAR(120) NOT NULL,
    status VARCHAR(40) NOT NULL,
    spine_order INT NOT NULL,
    body_markdown TEXT NOT NULL,
    published_at DATETIME NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_ebook_cms_chapter_slug (book_slug, chapter_slug),
    KEY idx_ebook_cms_chapter_book_id (ebook_cms_book_id)
);

INSERT INTO ebook_cms_book (
    title,
    slug,
    author_name,
    genre_name,
    status,
    cover_image_url,
    summary,
    epub_download_url,
    epub_mime_type,
    epub_sha256,
    published_at,
    updated_at
) VALUES
(
    'JSONから始める電子書籍CMS',
    'json-first-ebook-cms',
    'Sample Editor',
    'Guide',
    'published',
    '/assets/sample26/covers/json-first-ebook-cms.png',
    'JSON first の見立てから、公開サイト、app API、編集 API までを Mtool で組み立てるサンプルです。',
    '/assets/epub/json-first-mini-book/json-first-mini-book.epub',
    'application/epub+zip',
    '6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea',
    '2026-06-19 09:00:00',
    '2026-06-19 09:30:00'
),
(
    '未公開の編集メモ',
    'draft-editor-notes',
    'Sample Editor',
    'Internal',
    'draft',
    '/assets/sample26/covers/draft-editor-notes.png',
    'public surface には出さない draft book です。',
    '',
    '',
    '',
    NULL,
    '2026-06-19 10:00:00'
);

INSERT INTO ebook_cms_chapter (
    ebook_cms_book_id,
    book_slug,
    chapter_title,
    chapter_slug,
    status,
    spine_order,
    body_markdown,
    published_at,
    updated_at
) VALUES
(
    1,
    'json-first-ebook-cms',
    'はじめに',
    'intro',
    'published',
    1,
    'JSON で考えた本の情報を、Mtool の sample では DB / API / HTML output に変換して見せます。',
    '2026-06-19 09:00:00',
    '2026-06-19 09:30:00'
),
(
    1,
    'json-first-ebook-cms',
    '編集 API',
    'editor-api',
    'draft',
    2,
    'この章は編集者向け API の更新・公開対象として seed しています。',
    NULL,
    '2026-06-19 10:10:00'
),
(
    2,
    'draft-editor-notes',
    '内部メモ',
    'internal-note',
    'draft',
    1,
    'draft book の章なので public surface には出ません。',
    NULL,
    '2026-06-19 10:15:00'
);
