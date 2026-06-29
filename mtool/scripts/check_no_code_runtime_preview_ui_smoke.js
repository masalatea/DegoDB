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
    } else if (name === 'output-dir') {
      config.outputDir = path.resolve(value);
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  return config;
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
    await requireVisible(page.locator('.no-code-screen[data-screen-key="todo_item_list"]'), 'list screen');
    await requireVisible(page.locator('.no-code-screen[data-screen-key="todo_item_detail"]'), 'detail screen');
    await requireVisible(page.locator('.no-code-screen[data-screen-key="todo_item_form"]'), 'form screen');
    await requireVisible(page.locator('.no-code-screen[data-screen-key="todo_item_list"] table'), 'list table');
    await requireVisible(page.locator('.no-code-screen[data-screen-key="todo_item_detail"] dl.no-code-detail'), 'detail layout');
    await requireVisible(page.locator('.no-code-screen[data-screen-key="todo_item_form"] form.no-code-form'), 'form layout');

    const metrics = await page.evaluate(() => {
      const sections = Array.from(document.querySelectorAll('.no-code-screen'));
      const actions = Array.from(document.querySelectorAll('.no-code-actions button'));
      const preview = window.__noCodeRuntimePreview || {};
      const previewScreens = Array.isArray(preview.screens) ? preview.screens : [];
      const previewActions = previewScreens.flatMap((screen) => Array.isArray(screen.actions) ? screen.actions : []);
      const updateAction = previewActions.find((action) => action.action_key === 'update_todo_item') || {};
      const disabledDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction('update_todo_item', {
          id: 1,
          title: 'Browser smoke update',
          status: 'done',
          body: 'Generated browser smoke payload',
        })
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };

      previewActions.forEach((action) => {
        if (action.action_key === 'update_todo_item') {
          action.enabled = true;
          action.availability = 'enabled';
        }
      });
      const authorizedDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
        ? window.noCodeRuntimeDispatchAction('update_todo_item', {
          id: 1,
          title: 'Browser smoke update',
          status: 'done',
          body: 'Generated browser smoke payload',
        })
        : { ok: false, executed: false, error: 'missing noCodeRuntimeDispatchAction' };

      return {
        title: document.title,
        runtimeVersion: document.querySelector('.no-code-preview')?.getAttribute('data-runtime-version') || '',
        sectionCount: sections.length,
        actionCount: actions.length,
        disabledActionCount: actions.filter((button) => button.disabled).length,
        actionMetadata: {
          actionKey: updateAction.action_key || '',
          operationKey: updateAction.operation_key || '',
          operationType: updateAction.operation_type || '',
          fields: Array.isArray(updateAction.fields) ? updateAction.fields : [],
        },
        disabledDispatch,
        authorizedDispatch,
        bodyText: document.body.innerText,
      };
    });

    if (metrics.runtimeVersion !== 'no-code-runtime-v0') {
      throw new Error(`runtime version mismatch: ${metrics.runtimeVersion}`);
    }
    if (metrics.sectionCount !== 3) {
      throw new Error(`screen count mismatch: ${metrics.sectionCount}`);
    }
    if (!metrics.bodyText.includes('todo_item_list') || !metrics.bodyText.includes('todo_item_form')) {
      throw new Error('generated screen labels were not found in body text.');
    }
    if (metrics.actionMetadata.operationKey !== 'update_todo_item' || metrics.actionMetadata.operationType !== 'update') {
      throw new Error('generated update action metadata was not found.');
    }
    const fieldRoles = Object.fromEntries(metrics.actionMetadata.fields.map((field) => [field.field_key, field.role]));
    if (fieldRoles.id !== 'key' || fieldRoles.title !== 'input' || fieldRoles.body !== 'input') {
      throw new Error('generated update action fields do not describe the operation boundary.');
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
    if (authorizedIntent.operation_key !== 'update_todo_item' || authorizedIntent.operation_type !== 'update') {
      throw new Error('authorized browser dispatch did not use generated update operation metadata.');
    }
    if ((authorizedIntent.payload?.key || {}).id !== 1) {
      throw new Error('authorized browser dispatch did not map the generated key field.');
    }
    if ((authorizedIntent.payload?.input || {}).body !== 'Generated browser smoke payload') {
      throw new Error('authorized browser dispatch did not map generated input fields.');
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
