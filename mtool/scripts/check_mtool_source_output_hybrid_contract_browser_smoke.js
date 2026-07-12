#!/usr/bin/env node
'use strict';

const fs = require('fs');
const os = require('os');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_mtool_source_output_hybrid_contract_browser_smoke.js [options]

Options:
  --base-url=URL          admin base URL (default: http://127.0.0.1:8081)
  --username=USER         admin stub username (default: env ADMIN_AUTH_STUB_USER or admin-local)
  --password=PASSWORD     admin stub password (default: env ADMIN_AUTH_STUB_PASSWORD or change-this-admin-password)
  --expect=enabled|off    expected inspection route state (default: enabled)
  --selector=KEY          source_output_key selector for enabled route (default: OPENAPI-JSON)
  --headed                launch Chrome headed
  --headless              launch Chrome headless
  --help                  show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function parseArgs(argv) {
  const config = {
    baseUrl: process.env.ADMIN_BASE_URL || 'http://127.0.0.1:8081',
    username: process.env.ADMIN_AUTH_STUB_USER || 'admin-local',
    password: process.env.ADMIN_AUTH_STUB_PASSWORD || 'change-this-admin-password',
    expectedState: process.env.MTOOL_INSPECTION_EXPECT_STATE || 'enabled',
    selector: process.env.MTOOL_INSPECTION_SELECTOR || 'OPENAPI-JSON',
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
    const separator = body.indexOf('=');
    const name = body.slice(0, separator);
    const value = body.slice(separator + 1);
    if (name === 'base-url') {
      config.baseUrl = value.replace(/\/+$/, '');
    } else if (name === 'username') {
      config.username = value;
    } else if (name === 'password') {
      config.password = value;
    } else if (name === 'expect') {
      config.expectedState = value;
    } else if (name === 'selector') {
      config.selector = value;
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
      ? fs.readdirSync(npxRoot).map((entry) => path.join(npxRoot, entry, 'node_modules/playwright')).filter((candidate) => fs.existsSync(path.join(candidate, 'package.json')))
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

  const canonicalSourceOutputsPath = '/projects/MTOOL/source-outputs';
  const inspectionRoutePath = '/projects/MTOOL/source-outputs/no-code-inspection';
  const inspectionPath = `${inspectionRoutePath}?source_output_key=${encodeURIComponent(config.selector)}`;
  await page.goto(`${config.baseUrl}${inspectionPath}`, { waitUntil: 'domcontentloaded' });
  const unauthenticatedUrl = page.url();
  if (!unauthenticatedUrl.includes('/login')) {
    throw new Error(`unauthenticated inspection route did not redirect to login: ${unauthenticatedUrl}`);
  }

  await login(page, config, inspectionPath);

  await page.goto(`${config.baseUrl}${canonicalSourceOutputsPath}`, { waitUntil: 'domcontentloaded' });
  const entryPoint = page.locator('[data-mtool-no-code-inspection-entry-point="true"]').first();
  const entryPointCount = await entryPoint.count();
  if (config.expectedState === 'off') {
    if (entryPointCount !== 0) {
      throw new Error('canonical Source Outputs entry point was visible while feature flag was expected off');
    }
  } else {
    if (entryPointCount !== 1) {
      throw new Error(`canonical Source Outputs entry point count was ${entryPointCount}, expected 1`);
    }
    const entryPointHref = await entryPoint.locator('a').first().getAttribute('href');
    if (entryPointHref !== inspectionRoutePath) {
      throw new Error(`unexpected canonical Source Outputs entry point href: ${entryPointHref}`);
    }
    const entryPointText = await entryPoint.innerText();
    if (!entryPointText.includes('Open read-only no-code inspection')) {
      throw new Error(`canonical Source Outputs entry point text was unexpected: ${entryPointText}`);
    }
    if (!entryPointText.includes('does not replace canonical Source Outputs')) {
      throw new Error('canonical Source Outputs entry point did not explain non-replacement boundary');
    }
  }
  if ((await page.locator('[data-runtime-execute]').count()) !== 0) {
    throw new Error('runtime execution controls were rendered on canonical Source Outputs page');
  }
  if ((await page.locator('[data-guarded-click-submit]').count()) !== 0) {
    throw new Error('guarded submit controls were rendered on canonical Source Outputs page');
  }

  await page.goto(`${config.baseUrl}${inspectionPath}`, { waitUntil: 'domcontentloaded' });

  const marker = page.locator('[data-mtool-no-code-hybrid-contract="true"]').first();
  const markerCount = await marker.count();
  const bodyText = await page.locator('body').innerText();

  if (config.expectedState === 'off') {
    if (markerCount !== 0) {
      throw new Error('hybrid contract marker was visible while inspection route was expected off');
    }
    if (!/not found|見つかりません|404/i.test(bodyText)) {
      throw new Error(`off-state page did not look like not found: ${bodyText.slice(0, 300)}`);
    }
  } else {
    if (markerCount !== 1) {
      throw new Error(`hybrid contract marker count was ${markerCount}, expected 1`);
    }
    const rawContract = await marker.textContent();
    const contract = JSON.parse(rawContract || '{}');
    if (contract.contract_version !== 'no-code-mtool-source-output-inspection-hybrid-v0') {
      throw new Error(`unexpected contract_version: ${contract.contract_version}`);
    }
    if (contract.route?.path !== inspectionRoutePath) {
      throw new Error(`unexpected route path: ${contract.route?.path}`);
    }
    if (!Array.isArray(contract.excluded_operations) || !contract.excluded_operations.includes('generated_post_execution')) {
      throw new Error('generated_post_execution exclusion was not present in contract');
    }
    if ((await page.locator('[data-runtime-execute]').count()) !== 0) {
      throw new Error('runtime execution controls were rendered');
    }
    if ((await page.locator('[data-guarded-click-submit]').count()) !== 0) {
      throw new Error('guarded submit controls were rendered');
    }
    if ((await page.locator('[data-screen-key="mtool_source_output_review_form"]').count()) !== 0) {
      throw new Error('editable form screen was rendered');
    }
    if ((await page.locator('[data-canonical-source-outputs-link]').count()) !== 1) {
      throw new Error('canonical Source Outputs return link was not rendered exactly once');
    }
  }

  const inspectionPosts = requests.filter((request) => request.method === 'POST' && request.url.includes('/source-outputs/no-code-inspection'));
  const sourceOutputOperationPosts = requests.filter((request) => request.method === 'POST' && /\/source-outputs\/.+\/operations\//.test(request.url));
  if (inspectionPosts.length !== 0 || sourceOutputOperationPosts.length !== 0) {
    throw new Error(`unexpected Source Output POST requests: ${JSON.stringify({ inspectionPosts, sourceOutputOperationPosts })}`);
  }

  await browser.close();
  process.stdout.write(`${JSON.stringify({
    ok: true,
    expected_state: config.expectedState,
    unauthenticated_redirected_to_login: unauthenticatedUrl.includes('/login'),
    canonical_entry_point_count: entryPointCount,
    hybrid_contract_marker_count: markerCount,
    inspection_post_count: inspectionPosts.length,
    source_output_operation_post_count: sourceOutputOperationPosts.length,
    request_count: requests.length,
  }, null, 2)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.stack || error.message}\n`);
  process.exitCode = 1;
});
