import fs from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const sampleRoot = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');

const requiredFiles = [
  'README.md',
  'package.json',
  'tsconfig.json',
  'vite.config.ts',
  'capacitor.config.ts',
  'index.html',
  'src/App.tsx',
  'src/main.tsx',
  'src/mtoolArtifacts.ts',
  'src/mtoolNoCodeBridge.ts',
  'src/MtoolArtifactSummary.tsx',
  'src/MtoolScreenRenderer.tsx',
  'src/MtoolActionIntentPanel.tsx',
  'src/mtool-artifacts/bridge-contract.sample.json',
  'src/mtool-artifacts/react-wrapper-app-handoff.sample.json',
  'src/mtool-artifacts/mobile-wrapper-bundle-manifest.sample.json',
  'src/mtool-artifacts/external-output.sample.json'
];

const requiredMarkers = [
  'artifact-import-index-review',
  'navigation-selection',
  'screen-rendering-',
  'field-rendering-readonly-detail',
  'local-form-draft-required-validation',
  'action-intent-draft-submit-handoff-boundary',
  'blocked-error-state',
  'ownership-boundary-display',
  'optional-external-output-boundary',
  'no-code-runtime-action-intent-v0',
  'mock/disabled',
  'requiredValidationMessages',
  'createActionIntent'
];

function read(relativePath) {
  return fs.readFileSync(path.join(sampleRoot, relativePath), 'utf8');
}

function readJson(relativePath) {
  return JSON.parse(read(relativePath));
}

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

for (const file of requiredFiles) {
  assert(fs.existsSync(path.join(sampleRoot, file)), `Missing required file: ${file}`);
}

assert(!fs.existsSync(path.join(sampleRoot, 'ios')), 'sample35 must not check in ios/');
assert(!fs.existsSync(path.join(sampleRoot, 'android')), 'sample35 must not check in android/');

const packageJson = readJson('package.json');
assert(packageJson.scripts?.validate === 'node scripts/validate-sample.mjs', 'package.json must expose validate script');
assert(packageJson.scripts?.['cap:sync'] === 'npx cap sync', 'package.json must keep cap sync as optional external-owner script');
assert(packageJson.dependencies?.['@capacitor/core'], 'package.json must declare Capacitor dependency');

const bridge = readJson('src/mtool-artifacts/bridge-contract.sample.json');
assert(bridge.contract_schema_version === 'no-code-react-bridge-contract-v0', 'bridge contract schema mismatch');
assert(bridge.action_intent_version === 'no-code-runtime-action-intent-v0', 'action intent version mismatch');

const screens = bridge.runtime_preview?.screens ?? [];
const screenTypes = new Set(screens.map((screen) => screen.screen_type));
for (const type of ['list', 'detail', 'form']) {
  assert(screenTypes.has(type), `runtime preview must include ${type} screen`);
}

const action = screens.find((screen) => screen.screen_type === 'form')?.actions?.[0];
assert(action?.fields?.some((field) => field.role === 'key' && field.required), 'action must include required key field');
assert(action?.fields?.some((field) => field.role === 'input' && field.required && field.client_write), 'action must include required writable input field');
assert(action?.availability === 'disabled', 'fixture must include blocked/unavailable action state');

const handoff = readJson('src/mtool-artifacts/react-wrapper-app-handoff.sample.json');
assert(handoff.schema_version === 'mobile-react-wrapper-app-handoff-v1', 'handoff schema mismatch');
assert(handoff.capacitor_preparation_boundary?.mtool_does_not_initialize_capacitor_project === true, 'Capacitor init boundary missing');
assert(handoff.capacitor_preparation_boundary?.mtool_does_not_create_native_project_files === true, 'native project boundary missing');

const manifest = readJson('src/mtool-artifacts/mobile-wrapper-bundle-manifest.sample.json');
assert(manifest.schema_version === 'mobile-wrapper-bundle-manifest-v1', 'bundle manifest schema mismatch');
assert(manifest.artifact_order?.includes('capacitor_artifact_import_sample'), 'bundle manifest must include sample artifact');
assert(manifest.artifact_order?.includes('external_optional_output'), 'bundle manifest must include optional external output artifact');
assert(manifest.artifact_order?.includes('ai_task_packet'), 'bundle manifest must include AI task packet artifact');
assert(manifest.artifact_order?.includes('output_mode_config'), 'bundle manifest must include output mode config artifact');

const externalOutput = readJson('src/mtool-artifacts/external-output.sample.json');
assert(externalOutput.schema_version === 'mobile-external-optional-output-v1', 'external output schema mismatch');
assert(externalOutput.mode === 'external_no_code', 'external output mode mismatch');
assert(externalOutput.target === 'react_web_capacitor', 'external output target mismatch');
assert(externalOutput.baseline?.keeps_mtool_no_code === true, 'external output must keep mtool_no_code baseline');
assert(externalOutput.baseline?.replacement_claim === false, 'external output must not claim replacement');
assert(externalOutput.project_identity?.project_key === handoff.project?.project_key, 'external output project identity mismatch');
assert(externalOutput.source_artifacts?.no_code_runtime?.ref === handoff.source_artifacts?.no_code_runtime?.ref, 'external output no-code runtime ref mismatch');
assert(externalOutput.readiness?.server_remains_authoritative === true, 'external output must keep server authoritative');
assert(externalOutput.server_authority?.authorization === 'Mtool/server-owned', 'external output authorization boundary missing');
assert(externalOutput.ownership_boundary?.external_custom_extension_owned?.includes('React app shell'), 'external output external owner boundary missing');
assert(externalOutput.requires_user_confirmation?.includes('initialize Capacitor'), 'external output must require confirmation before Capacitor init');
assert(externalOutput.forbidden_without_artifact?.includes('automatic dependency installation'), 'external output must forbid dependency install without artifact');
assert(externalOutput.non_goals?.includes('replace_mtool_no_code_runtime'), 'external output non-goals must include no-code replacement');

const sourceBundle = [
  read('src/App.tsx'),
  read('src/MtoolArtifactSummary.tsx'),
  read('src/MtoolScreenRenderer.tsx'),
  read('src/MtoolActionIntentPanel.tsx'),
  read('src/mtoolNoCodeBridge.ts')
].join('\n');

for (const marker of requiredMarkers) {
  assert(sourceBundle.includes(marker), `Missing Mtool operation coverage marker: ${marker}`);
}

const readme = read('README.md');
assert(readme.includes('Mtool does not initialize Capacitor'), 'README must state Mtool does not initialize Capacitor');
assert(readme.includes('Covered Mtool operations'), 'README must list covered Mtool operations');

console.log(JSON.stringify({
  ok: true,
  sample: 'sample35-capacitor-artifact-import',
  covered_screen_types: [...screenTypes].sort(),
  external_output_mode: externalOutput.mode,
  operation_markers: requiredMarkers.length,
  native_project_files_checked_in: false
}, null, 2));
