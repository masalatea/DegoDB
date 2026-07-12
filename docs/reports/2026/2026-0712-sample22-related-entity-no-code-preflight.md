# Sample22 Related-Entity No-Code Preflight

## 結論

Sample22はrelated-entity No Codeの代表候補として適切だが、seed追加だけでは完了しない。現在のshared contract field metadataにはrelation・lookupをfirst-classで表す項目がなく、generatorもそれをscreen fieldへ運ばない。`notes`の文字列解釈にはせず、optionalな共通metadata foundationを1つ追加してからSample22へ適用する。

## 既存evidence

- physical `ebook_workflow_chapter.ebook_workflow_book_id`は`ebook_workflow_book.id`へのFKを持つ。
- public read model `ebook_workflow_published_chapter`は`book_id`、`book_slug`、`chapter_id`、`chapter_slug`を持つ。
- list/detail DBAccessはbook slugをargument filterにし、published固定条件とspine順sortを持つ。
- editor DBAccessはdraft insert/update/order/publishを持つが、このsliceでは実行しない。
- Sample22にはまだshared contract、managed operation、`NO-CODE-RUNTIME` Source Outputがない。

## 現行gap

`project_shared_contract_fields`が保持するのはfield name、sync/operation/no-code/app-persistence role、notesだけであり、次を宣言できない。

- relation kind
- target contract
- target key field
- target label field
- lookup presentation
- parent binding required/fail-closed policy

DataClass referenceやDBAccess whereから推測できる場合でも、No Code UIのlabel・selection・parent ownershipまでは一意に決まらない。generatorは推測せず、明示metadataを優先する。

## #791 Contract

### Optional shared-contract field relation metadata

shared contract fieldへ後方互換なoptional metadataを追加する。

- `relation_kind`: initial allowed value `belongs_to`
- `relation_contract_key`: 同一project内のtarget shared contract
- `relation_key_field`: target contractのkey field
- `relation_label_field`: target contractの表示field
- `relation_ui_role`: initial allowed values `parent`, `lookup`
- `relation_required`: boolean

空値は従来のscalar fieldとして扱う。部分指定、unknown contract/field、self-invalid relationはfail closedにする。notes parsingは行わない。

### Generated artifact

`screen-definition.json`、`runtime-preview.json`、HTML field markerへ正規化したrelation blockを運ぶ。lookup fieldはtarget keyをvalue、target labelをcaptionとして扱う。read-only list/detailではkeyだけでなくlabelを表示でき、formではlookup metadataとoption source boundaryを表示する。ただしmutation submitは追加しない。

### Sample22 contracts

1. `ebook_workflow_book`
   - key: `id`
   - label: `title`
   - lookup source用のread-only contract
2. `ebook_workflow_published_chapter`
   - key: `chapter_id`
   - parent/lookup field: `book_id -> ebook_workflow_book.id`
   - list/detail/formはread-only
   - `book_slug`はvisible context fieldとして残す

Sample22へ`NO-CODE-RUNTIME`を追加するが、既存OpenAPI/editor DBAccessやcurated/custom editor UIは置換しない。managed mutation operation、publish action、chapter reorder actionは追加しない。

## Test Exit

- config DB bootstrap/repository/bundleがoptional relation metadataをround-tripする。
- legacy/既存shared contractはrelation blockなしで同一出力を維持する。
- invalid/partial relation metadataはstable errorでfail closedする。
- Sample22 artifactが2 contractとbook/chapter relationを持つ。
- generated list/detailがbook labelを表示し、formがlookup option source・required markerを持つ。
- missing parent/unknown optionはfail closedまたはunavailable reasonを表示する。
- fast PHPUnit JSON/DOM contractをprimary gateとし、browser smokeはfirst sliceでは必須にしない。
- mutation、generated executable button、既存route replacementはゼロ。

## Estimate

1.5 - 2.5 days。config schema・repository・bundle・generator・Sample22 seed/testを同じsemantic implementation commitに含める。

Status: `DONE_ONE_FOUNDATION_GAP`
