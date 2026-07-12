#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_sample19_material_insight_preview_browser_smoke.js [options]

Options:
  --base-url=URL          admin base URL (default: http://127.0.0.1:18181)
  --username=USER         admin stub username (default: env ADMIN_AUTH_STUB_USER or admin-local)
  --password=PASSWORD     admin stub password (default: env ADMIN_AUTH_STUB_PASSWORD or change-this-admin-password)
  --expect=enabled|off    expected preview route state (default: enabled)
  --headed                launch Chrome headed
  --headless              launch Chrome headless
  --help                  show this help`;
}

function parseArgs(argv) {
  const config = {
    baseUrl: process.env.ADMIN_BASE_URL || 'http://127.0.0.1:18181',
    username: process.env.ADMIN_AUTH_STUB_USER || 'admin-local',
    password: process.env.ADMIN_AUTH_STUB_PASSWORD || 'change-this-admin-password',
    expectedState: process.env.SAMPLE19_MATERIAL_INSIGHT_EXPECT_STATE || 'enabled',
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

    const [name, ...parts] = argument.slice(2).split('=');
    const value = parts.join('=');
    if (name === 'base-url') {
      config.baseUrl = value.replace(/\/+$/, '');
    } else if (name === 'username') {
      config.username = value;
    } else if (name === 'password') {
      config.password = value;
    } else if (name === 'expect') {
      config.expectedState = value;
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  config.baseUrl = config.baseUrl.replace(/\/+$/, '');
  if (!['enabled', 'off'].includes(config.expectedState)) {
    throw new Error('--expect must be enabled or off');
  }

  return config;
}

function findPlaywright() {
  if (process.env.PLAYWRIGHT_PACKAGE_ROOT) {
    return process.env.PLAYWRIGHT_PACKAGE_ROOT;
  }
  try {
    return require.resolve('playwright');
  } catch (_error) {
    const npxRoot = path.join(os.homedir(), '.npm', '_npx');
    const candidates = fs.existsSync(npxRoot)
      ? fs.readdirSync(npxRoot)
        .map((entry) => path.join(npxRoot, entry, 'node_modules/playwright'))
        .filter((candidate) => fs.existsSync(path.join(candidate, 'package.json')))
      : [];
    if (!candidates.length) {
      throw new Error('playwright package was not found. Set PLAYWRIGHT_PACKAGE_ROOT.');
    }
    return candidates[candidates.length - 1];
  }
}

function chromePath() {
  const explicit = process.env.PLAYWRIGHT_CHROME_EXECUTABLE || '';
  return explicit && fs.existsSync(explicit) ? explicit : '';
}

async function login(page, config, targetPath) {
  await page.goto(`${config.baseUrl}/login?redirect=${encodeURIComponent(targetPath)}`, { waitUntil: 'domcontentloaded' });
  await page.locator('input[name="username"]').fill(config.username);
  await page.locator('input[name="password"]').fill(config.password);
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.locator('button[type="submit"]').click(),
  ]);
}

async function main() {
  const config = parseArgs(process.argv);
  if (config.help) {
    process.stdout.write(`${usage()}\n`);
    return;
  }

  const playwright = require(findPlaywright());
  const launchOptions = { headless: config.headless };
  const executablePath = chromePath();
  if (executablePath) {
    launchOptions.executablePath = executablePath;
  }

  const browser = await playwright.chromium.launch(launchOptions);
  const page = await browser.newPage();
  const requests = [];
  page.on('request', (request) => requests.push({ method: request.method(), url: request.url() }));

  const previewPath = '/projects/SAMPLE19/material-insight';
  await page.goto(`${config.baseUrl}${previewPath}`, { waitUntil: 'domcontentloaded' });
  const unauthenticatedUrl = page.url();
  if (!unauthenticatedUrl.includes('/login')) {
    throw new Error(`unauthenticated preview route did not redirect to login: ${unauthenticatedUrl}`);
  }
  await login(page, config, previewPath);
  const previewResponse = await page.goto(`${config.baseUrl}${previewPath}`, { waitUntil: 'domcontentloaded' });
  const previewStatus = previewResponse ? previewResponse.status() : 0;

  const rootCount = await page.locator('[data-material-insight-preview="true"]').count();
  const qaCount = await page.locator('[data-material-insight-qa-card]').count();
  const screenCount = await page.locator('[data-material-insight-ui-screen]').count();
  const prohibitedCount = await page.locator('[data-material-insight-prohibited-action]').count();

  if (config.expectedState === 'off') {
    if (rootCount !== 0 || qaCount !== 0 || screenCount !== 0) {
      throw new Error(`preview markers were visible while expected off: ${JSON.stringify({ rootCount, qaCount, screenCount })}`);
    }
    if (previewStatus !== 404 && !/not found|見つかりません|404/i.test(await page.locator('body').innerText())) {
      throw new Error(`off-state route did not look disabled after login: status=${previewStatus}`);
    }
  } else {
    if (rootCount !== 1) {
      throw new Error(`preview root marker count was ${rootCount}, expected 1`);
    }
    if (qaCount < 3) {
      throw new Error(`expected at least 3 Q&A cards, saw ${qaCount}`);
    }
    if (screenCount < 2) {
      throw new Error(`expected at least 2 UI outline screens, saw ${screenCount}`);
    }
    if (prohibitedCount < 6) {
      throw new Error(`expected prohibited actions, saw ${prohibitedCount}`);
    }
    for (const selector of [
      '[data-material-insight-source-hash="true"]',
      '[data-material-insight-basis="true"]',
      '[data-material-insight-mutation="false"]',
      '[data-material-insight-ai-call="false"]',
      '[data-material-insight-prohibited-action="apply"]',
      '[data-material-insight-prohibited-action="route_execution"]',
    ]) {
      if ((await page.locator(selector).count()) !== 1) {
        throw new Error(`expected exactly one marker: ${selector}`);
      }
    }
    for (const forbidden of [
      'form',
      'button',
      'script',
      '[data-runtime-execute]',
      '[data-guarded-click-submit]',
    ]) {
      if ((await page.locator(forbidden).count()) !== 0) {
        throw new Error(`forbidden element/control rendered: ${forbidden}`);
      }
    }
  }

  const previewPosts = requests.filter((request) => request.method === 'POST' && request.url.includes('/material-insight'));
  const actionPosts = requests.filter((request) => request.method === 'POST' && /(apply|import|build|publish)/i.test(request.url));
  if (previewPosts.length !== 0 || actionPosts.length !== 0) {
    throw new Error(`unexpected preview/action POST requests: ${JSON.stringify({ previewPosts, actionPosts })}`);
  }

  await browser.close();
  process.stdout.write(`${JSON.stringify({
    ok: true,
    expected_state: config.expectedState,
    unauthenticated_redirected_to_login: unauthenticatedUrl.includes('/login'),
    preview_status: previewStatus,
    preview_root_count: rootCount,
    qa_card_count: qaCount,
    ui_screen_count: screenCount,
    prohibited_action_count: prohibitedCount,
    preview_post_count: previewPosts.length,
    action_post_count: actionPosts.length,
    request_count: requests.length,
  }, null, 2)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.stack || error.message}\n`);
  process.exitCode = 1;
});
