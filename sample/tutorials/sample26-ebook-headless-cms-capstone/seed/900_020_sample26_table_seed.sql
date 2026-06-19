DROP TABLE IF EXISTS EbookCmsChapter;
DROP TABLE IF EXISTS EbookCmsBook;

CREATE TABLE EbookCmsBook (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(160) NOT NULL,
    Slug VARCHAR(120) NOT NULL,
    AuthorName VARCHAR(120) NOT NULL,
    GenreName VARCHAR(80) NOT NULL,
    Status VARCHAR(40) NOT NULL,
    CoverImageUrl VARCHAR(255) NOT NULL,
    Summary TEXT NOT NULL,
    EpubDownloadUrl VARCHAR(255) NOT NULL,
    EpubMimeType VARCHAR(120) NOT NULL,
    EpubSha256 VARCHAR(64) NOT NULL,
    PublishedAt DATETIME NULL,
    UpdatedAt DATETIME NOT NULL,
    UNIQUE KEY uq_ebook_cms_book_slug (Slug)
);

CREATE TABLE EbookCmsChapter (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    EbookCmsBookId INT NOT NULL,
    BookSlug VARCHAR(120) NOT NULL,
    ChapterTitle VARCHAR(160) NOT NULL,
    ChapterSlug VARCHAR(120) NOT NULL,
    Status VARCHAR(40) NOT NULL,
    SpineOrder INT NOT NULL,
    BodyMarkdown TEXT NOT NULL,
    PublishedAt DATETIME NULL,
    UpdatedAt DATETIME NOT NULL,
    UNIQUE KEY uq_ebook_cms_chapter_slug (BookSlug, ChapterSlug),
    KEY idx_ebook_cms_chapter_book_id (EbookCmsBookId)
);

INSERT INTO EbookCmsBook (
    Title,
    Slug,
    AuthorName,
    GenreName,
    Status,
    CoverImageUrl,
    Summary,
    EpubDownloadUrl,
    EpubMimeType,
    EpubSha256,
    PublishedAt,
    UpdatedAt
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

INSERT INTO EbookCmsChapter (
    EbookCmsBookId,
    BookSlug,
    ChapterTitle,
    ChapterSlug,
    Status,
    SpineOrder,
    BodyMarkdown,
    PublishedAt,
    UpdatedAt
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
