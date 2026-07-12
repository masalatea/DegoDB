# Sample19 material insight preview route lane closure

Date: 2026-07-12

## Summary

#812 closes the Sample19 material insight preview route first slice.

#811 added:

- a default-off authenticated preview route;
- fixed Sample19 fixture loading;
- `material_insight_v0` build and validation before rendering;
- source/basis/Q&A/UI/prohibited-action markers;
- no form/button/script/POST/generated execution controls in fast HTML;
- full `make test` evidence.

## Decision

Promote browser evidence next.

The route is visible, authenticated, and flag-controlled. Fast tests prove the contract shape, but the next useful confidence step is real browser evidence for the operational boundary:

- disabled flag returns not found;
- unauthenticated flag-on access redirects to login;
- authenticated flag-on route renders the material insight markers;
- no POST targets the preview route or apply/import/build/publish routes;
- disabling the flag hides the route again.

## Next lane

#813: Sample19 material insight preview browser evidence.
