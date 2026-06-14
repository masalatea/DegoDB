# 2026-05-20 daCustomProxy Method-And-Enum Sample

## 結論

- `sample20-dacustomproxy-method-and-enum` を追加し、`daCustomProxy` の no-top-level method-and-enum variant を fixture-based sample として固定した。
- 入力は `tests/fixtures/legacy-dbclasses/data-daCustomProxy.php` の curated copy を使い、`original-codes/` を test input に直接戻さない。
- これで method-and-enum lane は、`Req` の top-level helper あり representative に加えて、top-level helper なしの最小 variant でも durable reference compare を回せるようになった。

## 背景

- method-and-enum lane は件数が多いが、sample として固定されていた representative は `sample13-req-method-and-enum` だけだった。
- `Req` は `wrapper method + top-level helper + trailing enum` を持つ variant で、lane の一側面しか見ていない。
- 同 lane には `daCustomProxy` のような `wrapper method 1 件 + trailing enum 1 件 + top-level helper なし` の軽い亜種もあるため、2 段 rollout の「simple から先に固定する」方針に沿って次の representative に選んだ。

## 実装

- `tests/fixtures/legacy-dbclasses/data-daCustomProxy.php`
  - host-only `original-codes/` から必要最小限だけ curated copy を追加した。
- `mtool/scripts/lib/sample20_dacustomproxy_method_and_enum_output_check.php`
- `mtool/scripts/check_sample20_dacustomproxy_method_and_enum_outputs.php`
- `tests/Integration/Sample20DaCustomProxyMethodAndEnumOutputTest.php`
  - file-based sample の output generate / reference compare を追加した。
- `sample/sample20-dacustomproxy-method-and-enum/`
  - README と durable reference を追加した。
- `tests/bootstrap.php`
- `Makefile`
- `tests/README.md`
- `sample/README.md`
- `docs/internal/runtime-architecture.md`
- `docs/reports/2026/2026-0520-resume-prompt.md`
  - sample20 と最新 test count を current gate / coverage に反映した。

## 検証

- `php mtool/scripts/check_sample20_dacustomproxy_method_and_enum_outputs.php`
- `make test`
  - `52 tests / 1144 assertions`

## 次

1. method-and-enum lane は `top-level helper あり` と `なし` の representative が揃ったので、必要なら次は `Project` / `ProjectSourceOutput` の multi-method 側を足す
2. あるいは sample 拡張をいったん止めて、host-only helper inventory / archive readiness の整理に戻る
3. runtime promote は引き続き sample gate 緑化後にだけ行い、`original-codes/` は host-only reference のまま維持する
