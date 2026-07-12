#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');

const root = path.resolve(__dirname, '..', '..');
const htmlPath = path.resolve(process.argv[2] || path.join(root, 'work/source-outputs/SAMPLE18/NO-CODE-RUNTIME/runtime-preview.html'));
const artifactKey = '20260712-010203-abcdef12';
const previewUrl = 'https://preview.test/runtime-preview.html';
const availabilityUrl = 'https://preview.test/action-availability.json';

function findPlaywright() {
  if (process.env.PLAYWRIGHT_PACKAGE_ROOT) {
    return process.env.PLAYWRIGHT_PACKAGE_ROOT;
  }
  try {
    return require.resolve('playwright');
  } catch (_error) {
    const npxRoot = path.join(os.homedir(), '.npm', '_npx');
    const candidates = fs.existsSync(npxRoot)
      ? fs.readdirSync(npxRoot).map((entry) => path.join(npxRoot, entry, 'node_modules/playwright')).filter((entry) => fs.existsSync(path.join(entry, 'package.json')))
      : [];
    if (!candidates.length) {
      throw new Error('playwright package was not found. Set PLAYWRIGHT_PACKAGE_ROOT.');
    }
    return candidates[candidates.length - 1];
  }
}

function chromePath() {
  const candidates = [
    process.env.PLAYWRIGHT_CHROME_EXECUTABLE || '',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/Applications/Chromium.app/Contents/MacOS/Chromium',
  ];
  return candidates.find((candidate) => candidate && fs.existsSync(candidate)) || '';
}

function bindingScript(overrides = {}) {
  return `<script type="application/json" id="no-code-runtime-execution-binding">${JSON.stringify({
    project_key: 'SAMPLE18',
    artifact_key: artifactKey,
    revision_id: 'smoke-revision',
    action_availability_url: availabilityUrl,
    ...overrides,
  })}</script>`;
}

function injectBinding(html, overrides = {}) {
  return html.includes('<script>')
    ? html.replace('<script>', `${bindingScript(overrides)}\n<script>`)
    : html.replace('</body>', `${bindingScript(overrides)}\n</body>`);
}

async function controlState(page, actionKey) {
  return page.locator(`button[data-action-key="${actionKey}"]`).first().evaluate((button) => ({
    disabled: button.disabled,
    actionEnabled: button.getAttribute('data-action-enabled'),
    actionState: button.getAttribute('data-action-state'),
    submitTrigger: button.getAttribute('data-action-submit-trigger'),
  }));
}

