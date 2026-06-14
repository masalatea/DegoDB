# 2026-05-20 BuildSourceFuncCache Companion Declarations Sample

## 結論

- `sample14-buildsourcefunccache-companion-declarations` を追加し、`BuildSourceFuncCache` の companion declarations 3-class variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-BuildSourceFuncCache.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで companion-declarations lane は既存の `CompareOutput` 2-class variant に加えて、複数 helper + 2 trailing enum を持つ 3-class variant でも reference compare を回せるようになった。

## 背景

- current rollout では plain DTO `63/63` がすでに direct replacement 済みで、残件は non-plain `data-*` の sample gate 拡張である。
- companion-declarations lane には `CompareOutput` のような 2-class variant だけでなく、`BuildSourceFuncCache` のような 3-class + multi-helper variant も残っている。
- `Sample10CompareOutputCompanionDeclarationsOutputTest.php` は lane の入口として有効だが、この亜種までは固定していなかった。

## 実装

- `tests/fixtures/legacy-dbclasses/data-BuildSourceFuncCache.php`
  - host-only `original-codes/` から必要最小限だけ curated copy を追加した。
- `mtool/scripts/lib/sample14_buildsourcefunccache_companion_declarations_output_check.php`
- `mtool/scripts/check_sample14_buildsourcefunccache_companion_declarations_outputs.php`
- `tests/Integration/Sample14BuildSourceFuncCacheCompanionDeclarationsOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample14-buildsourcefunccache-companion-declarations/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample14 を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample14_buildsourcefunccache_companion_declarations_outputs.php`
- `make test`

## 次

1. companion-declarations lane の no-top-level variant も必要なら `BuildLog` または `LiveCheckResult` で追加する
2. method-and-enum / top-level-declaration lane も同様に representative sample を少しずつ増やす
3. self-generated runtime への promote は引き続き sample gate 緑化後にだけ行う
