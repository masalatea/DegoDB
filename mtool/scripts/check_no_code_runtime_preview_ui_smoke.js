#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_no_code_runtime_preview_ui_smoke.js [options]

Options:
  --html=PATH                    runtime-preview.html path
  --url=URL                      runtime-preview.html URL
  --profile=sample07|sample28|sample29|sample31
                                 expected no-code runtime shape (default: sample07)
  --execution-binding=ignore|none|required
                                 expected execution binding state (default: ignore)
  --execution-url-contains=TEXT  required substring when execution binding is required
  --demo-processing=ignore|none|available
                                 expected demo processing binding; available forces the submit probe flag (default: ignore)
  --submit-probe=none|enabled-fetch-stub|enabled-real-fetch
                                 probe server submit payload with stubbed or real fetch (default: none)
  --status-probe=real|stub-done|stub-failed
                                 status JSON behavior after submit probe (default: real)
  --admin-user=USER              admin login user for enabled-real-fetch
  --admin-password=PASS          admin login password for enabled-real-fetch
  --output-dir=PATH              artifact directory root (default: output/playwright/no-code-runtime-preview)
  --headed                       launch Chrome headed
  --headless                     launch Chrome headless
  --help                         show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function parseArgs(argv) {
  const config = {
    htmlPath: path.join(repoRoot(), 'work', 'source-outputs', 'SAMPLE07', 'NO-CODE-RUNTIME', 'runtime-preview.html'),
    url: '',
    outputDir: path.join(repoRoot(), 'output', 'playwright', 'no-code-runtime-preview'),
    headless: true,
    help: false,
    executionBinding: 'ignore',
    executionUrlContains: '',
    demoProcessing: 'ignore',
    submitProbe: 'none',
    statusProbe: 'real',
    adminUser: process.env.ADMIN_AUTH_STUB_USER || 'admin-local',
    adminPassword: process.env.ADMIN_AUTH_STUB_PASSWORD || 'change-this-admin-password',
    expected: expectedProfile('sample07'),
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--help' || argument === '-h') {
      config.help = true;
      continue;
    }
    if (argument === '--headed') {
      config.headless = false;
      continue;
    }
    if (argument === '--headless') {
      config.headless = true;
      continue;
    }
    if (!argument.startsWith('--') || !argument.includes('=')) {
      throw new Error(`Unknown argument: ${argument}`);
    }

    const body = argument.slice(2);
    const separatorIndex = body.indexOf('=');
    const name = body.slice(0, separatorIndex).trim();
    const value = body.slice(separatorIndex + 1).trim();
    if (name === 'html') {
      config.htmlPath = path.resolve(value);
    } else if (name === 'url') {
      config.url = value;
    } else if (name === 'profile') {
      config.expected = expectedProfile(value);
    } else if (name === 'execution-binding') {
      if (!['ignore', 'none', 'required'].includes(value)) {
        throw new Error(`Unknown execution binding expectation: ${value}`);
      }
      config.executionBinding = value;
    } else if (name === 'execution-url-contains') {
      config.executionUrlContains = value;
    } else if (name === 'demo-processing') {
      if (!['ignore', 'none', 'available'].includes(value)) {
        throw new Error(`Unknown demo processing expectation: ${value}`);
      }
      config.demoProcessing = value;
    } else if (name === 'submit-probe') {
      if (!['none', 'enabled-fetch-stub', 'enabled-real-fetch'].includes(value)) {
        throw new Error(`Unknown submit probe: ${value}`);
      }
      config.submitProbe = value;
    } else if (name === 'status-probe') {
      if (!['real', 'stub-done', 'stub-failed'].includes(value)) {
        throw new Error(`Unknown status probe: ${value}`);
      }
      config.statusProbe = value;
    } else if (name === 'admin-user') {
      config.adminUser = value;
    } else if (name === 'admin-password') {
      config.adminPassword = value;
    } else if (name === 'output-dir') {
      config.outputDir = path.resolve(value);
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  return config;
}

function expectedProfile(name) {
  if (name === 'sample07') {
    return {
      profile: name,
      projectKey: 'SAMPLE07',
      listScreenKey: 'todo_item_list',
      listScreenTitle: 'Todo Item List',
      detailScreenKey: 'todo_item_detail',
      detailScreenTitle: 'Todo Item Detail',
      formScreenKey: 'todo_item_form',
      formScreenTitle: 'Todo Item Form',
      actionKey: 'update_todo_item',
      operationKey: 'update_todo_item',
      operationType: 'update',
      keyField: 'id',
      keyValue: 1,
      formFieldCount: 4,
      draftInputCountAfterEdit: 3,
      inputFields: ['title', 'status', 'body'],
      requiredInputField: 'body',
      requiredInputValue: 'Generated browser smoke payload',
      payload: {
        id: 1,
        title: 'Browser smoke update',
        status: 'done',
        body: 'Generated browser smoke payload',
      },
    };
  }

  if (name === 'sample28') {
    return {
      profile: name,
      projectKey: 'SAMPLE28',
      listScreenKey: 'no_code_ticket_list',
      listScreenTitle: 'No Code Ticket List',
      detailScreenKey: 'no_code_ticket_detail',
      detailScreenTitle: 'No Code Ticket Detail',
      formScreenKey: 'no_code_ticket_form',
      formScreenTitle: 'No Code Ticket Form',
      actionKey: 'update_no_code_ticket',
      operationKey: 'update_no_code_ticket',
      operationType: 'update',
      keyField: 'id',
      keyValue: 1001,
      formFieldCount: 4,
      draftInputCountAfterEdit: 4,
      inputFields: ['title', 'status', 'priority', 'body'],
      requiredInputField: 'body',
      requiredInputValue: 'Generated sample28 browser smoke payload',
      seededPreview: true,
      seededText: 'Review generated customer fields',
      payload: {
        id: 1001,
        title: 'Sample28 browser smoke update',
        status: 'active',
        priority: 'high',
        body: 'Generated sample28 browser smoke payload',
      },
    };
  }

  if (name === 'sample29') {
    return {
      profile: name,
      projectKey: 'SAMPLE29',
      listScreenKey: 'support_case_list',
      listScreenTitle: 'Support Case List',
      detailScreenKey: 'support_case_detail',
      detailScreenTitle: 'Support Case Detail',
      formScreenKey: 'support_case_form',
      formScreenTitle: 'Support Case Form',
      actionKey: 'update_support_case',
      operationKey: 'update_support_case',
      operationType: 'update',
      keyField: 'id',
      keyValue: 2001,
      formFieldCount: 7,
      draftInputCountAfterEdit: 1,
      inputFields: ['subject', 'status', 'severity', 'next_action'],
      requiredInputField: 'next_action',
      requiredInputValue: 'Generated sample29 browser smoke next action',
      payload: {
        id: 2001,
        subject: 'Sample29 browser smoke update',
        status: 'active',
        severity: 'medium',
        next_action: 'Generated sample29 browser smoke next action',
      },
    };
  }

  if (name === 'sample31') {
    return {
      profile: name,
      projectKey: 'SAMPLE31',
      listScreenKey: 'inventory_request_list',
      listScreenTitle: 'Inventory Request List',
      detailScreenKey: 'inventory_request_detail',
      detailScreenTitle: 'Inventory Request Detail',
      formScreenKey: 'inventory_request_form',
      formScreenTitle: 'Inventory Request Form',
      actionKey: 'update_inventory_request',
      operationKey: 'update_inventory_request',
      operationType: 'update',
      keyField: 'id',
      keyValue: 3101,
      formFieldCount: 7,
      draftInputCountAfterEdit: 1,
      inputFields: ['item_sku', 'quantity_needed', 'status', 'fulfillment_note'],
      requiredInputField: 'fulfillment_note',
      requiredInputValue: 'Generated sample31 browser smoke fulfillment note',
      payload: {
        id: 3101,
        item_sku: 'SKU-BOARD-84',
        quantity_needed: 18,
        status: 'allocated',
        fulfillment_note: 'Generated sample31 browser smoke fulfillment note',
      },
    };
  }

  throw new Error(`Unknown profile: ${name}`);
}

function findPlaywrightPackageRoot() {
  const explicit = process.env.PLAYWRIGHT_PACKAGE_ROOT || '';
  if (explicit !== '' && fs.existsSync(path.join(explicit, 'package.json'))) {
    return explicit;
  }

  try {
    return require.resolve('playwright');
  } catch (_error) {
    // fall through
  }

  const npxRoot = path.join(os.homedir(), '.npm', '_npx');
  if (!fs.existsSync(npxRoot)) {
    throw new Error('playwright package was not found. Set PLAYWRIGHT_PACKAGE_ROOT.');
  }

  const candidates = [];
  for (const entry of fs.readdirSync(npxRoot, { withFileTypes: true })) {
    if (!entry.isDirectory()) {
      continue;
    }

    const candidate = path.join(npxRoot, entry.name, 'node_modules', 'playwright');
    const packageJsonPath = path.join(candidate, 'package.json');
    if (fs.existsSync(packageJsonPath)) {
      candidates.push({ path: candidate, mtimeMs: fs.statSync(packageJsonPath).mtimeMs });
    }
  }

  if (candidates.length === 0) {
    throw new Error('cached playwright package was not found. Set PLAYWRIGHT_PACKAGE_ROOT.');
  }

  candidates.sort((left, right) => right.mtimeMs - left.mtimeMs);
  return candidates[0].path;
}

function resolveChromeExecutablePath() {
  const candidates = [
    process.env.PLAYWRIGHT_CHROME_EXECUTABLE || '',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/Applications/Chromium.app/Contents/MacOS/Chromium',
    '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
  ];

  for (const candidate of candidates) {
    if (candidate !== '' && fs.existsSync(candidate)) {
      return candidate;
    }
  }

  return '';
}

function timestamp() {
  return new Date().toISOString().replace(/[-:]/g, '').replace(/\..+$/, 'Z');
}

async function requireVisible(locator, label) {
  const count = await locator.count();
  if (count < 1) {
    throw new Error(`${label} was not found.`);
  }
  if (!(await locator.first().isVisible())) {
    throw new Error(`${label} is not visible.`);
  }
}

async function loginAdmin(page, baseUrl, username, password) {
  await page.goto(`${baseUrl}/login?redirect=%2Fdashboard`, { waitUntil: 'load' });
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'load' }),
    page.click('button[type="submit"], input[type="submit"]'),
  ]);
  const dashboardResponse = await page.goto(`${baseUrl}/dashboard`, { waitUntil: 'load' });
  if (!dashboardResponse || dashboardResponse.status() !== 200) {
    throw new Error('admin login did not reach dashboard.');
  }
}

