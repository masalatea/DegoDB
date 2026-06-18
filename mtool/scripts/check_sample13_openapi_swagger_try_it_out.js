#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_sample13_openapi_swagger_try_it_out.js [options]

Options:
  --lab-host=HOST                lab host (default: 127.0.0.1)
  --lab-port=PORT                lab port (default: LAB_HTTP_PORT or 18222)
  --lab-user=USER                lab stub username
  --lab-password=PASS            lab stub password
  --db-source-key=KEY            runtime db source key (default: config_db)
  --output-dir=PATH              artifact directory root (default: output/playwright/sample13-openapi-swagger)
  --headed                       launch Chrome headed
  --headless                     launch Chrome headless
  --help                         show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function parseEnvDefaults() {
  const envPath = path.join(repoRoot(), '.env');
  if (!fs.existsSync(envPath)) {
    return {};
  }

  const defaults = {};
  const lines = fs.readFileSync(envPath, 'utf8').split(/\r?\n/);
  for (const line of lines) {
    const trimmed = line.trim();
    if (trimmed === '' || trimmed.startsWith('#') || !trimmed.includes('=')) {
      continue;
    }

    const separatorIndex = trimmed.indexOf('=');
    const key = trimmed.slice(0, separatorIndex).trim();
    let value = trimmed.slice(separatorIndex + 1).trim();
    if (
      value.length >= 2
      && ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'")))
    ) {
      value = value.slice(1, -1);
    }

    defaults[key] = value;
  }

  return defaults;
}

