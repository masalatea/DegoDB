# Sample28 No-Code Data App MVP

- Role: user-facing data-first no-code app MVP sample.
- Path: canonical table metadata -> shared contract -> managed operation -> `NO-CODE-RUNTIME` artifact -> generated list/detail/form preview.
- Current MVP scope: sample scaffold, catalog entry, minimal `no_code_ticket` model, no-code runtime artifact generation, and headless browser smoke for generated action dispatch.

Run:

```bash
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh up
./sample/tutorials/sample28-no-code-data-app-mvp/run.sh apply-seed
make sample28-pack-runtime-test
make sample28-no-code-runtime-ui-smoke
bash mtool/scripts/check_sample_pack_compose_smoke.sh --pack=sample28-no-code-data-app-mvp
bash mtool/scripts/check_sample_pack_runtime_smoke.sh --pack=sample28-no-code-data-app-mvp
```

Generated runtime preview target:

```text
work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.html
work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/runtime-preview.json
work/source-outputs/SAMPLE28/NO-CODE-RUNTIME/screen-definition.json
```

The generated browser smoke verifies:

- `no_code_ticket_list`
- `no_code_ticket_detail`
- `no_code_ticket_form`
- disabled action dispatch fails closed
- authorized `update_no_code_ticket` dispatch maps key/input fields into a runtime action intent
