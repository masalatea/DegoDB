# Sample31 No-Code Inventory Request Demo

- Role: third data-first no-code domain sample.
- Path: canonical table metadata -> shared contract -> managed operation -> `NO-CODE-RUNTIME` artifact -> generated list/detail/form preview.
- Current first-slice scope: inventory request table with warehouse/item/fulfillment fields, no-code runtime artifact generation, and headless browser smoke for generated action dispatch.

Run:

```bash
./sample/tutorials/sample31-no-code-inventory-request-demo/run.sh up
./sample/tutorials/sample31-no-code-inventory-request-demo/run.sh apply-seed
make sample31-pack-runtime-test
make sample31-no-code-runtime-ui-smoke
make sample31-no-code-public-runtime-browser-smoke
bash mtool/scripts/check_sample_pack_compose_smoke.sh --pack=sample31-no-code-inventory-request-demo
bash mtool/scripts/check_sample_pack_runtime_smoke.sh --pack=sample31-no-code-inventory-request-demo
```

Generated runtime preview target:

```text
work/source-outputs/SAMPLE31/NO-CODE-RUNTIME/runtime-preview.html
work/source-outputs/SAMPLE31/NO-CODE-RUNTIME/runtime-preview.json
work/source-outputs/SAMPLE31/NO-CODE-RUNTIME/screen-definition.json
```

The generated browser smoke verifies:

- `inventory_request_list`
- `inventory_request_detail`
- `inventory_request_form`
- disabled action dispatch fails closed
- authorized `update_inventory_request` dispatch maps key/input fields into a runtime action intent

The public runtime browser smoke verifies:

- artifact preview has no execution binding
- current / alias previews can submit to the real endpoint with scoped stub auth
- direct endpoint enqueue creates pending sync outbox items for `update_inventory_request`
- generated server DBAccess outbox processing updates an isolated SQLite `inventory_request` row