async function runSmoke(config) {
  if (config.url === '' && !fs.existsSync(config.htmlPath)) {
    throw new Error(`HTML preview was not found: ${config.htmlPath}`);
  }

  fs.mkdirSync(config.outputDir, { recursive: true });

  const playwright = require(findPlaywrightPackageRoot());
  const executablePath = resolveChromeExecutablePath();
  const browser = await playwright.chromium.launch({
    headless: config.headless,
    ...(executablePath !== '' ? { executablePath } : {}),
  });

  const screenshotPath = path.join(config.outputDir, `no-code-runtime-preview-${timestamp()}.png`);
  try {
    const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
    const targetUrl = config.url !== '' ? config.url : `file://${config.htmlPath}`;
    if (config.submitProbe === 'enabled-real-fetch') {
      if (!targetUrl.startsWith('http://') && !targetUrl.startsWith('https://')) {
        throw new Error('enabled-real-fetch requires an HTTP URL.');
      }
      const parsedUrl = new URL(targetUrl);
      await loginAdmin(page, parsedUrl.origin, config.adminUser, config.adminPassword);
    }
    await page.goto(targetUrl, { waitUntil: 'load' });

    await requireVisible(page.locator('.no-code-preview'), 'preview root');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"]`), 'list screen');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.detailScreenKey}"]`), 'detail screen');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.formScreenKey}"]`), 'form screen');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] table`), 'list table');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.detailScreenKey}"] dl.no-code-detail`), 'detail layout');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.formScreenKey}"] form.no-code-form`), 'form layout');

    const evaluateExpected = {
      ...config.expected,
      submitProbe: config.submitProbe,
      statusProbe: config.statusProbe,
      demoProcessing: config.demoProcessing,
    };
    const metrics = await page.evaluate(async (expected) => {
      const sections = Array.from(document.querySelectorAll('.no-code-screen'));
      const actions = Array.from(document.querySelectorAll('.no-code-actions button'));
      const preview = window.__noCodeRuntimePreview || {};
      const executionBinding = window.__noCodeRuntimeExecutionBinding || {};
      if (expected.demoProcessing === 'available') {
        executionBinding.demo_processing = 'available';
      }
      const previewScreens = Array.isArray(preview.screens) ? preview.screens : [];
      const previewActions = previewScreens.flatMap((screen) => Array.isArray(screen.actions) ? screen.actions : []);
      const updateAction = previewActions.find((action) => action.action_key === expected.actionKey) || {};
      const disabledDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction(expected.actionKey, expected.payload)
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };
      const formScreen = document.querySelector(`.no-code-screen[data-screen-key="${expected.formScreenKey}"]`);
      const draftBeforeEdit = formScreen?.querySelector('[data-intent-draft-output]')?.textContent || '';
      const draftSummaryBeforeEdit = formScreen?.querySelector('[data-intent-draft-summary]')?.textContent || '';
      const requiredInput = formScreen?.querySelector(`[name="${expected.requiredInputField}"]`);
      if (requiredInput) {
        requiredInput.value = expected.requiredInputValue;
        requiredInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      const requiredHint = formScreen?.querySelector(`[data-required-field="${expected.requiredInputField}"]`);
      const requiredHintStateAfterEdit = requiredHint?.getAttribute('data-required-state') || '';
      const requiredHintTextAfterEdit = requiredHint?.textContent?.trim() || '';
      const draftAfterEdit = formScreen?.querySelector('[data-intent-draft-output]')?.textContent || '';
      const draftSummaryAfterEdit = formScreen?.querySelector('[data-intent-draft-summary]')?.textContent || '';
      const draftMetaAfterEdit = formScreen?.querySelector('[data-intent-draft-meta]')?.textContent || '';
      const draftPayloadAfterEdit = formScreen?.querySelector('[data-intent-draft-payload]')?.textContent || '';
      let parsedDraftAfterEdit = {};
      try {
        parsedDraftAfterEdit = draftAfterEdit ? JSON.parse(draftAfterEdit) : {};
      } catch (error) {
        parsedDraftAfterEdit = { parse_error: String(error && error.message ? error.message : error) };
      }

      previewActions.forEach((action) => {
        if (action.action_key === expected.actionKey) {
          action.enabled = true;
          action.availability = 'enabled';
          action.failed_checks = [];
        }
      });
      const authorizedDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction(expected.actionKey, expected.payload)
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };
      const blankRequiredPayload = { ...expected.payload, [expected.requiredInputField]: '   ' };
      const blankRequiredDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction(expected.actionKey, blankRequiredPayload)
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };
      try {
        Object.defineProperty(navigator, 'clipboard', {
          configurable: true,
          value: {
            writeText: async (text) => {
              window.__noCodeRuntimeCopiedDraftText = text;
              if (String(text).includes('/sync-outbox/')) {
                window.__noCodeRuntimeCopiedOutboxDetailPath = text;
              }
            },
          },
        });
      } catch (error) {
        window.__noCodeRuntimeClipboardStubError = String(error && error.message ? error.message : error);
      }
      const draftCopyButton = formScreen?.querySelector('[data-intent-draft-copy]');
      if (draftCopyButton) {
        draftCopyButton.click();
        await new Promise((resolve) => setTimeout(resolve, 0));
      }
      const draftCopyStatusAfterClick = formScreen?.querySelector('[data-intent-draft-copy-status]')?.textContent || '';
      const copiedDraftText = window.__noCodeRuntimeCopiedDraftText || '';
      if (requiredInput) {
        requiredInput.value = '   ';
        requiredInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      const requiredHintStateAfterBlank = requiredHint?.getAttribute('data-required-state') || '';
      const requiredHintTextAfterBlank = requiredHint?.textContent?.trim() || '';
      const idleFeedbackCountBeforeSubmit = Array.from(document.querySelectorAll('.no-code-action-feedback')).filter((element) => element.getAttribute('data-state') === 'idle').length;
      const intentDraftStateBadgeStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-intent-draft-state-badge]')).map((element) => element.getAttribute('data-state') || '');
      const intentDraftStateBadgeTextBeforeSubmit = Array.from(document.querySelectorAll('[data-intent-draft-state-badge]')).map((element) => element.textContent?.trim() || '');
      const runtimeExecuteStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-execute]')).map((element) => element.getAttribute('data-runtime-execute-state') || '');
      const runtimeExecuteStatusTextBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-execute-status]')).map((element) => element.textContent?.trim() || '');
      const runtimeResultRefreshStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-result-refresh]')).map((element) => element.getAttribute('data-runtime-result-refresh-state') || '');
      const runtimeResultRefreshStatusTextBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-result-refresh-status]')).map((element) => element.textContent?.trim() || '');
      const runtimeFlowStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-flow-state]')).map((element) => element.getAttribute('data-runtime-flow-state') || '');
      const runtimeFlowSubmitStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-flow-step="submit"]')).map((element) => element.getAttribute('data-state') || '');
      const runtimeFlowTrackStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-flow-step="track"]')).map((element) => element.getAttribute('data-state') || '');
      const runtimeFlowRefreshStatesBeforeSubmit = Array.from(document.querySelectorAll('[data-runtime-flow-step="refresh"]')).map((element) => element.getAttribute('data-state') || '');
      const intentDraftStatesBeforeSubmit = Array.from(document.querySelectorAll('.no-code-intent-draft')).map((element) => element.getAttribute('data-intent-draft-state') || '');
      let submitProbe = { skipped: true };
      if (expected.submitProbe === 'enabled-fetch-stub' || expected.submitProbe === 'enabled-real-fetch') {
        const form = formScreen?.querySelector('form.no-code-form');
        previewActions.forEach((action) => {
          if (action.action_key === expected.actionKey) {
            action.enabled = true;
            action.availability = 'enabled';
            action.failed_checks = [];
          }
        });
        let keyInput = formScreen?.querySelector(`[name="${expected.keyField}"]`);
        if (!keyInput && form) {
          keyInput = document.createElement('input');
          keyInput.type = 'hidden';
          keyInput.name = expected.keyField;
          form.appendChild(keyInput);
        }
        if (keyInput) {
          keyInput.value = String(expected.keyValue);
          keyInput.dispatchEvent(new Event('input', { bubbles: true }));
          keyInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        for (const fieldName of expected.inputFields) {
          let control = formScreen?.querySelector(`[name="${fieldName}"]`);
          if (!control && form && Object.prototype.hasOwnProperty.call(expected.payload, fieldName)) {
            control = document.createElement('input');
            control.type = 'hidden';
            control.name = fieldName;
            form.appendChild(control);
          }
          if (control && Object.prototype.hasOwnProperty.call(expected.payload, fieldName)) {
            control.value = String(expected.payload[fieldName]);
            control.dispatchEvent(new Event('input', { bubbles: true }));
            control.dispatchEvent(new Event('change', { bubbles: true }));
          }
          if (form && Object.prototype.hasOwnProperty.call(expected.payload, fieldName)) {
            // The submit probe verifies endpoint payload handoff, so hidden overrides keep it independent of widget option sets.
            const overrideControl = document.createElement('input');
            overrideControl.type = 'hidden';
            overrideControl.name = fieldName;
            overrideControl.value = String(expected.payload[fieldName]);
            form.appendChild(overrideControl);
          }
        }
        if (requiredInput) {
          requiredInput.value = expected.requiredInputValue;
          requiredInput.dispatchEvent(new Event('input', { bubbles: true }));
          requiredInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        await new Promise((resolve) => setTimeout(resolve, 0));

        const executeButton = formScreen?.querySelector('[data-runtime-execute]');
        const forcedDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
          ? window.noCodeRuntimeDispatchAction(expected.actionKey, expected.payload)
          : { ok: false };
        if (executeButton && forcedDispatch.ok) {
          // Policy and field readiness are checked elsewhere; this probe must click through to the real endpoint.
          executeButton.disabled = false;
          executeButton.setAttribute('data-runtime-execute-state', 'ready');
        }
        const stateBeforeClick = executeButton?.getAttribute('data-runtime-execute-state') || '';
        const disabledBeforeClick = !!executeButton?.disabled;
        window.__noCodeRuntimeSubmitProbe = {
          fetchCalled: false,
          url: '',
          method: '',
          credentials: '',
          entries: [],
          responseStatus: 0,
          responseOk: null,
          responseSyncIntent: '',
          responseOutboxStatus: '',
          responseDemoProcessingOutcome: '',
          responseDemoProcessingProcessed: null,
        };
        window.__noCodeRuntimeStatusProbe = {
          fetchCalled: false,
          url: '',
          method: '',
          credentials: '',
          responseStatus: 0,
          responseOk: null,
          responseOutboxStatus: '',
          responseHandoffState: '',
          fetchCount: 0,
        };
        window.__noCodeRuntimeDataProbe = {
          fetchCalled: false,
          url: '',
          method: '',
          credentials: '',
          responseStatus: 0,
          responseOk: null,
          contractVersion: '',
          screenCount: 0,
          firstRowKey: '',
        };
        const nativeFetch = window.fetch.bind(window);
        window.fetch = async (url, options = {}) => {
          const method = String(options.method || 'GET');
          if (method.toUpperCase() === 'GET' && String(url).endsWith('/runtime-data.json')) {
            const dataProbe = {
              fetchCalled: true,
              url: String(url),
              method,
              credentials: String(options.credentials || ''),
              responseStatus: 0,
              responseOk: null,
              contractVersion: '',
              screenCount: 0,
              firstRowKey: '',
            };
            window.__noCodeRuntimeDataProbe = dataProbe;
            if (expected.statusProbe !== 'real') {
              const row = {};
              row[expected.keyField] = { value: expected.keyValue, display_value: String(expected.keyValue) };
              for (const [fieldKey, fieldValue] of Object.entries(expected.payload || {})) {
                row[fieldKey] = { value: fieldValue, display_value: String(fieldValue) };
              }
              const rows = [row, row, row].map((item) => ({ ...item }));
              if (expected.seededText && rows[1]) {
                rows[1].title = { value: expected.seededText, display_value: expected.seededText };
              }
              const payload = {
                ok: true,
                contract_version: 'no-code-runtime-data-v0',
                project_key: expected.projectKey,
                selection: {
                  kind: 'stub',
                  artifact_key: 'stub-runtime-data-artifact',
                },
                screen_definition_version: 'stub-runtime-data-screen-definition',
                runtime_preview_version: 'no-code-runtime-v0',
                screens: [
                  {
                    screen_key: expected.listScreenKey,
                    screen_type: 'list',
                    contract_key: expected.contractKey,
                    data: { rows },
                  },
                  {
                    screen_key: expected.detailScreenKey,
                    screen_type: 'detail',
                    contract_key: expected.contractKey,
                    data: { item: row },
                  },
                  {
                    screen_key: expected.formScreenKey,
                    screen_type: 'form',
                    contract_key: expected.contractKey,
                    data: { item: row },
                  },
                ],
                error: '',
              };
              dataProbe.responseStatus = 200;
              dataProbe.responseOk = true;
              dataProbe.contractVersion = payload.contract_version;
              dataProbe.screenCount = payload.screens.length;
              dataProbe.firstRowKey = String(expected.keyValue);
              window.__noCodeRuntimeDataProbe = dataProbe;
              return {
                json: async () => payload,
              };
            }
            const response = await nativeFetch(url, options);
            dataProbe.responseStatus = response.status;
            const payload = await response.clone().json().catch(() => null);
            dataProbe.responseOk = payload && typeof payload.ok === 'boolean' ? payload.ok : null;
            dataProbe.contractVersion = payload?.contract_version || '';
            dataProbe.screenCount = Array.isArray(payload?.screens) ? payload.screens.length : 0;
            const firstScreen = Array.isArray(payload?.screens) ? payload.screens[0] : null;
            const firstRow = Array.isArray(firstScreen?.data?.rows) ? firstScreen.data.rows[0] : null;
            dataProbe.firstRowKey = firstRow?.[expected.keyField]?.display_value || '';
            window.__noCodeRuntimeDataProbe = dataProbe;
            return response;
          }
          if (method.toUpperCase() === 'GET' && String(url).endsWith('.json')) {
            const previousStatusProbe = window.__noCodeRuntimeStatusProbe || {};
            const stubStatus = expected.statusProbe === 'stub-failed' ? 'failed' : 'done';
            const stubHandoffState = expected.statusProbe === 'stub-failed' ? 'needs_review' : 'complete';
            const statusProbe = {
              fetchCalled: true,
              url: String(url),
              method,
              credentials: String(options.credentials || ''),
              responseStatus: 0,
              responseOk: null,
              responseOutboxStatus: '',
              responseHandoffState: '',
              fetchCount: Number(previousStatusProbe.fetchCount || 0) + 1,
            };
            window.__noCodeRuntimeStatusProbe = statusProbe;
            if (expected.statusProbe !== 'real') {
              const payload = {
                ok: true,
                project_key: expected.projectKey,
                dedupe_key: 'stub-runtime-status-dedupe',
                status: stubStatus,
                handoff: {
                  state: stubHandoffState,
                  label: expected.statusProbe === 'stub-failed'
                    ? 'This sync outbox item failed and needs operator review.'
                    : 'This sync outbox item has completed processing.',
                  next_step: expected.statusProbe === 'stub-failed'
                    ? 'Use retry eligibility below to decide whether it can be requeued.'
                    : 'Inspect the intent payload and downstream data if a business-row verification is needed.',
                  reasons: [],
                },
                retry_eligibility: {
                  state: expected.statusProbe === 'stub-failed' ? 'available' : 'not_needed',
                  label: '',
                  action_label: '',
                  allowed: expected.statusProbe === 'stub-failed',
                  reasons: [],
                },
                attempts: 1,
                last_error: expected.statusProbe === 'stub-failed' ? 'stubbed terminal failure' : '',
                operation_key: expected.operationKey,
                operation_type: expected.operationType,
                detail_path: `/projects/${encodeURIComponent(expected.projectKey)}/sync-outbox/stub-runtime-status-dedupe`,
                updated_at: '2026-07-05T00:00:00+00:00',
              };
              statusProbe.responseStatus = 200;
              statusProbe.responseOk = true;
              statusProbe.responseOutboxStatus = payload.status;
              statusProbe.responseHandoffState = payload.handoff.state;
              window.__noCodeRuntimeStatusProbe = statusProbe;
              return {
                json: async () => payload,
              };
            }
            const response = await nativeFetch(url, options);
            statusProbe.responseStatus = response.status;
            const payload = await response.clone().json().catch(() => null);
            statusProbe.responseOk = payload && typeof payload.ok === 'boolean' ? payload.ok : null;
            statusProbe.responseOutboxStatus = payload?.status || '';
            statusProbe.responseHandoffState = payload?.handoff?.state || '';
            window.__noCodeRuntimeStatusProbe = statusProbe;
            return response;
          }
          const entries = [];
          if (options.body && typeof options.body.entries === 'function') {
            for (const [key, value] of options.body.entries()) {
              entries.push([key, String(value)]);
            }
          }
          const probe = {
            fetchCalled: true,
            url: String(url),
            method: String(options.method || ''),
            credentials: String(options.credentials || ''),
            entries,
            responseStatus: 0,
            responseOk: null,
            responseSyncIntent: '',
            responseOutboxStatus: '',
            responseOutboxId: '',
            responseOutboxDedupeKey: '',
            responseOutboxOperationKey: '',
            responseDemoProcessingOutcome: '',
            responseDemoProcessingProcessed: null,
            responseDemoProcessingError: '',
          };
          window.__noCodeRuntimeSubmitProbe = probe;
          if (expected.submitProbe === 'enabled-real-fetch') {
            const response = await nativeFetch(url, options);
            probe.responseStatus = response.status;
            const payload = await response.clone().json().catch(() => null);
            probe.responseOk = payload && typeof payload.ok === 'boolean' ? payload.ok : null;
            probe.responseSyncIntent = payload?.result?.sync_intent?.intent_version || '';
            probe.responseOutboxStatus = payload?.result?.executor_result?.item?.status || '';
            probe.responseOutboxId = payload?.result?.executor_result?.item?.id || '';
            probe.responseOutboxDedupeKey = payload?.result?.executor_result?.item?.dedupe_key || '';
            probe.responseOutboxOperationKey = payload?.result?.executor_result?.item?.operation_key || '';
            probe.responseDemoProcessingOutcome = payload?.demo_processing?.outcome || '';
            probe.responseDemoProcessingProcessed = typeof payload?.demo_processing?.processed === 'boolean' ? payload.demo_processing.processed : null;
            probe.responseDemoProcessingError = payload?.demo_processing?.error || '';
            window.__noCodeRuntimeSubmitProbe = probe;
            return response;
          }
          probe.responseStatus = 200;
          probe.responseOk = true;
          probe.responseSyncIntent = 'managed-operation-sync-intent-v0';
          probe.responseOutboxStatus = 'pending';
          probe.responseOutboxId = '999';
          probe.responseOutboxDedupeKey = 'stub-runtime-status-dedupe';
          probe.responseOutboxOperationKey = expected.operationKey;
          window.__noCodeRuntimeSubmitProbe = probe;
          return {
            json: async () => ({
              ok: true,
              executed: true,
              error: '',
              message: '',
              intent: {
                operation_key: expected.operationKey,
              },
              result: {
                sync_intent: {
                  intent_version: 'managed-operation-sync-intent-v0',
                  operation_key: expected.operationKey,
                },
                executor_result: {
                  item: {
                    id: 999,
                    project_key: expected.projectKey,
                    status: 'pending',
                    dedupe_key: 'stub-runtime-status-dedupe',
                    operation_key: expected.operationKey,
                  },
                },
              },
            }),
          };
        };
        if (executeButton) {
          executeButton.click();
          for (let attempt = 0; attempt < 30; attempt += 1) {
            await new Promise((resolve) => setTimeout(resolve, 100));
            const state = executeButton.getAttribute('data-runtime-execute-state') || '';
            if (state !== 'working') {
              break;
            }
          }
          for (let attempt = 0; attempt < 30; attempt += 1) {
            await new Promise((resolve) => setTimeout(resolve, 100));
            const pollState = formScreen?.querySelector('[data-runtime-execute-status]')?.getAttribute('data-runtime-outbox-status-poll-state') || '';
            if (pollState === '' || pollState === 'checked' || pollState === 'error' || pollState === 'timeout') {
              break;
            }
          }
        }
        const outboxCopyButton = formScreen?.querySelector('[data-runtime-outbox-detail-copy]');
        const resultRefreshButton = formScreen?.querySelector('[data-runtime-result-refresh]');
        if (outboxCopyButton && !outboxCopyButton.disabled) {
          outboxCopyButton.click();
          await new Promise((resolve) => setTimeout(resolve, 0));
        }
        const submitSnapshot = {
          stateAfterClick: executeButton?.getAttribute('data-runtime-execute-state') || '',
          statusAfterClick: formScreen?.querySelector('[data-runtime-execute-status]')?.textContent?.trim() || '',
          feedbackAfterClick: formScreen?.querySelector('.no-code-action-feedback')?.textContent?.trim() || '',
          statusOutboxDetailPath: formScreen?.querySelector('[data-runtime-execute-status]')?.getAttribute('data-runtime-outbox-detail-path') || '',
          statusOutboxStatusPath: formScreen?.querySelector('[data-runtime-execute-status]')?.getAttribute('data-runtime-outbox-status-path') || '',
          statusOutboxPollState: formScreen?.querySelector('[data-runtime-execute-status]')?.getAttribute('data-runtime-outbox-status-poll-state') || '',
          statusOutboxPollCount: formScreen?.querySelector('[data-runtime-execute-status]')?.getAttribute('data-runtime-outbox-status-poll-count') || '',
          feedbackOutboxDetailPath: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-runtime-outbox-detail-path') || '',
          runtimeFlowStateAfterClick: formScreen?.querySelector('[data-runtime-flow-state]')?.getAttribute('data-runtime-flow-state') || '',
          runtimeFlowSubmitStateAfterClick: formScreen?.querySelector('[data-runtime-flow-step="submit"]')?.getAttribute('data-state') || '',
          runtimeFlowTrackStateAfterClick: formScreen?.querySelector('[data-runtime-flow-step="track"]')?.getAttribute('data-state') || '',
          runtimeFlowRefreshStateAfterClick: formScreen?.querySelector('[data-runtime-flow-step="refresh"]')?.getAttribute('data-state') || '',
          runtimeFlowTextAfterClick: formScreen?.querySelector('[data-runtime-flow-state]')?.textContent?.trim() || '',
          outboxCopyDisabledAfterClick: !!outboxCopyButton?.disabled,
          outboxCopyPathAfterClick: outboxCopyButton?.getAttribute('data-runtime-outbox-detail-path') || '',
          outboxDetailLinkHiddenAfterClick: !!formScreen?.querySelector('[data-runtime-outbox-detail-link]')?.hidden,
          outboxDetailLinkHrefAfterClick: formScreen?.querySelector('[data-runtime-outbox-detail-link]')?.getAttribute('href') || '',
          outboxDetailLinkPathAfterClick: formScreen?.querySelector('[data-runtime-outbox-detail-link]')?.getAttribute('data-runtime-outbox-detail-path') || '',
          outboxCopyStatusAfterClick: formScreen?.querySelector('[data-runtime-outbox-detail-copy-status]')?.textContent?.trim() || '',
        };
        if (expected.statusProbe === 'stub-done') {
          for (let attempt = 0; attempt < 30; attempt += 1) {
            await new Promise((resolve) => setTimeout(resolve, 100));
            const refreshState = formScreen?.querySelector('[data-runtime-result-refresh]')?.getAttribute('data-runtime-result-refresh-state') || '';
            if (refreshState === 'success' || refreshState === 'error') {
              break;
            }
          }
        }
        if (expected.statusProbe === 'real' && resultRefreshButton && !resultRefreshButton.disabled) {
          resultRefreshButton.click();
          for (let attempt = 0; attempt < 30; attempt += 1) {
            await new Promise((resolve) => setTimeout(resolve, 100));
            const refreshState = resultRefreshButton.getAttribute('data-runtime-result-refresh-state') || '';
            if (refreshState === 'success' || refreshState === 'error') {
              break;
            }
          }
        }
        const runtimeDataHiddenKeyAfterRefresh = formScreen?.querySelector(`[data-runtime-hidden-action-key="${expected.keyField}"]`);
        const runtimeDataDraftAfterRefresh = formScreen?.querySelector('[data-intent-draft-output]')?.textContent || '';
        let parsedRuntimeDataDraftAfterRefresh = {};
        try {
          parsedRuntimeDataDraftAfterRefresh = runtimeDataDraftAfterRefresh ? JSON.parse(runtimeDataDraftAfterRefresh) : {};
        } catch (error) {
          parsedRuntimeDataDraftAfterRefresh = { parse_error: String(error && error.message ? error.message : error) };
        }
        const probeResult = window.__noCodeRuntimeSubmitProbe || {};
        const statusProbeResult = window.__noCodeRuntimeStatusProbe || {};
        const dataProbeResult = window.__noCodeRuntimeDataProbe || {};
        submitProbe = {
          skipped: false,
          stateBeforeClick,
          disabledBeforeClick,
          stateAfterClick: submitSnapshot.stateAfterClick,
          statusAfterClick: submitSnapshot.statusAfterClick,
          feedbackAfterClick: submitSnapshot.feedbackAfterClick,
          statusOutboxDetailPath: submitSnapshot.statusOutboxDetailPath,
          statusOutboxStatusPath: submitSnapshot.statusOutboxStatusPath,
          statusOutboxPollState: submitSnapshot.statusOutboxPollState,
          statusOutboxPollCount: submitSnapshot.statusOutboxPollCount,
          feedbackOutboxDetailPath: submitSnapshot.feedbackOutboxDetailPath,
          resultRefreshDisabledAfterClick: !!resultRefreshButton?.disabled,
          resultRefreshStateAfterClick: resultRefreshButton?.getAttribute('data-runtime-result-refresh-state') || '',
          resultRefreshStatusAfterClick: formScreen?.querySelector('[data-runtime-result-refresh-status]')?.textContent?.trim() || '',
          runtimeDataFetchCalled: !!dataProbeResult.fetchCalled,
          runtimeDataFetchUrl: dataProbeResult.url || '',
          runtimeDataFetchResponseStatus: dataProbeResult.responseStatus || 0,
          runtimeDataFetchResponseOk: dataProbeResult.responseOk,
          runtimeDataFetchContractVersion: dataProbeResult.contractVersion || '',
          runtimeDataFetchScreenCount: Number(dataProbeResult.screenCount || 0),
          runtimeDataFetchFirstRowKey: dataProbeResult.firstRowKey || '',
          runtimeDataListRowCountAfterRefresh: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
          runtimeDataHiddenKeyValueAfterRefresh: runtimeDataHiddenKeyAfterRefresh?.getAttribute('value') || '',
          runtimeDataDraftSummaryAfterRefresh: formScreen?.querySelector('[data-intent-draft-summary]')?.textContent?.trim() || '',
          runtimeDataDraftChecksAfterRefresh: Array.isArray(parsedRuntimeDataDraftAfterRefresh.draft_checks) ? parsedRuntimeDataDraftAfterRefresh.draft_checks : [],
          runtimeFlowStateAfterClick: submitSnapshot.runtimeFlowStateAfterClick,
          runtimeFlowSubmitStateAfterClick: submitSnapshot.runtimeFlowSubmitStateAfterClick,
          runtimeFlowTrackStateAfterClick: submitSnapshot.runtimeFlowTrackStateAfterClick,
          runtimeFlowRefreshStateAfterClick: submitSnapshot.runtimeFlowRefreshStateAfterClick,
          runtimeFlowTextAfterClick: submitSnapshot.runtimeFlowTextAfterClick,
          outboxCopyDisabledAfterClick: submitSnapshot.outboxCopyDisabledAfterClick,
          outboxCopyPathAfterClick: submitSnapshot.outboxCopyPathAfterClick,
          outboxDetailLinkHiddenAfterClick: submitSnapshot.outboxDetailLinkHiddenAfterClick,
          outboxDetailLinkHrefAfterClick: submitSnapshot.outboxDetailLinkHrefAfterClick,
          outboxDetailLinkPathAfterClick: submitSnapshot.outboxDetailLinkPathAfterClick,
          outboxCopyStatusAfterClick: submitSnapshot.outboxCopyStatusAfterClick,
          copiedOutboxDetailPath: window.__noCodeRuntimeCopiedOutboxDetailPath || '',
          fetchCalled: !!probeResult.fetchCalled,
          url: probeResult.url || '',
          method: probeResult.method || '',
          credentials: probeResult.credentials || '',
          entries: Array.isArray(probeResult.entries) ? probeResult.entries : [],
          responseStatus: probeResult.responseStatus || 0,
          responseOk: probeResult.responseOk,
          responseSyncIntent: probeResult.responseSyncIntent || '',
          responseOutboxStatus: probeResult.responseOutboxStatus || '',
          responseOutboxId: probeResult.responseOutboxId || '',
          responseOutboxDedupeKey: probeResult.responseOutboxDedupeKey || '',
          responseOutboxOperationKey: probeResult.responseOutboxOperationKey || '',
          responseDemoProcessingOutcome: probeResult.responseDemoProcessingOutcome || '',
          responseDemoProcessingProcessed: probeResult.responseDemoProcessingProcessed,
          responseDemoProcessingError: probeResult.responseDemoProcessingError || '',
          statusFetchCalled: !!statusProbeResult.fetchCalled,
          statusFetchUrl: statusProbeResult.url || '',
          statusFetchMethod: statusProbeResult.method || '',
          statusFetchCredentials: statusProbeResult.credentials || '',
          statusFetchResponseStatus: statusProbeResult.responseStatus || 0,
          statusFetchResponseOk: statusProbeResult.responseOk,
          statusFetchOutboxStatus: statusProbeResult.responseOutboxStatus || '',
          statusFetchHandoffState: statusProbeResult.responseHandoffState || '',
          statusFetchCount: Number(statusProbeResult.fetchCount || 0),
        };
      }

      return {
        title: document.title,
        runtimeVersion: document.querySelector('.no-code-preview')?.getAttribute('data-runtime-version') || '',
        runtimeState: document.querySelector('.no-code-preview')?.getAttribute('data-runtime-state') || '',
        previewLabelledBy: document.querySelector('.no-code-preview')?.getAttribute('aria-labelledby') || '',
        sectionCount: sections.length,
        regionCount: sections.filter((section) => section.getAttribute('role') === 'region' && section.getAttribute('aria-labelledby')).length,
        tableCaptionText: document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] caption`)?.textContent?.trim() || '',
        actionNavLabels: Array.from(document.querySelectorAll('.no-code-actions')).map((element) => element.getAttribute('aria-label') || ''),
        summaryCount: document.querySelectorAll('.no-code-screen-summary').length,
        formSummary: {
          fieldCount: document.querySelector(`.no-code-screen-summary[data-screen-summary="${expected.formScreenKey}"]`)?.getAttribute('data-field-count') || '',
          actionCount: document.querySelector(`.no-code-screen-summary[data-screen-summary="${expected.formScreenKey}"]`)?.getAttribute('data-action-count') || '',
        },
        listRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
        emptyScreenCount: sections.filter((section) => section.getAttribute('data-screen-state') === 'empty').length,
        readyScreenCount: sections.filter((section) => section.getAttribute('data-screen-state') === 'ready').length,
        actionCount: actions.length,
        disabledActionCount: actions.filter((button) => button.disabled).length,
        keyboardActionCount: actions.filter((button) => button.getAttribute('data-keyboard-activation') === 'enter-space').length,
        actionAffordanceCount: actions.filter((button) => button.getAttribute('data-action-affordance') === 'keyboard-intent-preview').length,
        actionHintCount: document.querySelectorAll('.no-code-action-hint[data-action-hint]').length,
        actionHintText: Array.from(document.querySelectorAll('.no-code-action-hint')).map((element) => element.textContent?.trim() || ''),
        describedActionCount: actions.filter((button) => {
          const hintId = button.getAttribute('aria-describedby') || '';
          return hintId !== '' && document.getElementById(hintId);
        }).length,
        requiredBadgeCount: document.querySelectorAll('.no-code-required-badge').length,
        requiredHintCount: document.querySelectorAll('[data-required-field]').length,
        requiredHintFields: Array.from(document.querySelectorAll('[data-required-field]')).map((element) => element.getAttribute('data-required-field') || ''),
        requiredHintStates: Array.from(document.querySelectorAll('[data-required-field]')).map((element) => element.getAttribute('data-required-state') || ''),
        requiredHintStateAfterEdit,
        requiredHintTextAfterEdit,
        requiredHintStateAfterBlank,
        requiredHintTextAfterBlank,
        requiredControlsWithDescriptions: Array.from(document.querySelectorAll('.no-code-form [required]')).filter((control) => {
          const hintId = control.getAttribute('aria-describedby') || '';
          return hintId !== '' && document.getElementById(hintId);
        }).length,
        idleFeedbackCount: idleFeedbackCountBeforeSubmit,
        intentDraftCount: document.querySelectorAll('.no-code-intent-draft').length,
        intentDraftStateBadgeCount: document.querySelectorAll('[data-intent-draft-state-badge]').length,
        intentDraftStateBadgeStates: intentDraftStateBadgeStatesBeforeSubmit,
        intentDraftStateBadgeText: intentDraftStateBadgeTextBeforeSubmit,
        intentDraftMetaCount: document.querySelectorAll('[data-intent-draft-meta]').length,
        intentDraftFieldsCount: document.querySelectorAll('[data-intent-draft-fields]').length,
        intentDraftPayloadCount: document.querySelectorAll('[data-intent-draft-payload]').length,
        intentDraftCopyButtonCount: document.querySelectorAll('[data-intent-draft-copy]').length,
        intentDraftCopyStatusCount: document.querySelectorAll('[data-intent-draft-copy-status]').length,
        runtimeExecuteButtonCount: document.querySelectorAll('[data-runtime-execute]').length,
        runtimeExecuteStatusCount: document.querySelectorAll('[data-runtime-execute-status]').length,
        runtimeResultRefreshButtonCount: document.querySelectorAll('[data-runtime-result-refresh]').length,
        runtimeResultRefreshStatusCount: document.querySelectorAll('[data-runtime-result-refresh-status]').length,
        runtimeResultRefreshStates: runtimeResultRefreshStatesBeforeSubmit,
        runtimeResultRefreshStatusText: runtimeResultRefreshStatusTextBeforeSubmit,
        runtimeFlowCount: document.querySelectorAll('[data-runtime-flow-state]').length,
        runtimeFlowSubmitCount: document.querySelectorAll('[data-runtime-flow-step="submit"]').length,
        runtimeFlowTrackCount: document.querySelectorAll('[data-runtime-flow-step="track"]').length,
        runtimeFlowRefreshCount: document.querySelectorAll('[data-runtime-flow-step="refresh"]').length,
        runtimeFlowStates: runtimeFlowStatesBeforeSubmit,
        runtimeFlowSubmitStates: runtimeFlowSubmitStatesBeforeSubmit,
        runtimeFlowTrackStates: runtimeFlowTrackStatesBeforeSubmit,
        runtimeFlowRefreshStates: runtimeFlowRefreshStatesBeforeSubmit,
        runtimeOutboxCopyButtonCount: document.querySelectorAll('[data-runtime-outbox-detail-copy]').length,
        runtimeOutboxCopyStatusCount: document.querySelectorAll('[data-runtime-outbox-detail-copy-status]').length,
        runtimeOutboxDetailLinkCount: document.querySelectorAll('[data-runtime-outbox-detail-link]').length,
        runtimeExecuteStates: runtimeExecuteStatesBeforeSubmit,
        runtimeExecuteStatusText: runtimeExecuteStatusTextBeforeSubmit,
        executionBindingUrl: executionBinding.execution_url || '',
        executionBindingProjectKey: executionBinding.project_key || '',
        executionBindingDemoProcessing: executionBinding.demo_processing || '',
        submitProbe,
        intentDraftJsonDetailsCount: document.querySelectorAll('[data-intent-draft-json-details]').length,
        intentDraftJsonSummaryText: Array.from(document.querySelectorAll('[data-intent-draft-json-details] summary')).map((element) => element.textContent?.trim() || ''),
        intentDraftStates: intentDraftStatesBeforeSubmit,
        draftBeforeEdit,
        draftAfterEdit,
        draftSummaryBeforeEdit,
        draftSummaryAfterEdit,
        draftMetaAfterEdit,
        draftFieldsAfterEdit: formScreen?.querySelector('[data-intent-draft-fields]')?.textContent?.trim() || '',
        draftPayloadAfterEdit,
        draftCopyStatusAfterClick,
        copiedDraftTextMatchesAfterEdit: copiedDraftText === draftAfterEdit,
        draftAfterEditChecks: Array.isArray(parsedDraftAfterEdit.draft_checks) ? parsedDraftAfterEdit.draft_checks : [],
        draftAfterEditPolicyChecks: Array.isArray(parsedDraftAfterEdit.policy_failed_checks) ? parsedDraftAfterEdit.policy_failed_checks : [],
        actionMetadata: {
          actionKey: updateAction.action_key || '',
          operationKey: updateAction.operation_key || '',
          operationType: updateAction.operation_type || '',
          fields: Array.isArray(updateAction.fields) ? updateAction.fields : [],
        },
        disabledDispatch,
        authorizedDispatch,
        blankRequiredDispatch,
        bodyText: document.body.innerText,
      };
    }, evaluateExpected);

    if (metrics.runtimeVersion !== 'no-code-runtime-v0') {
      throw new Error(`runtime version mismatch: ${metrics.runtimeVersion}`);
    }
    if (metrics.runtimeState !== 'ready') {
      throw new Error(`runtime state mismatch: ${metrics.runtimeState}`);
    }
    if (metrics.previewLabelledBy !== 'no-code-preview-title') {
      throw new Error(`preview aria-labelledby mismatch: ${metrics.previewLabelledBy}`);
    }
    if (metrics.sectionCount !== 3) {
      throw new Error(`screen count mismatch: ${metrics.sectionCount}`);
    }
    if (metrics.regionCount !== 3) {
      throw new Error(`screen region count mismatch: ${metrics.regionCount}`);
    }
    if (!metrics.tableCaptionText.endsWith('List records')) {
      throw new Error(`list table caption mismatch: ${metrics.tableCaptionText}`);
    }
    if (!metrics.actionNavLabels.some((label) => label.endsWith('Form actions'))) {
      throw new Error(`form action nav label missing: ${metrics.actionNavLabels.join(', ')}`);
    }
    if (metrics.summaryCount !== 3) {
      throw new Error(`screen summary count mismatch: ${metrics.summaryCount}`);
    }
    if (metrics.formSummary.fieldCount !== String(config.expected.formFieldCount || config.expected.inputFields.length) || metrics.formSummary.actionCount !== '1') {
      throw new Error(`form screen summary mismatch: ${JSON.stringify(metrics.formSummary)}`);
    }
    if (config.expected.seededPreview) {
      if (metrics.listRowCount < 3) {
        throw new Error(`seeded preview row count mismatch: ${metrics.listRowCount}`);
      }
      if (metrics.emptyScreenCount !== 0 || metrics.readyScreenCount !== 3) {
        throw new Error(`seeded preview screen states mismatch: empty=${metrics.emptyScreenCount} ready=${metrics.readyScreenCount}`);
      }
      if (!metrics.bodyText.includes(config.expected.seededText)) {
        throw new Error(`seeded preview text was not found: ${config.expected.seededText}`);
      }
    } else if (!(metrics.submitProbe && metrics.submitProbe.runtimeDataFetchCalled && metrics.submitProbe.runtimeDataListRowCountAfterRefresh >= 1) && metrics.emptyScreenCount < 1) {
      throw new Error('generated empty screen state was not found.');
    }
    if (metrics.keyboardActionCount !== metrics.actionCount || metrics.actionAffordanceCount !== metrics.actionCount) {
      throw new Error(`generated action keyboard affordance markers mismatch: ${metrics.keyboardActionCount}/${metrics.actionAffordanceCount}/${metrics.actionCount}`);
    }
    if (metrics.actionHintCount !== metrics.actionCount || metrics.describedActionCount !== metrics.actionCount) {
      throw new Error(`generated action hints mismatch: ${metrics.actionHintCount}/${metrics.describedActionCount}/${metrics.actionCount}`);
    }
    if (!metrics.actionHintText.some((text) => text.includes('press Enter or Space') || text.includes('Disabled in this preview'))) {
      throw new Error('generated action keyboard hint was not found.');
    }
    if (metrics.requiredBadgeCount === 0 || metrics.requiredBadgeCount !== metrics.requiredHintCount || metrics.requiredHintCount !== metrics.requiredControlsWithDescriptions) {
      throw new Error(`required field guidance mismatch: badges=${metrics.requiredBadgeCount} hints=${metrics.requiredHintCount} described=${metrics.requiredControlsWithDescriptions}`);
    }
    if (!metrics.requiredHintFields.includes(config.expected.requiredInputField)) {
      throw new Error(`required field hint missing for ${config.expected.requiredInputField}: ${metrics.requiredHintFields.join(', ')}`);
    }
    if (metrics.requiredHintStateAfterEdit !== 'ok' || !metrics.requiredHintTextAfterEdit.includes('Required input value is present')) {
      throw new Error(`required field live hint did not show ok state after edit: ${metrics.requiredHintStateAfterEdit} / ${metrics.requiredHintTextAfterEdit}`);
    }
    if (metrics.requiredHintStateAfterBlank !== 'missing' || !metrics.requiredHintTextAfterBlank.includes('Missing required input value')) {
      throw new Error(`required field live hint did not show missing state after blank input: ${metrics.requiredHintStateAfterBlank} / ${metrics.requiredHintTextAfterBlank}`);
    }
    if (metrics.idleFeedbackCount !== 3) {
      throw new Error(`initial action feedback state mismatch: ${metrics.idleFeedbackCount}`);
    }
    if (metrics.intentDraftCount !== 3) {
      throw new Error(`intent draft panel count mismatch: ${metrics.intentDraftCount}`);
    }
    if (metrics.intentDraftStateBadgeCount !== 3 || !metrics.intentDraftStateBadgeStates.every((state) => state === 'blocked') || !metrics.intentDraftStateBadgeText.every((text) => text === 'Blocked')) {
      throw new Error(`intent draft state badge mismatch: ${metrics.intentDraftStateBadgeStates.join(', ')} / ${metrics.intentDraftStateBadgeText.join(', ')}`);
    }
    if (metrics.intentDraftMetaCount !== 3) {
      throw new Error(`intent draft meta count mismatch: ${metrics.intentDraftMetaCount}`);
    }
    if (metrics.intentDraftFieldsCount !== 3) {
      throw new Error(`intent draft field summary count mismatch: ${metrics.intentDraftFieldsCount}`);
    }
    if (metrics.intentDraftPayloadCount !== 3) {
      throw new Error(`intent draft payload summary count mismatch: ${metrics.intentDraftPayloadCount}`);
    }
    if (metrics.intentDraftCopyButtonCount !== 3 || metrics.intentDraftCopyStatusCount !== 3) {
      throw new Error(`intent draft copy controls mismatch: ${metrics.intentDraftCopyButtonCount}/${metrics.intentDraftCopyStatusCount}`);
    }
    if (metrics.runtimeExecuteButtonCount !== 3 || metrics.runtimeExecuteStatusCount !== 3) {
      throw new Error(`runtime execute controls mismatch: ${metrics.runtimeExecuteButtonCount}/${metrics.runtimeExecuteStatusCount}`);
    }
    if (metrics.runtimeResultRefreshButtonCount !== 3) {
      throw new Error(`runtime result refresh button count mismatch: ${metrics.runtimeResultRefreshButtonCount}`);
    }
    if (metrics.runtimeResultRefreshStatusCount !== 3) {
      throw new Error(`runtime result refresh status count mismatch: ${metrics.runtimeResultRefreshStatusCount}`);
    }
    if (!metrics.runtimeResultRefreshStates.every((state) => state === 'waiting')) {
      throw new Error(`runtime result refresh initial state mismatch: ${metrics.runtimeResultRefreshStates.join(', ')}`);
    }
    if (!metrics.runtimeResultRefreshStatusText.every((text) => text.includes('Artifact-key previews stay static; current or alias previews can fetch live runtime data when available.'))) {
      throw new Error(`runtime result refresh initial status mismatch: ${metrics.runtimeResultRefreshStatusText.join(' | ')}`);
    }
    if (metrics.runtimeFlowCount !== 3 || metrics.runtimeFlowSubmitCount !== 3 || metrics.runtimeFlowTrackCount !== 3 || metrics.runtimeFlowRefreshCount !== 3) {
      throw new Error(`runtime flow indicator count mismatch: ${metrics.runtimeFlowCount}/${metrics.runtimeFlowSubmitCount}/${metrics.runtimeFlowTrackCount}/${metrics.runtimeFlowRefreshCount}`);
    }
    if (!metrics.runtimeFlowStates.every((state) => ['waiting', 'ready', 'blocked'].includes(state))) {
      throw new Error(`runtime flow initial state mismatch: ${metrics.runtimeFlowStates.join(', ')}`);
    }
    if (!metrics.runtimeFlowSubmitStates.every((state) => ['waiting', 'ready', 'blocked'].includes(state))) {
      throw new Error(`runtime flow submit state mismatch: ${metrics.runtimeFlowSubmitStates.join(', ')}`);
    }
    if (!metrics.runtimeFlowTrackStates.every((state) => state === 'waiting') || !metrics.runtimeFlowRefreshStates.every((state) => state === 'waiting')) {
      throw new Error(`runtime flow track/refresh initial state mismatch: ${metrics.runtimeFlowTrackStates.join(', ')} / ${metrics.runtimeFlowRefreshStates.join(', ')}`);
    }
    if (metrics.runtimeOutboxCopyButtonCount !== 3 || metrics.runtimeOutboxCopyStatusCount !== 3) {
      throw new Error(`runtime outbox copy controls mismatch: ${metrics.runtimeOutboxCopyButtonCount}/${metrics.runtimeOutboxCopyStatusCount}`);
    }
    if (metrics.runtimeOutboxDetailLinkCount !== 3) {
      throw new Error(`runtime outbox detail link count mismatch: ${metrics.runtimeOutboxDetailLinkCount}`);
    }
    if (!metrics.runtimeExecuteStates.every((state) => ['unavailable', 'blocked', 'ready'].includes(state))) {
      throw new Error(`runtime execute state mismatch: ${metrics.runtimeExecuteStates.join(', ')}`);
    }
    if (!metrics.runtimeExecuteStatusText.some((text) => text.includes('Server execution') || text.includes('Resolve draft blockers'))) {
      throw new Error(`runtime execute status text missing: ${metrics.runtimeExecuteStatusText.join(' | ')}`);
    }
    if (config.executionBinding === 'none' && metrics.executionBindingUrl !== '') {
      throw new Error(`execution binding should not be injected for this preview: ${metrics.executionBindingUrl}`);
    }
    if (config.executionBinding === 'required') {
      if (metrics.executionBindingUrl === '' || metrics.executionBindingProjectKey !== config.expected.projectKey) {
        throw new Error(`execution binding missing or project mismatch: url=${metrics.executionBindingUrl} project=${metrics.executionBindingProjectKey}`);
      }
      if (config.executionUrlContains !== '' && !metrics.executionBindingUrl.includes(config.executionUrlContains)) {
        throw new Error(`execution binding URL mismatch: ${metrics.executionBindingUrl} does not include ${config.executionUrlContains}`);
      }
    }
    if (config.demoProcessing === 'none' && metrics.executionBindingDemoProcessing !== '') {
      throw new Error(`demo processing binding should not be injected: ${metrics.executionBindingDemoProcessing}`);
    }
    if (config.demoProcessing === 'available' && metrics.executionBindingDemoProcessing !== 'available') {
      throw new Error(`demo processing binding should be available: ${metrics.executionBindingDemoProcessing}`);
    }
    if (config.submitProbe === 'enabled-fetch-stub' || config.submitProbe === 'enabled-real-fetch') {
      const probe = metrics.submitProbe || {};
      const entries = Object.fromEntries(Array.isArray(probe.entries) ? probe.entries : []);
      if (probe.skipped || probe.stateBeforeClick !== 'ready' || probe.disabledBeforeClick) {
        throw new Error(`submit probe did not reach ready state: ${JSON.stringify(probe)}`);
      }
      if (!probe.fetchCalled || probe.method !== 'POST' || probe.credentials !== 'same-origin') {
        throw new Error(`submit probe did not POST through fetch: ${JSON.stringify(probe)}`);
      }
      if (config.executionUrlContains !== '' && !probe.url.includes(config.executionUrlContains)) {
        throw new Error(`submit probe URL mismatch: ${probe.url}`);
      }
      if (!entries._csrf || entries.project_key !== config.expected.projectKey || !entries.artifact_key || entries.action_key !== config.expected.actionKey) {
        throw new Error(`submit probe binding entries mismatch: ${JSON.stringify(entries)}`);
      }
      if (config.demoProcessing === 'available' && entries.runtime_demo_process !== '1') {
        throw new Error(`submit probe did not request demo processing: ${JSON.stringify(entries)}`);
      }
      if (config.demoProcessing === 'none' && Object.prototype.hasOwnProperty.call(entries, 'runtime_demo_process')) {
        throw new Error(`submit probe unexpectedly requested demo processing: ${JSON.stringify(entries)}`);
      }
      if (entries[`input[${config.expected.keyField}]`] !== String(config.expected.keyValue)) {
        throw new Error(`submit probe key input mismatch: ${JSON.stringify(entries)}`);
      }
      if (entries[`input[${config.expected.requiredInputField}]`] !== config.expected.requiredInputValue) {
        throw new Error(`submit probe required input mismatch: ${JSON.stringify(entries)}`);
      }
      if (probe.stateAfterClick !== 'success' || !probe.statusAfterClick.includes('Server execution accepted')) {
        throw new Error(`submit probe did not show accepted state: ${JSON.stringify(probe)}`);
      }
      if (config.submitProbe === 'enabled-fetch-stub' && config.statusProbe === 'stub-done') {
        const expectedOutboxPath = `/projects/${encodeURIComponent(config.expected.projectKey)}/sync-outbox/stub-runtime-status-dedupe`;
        const expectedOutboxStatusPath = `${expectedOutboxPath}.json`;
        if (probe.statusOutboxDetailPath !== expectedOutboxPath || probe.feedbackOutboxDetailPath !== expectedOutboxPath) {
          throw new Error(`stub submit probe did not expose outbox detail path: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusFetchCalled || probe.statusFetchMethod !== 'GET' || probe.statusFetchCredentials !== 'same-origin' || probe.statusFetchUrl !== expectedOutboxStatusPath) {
          throw new Error(`stub submit probe did not poll outbox status JSON: ${JSON.stringify(probe)}`);
        }
        if (probe.statusFetchResponseStatus !== 200 || probe.statusFetchResponseOk !== true || probe.statusFetchOutboxStatus !== 'done' || probe.statusFetchHandoffState !== 'complete') {
          throw new Error(`stub submit probe terminal done status mismatch: ${JSON.stringify(probe)}`);
        }
        if (probe.statusOutboxPollState !== 'checked' || probe.statusOutboxPollCount !== '1' || probe.statusFetchCount !== 1) {
          throw new Error(`stub submit probe did not stop after terminal status: ${JSON.stringify(probe)}`);
        }
        if (probe.runtimeFlowStateAfterClick !== 'complete' || probe.runtimeFlowSubmitStateAfterClick !== 'done' || probe.runtimeFlowTrackStateAfterClick !== 'done' || probe.runtimeFlowRefreshStateAfterClick !== 'ready') {
          throw new Error(`stub submit probe did not show complete runtime flow: ${JSON.stringify(probe)}`);
        }
        if (!probe.runtimeFlowTextAfterClick.includes('Sync outbox item is done.') || !probe.runtimeFlowTextAfterClick.includes('Refresh this screen to load the latest data.')) {
          throw new Error(`stub submit probe complete flow text mismatch: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes('Live outbox check: done.') || !probe.feedbackAfterClick.includes('This sync outbox item has completed processing.')) {
          throw new Error(`stub submit probe did not show terminal done guidance: ${JSON.stringify(probe)}`);
        }
        if (!probe.runtimeDataFetchCalled || probe.runtimeDataFetchResponseStatus !== 200 || probe.runtimeDataFetchResponseOk !== true) {
          throw new Error(`stub submit probe terminal done did not auto-refresh runtime data: ${JSON.stringify(probe)}`);
        }
        if (probe.runtimeDataFetchContractVersion !== 'no-code-runtime-data-v0' || probe.runtimeDataFetchFirstRowKey !== String(config.expected.keyValue)) {
          throw new Error(`stub submit probe terminal done runtime data mismatch: ${JSON.stringify(probe)}`);
        }
        if (!probe.resultRefreshStatusAfterClick.includes('Fresh runtime data loaded from')) {
          throw new Error(`stub submit probe terminal done did not show auto-refresh status: ${JSON.stringify(probe)}`);
        }
      }
      if (config.submitProbe === 'enabled-fetch-stub' && config.statusProbe === 'stub-failed') {
        const expectedOutboxPath = `/projects/${encodeURIComponent(config.expected.projectKey)}/sync-outbox/stub-runtime-status-dedupe`;
        const expectedOutboxStatusPath = `${expectedOutboxPath}.json`;
        if (probe.statusOutboxDetailPath !== expectedOutboxPath || probe.feedbackOutboxDetailPath !== expectedOutboxPath) {
          throw new Error(`stub failed submit probe did not expose outbox detail path: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusFetchCalled || probe.statusFetchMethod !== 'GET' || probe.statusFetchCredentials !== 'same-origin' || probe.statusFetchUrl !== expectedOutboxStatusPath) {
          throw new Error(`stub failed submit probe did not poll outbox status JSON: ${JSON.stringify(probe)}`);
        }
        if (probe.statusFetchResponseStatus !== 200 || probe.statusFetchResponseOk !== true || probe.statusFetchOutboxStatus !== 'failed' || probe.statusFetchHandoffState !== 'needs_review') {
          throw new Error(`stub failed submit probe terminal status mismatch: ${JSON.stringify(probe)}`);
        }
        if (probe.statusOutboxPollState !== 'checked' || probe.statusOutboxPollCount !== '1' || probe.statusFetchCount !== 1) {
          throw new Error(`stub failed submit probe did not stop after terminal status: ${JSON.stringify(probe)}`);
        }
        if (probe.runtimeFlowStateAfterClick !== 'needs_review' || probe.runtimeFlowSubmitStateAfterClick !== 'done' || probe.runtimeFlowTrackStateAfterClick !== 'error' || probe.runtimeFlowRefreshStateAfterClick !== 'ready') {
          throw new Error(`stub failed submit probe did not show needs-review runtime flow: ${JSON.stringify(probe)}`);
        }
        if (!probe.runtimeFlowTextAfterClick.includes('Sync outbox item needs operator review.') || !probe.runtimeFlowTextAfterClick.includes('Refresh remains available after review.')) {
          throw new Error(`stub failed submit probe needs-review flow text mismatch: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes('Live outbox check: failed.') || !probe.feedbackAfterClick.includes('This sync outbox item failed and needs operator review.')) {
          throw new Error(`stub failed submit probe did not show terminal failure guidance: ${JSON.stringify(probe)}`);
        }
      }
      if (config.submitProbe === 'enabled-real-fetch') {
        if (probe.responseStatus !== 200 || probe.responseOk !== true) {
          throw new Error(`real submit probe response mismatch: ${JSON.stringify(probe)}`);
        }
        if (probe.responseSyncIntent !== 'managed-operation-sync-intent-v0' || probe.responseOutboxStatus !== 'pending') {
          throw new Error(`real submit probe sync result mismatch: ${JSON.stringify(probe)}`);
        }
        if (!probe.responseOutboxId || !probe.responseOutboxDedupeKey || probe.responseOutboxOperationKey !== config.expected.operationKey) {
          throw new Error(`real submit probe outbox trace mismatch: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes('Sync outbox status: pending') || !probe.feedbackAfterClick.includes('Sync outbox status: pending')) {
          throw new Error(`real submit probe did not show pending sync status: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes(`Sync outbox item: #${probe.responseOutboxId}`) || !probe.feedbackAfterClick.includes(`Operation: ${config.expected.operationKey}`)) {
          throw new Error(`real submit probe did not show outbox trace: ${JSON.stringify(probe)}`);
        }
        const expectedOutboxPath = `/projects/${encodeURIComponent(config.expected.projectKey)}/sync-outbox/${encodeURIComponent(probe.responseOutboxDedupeKey)}`;
        if (!probe.statusAfterClick.includes(expectedOutboxPath) || !probe.feedbackAfterClick.includes(expectedOutboxPath)) {
          throw new Error(`real submit probe did not show outbox detail path: ${JSON.stringify(probe)}`);
        }
        if (probe.statusOutboxDetailPath !== expectedOutboxPath || probe.feedbackOutboxDetailPath !== expectedOutboxPath) {
          throw new Error(`real submit probe did not expose outbox detail path attribute: ${JSON.stringify(probe)}`);
        }
        const expectedOutboxStatusPath = `${expectedOutboxPath}.json`;
        if (!probe.statusFetchCalled || probe.statusFetchMethod !== 'GET' || probe.statusFetchCredentials !== 'same-origin' || probe.statusFetchUrl !== expectedOutboxStatusPath) {
          throw new Error(`real submit probe did not poll outbox status JSON: ${JSON.stringify(probe)}`);
        }
        if (probe.statusFetchResponseStatus !== 200 || probe.statusFetchResponseOk !== true || probe.statusFetchOutboxStatus !== 'pending' || probe.statusFetchHandoffState !== 'queued') {
          throw new Error(`real submit probe outbox status JSON response mismatch: ${JSON.stringify(probe)}`);
        }
        if (probe.statusOutboxStatusPath !== expectedOutboxStatusPath || probe.statusOutboxPollState !== 'timeout' || probe.statusOutboxPollCount !== '3') {
          throw new Error(`real submit probe did not expose status polling attributes: ${JSON.stringify(probe)}`);
        }
        if (probe.statusFetchCount !== 3) {
          throw new Error(`real submit probe did not run bounded status polling: ${JSON.stringify(probe)}`);
        }
        if (probe.resultRefreshDisabledAfterClick || probe.resultRefreshStateAfterClick !== 'success') {
          throw new Error(`real submit probe did not enable result refresh: ${JSON.stringify(probe)}`);
        }
        if (probe.runtimeFlowStateAfterClick !== 'timeout' || probe.runtimeFlowSubmitStateAfterClick !== 'done' || probe.runtimeFlowTrackStateAfterClick !== 'waiting' || probe.runtimeFlowRefreshStateAfterClick !== 'ready') {
          throw new Error(`real submit probe did not show bounded timeout runtime flow: ${JSON.stringify(probe)}`);
        }
        if (!probe.runtimeFlowTextAfterClick.includes('Submit accepted.') || !probe.runtimeFlowTextAfterClick.includes('Status is still queued after bounded checks.') || !probe.runtimeFlowTextAfterClick.includes('Refresh this screen or open the outbox detail.')) {
          throw new Error(`real submit probe runtime flow text mismatch: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes('Live outbox check: pending.') || !probe.feedbackAfterClick.includes('Live outbox check: pending.')) {
          throw new Error(`real submit probe did not show live outbox status: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes('Live outbox check stopped after 3 attempts.') || !probe.feedbackAfterClick.includes('Live outbox check stopped after 3 attempts.')) {
          throw new Error(`real submit probe did not show bounded polling timeout guidance: ${JSON.stringify(probe)}`);
        }
        const expectsRuntimeDataRefresh = config.statusProbe === 'real';
        if (expectsRuntimeDataRefresh && !probe.resultRefreshStatusAfterClick.includes('Fresh runtime data loaded from')) {
          throw new Error(`real submit probe did not show result refresh status: ${JSON.stringify(probe)}`);
        }
        if (!expectsRuntimeDataRefresh && !probe.resultRefreshStatusAfterClick.includes('Refresh preview fetches read-only live runtime data for this current or alias selection.')) {
          throw new Error(`real submit probe did not show runtime data refresh readiness: ${JSON.stringify(probe)}`);
        }
        if (expectsRuntimeDataRefresh && (!probe.runtimeDataFetchCalled || probe.runtimeDataFetchResponseStatus !== 200 || probe.runtimeDataFetchResponseOk !== true)) {
          throw new Error(`real submit probe did not fetch runtime data: ${JSON.stringify(probe)}`);
        }
        if (expectsRuntimeDataRefresh && (probe.runtimeDataFetchContractVersion !== 'no-code-runtime-data-v0' || probe.runtimeDataFetchScreenCount !== 3 || probe.runtimeDataFetchFirstRowKey !== String(config.expected.keyValue))) {
          throw new Error(`real submit probe runtime data contract mismatch: ${JSON.stringify(probe)}`);
        }
        if (expectsRuntimeDataRefresh && probe.runtimeDataListRowCountAfterRefresh < 1) {
          throw new Error(`real submit probe did not render fresh runtime rows: ${JSON.stringify(probe)}`);
        }
        if (expectsRuntimeDataRefresh && probe.runtimeDataHiddenKeyValueAfterRefresh !== String(config.expected.keyValue)) {
          throw new Error(`real submit probe did not preserve refreshed form key: ${JSON.stringify(probe)}`);
        }
        if (expectsRuntimeDataRefresh && probe.runtimeDataDraftChecksAfterRefresh.includes(`key.missing:${config.expected.keyField}`)) {
          throw new Error(`real submit probe refreshed draft lost the form key: ${JSON.stringify(probe)}`);
        }
        if (expectsRuntimeDataRefresh && !probe.runtimeDataDraftSummaryAfterRefresh.includes('Ready draft:')) {
          throw new Error(`real submit probe refreshed draft was not ready after key preservation: ${JSON.stringify(probe)}`);
        }
        if (probe.outboxCopyDisabledAfterClick || probe.outboxCopyPathAfterClick !== expectedOutboxPath || probe.copiedOutboxDetailPath !== expectedOutboxPath) {
          throw new Error(`real submit probe did not copy outbox detail path: ${JSON.stringify(probe)}`);
        }
        if (probe.outboxDetailLinkHiddenAfterClick || probe.outboxDetailLinkHrefAfterClick !== expectedOutboxPath || probe.outboxDetailLinkPathAfterClick !== expectedOutboxPath) {
          throw new Error(`real submit probe did not expose outbox detail link: ${JSON.stringify(probe)}`);
        }
        if (!probe.outboxCopyStatusAfterClick.includes('Outbox detail path copied.')) {
          throw new Error(`real submit probe did not show outbox copy status: ${JSON.stringify(probe)}`);
        }
        if (!probe.statusAfterClick.includes('Next result check: process the sync outbox item, then reload this generated preview artifact or open the outbox detail.') || !probe.feedbackAfterClick.includes('Next result check: process the sync outbox item, then reload this generated preview artifact or open the outbox detail.')) {
          throw new Error(`real submit probe did not show result follow-up guidance: ${JSON.stringify(probe)}`);
        }
      }
    }
    if (metrics.intentDraftJsonDetailsCount !== 3 || !metrics.intentDraftJsonSummaryText.every((text) => text === 'Draft JSON')) {
      throw new Error(`intent draft JSON details mismatch: ${metrics.intentDraftJsonDetailsCount}/${metrics.intentDraftJsonSummaryText.join(', ')}`);
    }
    if (!metrics.intentDraftStates.includes('blocked')) {
      throw new Error(`blocked intent draft state was not found: ${metrics.intentDraftStates.join(', ')}`);
    }
    if (!metrics.draftAfterEdit.includes(config.expected.requiredInputValue)) {
      throw new Error(`intent draft did not update after editing ${config.expected.requiredInputField}: ${metrics.draftAfterEdit}`);
    }
    if (metrics.draftBeforeEdit === metrics.draftAfterEdit) {
      throw new Error('intent draft did not change after editable form input changed.');
    }
    if (!metrics.draftAfterEditChecks.includes('action.disabled')) {
      throw new Error(`intent draft did not explain disabled action: ${metrics.draftAfterEditChecks.join(', ')}`);
    }
    if (!metrics.draftAfterEditChecks.includes(`key.missing:${config.expected.keyField}`)) {
      throw new Error(`intent draft did not explain missing key field: ${metrics.draftAfterEditChecks.join(', ')}`);
    }
    if (!metrics.draftSummaryAfterEdit.includes('Blocked draft:')) {
      throw new Error(`intent draft summary did not describe blocked state: ${metrics.draftSummaryAfterEdit}`);
    }
    if (!metrics.draftSummaryAfterEdit.includes('action.disabled') || !metrics.draftSummaryAfterEdit.includes(`key.missing:${config.expected.keyField}`)) {
      throw new Error(`intent draft summary did not include expected checks: ${metrics.draftSummaryAfterEdit}`);
    }
    if (!metrics.draftSummaryAfterEdit.includes('policy:') || !metrics.draftSummaryAfterEdit.includes('principal.missing')) {
      throw new Error(`intent draft summary did not include expected policy checks: ${metrics.draftSummaryAfterEdit}`);
    }
    if (!metrics.draftMetaAfterEdit.includes(`Action: ${config.expected.actionKey}`) || !metrics.draftMetaAfterEdit.includes(`Operation: ${config.expected.operationKey}`) || !metrics.draftMetaAfterEdit.includes(`Type: ${config.expected.operationType}`)) {
      throw new Error(`intent draft metadata did not include expected action boundary: ${metrics.draftMetaAfterEdit}`);
    }
    if (!metrics.draftFieldsAfterEdit.includes(`key=${config.expected.keyField}`) || !config.expected.inputFields.every((field) => metrics.draftFieldsAfterEdit.includes(field)) || !metrics.draftFieldsAfterEdit.includes('filter=(none)')) {
      throw new Error(`intent draft field summary did not include expected field names: ${metrics.draftFieldsAfterEdit}`);
    }
    if (!metrics.draftPayloadAfterEdit.includes('Payload: 0 key fields') || !metrics.draftPayloadAfterEdit.includes(`${config.expected.draftInputCountAfterEdit || config.expected.inputFields.length} input fields`) || !metrics.draftPayloadAfterEdit.includes('0 filter fields')) {
      throw new Error(`intent draft payload summary did not include expected payload counts: ${metrics.draftPayloadAfterEdit}`);
    }
    if (metrics.draftCopyStatusAfterClick !== 'Draft JSON copied.' || !metrics.copiedDraftTextMatchesAfterEdit) {
      throw new Error(`intent draft copy did not copy the current draft JSON: ${metrics.draftCopyStatusAfterClick}`);
    }
    if (!metrics.bodyText.includes(config.expected.listScreenTitle) || !metrics.bodyText.includes(config.expected.formScreenTitle)) {
      throw new Error('generated human-readable screen labels were not found in body text.');
    }
    if (metrics.actionMetadata.operationKey !== config.expected.operationKey || metrics.actionMetadata.operationType !== config.expected.operationType) {
      throw new Error('generated update action metadata was not found.');
    }
    const fieldRoles = Object.fromEntries(metrics.actionMetadata.fields.map((field) => [field.field_key, field.role]));
    if (fieldRoles[config.expected.keyField] !== 'key') {
      throw new Error('generated update action fields do not describe the operation boundary.');
    }
    for (const inputField of config.expected.inputFields) {
      if (fieldRoles[inputField] !== 'input') {
        throw new Error(`generated update action field is not an input: ${inputField}`);
      }
    }
    if (metrics.disabledDispatch.ok || metrics.disabledDispatch.executed) {
      throw new Error('disabled generated action should fail closed in browser dispatch.');
    }
    if (!metrics.disabledDispatch.error.includes('action is not enabled')) {
      throw new Error(`unexpected disabled browser dispatch error: ${metrics.disabledDispatch.error}`);
    }
    if (!metrics.authorizedDispatch.ok || !metrics.authorizedDispatch.executed) {
      throw new Error(`authorized browser dispatch probe failed: ${metrics.authorizedDispatch.error}`);
    }
    const authorizedIntent = metrics.authorizedDispatch.intent || {};
    if (authorizedIntent.intent_version !== 'no-code-runtime-action-intent-v0') {
      throw new Error('authorized browser dispatch did not build a runtime action intent.');
    }
    if (authorizedIntent.operation_key !== config.expected.operationKey || authorizedIntent.operation_type !== config.expected.operationType) {
      throw new Error('authorized browser dispatch did not use generated update operation metadata.');
    }
    if ((authorizedIntent.payload?.key || {})[config.expected.keyField] !== config.expected.keyValue) {
      throw new Error('authorized browser dispatch did not map the generated key field.');
    }
    if ((authorizedIntent.payload?.input || {})[config.expected.requiredInputField] !== config.expected.requiredInputValue) {
      throw new Error('authorized browser dispatch did not map generated input fields.');
    }
    if (metrics.blankRequiredDispatch.ok || metrics.blankRequiredDispatch.executed) {
      throw new Error('blank required generated action input should fail closed in browser dispatch.');
    }
    if (!metrics.blankRequiredDispatch.error.includes(`input.missing:${config.expected.requiredInputField}`)) {
      throw new Error(`unexpected blank required browser dispatch error: ${metrics.blankRequiredDispatch.error}`);
    }
    if (metrics.blankRequiredDispatch.message !== `Required input is missing: ${config.expected.requiredInputField}`) {
      throw new Error(`unexpected blank required browser dispatch message: ${metrics.blankRequiredDispatch.message}`);
    }

    await page.screenshot({ path: screenshotPath, fullPage: true });

    return {
      ok: true,
      html: config.htmlPath,
      url: config.url,
      screenshot: screenshotPath,
      metrics,
    };
  } finally {
    await browser.close();
  }
}

async function main() {
  const config = parseArgs(process.argv);
  if (config.help) {
    process.stdout.write(`${usage()}\n`);
    return;
  }

  const result = await runSmoke(config);
  process.stdout.write(`${JSON.stringify(result, null, 2)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.stack || error.message}\n`);
  process.exit(1);
});