function parseArgs(argv, defaults) {
  const config = {
    labHost: '127.0.0.1',
    labPort: process.env.LAB_HTTP_PORT || defaults.LAB_HTTP_PORT || '18222',
    labUser: process.env.LAB_AUTH_STUB_USER || defaults.LAB_AUTH_STUB_USER || 'lab-local',
    labPassword: process.env.LAB_AUTH_STUB_PASSWORD || defaults.LAB_AUTH_STUB_PASSWORD || 'change-this-lab-password',
    dbSourceKey: 'config_db',
    outputDir: path.join(repoRoot(), 'output', 'playwright', 'sample13-openapi-swagger'),
    headless: true,
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
      throw new Error(`不明な引数です: ${argument}`);
    }

    const body = argument.slice(2);
    const separatorIndex = body.indexOf('=');
    const name = body.slice(0, separatorIndex).trim();
    const value = body.slice(separatorIndex + 1);

    switch (name) {
      case 'lab-host':
        config.labHost = value.trim();
        break;
      case 'lab-port':
        config.labPort = value.trim();
        break;
      case 'lab-user':
        config.labUser = value.trim();
        break;
      case 'lab-password':
        config.labPassword = value;
        break;
      case 'db-source-key':
        config.dbSourceKey = value.trim();
        break;
      case 'output-dir':
        config.outputDir = path.resolve(value.trim());
        break;
      default:
        throw new Error(`不明な option です: --${name}`);
    }
  }

  if (config.labHost === '' || config.labPort === '' || config.dbSourceKey === '') {
    throw new Error('lab host, lab port, db source key は必須です。');
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
    throw new Error('playwright package が見つかりません。PLAYWRIGHT_PACKAGE_ROOT を指定してください。');
  }

  const candidates = [];
  for (const entry of fs.readdirSync(npxRoot, { withFileTypes: true })) {
    if (!entry.isDirectory()) {
      continue;
    }

    const candidate = path.join(npxRoot, entry.name, 'node_modules', 'playwright');
    const packageJsonPath = path.join(candidate, 'package.json');
    if (!fs.existsSync(packageJsonPath)) {
      continue;
    }

    candidates.push({
      path: candidate,
      mtimeMs: fs.statSync(packageJsonPath).mtimeMs,
    });
  }

  if (candidates.length === 0) {
    throw new Error('cached playwright package が見つかりません。PLAYWRIGHT_PACKAGE_ROOT を指定してください。');
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

function ensureDirectory(targetDir) {
  fs.mkdirSync(targetDir, { recursive: true });
}

function timestamp() {
  const now = new Date();
  const pad = (value) => String(value).padStart(2, '0');
  return [
    now.getFullYear(),
    pad(now.getMonth() + 1),
    pad(now.getDate()),
    '-',
    pad(now.getHours()),
    pad(now.getMinutes()),
    pad(now.getSeconds()),
  ].join('');
}

function writeJson(filePath, payload) {
  fs.writeFileSync(filePath, `${JSON.stringify(payload, null, 2)}\n`, 'utf8');
}

function decodeJsonObject(rawText, description) {
  let decoded;
  try {
    decoded = JSON.parse(rawText);
  } catch (error) {
    throw new Error(
      `${description} が JSON として不正です: ${error instanceof Error ? error.message : String(error)}\n\n${rawText}`,
    );
  }

  if (!decoded || Array.isArray(decoded) || typeof decoded !== 'object') {
    throw new Error(`${description} は JSON object である必要があります: ${rawText}`);
  }

  return decoded;
}

function extractJsonObjectFromResponse(rawText, description) {
  const jsonStartIndex = String(rawText || '').indexOf('{');
  if (jsonStartIndex === -1) {
    throw new Error(`${description} から JSON を抽出できませんでした: ${rawText}`);
  }

  return decodeJsonObject(String(rawText).slice(jsonStartIndex), description);
}

async function findOperationCard(page, operationName) {
  const operationCard = page.locator('.operation-card').filter({ hasText: operationName }).first();
  await operationCard.waitFor();
  await operationCard.scrollIntoViewIfNeeded();
  return operationCard;
}

async function setBaseUrl(page, baseUrl) {
  const baseUrlInput = page.locator('input[name="base_url"]');
  await baseUrlInput.waitFor();
  await baseUrlInput.fill(baseUrl);
  await baseUrlInput.evaluate((input) => {
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });
}

async function executeViewerOperation(page, artifactDir, operationName, options = {}) {
  const operationCard = await findOperationCard(page, operationName);
  const requestMethod = String((await operationCard.getAttribute('data-method')) || 'POST').trim().toUpperCase();
  const resolvedUrl = (await operationCard.locator('.resolved-url').innerText()).trim();
  if (options.sourceKey && !resolvedUrl.includes(`db_source_key=${encodeURIComponent(options.sourceKey)}`)) {
    throw new Error(`resolved URL に db_source_key が反映されていません: ${resolvedUrl}`);
  }
  if (options.expectedUrlPrefix && !resolvedUrl.startsWith(options.expectedUrlPrefix)) {
    throw new Error(`resolved URL が想定 base URL を使っていません: ${resolvedUrl}`);
  }

  const requestInput = operationCard.locator('textarea.request-input');
  let requestPayloadText = await requestInput.inputValue();
  if (typeof options.payload !== 'undefined') {
    requestPayloadText = `${JSON.stringify(options.payload, null, 2)}\n`;
    await requestInput.fill(requestPayloadText);
  }

  const normalizedRequestPayloadText = requestPayloadText.trim();
  if (normalizedRequestPayloadText === '' && requestMethod !== 'GET' && requestMethod !== 'HEAD') {
    throw new Error(`${operationName} の request textarea が空です。`);
  }

  let expectedPostData = null;
  if (requestMethod !== 'GET' && requestMethod !== 'HEAD') {
    const decodedRequestPayload = decodeJsonObject(
      normalizedRequestPayloadText,
      `${operationName} の request payload`,
    );
    expectedPostData = JSON.stringify(decodedRequestPayload);
  }

  const responseLocator = operationCard.locator('.response-output');
  const initialResponseText = await responseLocator.innerText();
  const beforePath = path.join(artifactDir, 'swagger-viewer-before-try-it-out.png');
  await page.screenshot({
    path: beforePath,
    fullPage: true,
  });

  const networkResponsePromise = page.waitForResponse((response) => {
    if (response.url() !== resolvedUrl) {
      return false;
    }

    if (response.request().method().toUpperCase() !== requestMethod) {
      return false;
    }

    if (expectedPostData !== null && String(response.request().postData() || '') !== expectedPostData) {
      return false;
    }

    return true;
  }, { timeout: 30000 });

  await operationCard.locator('button.try-request').evaluate((button) => button.click());
  const networkResponse = await networkResponsePromise;
  const networkResponseText = await networkResponse.text();

  try {
    await page.waitForFunction(
      (element, previousText) => (
        element
        && typeof element.textContent === 'string'
        && element.textContent.trim() !== String(previousText).trim()
        && !element.textContent.includes('Loading...')
      ),
      await responseLocator.elementHandle(),
      initialResponseText,
      { timeout: 5000 },
    );
  } catch (_error) {
    // The network response is the source of truth; keep going if DOM repaint is late.
  }

  const responseText = await responseLocator.innerText();
  const afterPath = path.join(artifactDir, 'swagger-viewer-after-try-it-out.png');
  await page.screenshot({
    path: afterPath,
    fullPage: true,
  });

  const responseStatusLine = `HTTP ${networkResponse.status()} ${networkResponse.statusText()}`.trim();
  if (networkResponse.status() !== (options.expectedStatus || 200)) {
    throw new Error(`${responseStatusLine}\n\n${networkResponseText}`);
  }

  const decodedResponse = extractJsonObjectFromResponse(
    networkResponseText,
    `${operationName} の response payload`,
  );

  return {
    operation_name: operationName,
    request_method: requestMethod,
    resolved_url: resolvedUrl,
    request_payload: normalizedRequestPayloadText,
    response_status_line: responseStatusLine,
    response_output_preview: responseText,
    decoded_response: decodedResponse,
    screenshots: [beforePath, afterPath],
  };
}

async function runBrowserSmoke(config, artifactDir) {
  const playwrightModuleId = findPlaywrightPackageRoot();
  const playwright = require(playwrightModuleId);
  const chromeExecutablePath = resolveChromeExecutablePath();
  const launchOptions = {
    headless: config.headless,
  };
  if (chromeExecutablePath !== '') {
    launchOptions.executablePath = chromeExecutablePath;
  }

  const browser = await playwright.chromium.launch(launchOptions);
  const context = await browser.newContext({
    viewport: {
      width: 1600,
      height: 1200,
    },
  });
  const page = await context.newPage();

  const viewerPath = `/runs/swagger/SAMPLE13?source_output_key=OPENAPI-JSON&db_source_key=${encodeURIComponent(config.dbSourceKey)}`;
  const loginUrl = `http://${config.labHost}:${config.labPort}/login?redirect=${encodeURIComponent(viewerPath)}`;
  const expectedViewerUrl = new URL(`http://${config.labHost}:${config.labPort}${viewerPath}`);
  const proxyBaseUrl = `http://${config.labHost}:${config.labPort}/runs/proxy/SAMPLE13/API-PROXY-SERVER`;
  const operationName = 'ApiTask.GetApiTask';

  try {
    await page.goto(loginUrl, { waitUntil: 'domcontentloaded' });
    await page.locator('input[name="username"]').fill(config.labUser);
    await page.locator('input[name="password"]').fill(config.labPassword);
    await Promise.all([
      page.waitForURL((url) => (
        url.pathname === expectedViewerUrl.pathname
        && url.search === expectedViewerUrl.search
      )),
      page.locator('button[type="submit"]').click(),
    ]);

    await page.locator('select[name="db_source_key"]').waitFor();

    const selectedSourceKey = await page.locator('select[name="db_source_key"]').inputValue();
    if (selectedSourceKey !== config.dbSourceKey) {
      throw new Error(`viewer の db_source_key selection が想定外です: ${selectedSourceKey}`);
    }

    await setBaseUrl(page, proxyBaseUrl);

    const result = await executeViewerOperation(page, artifactDir, operationName, {
      sourceKey: config.dbSourceKey,
      expectedUrlPrefix: proxyBaseUrl,
      payload: {
        param_ApiTask_Id_where: 1,
      },
    });

    if (result.decoded_response._status !== 'OK') {
      throw new Error(`response _status が想定外です: ${JSON.stringify(result.decoded_response)}`);
    }

    const row = result.decoded_response.Result;
    if (!row || typeof row !== 'object' || Array.isArray(row)) {
      throw new Error(`Result が object ではありません: ${JSON.stringify(result.decoded_response)}`);
    }

    const title = String(row.Title || '').trim();
    if (title === '') {
      throw new Error(`Result.Title が空です: ${JSON.stringify(result.decoded_response)}`);
    }

    return {
      ok: true,
      viewer_path: viewerPath,
      selected_source_key: selectedSourceKey,
      base_url: proxyBaseUrl,
      operation_name: operationName,
      resolved_url: result.resolved_url,
      response_status_line: result.response_status_line,
      result_title: title,
      result_keys: Object.keys(row),
      response_output_preview: result.response_output_preview,
      screenshots: result.screenshots,
    };
  } finally {
    await context.close();
    await browser.close();
  }
}

async function main() {
  const defaults = parseEnvDefaults();
  const config = parseArgs(process.argv, defaults);

  if (config.help) {
    process.stdout.write(`${usage()}\n`);
    return;
  }

  ensureDirectory(config.outputDir);
  const artifactDir = path.join(config.outputDir, timestamp());
  ensureDirectory(artifactDir);

  const browserResult = await runBrowserSmoke(config, artifactDir);
  const result = {
    ok: true,
    project_key: 'SAMPLE13',
    source_output_key: 'OPENAPI-JSON',
    proxy_source_output_key: 'API-PROXY-SERVER',
    db_source_key: config.dbSourceKey,
    artifact_dir: artifactDir,
    browser: browserResult,
  };

  writeJson(path.join(artifactDir, 'result.json'), result);
  process.stdout.write(`${JSON.stringify(result, null, 2)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error instanceof Error ? error.stack || error.message : String(error)}\n`);
  process.exit(1);
});
