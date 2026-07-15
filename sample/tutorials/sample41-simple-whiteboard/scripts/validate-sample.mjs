import fs from 'node:fs';
import path from 'node:path';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

for (const file of [
  'README.md',
  'public/index.html',
  'public/styles.css',
  'public/whiteboard.js',
  'src/whiteboard-room-store.mjs',
  'src/server.mjs',
  'scripts/validate-room-sync.mjs',
  'scripts/validate-sample.mjs'
]) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'package.json')), 'sample41 must not require package.json');
assert(!fs.existsSync(path.join(sampleRoot, 'node_modules')), 'sample41 must not include node_modules');

const html = fs.readFileSync(path.join(sampleRoot, 'public/index.html'), 'utf8');
const js = fs.readFileSync(path.join(sampleRoot, 'public/whiteboard.js'), 'utf8');
const css = fs.readFileSync(path.join(sampleRoot, 'public/styles.css'), 'utf8');

assert(html.includes('<canvas id="board"'), 'whiteboard canvas exists');
assert(html.includes('id="tool"'), 'tool selector exists');
assert(html.includes('value="pen"'), 'pen tool exists');
assert(html.includes('value="eraser"'), 'eraser tool exists');
assert(html.includes('value="text"'), 'text tool exists');
assert(html.includes('id="color"'), 'color picker exists');
assert(html.includes('id="size"'), 'pen size control exists');
assert(html.includes('id="textValue"'), 'text input exists');
assert(html.includes('id="exportPng"'), 'PNG export button exists');

assert(js.includes('pointerdown'), 'pointer drawing starts on pointerdown');
assert(js.includes('pointermove'), 'pointer drawing continues on pointermove');
assert(js.includes('toDataURL'), 'PNG export uses canvas data URL');
assert(js.includes('SAMPLE41_ROOM_SLUG'), 'room-aware client hook exists');
assert(js.includes('/operations'), 'client can sync operations to room API');
assert(js.includes('/board'), 'client can load latest board from room API');
assert(js.includes('EventSource'), 'client can subscribe to room update events');
assert(js.includes('board.updated'), 'client listens for board update events');
assert(js.includes('pointerleave'), 'drawing ends when pointer leaves the canvas');
assert(js.includes('pointercancel'), 'touch/pointer cancellation is handled');
assert(js.includes('setPointerCapture'), 'pointer capture supports mouse, pen, and touch drawing');
assert(js.includes("type: 'stroke'"), 'serializable stroke operations exist');
assert(js.includes("type: 'text'"), 'serializable text operations exist');
assert(js.includes("tool === 'text'"), 'text tool places text operation');
assert(js.includes("stroke.tool === 'eraser'"), 'eraser stroke rendering exists');
assert(js.includes('export { boardState, redraw }'), 'board state is exported for future validator/reuse');

assert(css.includes('touch-action: none'), 'canvas disables browser gesture hijacking while drawing');
assert(html.includes('name="viewport"'), 'mobile viewport is configured');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample41-simple-whiteboard',
  tools: ['pen', 'eraser', 'text'],
  controls: ['color', 'size', 'undo', 'clear', 'export_png'],
  input_modes: ['touch', 'mouse', 'pen'],
  dependency_free: true,
  static_first: true
}, null, 2));
