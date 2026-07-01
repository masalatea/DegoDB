# Sample27 App-local Persistence Demo

- Role: prove the first App-local persistence round trip from canonical project metadata.
- Path: server table row -> shared contract DTO -> App-local SQLite save -> App-local SQLite read.
- Scope: runtime contract test only. Generated App-local Source Output artifacts are intentionally left for a later slice.

Run:

```bash
./sample/tutorials/sample27-app-local-persistence-demo/run.sh up
./sample/tutorials/sample27-app-local-persistence-demo/run.sh apply-seed
make sample27-pack-runtime-test
```
