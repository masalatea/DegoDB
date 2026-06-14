# 2026-05-20 ProjectSourceOutput Method-And-Enum Sample

## 結論

- `sample22-projectsourceoutput-method-and-enum` を追加し、`ProjectSourceOutput` の heavy multi-method + top-level helper + enum variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-ProjectSourceOutput.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで method-and-enum lane は、`Req` の top-level helper あり representative、`daCustomProxy` の no-top-level 最小 representative、`Project` の multi-method representative に加えて、`ProjectSourceOutput` の heavy variant でも durable reference compare を回せるようになった。

## 背景

- method-and-enum lane は `Req` / `daCustomProxy` / `Project` までで代表性はかなり上がっていたが、class 数と trailing enum 数がさらに多い重い variant はまだ sample 固定していなかった。
- `ProjectSourceOutput` は `class_count=6`、wrapper method `11` 件、trailing enum `5` 件、top-level helper ありで、lane の heavy 側 representative として適している。
- 2 段 rollout の方針上、こうした complex/new form は runtime へ広げる前に focused sample gate を緑化しておく方が安全である。

## 実装

- `tests/fixtures/legacy-dbclasses/data-ProjectSourceOutput.php`
  - host-only `original-codes/mtool_lib/dbclasses/data-ProjectSourceOutput.php` から必要最小限だけ curated copy を追加した。
- `mtool/scripts/lib/sample22_projectsourceoutput_method_and_enum_output_check.php`
- `mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php`
- `tests/Integration/Sample22ProjectSourceOutputMethodAndEnumOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample22-projectsourceoutput-method-and-enum/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/runtime-architecture.md`
- `docs/internal/generated-code-strategy.md`
  - current sample gate / coverage に sample22 を反映した。

## 検証

- `php mtool/scripts/check_sample22_projectsourceoutput_method_and_enum_outputs.php`
  - pass
- `make test`
  - Docker crash により未完了。post-sample22 の full rerun は Docker 復旧後に再実行する。
  - sample22 直前の最後に確認できた full suite は `53 tests / 1150 assertions`。

## 次

1. Docker 復旧後に `make test` を再実行し、sample22 反映後の最新 count を確定する
2. runtime generator 本体は変えていないため、必要なら next は host-only helper inventory 整理か別 lane の sample gate 拡張へ戻る
3. `original-codes/` は引き続き host-only reference のまま維持し、fixture が必要な場合だけ `tests/fixtures/legacy-dbclasses/` へ最小限コピーする
