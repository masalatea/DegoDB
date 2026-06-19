#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_sample25_auth_swagger_try_it_out.js [options]

Options:
  --lab-host=HOST                lab host (default: 127.0.0.1)
  --lab-port=PORT                lab port (default: LAB_HTTP_PORT or 18282)
  --lab-user=USER                lab stub username
  --lab-password=PASS            lab stub password
  --project-token=TOKEN          ProjectToken helper value (default: sample25-token)
  --db-source-key=KEY            runtime db source key (default: config_db)
  --output-dir=PATH              artifact directory root (default: output/playwright/sample25-auth-swagger)
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
  for (const line of fs.readFileSync(envPath, 'utf8').split(/\r?\n/)) {
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
    labPort: process.env.LAB_HTTP_PORT || defaults.LAB_HTTP_PORT || '18282',
    labUser: process.env.LAB_AUTH_STUB_USER || defaults.LAB_AUTH_STUB_USER || 'lab-local',
    labPassword: process.env.LAB_AUTH_STUB_PASSWORD || defaults.LAB_AUTH_STUB_PASSWORD || 'change-this-lab-password',
    projectToken: 'sample25-token',
    dbSourceKey: 'config_db',
    outputDir: path.join(repoRoot(), 'output', 'playwright', 'sample25-auth-swagger'),
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
      case 'project-token':
        config.projectToken = value;
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

  if (config.labHost === '' || config.labPort === '' || config.dbSourceKey === '' || config.projectToken === '') {
    throw new Error('lab host, lab port, db source key, project token は必須です。');
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
    if (fs.existsSync(packageJsonPath)) {
      candidates.push({
        path: candidate,
        mtimeMs: fs.statSync(packageJsonPath).mtimeMs,
      });
    }
  }

  if (candidates.length === 0) {
    throw new Error('cached playwright package が見つかりません。PLAYWRIGHT_PACKAGE_ROOT を指定してください。');
  }

  candidates.sort((left, right) => right.mtimeMs - left.mtimeMs);
  return candidates[0].path;
}

function resolveChromeExecutablePath() {
  for (const candidate of [
    process.env.PLAYWRIGHT_CHROME_EXECUTABLE || '',
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/Applications/Chromium.app/Contents/MacOS/Chromium',
    '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
  ]) {
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
  return `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}-${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
}

function writeJson(filePath, payload) {
  fs.writeFileSync(filePath, `${JSON.stringify(payload, null, 2)}\n`, 'utf8');
}

function decodeJsonObject(rawText, description) {
  try {
    const decoded = JSON.parse(rawText);
    if (!decoded || Array.isArray(decoded) || typeof decoded !== 'object') {
      throw new Error('not object');
    }

    return decoded;
  } catch (error) {
    throw new Error(`${description} が JSON object として不正です: ${error instanceof Error ? error.message : String(error)}\n\n${rawText}`);
  }
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

async function executeAuthRequiredOperation(page, artifactDir, config) {
  const operationName = 'EbookEditorChapter.GetEditorEbookChapter';
  const operationCard = await findOperationCard(page, operationName);
  const requestMethod = String((await operationCard.getAttribute('data-method')) || 'POST').trim().toUpperCase();
  const resolvedUrl = (await operationCard.locator('.resolved-url').innerText()).trim();
  const requestInput = operationCard.locator('textarea.request-input');
  const payload = {
    param_EbookEditorChapter_Id_where: 1,
  };
  const requestPayloadText = `${JSON.stringify(payload, null, 2)}\n`;
  await requestInput.fill(requestPayloadText);

  const projectTokenInput = page.locator('input[data-auth-helper="project-token"]');
  await projectTokenInput.waitFor();
  await projectTokenInput.fill(config.projectToken);

  const beforePath = path.join(artifactDir, 'sample25-auth-swagger-before.png');
  await page.screenshot({
    path: beforePath,
    fullPage: true,
  });

  const expectedPostData = JSON.stringify({
    ...payload,
    TOKEN: config.projectToken,
  });
  const networkResponsePromise = page.waitForResponse((response) => (
    response.url() === resolvedUrl
    && response.request().method().toUpperCase() === requestMethod
    && String(response.request().postData() || '') === expectedPostData
  ), { timeout: 30000 });

  await operationCard.locator('button.try-request').evaluate((button) => button.click());
  const networkResponse = await networkResponsePromise;
  const networkResponseText = await networkResponse.text();
  const responseStatusLine = `HTTP ${networkResponse.status()} ${networkResponse.statusText()}`.trim();

  const responseLocator = operationCard.locator('.response-output');
  try {
    await page.waitForFunction(
      (element) => element && typeof element.textContent === 'string' && !element.textContent.includes('Loading...'),
      await responseLocator.elementHandle(),
      { timeout: 5000 },
    );
  } catch (_error) {
    // The network response is the source of truth.
  }

  const afterPath = path.join(artifactDir, 'sample25-auth-swagger-after.png');
  await page.screenshot({
    path: afterPath,
    fullPage: true,
  });

  if (networkResponse.status() !== 200) {
    throw new Error(`${responseStatusLine}\n\n${networkResponseText}`);
  }

  const decodedResponse = extractJsonObjectFromResponse(networkResponseText, `${operationName} response`);
  if (decodedResponse._status !== 'OK') {
    throw new Error(`response _status が想定外です: ${JSON.stringify(decodedResponse)}`);
  }

  const row = decodedResponse.Result;
  if (!row || typeof row !== 'object' || Array.isArray(row) || String(row.ChapterTitle || '').trim() === '') {
    throw new Error(`Result row が想定外です: ${JSON.stringify(decodedResponse)}`);
  }

  return {
    operation_name: operationName,
    request_method: requestMethod,
    resolved_url: resolvedUrl,
    request_payload_sent: expectedPostData,
    response_status_line: responseStatusLine,
    chapter_title: String(row.ChapterTitle),
    response_output_preview: await responseLocator.innerText(),
    screenshots: [beforePath, afterPath],
  };
}

async function runBrowserSmoke(config, artifactDir) {
  const playwright = require(findPlaywrightPackageRoot());
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

  const viewerPath = `/runs/swagger/SAMPLE25?source_output_key=OPENAPI-JSON&db_source_key=${encodeURIComponent(config.dbSourceKey)}`;
  const loginUrl = `http://${config.labHost}:${config.labPort}/login?redirect=${encodeURIComponent(viewerPath)}`;
  const expectedViewerUrl = new URL(`http://${config.labHost}:${config.labPort}${viewerPath}`);
  const proxyBaseUrl = `http://${config.labHost}:${config.labPort}/runs/proxy/SAMPLE25/AUTH-PROXY-SERVER`;

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
    const operation = await executeAuthRequiredOperation(page, artifactDir, config);

    return {
      ok: true,
      viewer_path: viewerPath,
      selected_source_key: selectedSourceKey,
      base_url: proxyBaseUrl,
      operation,
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
    project_key: 'SAMPLE25',
    source_output_key: 'OPENAPI-JSON',
    proxy_source_output_key: 'AUTH-PROXY-SERVER',
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
