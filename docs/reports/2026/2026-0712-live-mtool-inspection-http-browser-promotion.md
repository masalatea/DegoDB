# Live Mtool Inspection HTTP/Browser Promotion

Status: `DONE_QUALIFIED`

## Decision

The contained Mtool Source Output inspection workflow is qualified as the first G-L3 dogfooding entry. G-L3 is satisfied.

## Real admin-stack evidence

The MTOOL core-seed stack was rebuilt and exercised through the in-app browser against `http://127.0.0.1:8081`.

### Default-off and authentication

- An unauthenticated request to the inspection route redirected to login with the original route preserved.
- After stub-admin login, the unset switch produced the normal not-found page.
- The web-admin service was then recreated with `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED=1`.
- Recreating the service invalidated the session as expected; a fresh login returned to the requested inspection route.

### Live read behavior

- The enabled route returned the generated Mtool Source Output list and detail screens.
- The list contained 29 current repository rows from the MTOOL core seed.
- Direct selection with `source_output_key=OPENAPI-JSON` rendered the matching row in detail.
- Browser DOM inspection reported:
  - enabled buttons: 0;
  - runtime execute controls: 0;
  - generated form screens: 0;
  - canonical return target: `/projects/MTOOL/source-outputs`.
- Activating the canonical return link navigated to the existing Source Outputs page with its normal title.

### Zero-mutation evidence

The Apache access log recorded only GET requests for:

- the unauthenticated inspection route;
- the enabled inspection route;
- the selected `OPENAPI-JSON` inspection route;
- the canonical Source Outputs return route.

The only POST in the browser sequence was the expected login form. No POST targeted `no-code-inspection` or any Source Output operation route.

## Compose correction

The promotion check found that root Compose did not yet pass `MTOOL_NO_CODE_SELF_INSPECTION_ENABLED` into web-admin. The pass-through was added and fixed with a fast contract assertion. This preserves default-off behavior while making explicit local enablement possible.

## Rollback verification

After browser verification, web-admin was recreated without the environment override. The running stack is restored to default-off. No data migration, request record, audit event, build artifact, or compensating mutation was created.

## Verification

- In-app browser: login redirect, off-state 404, enabled live rows, exact selection, disabled execution controls, canonical navigation passed.
- Apache access log: inspection POST count zero.
- PHP syntax and `git diff --check`: passed.
- Full suite: 431 tests, 13,918 assertions, 1 skipped.

## G-L3 conclusion

The workflow is genuinely Mtool self-use rather than only a fixture artifact: it is authenticated, reads current Mtool repository rows, renders them through the shared no-code screen contract, stays parallel to the canonical admin UI, and has a tested operational rollback switch. The declared bounded G-L3 condition is met.

## Explicit exclusions

- No generated mutation, review request, audit append, build, or publish.
- No canonical admin replacement.
- No public or lab exposure.
- No broader Source Output authorization policy.

## Next

#759 reviews the now-open G-L4 direction and parked product lanes, then chooses one bounded investigation or parks progression. It must not begin AI-driven mutation directly.
