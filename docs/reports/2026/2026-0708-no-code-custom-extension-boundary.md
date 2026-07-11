# 2026-07-08 No-code custom extension boundary

Status: `DONE`

## Decision

#433 records the custom extension boundary before continuing the Mtool no-code dogfooding artifact work.

The core rule is:

Generated no-code artifacts should remain regeneratable. Custom behavior should be declared and traced, not hidden as hand edits inside generated HTML.

## Layer Model

| Layer | Owner | Purpose | Examples |
| --- | --- | --- | --- |
| Standard generated UI | DegoDB no-code generator | Render the default list / detail / form / review surface from canonical metadata | table list, detail view, edit form, validation, submit feedback |
| Configured presentation | DegoDB metadata | Change presentation without custom code | `view_variant_preference`, future `view_profile`, density, field grouping |
| Custom UI slots | User / project extension | Insert non-standard UI at declared points | `before_list`, `row_action_extra`, `detail_sidebar`, `form_extra_panel`, `after_submit_guidance` |
| Custom operations | Managed Operation / Custom Proxy / external endpoint | Run non-standard business behavior outside generated UI code | bespoke approval, external API handoff, multi-step backend workflow |
| Full custom app handoff | Consumer app | Own the whole UI while consuming DegoDB contracts | React bridge app, dedicated frontend, mobile app |

## React Interpretation

React is a natural implementation target because this boundary maps to composition:

```tsx
<GeneratedList
  data={rows}
  columns={columns}
  rowActions={<CustomRowActions />}
  sidebar={<CustomSidebar />}
/>
```

But the DegoDB design should not make React the only source of truth. The extension points should live in no-code metadata / manifests first:

- HTML runtime can render placeholders, links, or external panels.
- React bridge can map slots to component props.
- Future adapters can expose the same extension points in their own idiom.

## Boundary Rules

- Do not make hand-edited generated HTML the main customization path.
- Do not put complex business logic inside generated UI templates.
- Do not let visual customization create a hidden data model.
- Do record custom slots, custom operation bindings, and handoff targets in manifests.
- Do preserve traceability back to Shared Contracts, fields, Managed Operations, Source Output, publish candidates, aliases, and outbox/review paths.

## Mtool Dogfooding Implication

The Mtool Source Output review probe should be used to classify real needs:

- what is covered by the standard generated review surface;
- what should be a configured presentation variant;
- what needs a custom UI slot;
- what should be a custom operation or Custom Proxy;
- what should be handed off to a full custom app.

This lets Mtool dogfooding validate the extension model without prematurely replacing Mtool's admin UI.

## Next Step

#434 should continue the Mtool dogfooding lane by generating or inspecting the Source Output review probe artifact path while applying this extension boundary. The expected output is not a broad custom slot implementation yet; it is a concrete finding list from the first Mtool surface.
