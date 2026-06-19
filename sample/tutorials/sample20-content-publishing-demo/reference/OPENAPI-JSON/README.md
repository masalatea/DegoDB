# OpenAPI Artifact

- source output: `SAMPLE20/OPENAPI-JSON`
- display name: `Sample20 OpenAPI JSON`
- function count: `2`
- proxy base URL: `http://127.0.0.1:8082/runs/proxy/SAMPLE20/API-PROXY-SERVER`

Files:

- `openapi.json` contains the generated OpenAPI 3.0.3 document.
- `build-plan.json` records the single-function proxy targets used to emit the spec.

Viewer:

- Lab/Admin viewer route: `/runs/swagger/SAMPLE20?source_output_key=OPENAPI-JSON`

Notes:

- This artifact models the generated single-function proxy runtime contract.
- Request bodies stay JSON `POST`.
- Response envelopes always include `_status` and `Message`.
