# 2026-05-20 LiveCheckResult Companion Declarations Sample

## 結論

- `sample16-livecheckresult-companion-declarations` を追加し、`LiveCheckResult` の 3-class no-top-level companion declarations variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-LiveCheckResult.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで companion-declarations lane は、top-level helper ありの variant と no-top-level 2-class variant に加えて、no-top-level 3-class variant でも reference compare を回せるようになった。

## 背景

- `sample15-buildlog-companion-declarations` で no-top-level companion declarations の最小 variant は固定できた。
- ただし同じ lane には `LiveCheckResult` のような `class_count=3` かつ trailing enum 2 件の亜種も残っている。
- lane coverage を少しずつ埋める方針に沿って、次の representative として `LiveCheckResult` を追加した。

## 実装

- `tests/fixtures/legacy-dbclasses/data-LiveCheckResult.php`
  - host-only `original-codes/` から必要最小限だけ curated copy を追加した。
- `mtool/scripts/lib/sample16_livecheckresult_companion_declarations_output_check.php`
- `mtool/scripts/check_sample16_livecheckresult_companion_declarations_outputs.php`
- `tests/Integration/Sample16LiveCheckResultCompanionDeclarationsOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample16-livecheckresult-companion-declarations/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/generated-code-strategy.md`
- `docs/internal/runtime-architecture.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample16 を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample16_livecheckresult_companion_declarations_outputs.php`
- `make test`

## 次

1. companion-declarations lane は主要亜種がかなり埋まったので、次は別 lane の representative sample を増やす
2. method-and-enum / top-level-declaration lane も同様に少しずつ coverage を広げる
3. self-generated runtime への promote は引き続き sample gate 緑化後にだけ行う
