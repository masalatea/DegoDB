# 2026-07-08 Mtool no-code dogfooding probe inventory

Status: `DONE`

## Decision

#431 chooses the first small Mtool no-code dogfooding probe after the #430 usage/view/settings layer closure.

Do not start with a full Mtool self-replacement. Start with a narrow review surface that can safely exercise the new no-code metadata layers:

- Source Output inspection as the operator entry point.
- Shared Contracts as the settings return path.
- Interface profile metadata as the generated no-code review subject.

## Why this surface

This surface is the best first probe because it is already data-flow-first:

- Source Output review is downstream of canonical project metadata and generated artifacts.
- Shared Contracts is where contract-level `usage_intent` and `view_variant_preference` now live.
- Interface profiles already summarize usage, source, view variants, traceability, and related settings.
- The surface is mostly read/review oriented, so the blast radius is lower than edit/build flows.
- It directly tests the product claim that no-code is an upper layer on a database-first metadata foundation.

## Candidate surfaces

| Candidate | Fit | First-probe decision |
| --- | --- | --- |
| Source Output / Shared Contracts / interface profiles | Strong. Already connected to no-code artifacts, usage intent, view preference, and settings navigation | Choose first |
| Project tables / fields | Useful, but can pull the probe toward schema editing too early | Later |
| DB Access functions / managed operations | Important, but mutation and policy semantics add risk | Later |
| Lab build / publish flows | Valuable, but build side effects make it a poor first probe | Later |
| Dashboard / project overview | Easy to view, but less connected to no-code metadata depth | Later |

## Next steps

| Step | Work unit | Status | Rough effort |
| --- | --- | --- | --- |
| #432 | Add or seed minimal Mtool project metadata for the selected review surface | TODO | 0.5 - 1 day |
| #433 | Generate and inspect the no-code runtime / screen-definition artifact for that Mtool surface | TODO | 0.5 day |
| #434 | Record browser/operator findings and missing settings/navigation discovered by dogfooding | TODO | 0.5 day |
| #435 | Close the probe with accepted capability, remaining gaps, and commit/push guidance | TODO | 0.5 day |

## Boundaries

- No push for this planning slice.
- Do not rewrite Mtool admin pages in this slice.
- Do not make generated no-code UI the canonical edit surface yet.
- Do not change public preview boundaries only to satisfy this probe.
- Keep the existing database-tool usage intact; the probe demonstrates no-code as an upper layer, not a replacement for the foundation.

## AI feel note

This is a good dogfooding path precisely because it is not flashy. It forces the generated no-code layer to explain Mtool's own metadata, and that will expose real product gaps: which settings are hard to find, which view variants are too generic, and whether the data-flow-first story holds when Mtool itself is the subject.