async function main() {
  if (!fs.existsSync(htmlPath)) {
    throw new Error(`runtime preview was not found: ${htmlPath}`);
  }
  const playwright = require(findPlaywright());
  const launchOptions = { headless: true };
  const executablePath = chromePath();
  if (executablePath) {
    launchOptions.executablePath = executablePath;
  }
  const browser = await playwright.chromium.launch(launchOptions);
  const page = await browser.newPage();
  const requests = [];
  page.on('request', (request) => requests.push({ method: request.method(), url: request.url() }));
  const sourceHtml = fs.readFileSync(htmlPath, 'utf8');
  let servedHtml = injectBinding(sourceHtml);
  const availabilityPayload = (overrides = {}) => ({
    ok: true,
    contract_version: 'server-action-availability-v1',
    project_key: 'SAMPLE18',
    selection: { kind: 'artifact', artifact_key: artifactKey, revision_id: 'smoke-revision' },
    mutation_enabled: false,
    actions: [{ action_key: 'create_task_card', availability: 'enabled', failed_checks: [] }],
    ...overrides,
  });
  const fulfillJson = (payload) => (route) => route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify(payload) });

  await page.route(previewUrl, (route) => route.fulfill({ status: 200, contentType: 'text/html', body: servedHtml }));
  await page.route(availabilityUrl, fulfillJson(availabilityPayload()));

  await page.goto(previewUrl, { waitUntil: 'domcontentloaded' });
  const before = await controlState(page, 'create_task_card');
  const diagnostic = page.locator('[data-action-control="create_task_card"] [data-server-action-availability-diagnostic]').first();
  await diagnostic.waitFor({ state: 'visible' });
  if ((await diagnostic.textContent()) !== 'Server availability: enabled') {
    throw new Error(`enabled diagnostic was not rendered: ${await diagnostic.textContent()}`);
  }
  const after = await controlState(page, 'create_task_card');
  if (JSON.stringify(before) !== JSON.stringify(after)) {
    throw new Error(`availability diagnostics changed control state: ${JSON.stringify({ before, after })}`);
  }
  await page.locator('button[data-action-key="create_task_card"]').first().click();
  await page.waitForTimeout(50);

  await page.unroute(availabilityUrl);
  await page.route(availabilityUrl, fulfillJson(availabilityPayload({
    actions: [{ action_key: 'create_task_card', availability: 'disabled', failed_checks: ['authorization_not_allowed'] }],
  })));
  await page.reload({ waitUntil: 'domcontentloaded' });
  await page.locator('[data-action-control="create_task_card"] [data-server-action-availability-diagnostic]').first().waitFor();
  if (!(await page.locator('[data-action-control="create_task_card"] [data-server-action-availability-diagnostic]').first().textContent()).includes('authorization_not_allowed')) {
    throw new Error('authorization-denied diagnostic was not rendered');
  }
  await page.locator('button[data-action-key="create_task_card"]').first().click();
  await page.waitForTimeout(50);

  await page.unroute(availabilityUrl);
  await page.route(availabilityUrl, fulfillJson(availabilityPayload({
    selection: { kind: 'artifact', artifact_key: '20260712-999999-deadbeef', revision_id: 'stale-revision' },
  })));
  await page.reload({ waitUntil: 'domcontentloaded' });
  await page.locator('[data-server-action-availability-diagnostic]').first().waitFor();
  if ((await page.locator('[data-server-action-availability-diagnostic]').first().textContent()) !== 'Server availability: stale preview; refresh required.') {
    throw new Error('stale diagnostic was not rendered');
  }
  await page.locator('button[data-action-key="create_task_card"]').first().click();
  await page.waitForTimeout(50);

  await page.unroute(availabilityUrl);
  await page.route(availabilityUrl, (route) => route.fulfill({ status: 401, contentType: 'text/html', body: '<p>login required</p>' }));
  await page.reload({ waitUntil: 'domcontentloaded' });
  await page.locator('[data-server-action-availability-diagnostic]').first().waitFor();
  if ((await page.locator('[data-server-action-availability-diagnostic]').first().textContent()) !== 'Server availability: unavailable.') {
    throw new Error('unavailable diagnostic was not rendered');
  }
  const finalState = await controlState(page, 'create_task_card');
  if (JSON.stringify(before) !== JSON.stringify(finalState)) {
    throw new Error(`failure diagnostics changed control state: ${JSON.stringify({ before, finalState })}`);
  }
  await page.locator('button[data-action-key="create_task_card"]').first().click();
  await page.waitForTimeout(50);
  if (!requests.some((request) => request.url === availabilityUrl && request.method === 'GET')) {
    throw new Error(`availability GET was not observed: ${JSON.stringify(requests)}`);
  }
  if (requests.some((request) => request.method === 'POST')) {
    throw new Error(`availability diagnostics unexpectedly issued POST: ${JSON.stringify(requests)}`);
  }

  const submitUrl = 'https://preview.test/samples/sample18-task-board/no-code/generated-submit';
  servedHtml = injectBinding(sourceHtml, {
    csrf_token: 'ui-authority-smoke-csrf',
    execution_url: 'https://preview.test/current/execute.json',
    generated_ui_execution_enabled: true,
    generated_ui_execution_allowlist: ['create_task_card'],
  });
  await page.unroute(availabilityUrl);
  await page.route(availabilityUrl, fulfillJson(availabilityPayload({
    actions: [
      { action_key: 'create_task_card', availability: 'enabled', failed_checks: [] },
      { action_key: 'complete_task_card', availability: 'enabled', failed_checks: [] },
    ],
  })));
  await page.route(submitUrl, (route) => route.fulfill({
    status: 200,
    contentType: 'application/json',
    body: JSON.stringify({ ok: true, result: 'executed', transaction_result: { transaction_status: 'committed' } }),
  }));
  await page.reload({ waitUntil: 'domcontentloaded' });
  await page.locator('[data-action-control="create_task_card"] [data-server-action-availability-diagnostic]').first().waitFor();
  await page.locator('button[data-action-key="create_task_card"]').first().click();
  await page.waitForTimeout(100);
  const postAfterCreate = requests.filter((request) => request.method === 'POST');
  if (postAfterCreate.length !== 1 || postAfterCreate[0].url !== submitUrl) {
    throw new Error(`all-gates create did not issue exactly one guarded POST: ${JSON.stringify(postAfterCreate)}`);
  }
  await page.locator('button[data-action-key="complete_task_card"]').first().click();
  await page.waitForTimeout(50);
  if (requests.filter((request) => request.method === 'POST').length !== 1) {
    throw new Error('excluded complete action unexpectedly issued guarded POST');
  }

  await browser.close();
  process.stdout.write(`${JSON.stringify({ ok: true, scenarios: ['ui-flag-off', 'denied', 'stale', 'unavailable', 'all-gates-create', 'excluded-complete'], availability_get: true, guarded_post_count: 1, control_unchanged_without_authority: true })}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.stack || error.message}\n`);
  process.exitCode = 1;
});
