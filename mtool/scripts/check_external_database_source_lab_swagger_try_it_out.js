#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');
const { spawnSync } = require('child_process');

function usage() {
  return `Usage:
  node mtool/scripts/check_external_database_source_lab_swagger_try_it_out.js [options]

Options:
  --project-key=KEY              project key (default: MTOOL)
  --source-key=KEY               source key to create or reuse
  --table-name=NAME              source table name (default: lab_experiments)
  --lab-host=HOST                lab host (default: 127.0.0.1)
  --lab-port=PORT                lab port (default: .env LAB_HTTP_PORT or 8082)
  --lab-user=USER                lab stub username
  --lab-password=PASS            lab stub password
  --output-dir=PATH              artifact directory root (default: output/playwright/external-source-lab-swagger)
  --headed                       launch Chrome headed
  --headless                     launch Chrome headless
  --list-only                    verify only the list operation and skip insert/update/delete cycle
  --crud-cycle                   run insert/get/update/delete/list cleanup cycle when supported (default)
  --skip-prepare                 skip PHP prepare smoke and reuse --source-key
  --keep-source                  keep the created source instead of deleting it
  --help                         show this help

Forwarded setup options:
  Any option accepted by php mtool/scripts/check_external_database_source_lab_swagger_flow.php
  may also be passed here. Browser-only flags above are filtered out before forwarding.`;
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
    projectKey: 'MTOOL',
    sourceKey: '',
    tableName: 'lab_experiments',
    labHost: '127.0.0.1',
    labPort: defaults.LAB_HTTP_PORT || '8082',
    labUser: defaults.LAB_AUTH_STUB_USER || 'lab-local',
    labPassword: defaults.LAB_AUTH_STUB_PASSWORD || '',
    outputDir: path.join(repoRoot(), 'output', 'playwright', 'external-source-lab-swagger'),
    headless: true,
    crudCycle: true,
    skipPrepare: false,
    keepSource: false,
    forwardedSetupArgs: [],
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

    if (argument === '--skip-prepare') {
      config.skipPrepare = true;
      continue;
    }

    if (argument === '--list-only') {
      config.crudCycle = false;
      continue;
    }

    if (argument === '--crud-cycle') {
      config.crudCycle = true;
      continue;
    }

    if (argument === '--keep-source') {
      config.keepSource = true;
      config.forwardedSetupArgs.push(argument);
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
      case 'project-key':
        config.projectKey = value.trim();
        break;
      case 'source-key':
        config.sourceKey = value.trim();
        break;
      case 'table-name':
        config.tableName = value.trim();
        break;
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
      case 'output-dir':
        config.outputDir = path.resolve(value.trim());
        break;
      default:
        break;
    }

    if (name !== 'output-dir') {
      config.forwardedSetupArgs.push(argument);
    }
  }

  if (config.skipPrepare && config.sourceKey === '') {
    throw new Error('--skip-prepare を使う場合は --source-key が必須です。');
  }

  if (config.projectKey === '' || config.tableName === '') {
    throw new Error('project key と table name は必須です。');
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

    const stats = fs.statSync(packageJsonPath);
    candidates.push({
      path: candidate,
      mtimeMs: stats.mtimeMs,
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

function runCommand(command, args, options = {}) {
  const result = spawnSync(command, args, {
    cwd: repoRoot(),
    encoding: 'utf8',
    maxBuffer: 20 * 1024 * 1024,
    ...options,
  });

  if (result.error) {
    throw result.error;
  }

  if (result.status !== 0) {
    const detail = (result.stderr || result.stdout || '').trim();
    throw new Error(`${command} ${args.join(' ')} failed: ${detail}`);
  }

  return result.stdout;
}

function forwardedCleanupArgs(forwardedSetupArgs) {
  return forwardedSetupArgs.filter((argument) => (
    argument.startsWith('--config-db-host-port=')
    || argument.startsWith('--config-db-name=')
    || argument.startsWith('--config-db-user=')
    || argument.startsWith('--config-db-password=')
  ));
}

function prepareExternalSource(config, artifactDir) {
  if (config.skipPrepare) {
    return {
      ok: true,
      project_key: config.projectKey,
      table_name: config.tableName,
      source_key: config.sourceKey,
      created_by_script: false,
      prepare_skipped: true,
      checks: [],
    };
  }

  const stdout = runCommand(
    'php',
    ['mtool/scripts/check_external_database_source_lab_swagger_flow.php', ...config.forwardedSetupArgs, '--keep-source'],
  );
  const decoded = JSON.parse(stdout);
  if (!decoded || decoded.ok !== true || typeof decoded.source_key !== 'string' || decoded.source_key.trim() === '') {
    throw new Error('prepare smoke の JSON 出力が不正です。');
  }

  writeJson(path.join(artifactDir, 'prepare.json'), decoded);

  return {
    ...decoded,
    created_by_script: true,
    prepare_skipped: false,
  };
}

function formatSqlDateTime(date = new Date()) {
  const pad = (value) => String(value).padStart(2, '0');
  return [
    date.getFullYear(),
    '-',
    pad(date.getMonth() + 1),
    '-',
    pad(date.getDate()),
    ' ',
    pad(date.getHours()),
    ':',
    pad(date.getMinutes()),
    ':',
    pad(date.getSeconds()),
  ].join('');
}

function operationArtifactSlug(operationName) {
  return String(operationName || '')
    .trim()
    .replace(/[^A-Za-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .toLowerCase() || 'operation';
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

function buildLabExperimentsCrudFixture(projectKey) {
  const uniqueToken = `${Date.now()}-${process.pid}`;
  const baseTimestamp = formatSqlDateTime();
  const updatedAt = formatSqlDateTime(new Date(Date.now() + 60 * 1000));
  const experimentKey = `EXP-SWAGGER-${uniqueToken}`.slice(0, 64);
  const insertedName = `Swagger Browser Smoke ${uniqueToken}`;
  const updatedName = `${insertedName} Updated`.slice(0, 191);

  return {
    experimentKey,
    insertedName,
    updatedName,
    insertedRuntimeTarget: 'external-source-smoke',
    updatedRuntimeTarget: 'external-source-smoke-updated',
    executedBy: 'codex-browser-smoke',
    insertedStatus: 'draft',
    updatedStatus: 'ready',
    insertedNotes: `insert via swagger browser smoke ${uniqueToken}`,
    updatedNotes: `update via swagger browser smoke ${uniqueToken}`,
    createdAt: baseTimestamp,
    updatedAt,
    projectKey,
  };
}

async function executeViewerOperation(page, artifactDir, operationName, options = {}) {
  const operationCard = await findOperationCard(page, operationName);
  const requestMethod = String((await operationCard.getAttribute('data-method')) || 'POST').trim().toUpperCase();
  const resolvedUrl = (await operationCard.locator('.resolved-url').innerText()).trim();
  if (options.sourceKey && !resolvedUrl.includes(`db_source_key=${encodeURIComponent(options.sourceKey)}`)) {
    throw new Error(`resolved URL に db_source_key が反映されていません: ${resolvedUrl}`);
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
  const screenshotPaths = [];

  if (options.beforeScreenshotName) {
    const beforePath = path.join(artifactDir, options.beforeScreenshotName);
    await page.screenshot({
      path: beforePath,
      fullPage: true,
    });
    screenshotPaths.push(beforePath);
  }

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

  let networkResponse;
  try {
    networkResponse = await networkResponsePromise;
  } catch (error) {
    const responsePreview = await responseLocator.innerText();
    throw new Error(
      [
        error instanceof Error ? error.message : String(error),
        `operation=${operationName}`,
        `request_method=${requestMethod}`,
        `resolved_url=${resolvedUrl}`,
        `request_payload=${normalizedRequestPayloadText}`,
        `response_output=${responsePreview.trim()}`,
      ].join('\n\n'),
    );
  }

  const networkResponseText = await networkResponse.text();
  const responseStatusLine = `HTTP ${networkResponse.status()} ${networkResponse.statusText()}`.trim();

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
    // Keep the network response as the source of truth even if DOM repaint is late.
  }

  const responseText = await responseLocator.innerText();

  if (options.afterScreenshotName) {
    const afterPath = path.join(artifactDir, options.afterScreenshotName);
    await page.screenshot({
      path: afterPath,
      fullPage: true,
    });
    screenshotPaths.push(afterPath);
  }

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
    screenshots: screenshotPaths,
  };
}

async function runLabExperimentsCrudCycle(page, artifactDir, projectKey, sourceKey, listResult) {
  const fixture = buildLabExperimentsCrudFixture(projectKey);
  const insertOperationName = 'lab_experiments.Insertlab_experiments';
  const getOperationName = 'lab_experiments.Getlab_experiments';
  const updateOperationName = 'lab_experiments.Updatelab_experiments';
  const deleteOperationName = 'lab_experiments.Deletelab_experiments';
  const listOperationName = 'lab_experiments.Getlab_experimentsList';

  const insertResult = await executeViewerOperation(page, artifactDir, insertOperationName, {
    payload: {
      lab_experimentsObj: {
        experiment_key: fixture.experimentKey,
        project_key: fixture.projectKey,
        name: fixture.insertedName,
        execution_status: fixture.insertedStatus,
        runtime_target: fixture.insertedRuntimeTarget,
        executed_by: fixture.executedBy,
        notes: fixture.insertedNotes,
        created_at: fixture.createdAt,
        updated_at: fixture.createdAt,
      },
    },
    sourceKey,
    beforeScreenshotName: `crud-${operationArtifactSlug(insertOperationName)}-before.png`,
    afterScreenshotName: `crud-${operationArtifactSlug(insertOperationName)}-after.png`,
  });
  if (insertResult.decoded_response._status !== 'OK') {
    throw new Error(`insert response が想定外です: ${JSON.stringify(insertResult.decoded_response)}`);
  }

  const insertedId = String(insertResult.decoded_response.InsertID || '').trim();
  if (!/^\d+$/.test(insertedId) || insertedId === '0') {
    throw new Error(`insert id が不正です: ${JSON.stringify(insertResult.decoded_response)}`);
  }

  const getInsertedResult = await executeViewerOperation(page, artifactDir, getOperationName, {
    payload: {
      param_lab_experiments_id_where: insertedId,
    },
    sourceKey,
    beforeScreenshotName: `crud-${operationArtifactSlug(getOperationName)}-inserted-before.png`,
    afterScreenshotName: `crud-${operationArtifactSlug(getOperationName)}-inserted-after.png`,
  });
  if (getInsertedResult.decoded_response._status !== 'OK') {
    throw new Error(`get inserted response が想定外です: ${JSON.stringify(getInsertedResult.decoded_response)}`);
  }

  const insertedRow = getInsertedResult.decoded_response.Result;
  if (!insertedRow || typeof insertedRow !== 'object' || String(insertedRow.id || '') !== insertedId) {
    throw new Error(`inserted row の取得結果が不正です: ${JSON.stringify(getInsertedResult.decoded_response)}`);
  }
  if (String(insertedRow.name || '') !== fixture.insertedName) {
    throw new Error(`inserted row の name が不正です: ${JSON.stringify(getInsertedResult.decoded_response)}`);
  }

  const updatePayload = {
    lab_experimentsObj: {
      ...insertedRow,
      experiment_key: fixture.experimentKey,
      project_key: fixture.projectKey,
      name: fixture.updatedName,
      execution_status: fixture.updatedStatus,
      runtime_target: fixture.updatedRuntimeTarget,
      executed_by: fixture.executedBy,
      notes: fixture.updatedNotes,
      created_at: String(insertedRow.created_at || fixture.createdAt),
      updated_at: fixture.updatedAt,
    },
  };
  const updateResult = await executeViewerOperation(page, artifactDir, updateOperationName, {
    payload: updatePayload,
    sourceKey,
    beforeScreenshotName: `crud-${operationArtifactSlug(updateOperationName)}-before.png`,
    afterScreenshotName: `crud-${operationArtifactSlug(updateOperationName)}-after.png`,
  });
  if (updateResult.decoded_response._status !== 'OK') {
    throw new Error(`update response が想定外です: ${JSON.stringify(updateResult.decoded_response)}`);
  }

  const getUpdatedResult = await executeViewerOperation(page, artifactDir, getOperationName, {
    payload: {
      param_lab_experiments_id_where: insertedId,
    },
    sourceKey,
    beforeScreenshotName: `crud-${operationArtifactSlug(getOperationName)}-updated-before.png`,
    afterScreenshotName: `crud-${operationArtifactSlug(getOperationName)}-updated-after.png`,
  });
  if (getUpdatedResult.decoded_response._status !== 'OK') {
    throw new Error(`get updated response が想定外です: ${JSON.stringify(getUpdatedResult.decoded_response)}`);
  }

  const updatedRow = getUpdatedResult.decoded_response.Result;
  if (!updatedRow || typeof updatedRow !== 'object' || String(updatedRow.id || '') !== insertedId) {
    throw new Error(`updated row の取得結果が不正です: ${JSON.stringify(getUpdatedResult.decoded_response)}`);
  }
  if (String(updatedRow.name || '') !== fixture.updatedName) {
    throw new Error(`updated row の name が不正です: ${JSON.stringify(getUpdatedResult.decoded_response)}`);
  }
  if (String(updatedRow.execution_status || '') !== fixture.updatedStatus) {
    throw new Error(`updated row の status が不正です: ${JSON.stringify(getUpdatedResult.decoded_response)}`);
  }

  const deleteResult = await executeViewerOperation(page, artifactDir, deleteOperationName, {
    payload: {
      param_lab_experiments_id_where: insertedId,
    },
    sourceKey,
    beforeScreenshotName: `crud-${operationArtifactSlug(deleteOperationName)}-before.png`,
    afterScreenshotName: `crud-${operationArtifactSlug(deleteOperationName)}-after.png`,
  });
  if (deleteResult.decoded_response._status !== 'OK') {
    throw new Error(`delete response が想定外です: ${JSON.stringify(deleteResult.decoded_response)}`);
  }

  const listAfterDeleteResult = await executeViewerOperation(page, artifactDir, listOperationName, {
    sourceKey,
    beforeScreenshotName: `crud-${operationArtifactSlug(listOperationName)}-after-delete-before.png`,
    afterScreenshotName: `crud-${operationArtifactSlug(listOperationName)}-after-delete-after.png`,
  });
  if (
    listAfterDeleteResult.decoded_response._status !== 'OK'
    || !Array.isArray(listAfterDeleteResult.decoded_response.Result)
  ) {
    throw new Error(`list after delete response が想定外です: ${JSON.stringify(listAfterDeleteResult.decoded_response)}`);
  }

  const finalRowNames = listAfterDeleteResult.decoded_response.Result
    .map((row) => (row && typeof row.name === 'string' ? row.name.trim() : ''))
    .filter((value) => value !== '');
  if (finalRowNames.includes(fixture.insertedName) || finalRowNames.includes(fixture.updatedName)) {
    throw new Error(`delete 後の list に smoke row が残っています: ${JSON.stringify(finalRowNames)}`);
  }

  return {
    executed: true,
    insert_operation_name: insertOperationName,
    get_operation_name: getOperationName,
    update_operation_name: updateOperationName,
    delete_operation_name: deleteOperationName,
    inserted_id: insertedId,
    experiment_key: fixture.experimentKey,
    inserted_name: fixture.insertedName,
    updated_name: fixture.updatedName,
    initial_row_count: listResult.row_count,
    final_row_count: Array.isArray(listAfterDeleteResult.decoded_response.Result)
      ? listAfterDeleteResult.decoded_response.Result.length
      : 0,
    final_row_names: finalRowNames,
    operations: {
      insert: insertResult,
      get_inserted: getInsertedResult,
      update: updateResult,
      get_updated: getUpdatedResult,
      delete: deleteResult,
      list_after_delete: listAfterDeleteResult,
    },
  };
}

async function runBrowserSmoke(config, prepared, artifactDir) {
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

  const sourceKey = String(prepared.source_key || config.sourceKey).trim();
  const tableName = String(prepared.table_name || config.tableName).trim();
  const listOperationName = `${tableName}.Get${tableName}List`;
  const viewerPath = `/runs/swagger/${encodeURIComponent(config.projectKey)}?source_output_key=OPENAPI-JSON&db_source_key=${encodeURIComponent(sourceKey)}`;
  const loginUrl = `http://${config.labHost}:${config.labPort}/login?redirect=${encodeURIComponent(viewerPath)}`;
  const expectedViewerUrl = new URL(`http://${config.labHost}:${config.labPort}${viewerPath}`);

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
    if (selectedSourceKey !== sourceKey) {
      throw new Error(`viewer の db_source_key selection が想定外です: ${selectedSourceKey}`);
    }

    const listResult = await executeViewerOperation(page, artifactDir, listOperationName, {
      sourceKey,
      beforeScreenshotName: 'swagger-viewer-before-try-it-out.png',
      afterScreenshotName: 'swagger-viewer-after-try-it-out.png',
    });
    if (listResult.decoded_response._status !== 'OK' || !Array.isArray(listResult.decoded_response.Result)) {
      throw new Error(`list response payload が想定外です: ${JSON.stringify(listResult.decoded_response)}`);
    }

    const rowNames = listResult.decoded_response.Result
      .map((row) => (row && typeof row.name === 'string' ? row.name.trim() : ''))
      .filter((value) => value !== '');

    let crudCycle = {
      executed: false,
      skipped_reason: '',
    };
    if (!config.crudCycle) {
      crudCycle.skipped_reason = 'list-only mode が指定されました。';
    } else if (tableName !== 'lab_experiments') {
      crudCycle.skipped_reason = 'current CRUD browser smoke は lab_experiments 専用です。';
    } else {
      crudCycle = await runLabExperimentsCrudCycle(
        page,
        artifactDir,
        config.projectKey,
        sourceKey,
        {
          row_count: listResult.decoded_response.Result.length,
          row_names: rowNames,
        },
      );
    }

    return {
      ok: true,
      viewer_path: viewerPath,
      selected_source_key: selectedSourceKey,
      list_operation_name: listOperationName,
      resolved_url: listResult.resolved_url,
      response_status_line: listResult.response_status_line,
      response_output_preview: listResult.response_output_preview,
      row_count: listResult.decoded_response.Result.length,
      row_names: rowNames,
      screenshots: listResult.screenshots,
      crud_cycle: crudCycle,
    };
  } finally {
    await context.close();
    await browser.close();
  }
}

function cleanupPreparedSource(config, prepared, artifactDir) {
  if (!prepared.created_by_script || config.keepSource) {
    return {
      attempted: false,
      deleted: false,
      source_key: prepared.source_key || config.sourceKey,
    };
  }

  try {
    const stdout = runCommand(
      'php',
      [
        'mtool/scripts/delete_database_source.php',
        `--source-key=${prepared.source_key}`,
        ...forwardedCleanupArgs(config.forwardedSetupArgs),
      ],
    );
    const decoded = JSON.parse(stdout);
    writeJson(path.join(artifactDir, 'cleanup.json'), decoded);

    return {
      attempted: true,
      deleted: decoded.deleted === true,
      source_key: prepared.source_key,
      error: '',
    };
  } catch (error) {
    const cleanupResult = {
      attempted: true,
      deleted: false,
      source_key: prepared.source_key,
      error: error instanceof Error ? error.message : String(error),
    };
    writeJson(path.join(artifactDir, 'cleanup.json'), cleanupResult);

    return cleanupResult;
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

  const prepared = prepareExternalSource(config, artifactDir);
  config.sourceKey = String(prepared.source_key || config.sourceKey).trim();
  config.tableName = String(prepared.table_name || config.tableName).trim();

  let browserResult = null;
  let cleanupResult = null;
  let ok = false;

  try {
    browserResult = await runBrowserSmoke(config, prepared, artifactDir);
    ok = true;
  } finally {
    cleanupResult = cleanupPreparedSource(config, prepared, artifactDir);
  }

  if (cleanupResult && cleanupResult.attempted && cleanupResult.deleted !== true && !config.keepSource) {
    ok = false;
  }

  const result = {
    ok,
    project_key: config.projectKey,
    source_key: config.sourceKey,
    table_name: config.tableName,
    artifact_dir: artifactDir,
    prepare: {
      prepare_skipped: prepared.prepare_skipped === true,
      created_by_script: prepared.created_by_script === true,
      checks: prepared.checks || [],
    },
    browser: browserResult,
    cleanup: cleanupResult,
  };

  writeJson(path.join(artifactDir, 'result.json'), result);
  process.stdout.write(`${JSON.stringify(result, null, 2)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error instanceof Error ? error.stack || error.message : String(error)}\n`);
  process.exit(1);
});
