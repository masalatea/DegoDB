# Review Artifact HTTP Route Guard Wrapper

Date: 2026-07-08

Status: `FIRST_SLICE_DONE`

## Summary

#467 adds the first HTTP wrapper for the `review_source_output_artifact` custom operation guard.

The route calls the existing plan-only dispatch preflight helper and renders blocked/plan-only results. It still does not create a review workflow, append operation audit records, enable generated buttons, publish artifacts, mutate Source Output state, or execute custom components.

## Implemented

- Added a narrow route for `POST /projects/{project_key}/source-outputs/{source_output_key}/operations/review-source-output-artifact`.
- Registered the route before the generic Source Output detail route.
- Added `mtool/app/project_source_output_operation_page.php` as the route wrapper.
- Reused `app_project_source_output_item_route_bootstrap()` for method enforcement, admin/config auth, project permission, and Source Output loading.
- Called `app_no_code_custom_operation_dispatch_preflight()` with CSRF state, principal, Source Output identity, and artifact request fields.
- Kept the wrapper fail-closed for non-review operation slugs.
- Added route contract coverage confirming `request-source-output-publish` is not routed by this slice.

## Boundary

- Current Mtool dogfooding metadata remains `availability: deferred`, so real route calls are blocked before plan-only acceptance.
- The wrapper renders the helper result; it does not append audit records yet.
- `request_source_output_publish` remains metadata-only.
- Generated HTML and React bridge handoffs remain disabled metadata.
- No mutation is added.

## Verification

- `php -l mtool/app/project_source_output_operation_page.php`
- `php -l mtool/app/http.php`
- `php -l mtool/app/router.php`
- `php -l tests/Integration/OpenApiSourceOutputContractTest.php`
- Focused PHPUnit route contract: `OK (24 tests, 1908 assertions)`
- Focused PHPUnit dispatch helper: `OK (6 tests, 54 assertions)`
- `make test`: `OK, but incomplete, skipped, or risky tests! Tests: 351, Assertions: 11384, Skipped: 1.`
- `git diff --check`

## Next Candidate

Add focused HTTP-level guard coverage for CSRF/deferred result rendering and decide whether blocked operation audit append should happen in the wrapper or remain a later persistence slice.
