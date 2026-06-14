# 2026-05-20 Provenance Metadata Wording Cleanup

## 結論

- `source_dump_path` と `bootstrap-reference` は、この時点では rename しない。
- 代わりに current code / prompt / report で、「それらは historical / provenance metadata であり、runtime input path ではない」と読むための wording を補強した。
- これにより boundary 誤読を減らしつつ、DB row や manifest schema を巻き込む rename を後段へ送った。

## 背景

- `original-codes/` の Docker/runtime/test 依存はすでに外れており、残る参照は host-side helper と provenance metadata にほぼ絞られていた。
- ただし `source_dump_path=original-codes/mtool.sql` や `origin.type=bootstrap-reference` という historical label は、文脈なしだと「まだ current runtime が使う path/value」に見えやすい。
- 一方で、`bootstrap-reference` は HTML / LanguageResource bootstrap row の `source_of_truth` としても残っており、軽い気持ちで rename すると DB row、manifest、docs の広い同期が必要になる。

## 実装

- `mtool/app/project_language_resource_catalog_loader.php`
  - copied legacy reference / file catalog の notes を provenance-only 前提の文言へ更新した。
- `mtool/app/project_html_repository.php`
  - HTML bootstrap reference note を provenance-only 前提の文言へ更新した。
- `mtool/app/html_template_repository.php`
  - `bootstrap-reference` note を provenance-only metadata と読める文言へ更新した。
- `mtool/app/language_resource_file_catalog.php`
  - `origin.source_dump_path` は host-side provenance であり runtime open path ではないことを inline comment で明示した。
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - 最新の再開 prompt を追加し、`legacy_dbclasses_path` を含む stale grep を外した。
- `docs/reports/2026/README.md`
  - 本記録と新しい resume prompt を index へ追加した。

## 判断

- 今回は schema key や stored value の rename ではなく、読み方の明文化に留めた。
- 先に wording を揃えることで、「runtime は `original-codes/` を読まない」という前提を崩さずに現在の storage 形を維持できる。
- 将来 rename するなら、manifest / DB row / exported catalog / docs を一括で動かす dedicated migration として扱うべきである。

## 検証

- `php -l mtool/app/project_language_resource_catalog_loader.php`
- `php -l mtool/app/project_html_repository.php`
- `php -l mtool/app/html_template_repository.php`
- `php -l mtool/app/language_resource_file_catalog.php`
- `make test`
- `make mtool-self-loop-check`

## 次

- `bootstrap-reference` や `source_dump_path` を本当に rename する必要があるかは、DB row / manifest migration を許容する段で再判断する。
- その前に進めるなら、`bootstrap_dbclasses.sh` を archive へ退避できる条件整理のほうが優先度は高い。
