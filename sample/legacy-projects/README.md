# legacy-projects

- 役割: Original Code 由来の sanitized representative project metadata を、runtime pack として durable に保持する category
- ここは shape-focused な migration fixture ではなく、project-shaped metadata / seed / resources を見るための棚
- 現時点の pack はすべて runtime pack で、`compose.yaml` / `run.sh` / `seed/` を持つ
- 番号は `50` 番台を legacy-project runtime pack 専用の帯として使う

current packs:

- `sample51-runtime-sql-server`
  - SQL Server 系 DBAccess metadata の最小参照
- `sample53-runtime-whiteboard`
  - collaboration-style metadata の representative pack
- `sample56-runtime-misc-proxy`
  - tooling / proxy mix の representative pack

補足:

- legacy project pack は個人情報を含む旧 language resource catalog を保持しない
- buildable output を持たせるのは、その pack 自身の metadata と provenance が明確な実 source がある場合だけに絞る
- `tests/Integration/LegacyProjectSampleCatalogTest.php` が remaining legacy project packs の canonical project key、seed、resource manifest contract を静的 gate として固定する

基本操作:

```bash
./sample/legacy-projects/sample51-runtime-sql-server/run.sh up
```

pack ごとの意図と seed 内容は各 pack README を参照します。
