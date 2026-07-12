# Shared Relation Metadata And Sample22 First Slice

## 実装

- shared contract fieldへ後方互換なoptional relation metadataを追加した。
- `belongs_to`、target contract、key/label field、parent/lookup role、required policyをfirst-classで保存・取得する。
- 完全指定または全空を要求し、unsupported value、partial metadata、unknown target contract/fieldをfail closedにした。
- manifest、screen definition、runtime previewへrelation blockを伝播する。
- target contract preview rowsからkey/label lookup optionsを構築し、ready/unavailable stateとoption sourceを公開する。
- generated form HTMLへrelation/lookupのstable data markerを追加した。

## Sample22

- `ebook_workflow_book`をread-only lookup source contractとして追加した。
- `ebook_workflow_published_chapter.book_id`をrequired `belongs_to` parent relationとして追加した。
- `NO-CODE-RUNTIME` Source Outputを追加した。
- list/detail/formはread-only、managed mutation operationと実行actionはゼロを維持した。
- 既存OpenAPI/editor DBAccessを置換していない。

## Verification

- PHP syntax checks: pass
- `make sample22-pack-runtime-test`: 1 test / 22 assertions
- `make test`: 466 tests / 14,161 assertions / 1 skipped
- `git diff --check`: pass

Status: `FIRST_SLICE_DONE`
