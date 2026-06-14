# 2026-05-21 Top-Level Sample README Catalog Guard

## 概要

- `sample/README.md` を active sample pack catalog の入口として扱う前提を明文化し、その内容を `SamplePackCatalogTest` で固定した。
- top-level README から `SamplePackCatalogTest` / `LegacyProjectSampleCatalogTest` の両方へ辿れるようにした。
- これにより、category README だけでなく `sample/README.md` でも current sample pack inventory と test gate の対応が読めるようになった。

## 変更内容

- `sample/README.md`
  - `catalog guard` 節を追加し、`SamplePackCatalogTest` と `LegacyProjectSampleCatalogTest` の役割を記載した。
- `tests/Integration/SamplePackCatalogTest.php`
  - top-level `sample/README.md` が
    - `sample/patterns/`
    - `sample/legacy-projects/`
    - `sample/_pack-support/`
    - current active pack 名一覧
    - catalog guard test 名
    を current catalog と一致させていることを検証する test を追加した。

## 背景

- category split 後は `sample/patterns/README.md` と `sample/legacy-projects/README.md` が入口として機能していたが、top-level `sample/README.md` も active inventory を一覧するため、ここが drift すると全体像が再び読みづらくなる。
- sample/test 整理を継続するなら、top-level README も current catalog から逸脱しないように test で固定しておく方が自然だった。

## 検証

- `docker compose -f compose.yaml -f sample/patterns/sample1-simple-table/compose.yaml exec -T web-admin phpunit --configuration /var/www/tests/phpunit.xml /var/www/tests/Integration/SamplePackCatalogTest.php /var/www/tests/Integration/LegacyProjectSampleCatalogTest.php`
  - `7 tests / 365 assertions`

## 補足

- この slice は doc/test の contract 強化であり、runtime generator や sample seed の意味論は変更していない。
