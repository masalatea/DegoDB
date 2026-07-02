#!/usr/bin/env node
'use strict';

const childProcess = require('child_process');
const fs = require('fs');
const http = require('http');
const net = require('net');
const os = require('os');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_no_code_react_bridge_browser_smoke.js [options]

Options:
  --bridge=PATH       generated NO-CODE-REACT-BRIDGE directory
                      (default: work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE)
  --work-dir=PATH     temporary browser smoke directory
                      (default: work/tmp/no-code-react-bridge-browser-smoke)
  --cache=PATH        npm cache directory
                      (default: work/tmp/npm-cache-react-bridge)
  --output-dir=PATH   screenshot output directory
                      (default: output/playwright/no-code-react-bridge)
  --profile=sample28  expected generated bridge shape (default: sample28)
  --headed            launch Chrome headed
  --headless          launch Chrome headless
  --keep-work-dir     keep temporary browser smoke directory
  --help              show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function parseArgs(argv) {
  const root = repoRoot();
  const config = {
    bridgeDir: path.join(root, 'work', 'source-outputs', 'SAMPLE28', 'NO-CODE-REACT-BRIDGE'),
    workDir: path.join(root, 'work', 'tmp', 'no-code-react-bridge-browser-smoke'),
    cacheDir: path.join(root, 'work', 'tmp', 'npm-cache-react-bridge'),
    outputDir: path.join(root, 'output', 'playwright', 'no-code-react-bridge'),
    expected: expectedProfile('sample28'),
    headless: true,
    keepWorkDir: false,
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
    if (argument === '--keep-work-dir') {
      config.keepWorkDir = true;
      continue;
    }
    if (!argument.startsWith('--') || !argument.includes('=')) {
      throw new Error(`Unknown argument: ${argument}`);
    }

    const body = argument.slice(2);
    const separatorIndex = body.indexOf('=');
    const name = body.slice(0, separatorIndex).trim();
    const value = body.slice(separatorIndex + 1).trim();
    if (name === 'bridge') {
      config.bridgeDir = path.resolve(value);
    } else if (name === 'work-dir') {
      config.workDir = path.resolve(value);
    } else if (name === 'cache') {
      config.cacheDir = path.resolve(value);
    } else if (name === 'output-dir') {
      config.outputDir = path.resolve(value);
    } else if (name === 'profile') {
      config.expected = expectedProfile(value);
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  return config;
}

function expectedProfile(name) {
  if (name === 'sample28') {
    return {
      profile: name,
      projectTitle: 'SAMPLE28 No-code React Bridge',
      listScreenKey: 'no_code_ticket_list',
      detailScreenKey: 'no_code_ticket_detail',
      formScreenKey: 'no_code_ticket_form',
      actionKey: 'update_no_code_ticket',
      operationKey: 'update_no_code_ticket',
      operationType: 'update',
      inputProbeField: 'body',
      formInputField: 'body',
      editedInputValue: 'Edited sample28 React bridge smoke body',
      requiredInputValues: {
        title: 'Edited sample28 React bridge smoke title',
        status: 'active',
        priority: 'high',
        body: 'Edited sample28 React bridge smoke body',
      },
    };
  }

  throw new Error(`Unknown profile: ${name}`);
}

function assertRequiredFiles(bridgeDir) {
  const required = [
    'bridge-contract.json',
    'index.html',
    'package.json',
    'tsconfig.json',
    'vite.config.ts',
    'src/App.tsx',
    'src/MtoolNoCodeRuntime.tsx',
    'src/main.tsx',
    'src/mtoolNoCodeBridge.ts',
    'CONSUMER-NOTES.md',
  ];
  for (const relativePath of required) {
    const absolutePath = path.join(bridgeDir, relativePath);
    if (!fs.existsSync(absolutePath)) {
      throw new Error(`required bridge file was not found: ${absolutePath}`);
    }
  }
}

function copyBridgeToWorkDir(bridgeDir, workDir) {
  fs.rmSync(workDir, { recursive: true, force: true });
  fs.mkdirSync(path.dirname(workDir), { recursive: true });
  fs.cpSync(bridgeDir, workDir, {
    recursive: true,
    filter: (source) => {
      const basename = path.basename(source);
      return basename !== 'node_modules' && basename !== 'dist' && basename !== 'package-lock.json';
    },
  });
}

function run(command, args, cwd, env) {
  const result = childProcess.spawnSync(command, args, {
    cwd,
    env,
    stdio: 'inherit',
  });
  if (result.error) {
    throw result.error;
  }
  if (result.status !== 0) {
    throw new Error(`${command} ${args.join(' ')} failed with exit code ${result.status}`);
  }
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

function getFreePort() {
  return new Promise((resolve, reject) => {
    const server = net.createServer();
    server.on('error', reject);
    server.listen(0, '127.0.0.1', () => {
      const address = server.address();
      const port = typeof address === 'object' && address ? address.port : 0;
      server.close(() => resolve(port));
    });
  });
}

function waitForHttp(url, timeoutMs) {
  const deadline = Date.now() + timeoutMs;

  return new Promise((resolve, reject) => {
    const attempt = () => {
      const request = http.get(url, (response) => {
        response.resume();
        if ((response.statusCode || 0) >= 200 && (response.statusCode || 0) < 500) {
          resolve();
          return;
        }
        retry();
      });
      request.on('error', retry);
      request.setTimeout(1000, () => {
        request.destroy();
        retry();
      });
    };
    const retry = () => {
      if (Date.now() > deadline) {
        reject(new Error(`timed out waiting for ${url}`));
        return;
      }
      setTimeout(attempt, 250);
    };

    attempt();
  });
}

function startVite(workDir, env, port) {
  const child = childProcess.spawn('npm', ['run', 'dev', '--', '--host', '127.0.0.1', '--port', String(port)], {
    cwd: workDir,
    env,
    stdio: ['ignore', 'pipe', 'pipe'],
  });
  let output = '';
  child.stdout.on('data', (chunk) => {
    output += chunk.toString();
  });
  child.stderr.on('data', (chunk) => {
    output += chunk.toString();
  });
  child.on('exit', (code) => {
    if (code !== null && code !== 0) {
      output += `\nVite exited with code ${code}`;
    }
  });

  return { child, output: () => output };
}

async function stopProcess(child) {
  if (!child || child.killed) {
    return;
  }
  child.kill('SIGTERM');
  await new Promise((resolve) => setTimeout(resolve, 500));
  if (!child.killed) {
    child.kill('SIGKILL');
  }
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

async function runBrowserSmoke(config) {
  assertRequiredFiles(config.bridgeDir);
  copyBridgeToWorkDir(config.bridgeDir, config.workDir);
  fs.mkdirSync(config.cacheDir, { recursive: true });
  fs.mkdirSync(config.outputDir, { recursive: true });

  const env = {
    ...process.env,
    npm_config_cache: config.cacheDir,
  };
  run('npm', ['install'], config.workDir, env);
  run('npm', ['run', 'build'], config.workDir, env);

  const port = await getFreePort();
  const server = startVite(config.workDir, env, port);
  const url = `http://127.0.0.1:${port}/`;
  await waitForHttp(url, 30000).catch((error) => {
    throw new Error(`${error.message}\n${server.output()}`);
  });

  const playwright = require(findPlaywrightPackageRoot());
  const executablePath = resolveChromeExecutablePath();
  const browser = await playwright.chromium.launch({
    headless: config.headless,
    ...(executablePath !== '' ? { executablePath } : {}),
  });
  const screenshotPath = path.join(config.outputDir, `no-code-react-bridge-${timestamp()}.png`);

  try {
    const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
    await page.goto(url, { waitUntil: 'networkidle' });
    await requireVisible(page.locator('[data-mtool-react-bridge="no-code-react-bridge-v0"]'), 'React bridge root');
    await requireVisible(page.locator(`[data-screen-key="${config.expected.listScreenKey}"]`), 'list screen');
    await requireVisible(page.locator(`[data-screen-key="${config.expected.detailScreenKey}"]`), 'detail screen');
    await requireVisible(page.locator(`[data-screen-key="${config.expected.formScreenKey}"]`), 'form screen');
    for (const [fieldKey, value] of Object.entries(config.expected.requiredInputValues)) {
      await page.locator(`[data-screen-key="${config.expected.formScreenKey}"] input[data-field-key="${fieldKey}"]`).fill(String(value));
    }
    await page.evaluate((expected) => {
      if (typeof window.__mtoolNoCodeReactBridgeCreateActionIntent !== 'function') {
        throw new Error('missing React bridge action intent helper');
      }

      window.__mtoolNoCodeReactBridgeCreateActionIntent(expected.formScreenKey, expected.actionKey);
    }, config.expected);
    await page.waitForSelector('[data-mtool-react-bridge-action-feedback][data-state="success"]', { timeout: 5000 });

    const metrics = await page.evaluate((expected) => {
      const root = document.querySelector('[data-mtool-react-bridge]');
      const sections = Array.from(document.querySelectorAll('[data-screen-key]'));
      const buttons = Array.from(document.querySelectorAll('button[data-action-key]'));
      const updateButton = buttons.find((button) => button.getAttribute('data-action-key') === expected.actionKey);
      const formInput = document.querySelector(`[data-screen-key="${expected.formScreenKey}"] input[data-field-key="${expected.formInputField}"]`);
      const formField = document.querySelector(`[data-screen-key="${expected.formScreenKey}"] label[data-field-key="${expected.formInputField}"]`);
      const formHint = document.querySelector(`[data-screen-key="${expected.formScreenKey}"] [data-field-hint="${expected.formInputField}"]`);
      const feedback = document.querySelector('[data-mtool-react-bridge-action-feedback]');
      const previousFormState = window.__mtoolNoCodeReactBridgeFormState?.[expected.formScreenKey] || {};
      window.__mtoolNoCodeReactBridgeFormState = window.__mtoolNoCodeReactBridgeFormState || {};
      window.__mtoolNoCodeReactBridgeFormState[expected.formScreenKey] = {
        ...previousFormState,
        [expected.formInputField]: '   ',
      };
      const blankRequiredResult = typeof window.__mtoolNoCodeReactBridgeCreateActionIntent === 'function'
        ? window.__mtoolNoCodeReactBridgeCreateActionIntent(expected.formScreenKey, expected.actionKey)
        : { ok: false, intent: null, error: 'missing React bridge action intent helper' };
      window.__mtoolNoCodeReactBridgeFormState[expected.formScreenKey] = previousFormState;

      return {
        bridgeVersion: root?.getAttribute('data-mtool-react-bridge') || '',
        runtimeVersion: root?.getAttribute('data-runtime-version') || '',
        sectionCount: sections.length,
        actionCount: buttons.length,
        disabledActionCount: buttons.filter((button) => button.getAttribute('data-action-enabled') === 'false').length,
        updateAction: {
          actionKey: updateButton?.getAttribute('data-action-key') || '',
          operationKey: updateButton?.getAttribute('data-operation-key') || '',
          operationType: updateButton?.getAttribute('data-operation-type') || '',
        },
        formInput: {
          found: formInput instanceof HTMLInputElement,
          displayValue: formInput instanceof HTMLInputElement ? formInput.getAttribute('data-display-value') || '' : '',
          value: formInput instanceof HTMLInputElement ? formInput.value : '',
          required: formInput instanceof HTMLInputElement ? formInput.required : false,
          readOnly: formInput instanceof HTMLInputElement ? formInput.readOnly : false,
          ariaRequired: formInput instanceof HTMLInputElement ? formInput.getAttribute('aria-required') || '' : '',
        },
        formField: {
          required: formField?.getAttribute('data-field-required') || '',
          readOnly: formField?.getAttribute('data-field-readonly') || '',
          hint: formHint?.textContent || '',
        },
        actionFeedback: {
          state: feedback?.getAttribute('data-state') || '',
          actionKey: feedback?.getAttribute('data-action-key') || '',
          text: feedback?.textContent || '',
        },
        contractSchemaVersion: window.__mtoolNoCodeReactBridgeContract?.contract_schema_version || '',
        contractBridgeVersion: window.__mtoolNoCodeReactBridgeContract?.bridge_version || '',
        contractRuntimePreviewInvariant: window.__mtoolNoCodeReactBridgeContract?.contract_invariants?.runtime_preview_version || '',
        probeIntent: window.__mtoolNoCodeReactBridgeLastIntent || null,
        lastIntent: window.__mtoolNoCodeReactBridgeLastIntent || null,
        lastActionResult: window.__mtoolNoCodeReactBridgeLastActionResult || null,
        blankRequiredResult,
        bodyText: document.body.innerText,
      };
    }, config.expected);

    if (metrics.bridgeVersion !== 'no-code-react-bridge-v0') {
      throw new Error(`bridge version mismatch: ${metrics.bridgeVersion}`);
    }
    if (metrics.runtimeVersion !== 'no-code-runtime-v0') {
      throw new Error(`runtime version mismatch: ${metrics.runtimeVersion}`);
    }
    if (metrics.contractBridgeVersion !== 'no-code-react-bridge-v0') {
      throw new Error('browser bridge contract was not exposed for smoke verification.');
    }
    if (metrics.contractSchemaVersion !== 'no-code-react-bridge-contract-v0') {
      throw new Error(`contract schema version mismatch: ${metrics.contractSchemaVersion}`);
    }
    if (metrics.contractRuntimePreviewInvariant !== 'no-code-runtime-v0') {
      throw new Error(`runtime preview invariant mismatch: ${metrics.contractRuntimePreviewInvariant}`);
    }
    if (metrics.sectionCount !== 3) {
      throw new Error(`screen count mismatch: ${metrics.sectionCount}`);
    }
    if (metrics.actionCount < 1 || metrics.disabledActionCount < 1) {
      throw new Error('generated action buttons or fail-closed disabled state were not found.');
    }
    if (!metrics.bodyText.includes(config.expected.projectTitle)) {
      throw new Error('generated React bridge title was not found.');
    }
    if (metrics.updateAction.operationKey !== config.expected.operationKey || metrics.updateAction.operationType !== config.expected.operationType) {
      throw new Error('generated update action bridge metadata was not found.');
    }
    if (!metrics.formInput.found) {
      throw new Error('generated React bridge form input was not found.');
    }
    if (metrics.formInput.value !== config.expected.editedInputValue) {
      throw new Error(`generated React bridge form input did not keep edited state: ${metrics.formInput.value}`);
    }
    if (!metrics.formInput.required || metrics.formInput.ariaRequired !== 'true' || metrics.formField.required !== 'true') {
      throw new Error('generated React bridge did not expose required field metadata.');
    }
    if (!metrics.formField.hint.includes('Required')) {
      throw new Error(`generated React bridge required field hint was not found: ${metrics.formField.hint}`);
    }
    if (metrics.actionFeedback.state !== 'success' || metrics.actionFeedback.actionKey !== config.expected.actionKey) {
      throw new Error('generated React bridge action feedback did not show the observed intent.');
    }
    if (!metrics.actionFeedback.text.includes(config.expected.actionKey)) {
      throw new Error(`generated React bridge action feedback text was not found: ${metrics.actionFeedback.text}`);
    }
    if (metrics.bodyText.includes('[object Object]')) {
      throw new Error('generated React bridge rendered raw runtime cell objects.');
    }
    const probeIntent = metrics.probeIntent || {};
    if (probeIntent.intent_version !== 'no-code-runtime-action-intent-v0') {
      throw new Error('React bridge probe helper did not emit a runtime action intent.');
    }
    const lastIntent = metrics.lastIntent || {};
    if (lastIntent.intent_version !== 'no-code-runtime-action-intent-v0') {
      throw new Error('React bridge did not expose the last runtime action intent.');
    }
    if (lastIntent.action_key !== config.expected.actionKey) {
      throw new Error(`React bridge probe emitted unexpected action: ${lastIntent.action_key}`);
    }
    if (!Object.prototype.hasOwnProperty.call(lastIntent.input || {}, config.expected.inputProbeField)) {
      throw new Error('React bridge probe did not pass the generated screen input.');
    }
    if ((lastIntent.input || {})[config.expected.inputProbeField] !== config.expected.editedInputValue) {
      throw new Error('React bridge probe did not emit the edited form value.');
    }
    if (metrics.blankRequiredResult.ok) {
      throw new Error('React bridge blank required input should fail closed.');
    }
    if (!String(metrics.blankRequiredResult.error || '').includes(`input.missing:${config.expected.formInputField}`)) {
      throw new Error(`unexpected React bridge blank required error: ${metrics.blankRequiredResult.error}`);
    }
    if (metrics.blankRequiredResult.message !== `Required input is missing: ${config.expected.formInputField}`) {
      throw new Error(`unexpected React bridge blank required message: ${metrics.blankRequiredResult.message}`);
    }
    if (metrics.lastActionResult?.ok) {
      throw new Error('React bridge last action result should retain the blank required failure.');
    }

    await page.screenshot({ path: screenshotPath, fullPage: true });

    return {
      ok: true,
      url,
      bridge_dir: config.bridgeDir,
      work_dir: config.keepWorkDir ? config.workDir : '',
      screenshot: screenshotPath,
      metrics,
    };
  } finally {
    await browser.close();
    await stopProcess(server.child);
    if (!config.keepWorkDir) {
      fs.rmSync(config.workDir, { recursive: true, force: true });
    }
  }
}

async function main() {
  const config = parseArgs(process.argv);
  if (config.help) {
    process.stdout.write(`${usage()}\n`);
    return;
  }

  const result = await runBrowserSmoke(config);
  process.stdout.write(`${JSON.stringify(result, null, 2)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.stack || error.message}\n`);
  process.exit(1);
});
