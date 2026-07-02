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
  --profile=sample07|sample28|sample29
                                 expected no-code runtime shape (default: sample07)
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
    outputDir: path.join(repoRoot(), 'output', 'playwright', 'no-code-runtime-preview'),
    headless: true,
    help: false,
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
    } else if (name === 'profile') {
      config.expected = expectedProfile(value);
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
      inputFields: ['title', 'status', 'priority', 'body'],
      requiredInputField: 'body',
      requiredInputValue: 'Generated sample28 browser smoke payload',
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

async function runSmoke(config) {
  if (!fs.existsSync(config.htmlPath)) {
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
    await page.goto(`file://${config.htmlPath}`, { waitUntil: 'load' });

    await requireVisible(page.locator('.no-code-preview'), 'preview root');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"]`), 'list screen');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.detailScreenKey}"]`), 'detail screen');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.formScreenKey}"]`), 'form screen');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] table`), 'list table');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.detailScreenKey}"] dl.no-code-detail`), 'detail layout');
    await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.formScreenKey}"] form.no-code-form`), 'form layout');

    const metrics = await page.evaluate((expected) => {
      const sections = Array.from(document.querySelectorAll('.no-code-screen'));
      const actions = Array.from(document.querySelectorAll('.no-code-actions button'));
      const preview = window.__noCodeRuntimePreview || {};
      const previewScreens = Array.isArray(preview.screens) ? preview.screens : [];
      const previewActions = previewScreens.flatMap((screen) => Array.isArray(screen.actions) ? screen.actions : []);
      const updateAction = previewActions.find((action) => action.action_key === expected.actionKey) || {};
      const disabledDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction(expected.actionKey, expected.payload)
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };

      previewActions.forEach((action) => {
        if (action.action_key === expected.actionKey) {
          action.enabled = true;
          action.availability = 'enabled';
        }
      });
      const authorizedDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction(expected.actionKey, expected.payload)
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };
      const blankRequiredPayload = { ...expected.payload, [expected.requiredInputField]: '   ' };
      const blankRequiredDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction(expected.actionKey, blankRequiredPayload)
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };

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
        idleFeedbackCount: Array.from(document.querySelectorAll('.no-code-action-feedback')).filter((element) => element.getAttribute('data-state') === 'idle').length,
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
    }, config.expected);

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
    if (metrics.formSummary.fieldCount !== String(config.expected.inputFields.length) || metrics.formSummary.actionCount !== '1') {
      throw new Error(`form screen summary mismatch: ${JSON.stringify(metrics.formSummary)}`);
    }
    if (metrics.emptyScreenCount < 1) {
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
    if (metrics.idleFeedbackCount !== 3) {
      throw new Error(`initial action feedback state mismatch: ${metrics.idleFeedbackCount}`);
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
