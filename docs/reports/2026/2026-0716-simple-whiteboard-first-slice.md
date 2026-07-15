# 2026-0716 Simple Whiteboard First Slice

## Summary

Added `sample41-simple-whiteboard` as a cut-out-friendly static whiteboard sample.

The sample is intentionally dependency-free and uses only browser APIs.

## Product shape

- Draw freehand strokes.
- Draw with a finger on phones/tablets or with a mouse/trackpad on desktop.
- Select pen color.
- Select pen size.
- Add text to the board.
- Erase with an eraser tool.
- Undo the latest operation.
- Clear the board.
- Export the board as a PNG.

## First-slice boundary

This is not a collaborative whiteboard yet.

It does not include:

- realtime sync;
- room membership;
- backend persistence;
- authentication;
- image upload;
- moderation;
- production deployment.

The board model is still structured as serializable operations (`stroke`, `text`) so a later shared-state / room sync slice can reuse the shape.

## Validation

```bash
node sample/tutorials/sample41-simple-whiteboard/scripts/validate-sample.mjs
```

The validator checks:

- required static files;
- no npm dependency;
- canvas exists;
- pen / eraser / text tools exist;
- color and size controls exist;
- pointer drawing handlers exist;
- PNG export exists;
- serializable `stroke` and `text` operations exist;
- touch drawing boundary is set.
- pointer events cover touch, mouse, and pen input.
