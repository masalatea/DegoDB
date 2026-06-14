# 2026-05-20 BuildLog Companion Declarations Sample

## 結論

- `sample15-buildlog-companion-declarations` を追加し、`BuildLog` の no-top-level companion declarations variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-BuildLog.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで companion-declarations lane は、top-level helper ありの `CompareOutput` / `BuildSourceFuncCache` だけでなく、top-level helper なしの enum companion variant でも reference compare を回せるようになった。

## 背景

- `sample14-buildsourcefunccache-companion-declarations` で 3-class + multi-helper variant は固定できたが、同じ lane には `BuildLog` / `LiveCheckResult` のような no-top-level variant も残っている。
- `BuildLog` は `class_count=2`、`has_top_level_function=false`、trailing enum 1 件という最小亜種なので、incremental に coverage を広げる次の対象として扱いやすい。

## 実装

- `tests/fixtures/legacy-dbclasses/data-BuildLog.php`
  - host-only `original-codes/` から必要最小限だけ curated copy を追加した。
- `mtool/scripts/lib/sample15_buildlog_companion_declarations_output_check.php`
- `mtool/scripts/check_sample15_buildlog_companion_declarations_outputs.php`
- `tests/Integration/Sample15BuildLogCompanionDeclarationsOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample15-buildlog-companion-declarations/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample15 を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample15_buildlog_companion_declarations_outputs.php`
- `make test`

## 次

1. companion-declarations lane の 3-class no-top-level variant も必要なら `LiveCheckResult` で追加する
2. method-and-enum / top-level-declaration lane も同様に representative sample を少しずつ増やす
3. self-generated runtime への promote は引き続き sample gate 緑化後にだけ行う
