# 2026-05-25 Lab Swagger Browser CRUD Smoke

## 要約

- `mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js` を list-only smoke から拡張し、default で `lab_experiments` の CRUD cycle を実ブラウザで通すようにした。
- current lane は `named external source -> admin import -> canonical sync -> publish -> lab Swagger viewer -> Insert/Get/Update/Get/Delete/List` まで browser で再現できる。
- backward compatibility のために `--list-only` flag を追加し、旧来の list smoke だけにも戻せるようにした。

## 変更点

- browser smoke script に operation 実行 helper を追加した
- `lab_experiments` 専用 fixture を生成し、unique `experiment_key` と row name を使って smoke row を insert する
- `Getlab_experiments` で insert / update 結果を読み戻し、`Deletelab_experiments` 後の `Getlab_experimentsList` で row が消えていることを検証する
- artifact は各 operation ごとの request / response / screenshot を `result.json` に残す
- `--list-only` を付けた時は CRUD cycle を skip し、従来通り list operation だけを検証する

## 検証

- `node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js`
  - `ok=true`
  - temporary source: `ext_smoke_05250559156009`
  - browser artifact dir: `output/playwright/external-source-lab-swagger/20260525-145915`
  - latest publish:
    - `OPENAPI-JSON=20260525-055920-e5294a55`
    - `DBTABLE-PROXY-SERVER=20260525-055920-8bfecd56`
  - CRUD cycle:
    - inserted id: `3`
    - experiment key: `EXP-SWAGGER-1779688764397-62039`
    - final row count: `2`
    - final row names: `Bootstrap Health Check`, `Compare Output Prototype`
- `node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js --list-only`
  - `ok=true`
  - temporary source: `ext_smoke_05250559593982`
  - browser artifact dir: `output/playwright/external-source-lab-swagger/20260525-145959`
  - `crud_cycle.executed=false`
  - `crud_cycle.skipped_reason=list-only mode が指定されました。`

## 今の意味

- browser lane は単なる list read smoke ではなく、Swagger viewer 上の mutation request まで current local stack で保証できる段階に入った
- `lab_experiments` を基準に、Swagger Try It Out から runtime DB source を切り替えた stateful CRUD 実験ができる
- 次段は wording 整理、browser lane の CI 化、他 table / auth-required endpoint への横展開が現実的な候補になる
