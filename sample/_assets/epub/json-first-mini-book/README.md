# JSON-first Mini Book EPUB Fixture

This directory contains a tiny EPUB fixture for the ebook CMS sample lane.

- EPUB fixture: `json-first-mini-book.epub`
- source/provenance tree: `source/`
  - kept only so reviewers can inspect the EPUB contents and copyright boundary
  - not used as a sample runtime input
  - not presented as an EPUB creation workflow
- media type: `application/epub+zip`
- size: `3125` bytes
- sha256: `6b52e37129d9f01097da7e9b598b0e06d60a5b8e3b4126870c799cdc6c1dd5ea`
- copyright note: all text in this fixture was written specifically for this repository sample. No third-party book text is included.

Purpose:

- provide a real `.epub` file for media metadata and reader display samples
- keep EPUB display / download visible without adding EPUB generation to Mtool samples
- allow later samples to record URL, MIME type, file size, checksum, version, and updated time for an EPUB asset

Out of scope:

- EPUB generation
- cover generation
- validation beyond simple ZIP/EPUB structure
- copyrighted or public-domain third-party text imports
