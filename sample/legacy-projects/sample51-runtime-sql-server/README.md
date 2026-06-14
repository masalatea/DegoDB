# sample51-runtime-sql-server

- canonical project key: `SAMPLE2`
- legacy reference: `Project.PID = 9` (`Test for SQL Server`)
- 役割: SQL Server 系 DBAccess metadata を最小構成で見る durable sample pack
- seed には representative な legacy `project_source_outputs` metadata を少量含む
- durable input: `seed/`
- disposable runtime root: `work/sample-packs/sample51-runtime-sql-server/`

`Project 1` 側の検証 scenario は `./tests/scenarios/mtool-single-proxy/run.sh up` を使う。

起動:

```bash
./sample/legacy-projects/sample51-runtime-sql-server/run.sh up
```
