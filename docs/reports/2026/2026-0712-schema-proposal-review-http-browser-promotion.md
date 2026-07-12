# Schema Proposal Review HTTP/Browser Promotion

Status: `DONE_ONE_GAP`

## Result

The Sample19 proposal review boundary passes real authenticated browser promotion. G-L4 remains one bounded proof short because the reviewed proposal is intentionally deterministic and not AI-authored.

## Stack selection

The MTOOL core stack correctly returned not found for `SAMPLE19` because that project is absent there. Browser verification therefore moved to the dedicated Sample19 stack at admin port `18181`, where the project seed is authoritative. This confirms project bootstrap remains fail-closed rather than bypassed for fixture convenience.

## Browser evidence

### Authentication and enablement

- Unauthenticated review URL redirected to login with the exact return path.
- The route is enabled only with `MTOOL_SCHEMA_PROPOSAL_REVIEW_ENABLED=1`.
- Stub admin login returned to the exact proposal route.

### Integrity and review content

The rendered page showed:

- proposal-only, apply unsupported, read-only/no mutation;
- deterministic fixture and not-AI-authored provenance;
- `schema-proposal-v0`, proposal ID, Sample19 project, deterministic timestamp;
- `article.json`, media type, verified SHA-256, `/article` root, no redaction;
- four entity rows with field/key/relationship signatures and evidence pointers;
- page state `reviewable`;
- four independently derived `unchanged` rows;
- one blocking question and one non-blocking assumption.

Browser DOM counts:

- verified source hash markers: 1;
- entities: 4;
- unchanged diffs: 4;
- blocking questions: 1;
- assumptions: 1;
- forms: 0;
- buttons: 0;
- scripts: 0;
- runtime execution bindings: 0;
- apply/approve/import links: 0.

The single return link navigated to `/projects/SAMPLE19`, whose canonical Project Hub loaded successfully.

## HTTP zero-mutation evidence

The Sample19 web-admin Apache access log recorded:

- GET review route -> login redirect;
- GET login page;
- POST login -> redirect;
- GET review page -> 200;
- GET Sample19 project hub -> 200.

No POST targeted the schema proposal route or any apply/import/metadata route. The only POST was authentication.

## Rollback

After verification, web-admin was recreated without the feature override. The dedicated Sample19 stack is restored to default-off. No proposal record, review transition, SQL, metadata write, or generated artifact was created.

## G-L4 decision

Do not mark G-L4 complete yet.

The artifact/review half of the gate is proven:

- proposal artifact is reviewable;
- source evidence and canonical diff are visible;
- unsafe/mismatched input fails closed;
- automatic mutation is absent by default and in the live page.

The remaining gap is the first half of the gate: demonstrating that an AI actually produces a valid proposal under controlled provenance. Current provenance explicitly says `deterministic_fixture` and `ai_authored=false`; treating it as AI proof would contradict the artifact.

## Verification

- In-app browser promotion: passed.
- Apache review-route POST count: zero.
- Fast/full verification inherited from #764: 448 tests, 14,018 assertions, 1 skipped.
- Stack restored default-off.

## Next

#766 defines the exact AI production contract before any external/local model call. It must fix provider/model approval, prompt/source fingerprints, privacy boundary, structured output parsing, provenance truthfulness, retry/non-determinism handling, comparison criteria, and proof that accepted AI output gains no apply authority.
