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
  --profile=sample07|sample18|sample28|sample29|sample31
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
  --runtime-filter-dom-only      stop after checking public runtime filter controls
  --runtime-enabled-candidate-surface
                                 stop after checking enabled-candidate managed action surface
  --runtime-ui-authority-stub-probe
                                 authenticate, require live create availability, and stub one guarded POST
  --runtime-managed-outbox-authority
                                 require live managed-outbox authority without mutating action state in the test
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
    runtimeFilterDomOnly: false,
    runtimeEnabledCandidateSurface: false,
    runtimeUiAuthorityStubProbe: false,
    runtimeManagedOutboxAuthority: false,
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
    if (argument === '--runtime-filter-dom-only') {
      config.runtimeFilterDomOnly = true;
      continue;
    }
    if (argument === '--runtime-enabled-candidate-surface') {
      config.runtimeEnabledCandidateSurface = true;
      continue;
    }
    if (argument === '--runtime-ui-authority-stub-probe') {
      config.runtimeUiAuthorityStubProbe = true;
      continue;
    }
    if (argument === '--runtime-managed-outbox-authority') {
      config.runtimeManagedOutboxAuthority = true;
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
      selectedKeyValue: 1002,
      searchQuery: 'Review generated customer fields',
      filterField: 'status',
      filterLabel: 'Status',
      filterOperatorLabel: 'Contains',
      filterValue: 'triage',
      secondFilterField: 'priority',
      secondFilterValue: '20',
      thirdFilterField: 'body',
      thirdFilterValue: 'Confirm imported',
      sortField: 'status',
      sortLabel: 'Status',
      sortDirectionLabel: 'Desc',
      sortDirection: 'desc',
      secondSortField: 'id',
      secondSortDirection: 'desc',
      thirdSortField: 'priority',
      thirdSortDirection: 'asc',
      sortFirstKeyValue: 1002,
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

  if (name === 'sample18') {
    return {
      profile: name,
      projectKey: 'SAMPLE18',
      listScreenKey: 'task_card_list',
      listScreenTitle: 'Task Card List',
      detailScreenKey: 'task_card_detail',
      detailScreenTitle: 'Task Card Detail',
      formScreenKey: 'task_card_form',
      formScreenTitle: 'Task Card Form',
      actionKey: 'update_task_card',
      operationKey: 'update_task_card',
      operationType: 'update',
      keyField: 'id',
      keyValue: 1801,
      formFieldCount: 9,
      draftInputCountAfterEdit: 1,
      inputFields: ['title', 'body', 'status', 'assigned_to', 'priority', 'due_date'],
      requiredInputField: 'title',
      requiredInputValue: 'Generated sample18 browser smoke title',
      selectedKeyValue: 1801,
      filterField: 'status',
      filterLabel: 'Status',
      filterOperatorLabel: 'Contains',
      filterValue: 'doing',
      managedActionKeys: ['complete_task_card', 'create_task_card', 'update_task_card'],
      managedActionOperationTypes: {
        complete_task_card: 'update',
        create_task_card: 'create',
        update_task_card: 'update',
      },
      managedActionSubmitUrl: '/samples/sample18-task-board/no-code/generated-submit',
      managedActionBindingState: 'blocked_preflight',
      managedActionCsrfSource: 'sample18_task_board_form_token',
      managedActionCsrfTokenField: '_csrf_token',
      managedActionCsrfSourceSelector: 'input[name=_csrf_token]',
      managedActionCsrfTransport: 'form_field',
      managedActionClickBindingState: 'blocked_route_enabled',
      managedActionSubmitTrigger: 'guarded_click',
      managedActionNetworkSubmitEnabled: 'true',
      managedActionGuardedClickInventoryState: 'implemented_blocked_route',
      managedActionEnableGateSet: 'csrf_handoff_and_blocked_route_verified',
      managedActionPayloadAssembly: 'operation_key_plus_action_fields_plus_csrf',
      managedActionBlockedResponseHandling: 'render_failure_feedback_without_retry',
      managedActionFailureDisplayTarget: 'no-code-action-feedback',
      managedActionFailClosedResult: 'generated_submit_disabled',
      payload: {
        id: 1801,
        title: 'Sample18 browser smoke update',
        body: 'Generated sample18 browser smoke body',
        status: 'doing',
        assigned_to: 'Aki',
        priority: '2',
        due_date: '2026-07-10',
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
      selectedKeyValue: 2002,
      searchQuery: 'Generated workflow',
      filterField: 'status',
      filterLabel: 'Status',
      filterOperatorLabel: 'Contains',
      filterValue: 'open',
      secondFilterField: 'severity',
      secondFilterValue: 'medium',
      thirdFilterField: 'customer_tier',
      thirdFilterValue: 'standard',
      sortField: 'status',
      sortLabel: 'Status',
      sortDirectionLabel: 'Asc',
      sortDirection: 'asc',
      secondSortField: 'id',
      secondSortDirection: 'desc',
      thirdSortField: 'severity',
      thirdSortDirection: 'asc',
      sortFirstKeyValue: 2002,
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
      formFieldCount: 8,
      draftInputCountAfterEdit: 1,
      inputFields: ['item_sku', 'quantity_needed', 'status', 'fulfillment_note'],
      requiredInputField: 'fulfillment_note',
      requiredInputValue: 'Generated sample31 browser smoke fulfillment note',
      selectedKeyValue: 3102,
      searchQuery: 'SKU-CABLE-99',
      filterField: 'status',
      filterLabel: 'Status',
      filterOperatorLabel: 'Contains',
      filterValue: 'review',
      secondFilterField: 'quantity_needed',
      secondFilterValue: '24',
      thirdFilterField: 'item_sku',
      thirdFilterValue: 'SKU-CABLE-99',
      typedFilterField: 'needed_by',
      typedFilterOperator: 'gte',
      sortField: 'status',
      sortLabel: 'Status',
      sortDirectionLabel: 'Desc',
      sortDirection: 'desc',
      secondSortField: 'id',
      secondSortDirection: 'desc',
      thirdSortField: 'quantity_needed',
      thirdSortDirection: 'asc',
      sortFirstKeyValue: 3102,
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

async function probeRuntimeFilterDomOnly(page, config) {
  if (!config.expected.filterField || !config.expected.filterLabel) {
    throw new Error(`runtime filter DOM-only probe requires filter metadata for ${config.expected.profile}`);
  }

  await page.waitForSelector(
    `.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] [data-runtime-data-controls]`,
    { timeout: 5000 },
  );
  const result = await page.evaluate((expected) => {
    const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
    const binding = window.__noCodeRuntimeExecutionBinding || {};
    const controls = list?.querySelector('[data-runtime-data-controls]');
    const filterField = controls?.querySelector('[data-runtime-filter-field]');
    const statusOption = filterField?.querySelector(`option[value="${expected.filterField}"]`);
    const filterOperator = controls?.querySelector('[data-runtime-filter-operator]');
    const filterValue = controls?.querySelector('[data-runtime-filter-value]');
    const submitButton = controls?.querySelector('[data-runtime-filter-submit]');
    return {
      executionBindingUrl: binding.execution_url || '',
      executionBindingProjectKey: binding.project_key || '',
      runtimeDataUrl: binding.runtime_data_url || '',
      controlsVisible: !!controls && getComputedStyle(controls).display !== 'none',
      filterFieldValue: filterField?.value || '',
      filterOptionLabel: statusOption?.textContent?.trim() || '',
      filterOptionType: statusOption?.getAttribute('data-runtime-field-type') || '',
      filterOperatorValue: filterOperator?.value || '',
      filterOperatorLabels: Array.from(filterOperator?.querySelectorAll('option') || []).map((option) => option.textContent?.trim() || ''),
      filterValueTagName: filterValue?.tagName || '',
      submitText: submitButton?.textContent?.trim() || '',
    };
  }, config.expected);

  if (config.executionBinding === 'none' && result.executionBindingUrl !== '') {
    throw new Error(`execution binding should not be injected for this preview: ${result.executionBindingUrl}`);
  }
  if (config.executionBinding === 'required') {
    if (result.executionBindingUrl === '' || result.executionBindingProjectKey !== config.expected.projectKey) {
      throw new Error(`execution binding missing or project mismatch: ${JSON.stringify(result)}`);
    }
    if (config.executionUrlContains !== '' && !result.executionBindingUrl.includes(config.executionUrlContains)) {
      throw new Error(`execution binding URL mismatch: ${result.executionBindingUrl} does not include ${config.executionUrlContains}`);
    }
    if (result.runtimeDataUrl === '') {
      throw new Error(`runtime data binding is missing: ${JSON.stringify(result)}`);
    }
  }
  if (!result.controlsVisible) {
    throw new Error(`runtime data controls were not visible: ${JSON.stringify(result)}`);
  }
  if (result.filterOptionLabel !== config.expected.filterLabel || result.filterOptionType === '') {
    throw new Error(`runtime filter option mismatch: ${JSON.stringify(result)}`);
  }
  if (!result.filterOperatorLabels.includes(config.expected.filterOperatorLabel)) {
    throw new Error(`runtime filter operator mismatch: ${JSON.stringify(result)}`);
  }
  if (result.filterValueTagName !== 'INPUT' || result.submitText === '') {
    throw new Error(`runtime filter controls incomplete: ${JSON.stringify(result)}`);
  }

  return result;
}

async function probeRuntimeDisabledActionSurface(page, config) {
  const expectedKeys = Array.isArray(config.expected.managedActionKeys) ? config.expected.managedActionKeys : [];
  if (expectedKeys.length === 0) {
    return { skipped: true };
  }

  await page.waitForSelector(
    `.no-code-screen[data-screen-key="${config.expected.formScreenKey}"] .no-code-actions button[data-action-key]`,
    { timeout: 5000 },
  );
  const result = await page.evaluate(async (expected) => {
    const listScreen = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
    const formScreen = document.querySelector(`.no-code-screen[data-screen-key="${expected.formScreenKey}"]`);
    const listRows = Array.from(listScreen?.querySelectorAll('tbody tr[data-runtime-row-key]') || []);
    const buttons = Array.from(formScreen?.querySelectorAll('.no-code-actions button[data-action-key]') || []);
    const hints = Array.from(formScreen?.querySelectorAll('.no-code-action-hint[data-action-hint]') || []);
    const actions = (window.__noCodeRuntimePreview?.screens || [])
      .filter((screen) => screen && screen.screen_key === expected.formScreenKey)
      .flatMap((screen) => Array.isArray(screen.actions) ? screen.actions : []);
    const requiredInput = formScreen?.querySelector(`[name="${expected.requiredInputField}"]`);
    if (requiredInput) {
      requiredInput.value = expected.requiredInputValue;
      requiredInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    const createButton = buttons.find((button) => button.getAttribute('data-action-key') === 'create_task_card') || buttons[0] || null;
    if (createButton) {
      createButton.click();
      for (let attempt = 0; attempt < 50; attempt += 1) {
        const state = createButton.getAttribute('data-action-state') || '';
        if (state === 'blocked' || state === 'error') {
          break;
        }
        await new Promise((resolve) => setTimeout(resolve, 100));
      }
    }
    return {
      skipped: false,
      rowKeyCount: listRows.length,
      rowKeys: listRows.map((row) => row.getAttribute('data-runtime-row-key') || ''),
      firstRowKey: listRows[0]?.getAttribute('data-runtime-row-key') || '',
      keys: buttons.map((button) => button.getAttribute('data-action-key') || ''),
      operationKeys: buttons.map((button) => button.getAttribute('data-operation-key') || ''),
      operationTypes: buttons.reduce((carry, button) => {
        carry[button.getAttribute('data-action-key') || ''] = button.getAttribute('data-operation-type') || '';
        return carry;
      }, {}),
      enabledStates: buttons.map((button) => button.getAttribute('data-action-enabled') || ''),
      actionStates: buttons.map((button) => button.getAttribute('data-action-state') || ''),
      disabledReasons: buttons.map((button) => button.getAttribute('data-action-disabled-reason') || ''),
      affordances: buttons.map((button) => button.getAttribute('data-action-affordance') || ''),
      submitUrls: buttons.map((button) => button.getAttribute('data-action-submit-url') || ''),
      bindingStates: buttons.map((button) => button.getAttribute('data-action-binding-state') || ''),
      csrfSources: buttons.map((button) => button.getAttribute('data-action-csrf-source') || ''),
      csrfTokenFields: buttons.map((button) => button.getAttribute('data-action-csrf-token-field') || ''),
      csrfSourceSelectors: buttons.map((button) => button.getAttribute('data-action-csrf-source-selector') || ''),
      csrfTransports: buttons.map((button) => button.getAttribute('data-action-csrf-transport') || ''),
      clickBindingStates: buttons.map((button) => button.getAttribute('data-action-click-binding-state') || ''),
      submitTriggers: buttons.map((button) => button.getAttribute('data-action-submit-trigger') || ''),
      networkSubmitEnabled: buttons.map((button) => button.getAttribute('data-action-network-submit-enabled') || ''),
      guardedClickInventoryStates: buttons.map((button) => button.getAttribute('data-action-guarded-click-inventory-state') || ''),
      enableGateSets: buttons.map((button) => button.getAttribute('data-action-enable-gate-set') || ''),
      payloadAssemblies: buttons.map((button) => button.getAttribute('data-action-payload-assembly') || ''),
      blockedResponseHandling: buttons.map((button) => button.getAttribute('data-action-blocked-response-handling') || ''),
      failureDisplayTargets: buttons.map((button) => button.getAttribute('data-action-failure-display-target') || ''),
      failClosedResults: buttons.map((button) => button.getAttribute('data-action-fail-closed-result') || ''),
      guardedClickProbe: createButton ? {
        key: createButton.getAttribute('data-action-key') || '',
        state: createButton.getAttribute('data-action-state') || '',
        disabled: createButton.disabled === true,
        lastSubmitResult: createButton.getAttribute('data-action-last-submit-result') || '',
        lastFailureCode: createButton.getAttribute('data-action-last-failure-code') || '',
        feedbackState: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-state') || '',
        feedbackResult: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-action-last-submit-result') || '',
        feedbackFailureCode: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-action-last-failure-code') || '',
        feedbackText: formScreen?.querySelector('.no-code-action-feedback')?.textContent?.trim() || '',
      } : null,
      disabledProperties: buttons.map((button) => button.disabled === true),
      hintKeys: hints.map((hint) => hint.getAttribute('data-action-hint') || ''),
      hintStates: hints.map((hint) => hint.getAttribute('data-action-state-hint') || ''),
      previewActionKeys: actions.map((action) => action.action_key || ''),
      previewActionAvailability: actions.reduce((carry, action) => {
        carry[action.action_key || ''] = action.availability || '';
        return carry;
      }, {}),
      executeButtonDisabled: !!formScreen?.querySelector('[data-runtime-execute]')?.disabled,
      executeState: formScreen?.querySelector('[data-runtime-execute]')?.getAttribute('data-runtime-execute-state') || '',
    };
  }, config.expected);

  if (JSON.stringify(result.keys) !== JSON.stringify(expectedKeys)) {
    throw new Error(`disabled managed action keys mismatch: ${JSON.stringify(result)}`);
  }
  if (JSON.stringify(result.previewActionKeys) !== JSON.stringify(expectedKeys)) {
    throw new Error(`preview managed action keys mismatch: ${JSON.stringify(result)}`);
  }
  for (const key of expectedKeys) {
    if (result.operationTypes[key] !== config.expected.managedActionOperationTypes[key]) {
      throw new Error(`managed action operation type mismatch: ${JSON.stringify(result)}`);
    }
    if (result.previewActionAvailability[key] !== 'disabled') {
      throw new Error(`managed action preview availability mismatch: ${JSON.stringify(result)}`);
    }
  }
  if (
    result.enabledStates.some((state) => state !== 'true')
    || result.actionStates.some((state) => !['ready', 'blocked'].includes(state))
    || result.disabledReasons.some((reason) => reason !== '')
    || result.affordances.some((affordance) => affordance !== 'keyboard-intent-preview')
    || result.submitUrls.some((submitUrl) => submitUrl !== config.expected.managedActionSubmitUrl)
    || result.bindingStates.some((state) => state !== config.expected.managedActionBindingState)
    || result.csrfSources.some((source) => source !== config.expected.managedActionCsrfSource)
    || result.csrfTokenFields.some((field) => field !== config.expected.managedActionCsrfTokenField)
    || result.csrfSourceSelectors.some((selector) => selector !== config.expected.managedActionCsrfSourceSelector)
    || result.csrfTransports.some((transport) => transport !== config.expected.managedActionCsrfTransport)
    || result.clickBindingStates.some((state) => state !== config.expected.managedActionClickBindingState)
    || result.submitTriggers.some((trigger) => trigger !== config.expected.managedActionSubmitTrigger)
    || result.networkSubmitEnabled.some((enabled) => enabled !== config.expected.managedActionNetworkSubmitEnabled)
    || result.guardedClickInventoryStates.some((state) => state !== config.expected.managedActionGuardedClickInventoryState)
    || result.enableGateSets.some((gateSet) => gateSet !== config.expected.managedActionEnableGateSet)
    || result.payloadAssemblies.some((assembly) => assembly !== config.expected.managedActionPayloadAssembly)
    || result.blockedResponseHandling.some((handling) => handling !== config.expected.managedActionBlockedResponseHandling)
    || result.failureDisplayTargets.some((target) => target !== config.expected.managedActionFailureDisplayTarget)
    || result.failClosedResults.some((resultCode) => resultCode !== config.expected.managedActionFailClosedResult)
    || result.rowKeyCount < 1
    || result.firstRowKey !== String(config.expected.selectedKeyValue || config.expected.keyValue)
    || !result.guardedClickProbe
    || result.guardedClickProbe.key !== 'create_task_card'
    || result.guardedClickProbe.state !== 'blocked'
    || result.guardedClickProbe.disabled !== false
    || result.guardedClickProbe.lastSubmitResult !== 'blocked'
    || result.guardedClickProbe.lastFailureCode !== config.expected.managedActionFailClosedResult
    || result.guardedClickProbe.feedbackState !== 'blocked'
    || result.guardedClickProbe.feedbackResult !== 'blocked'
    || result.guardedClickProbe.feedbackFailureCode !== config.expected.managedActionFailClosedResult
    || !result.guardedClickProbe.feedbackText.includes(config.expected.managedActionFailClosedResult)
    || result.disabledProperties.some((disabled) => disabled !== false)
    || JSON.stringify(result.hintKeys) !== JSON.stringify(expectedKeys)
    || result.hintStates.some((state) => state !== 'ready')
    || result.executeButtonDisabled !== true
    || !['blocked', 'unavailable'].includes(result.executeState)
  ) {
    throw new Error(`guarded managed action surface mismatch: ${JSON.stringify(result)}`);
  }

  return result;
}

async function probeRuntimeEnabledCandidateSurface(page, config) {
  const expectedKeys = Array.isArray(config.expected.managedActionKeys) ? config.expected.managedActionKeys : [];
  if (expectedKeys.length === 0) {
    return { skipped: true };
  }

  await page.waitForSelector(
    `.no-code-screen[data-screen-key="${config.expected.formScreenKey}"] .no-code-actions button[data-action-key]`,
    { timeout: 5000 },
  );
  const result = await page.evaluate(async (expected) => {
    const formScreen = document.querySelector(`.no-code-screen[data-screen-key="${expected.formScreenKey}"]`);
    const buttons = Array.from(formScreen?.querySelectorAll('.no-code-actions button[data-action-key]') || []);
    const executableKeys = new Set(expected.managedActionKeys || []);
    const previewScreens = window.__noCodeRuntimePreview?.screens || [];
    const previewActions = previewScreens
      .filter((screen) => screen && screen.screen_key === expected.formScreenKey)
      .flatMap((screen) => Array.isArray(screen.actions) ? screen.actions : []);
    const initialReadiness = buttons
      .filter((button) => executableKeys.has(button.getAttribute('data-action-key') || ''))
      .map((button) => ({
        key: button.getAttribute('data-action-key') || '',
        readinessState: button.getAttribute('data-action-readiness-state') || '',
        availabilityCandidate: button.getAttribute('data-action-availability-candidate') || '',
        canSubmit: button.getAttribute('data-action-can-submit') || '',
        executorConfigStatus: button.getAttribute('data-action-executor-config-status') || '',
      }));
    const previewReadiness = previewActions
      .filter((action) => action && executableKeys.has(action.action_key || ''))
      .map((action) => ({
        key: action.action_key || '',
        readinessState: action.readiness_metadata?.readiness_state || '',
        availabilityCandidate: action.readiness_metadata?.availability_candidate === true,
        canSubmit: action.readiness_metadata?.can_submit === true,
        executorConfigStatus: action.readiness_metadata?.executor_config_status || '',
      }));

    previewActions.forEach((action) => {
      if (!action || !executableKeys.has(action.action_key || '')) {
        return;
      }
      action.enabled = true;
      action.availability = 'enabled';
      action.failed_checks = [];
    });

    buttons.forEach((button) => {
      const key = button.getAttribute('data-action-key') || '';
      if (!executableKeys.has(key)) {
        return;
      }
      button.disabled = false;
      button.setAttribute('aria-disabled', 'false');
      button.setAttribute('data-action-enabled', 'true');
      button.setAttribute('data-action-availability', 'enabled');
      button.setAttribute('data-action-state', 'ready');
      button.removeAttribute('data-action-disabled-reason');
      button.removeAttribute('data-action-policy-failed-checks');
    });

    const requiredInput = formScreen?.querySelector(`[name="${expected.requiredInputField}"]`);
    if (requiredInput) {
      requiredInput.value = expected.requiredInputValue;
      requiredInput.dispatchEvent(new Event('input', { bubbles: true }));
      requiredInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    const nativeFetch = window.fetch.bind(window);
    window.__noCodeRuntimeEnabledCandidateSubmitProbe = {
      fetchCalled: false,
      url: '',
      method: '',
      credentials: '',
      entries: [],
    };
    if (!expected.skipLegacyGuardedClickProbe) {
      window.fetch = async (url, options = {}) => {
      const method = String(options.method || 'GET');
      if (String(url).includes(expected.managedActionSubmitUrl || '/no-code/generated-submit')) {
        const entries = [];
        if (options.body && typeof options.body.entries === 'function') {
          for (const [key, value] of options.body.entries()) {
            entries.push([key, String(value)]);
          }
        }
        window.__noCodeRuntimeEnabledCandidateSubmitProbe = {
          fetchCalled: true,
          url: String(url),
          method,
          credentials: String(options.credentials || ''),
          entries,
        };
        return {
          ok: false,
          status: 409,
          json: async () => ({
            ok: false,
            accepted: false,
            result: 'blocked',
            failure_code: expected.managedActionFailClosedResult || 'generated_submit_disabled',
          }),
        };
      }
        return nativeFetch(url, options);
      };
    }

    const createButton = buttons.find((button) => button.getAttribute('data-action-key') === 'create_task_card') || buttons[0] || null;
    if (createButton && !expected.skipLegacyGuardedClickProbe) {
      createButton.click();
      for (let attempt = 0; attempt < 50; attempt += 1) {
        const state = createButton.getAttribute('data-action-state') || '';
        if (state === 'success' || state === 'blocked' || state === 'error') {
          break;
        }
        await new Promise((resolve) => setTimeout(resolve, 100));
      }
    }

    return {
      skipped: false,
      keys: buttons.map((button) => button.getAttribute('data-action-key') || ''),
      enabledStates: buttons.map((button) => button.getAttribute('data-action-enabled') || ''),
      availabilityStates: buttons.map((button) => button.getAttribute('data-action-availability') || ''),
      disabledProperties: buttons.map((button) => button.disabled === true),
      disabledReasons: buttons.map((button) => button.getAttribute('data-action-disabled-reason') || ''),
      policyFailedChecks: buttons.map((button) => button.getAttribute('data-action-policy-failed-checks') || ''),
      initialReadiness,
      previewReadiness,
      previewActionAvailability: previewActions.reduce((carry, action) => {
        carry[action.action_key || ''] = action.availability || '';
        return carry;
      }, {}),
      previewActionEnabled: previewActions.reduce((carry, action) => {
        carry[action.action_key || ''] = action.enabled === true;
        return carry;
      }, {}),
      forbiddenAvailability: Array.from(document.querySelectorAll('[data-action-key="reopen_task_card"], [data-action-key="delete_task_card"]')).map((button) => ({
        key: button.getAttribute('data-action-key') || '',
        availability: button.getAttribute('data-action-availability') || '',
        enabled: button.getAttribute('data-action-enabled') || '',
      })),
      guardedClickProbe: createButton ? {
        key: createButton.getAttribute('data-action-key') || '',
        state: createButton.getAttribute('data-action-state') || '',
        disabled: createButton.disabled === true,
        lastSubmitResult: createButton.getAttribute('data-action-last-submit-result') || '',
        lastFailureCode: createButton.getAttribute('data-action-last-failure-code') || '',
        feedbackState: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-state') || '',
        feedbackResult: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-action-last-submit-result') || '',
        feedbackFailureCode: formScreen?.querySelector('.no-code-action-feedback')?.getAttribute('data-action-last-failure-code') || '',
        feedbackText: formScreen?.querySelector('.no-code-action-feedback')?.textContent?.trim() || '',
      } : null,
      submitProbe: window.__noCodeRuntimeEnabledCandidateSubmitProbe || {},
    };
  }, { ...config.expected, skipLegacyGuardedClickProbe: config.runtimeUiAuthorityStubProbe });

  if (JSON.stringify(result.keys) !== JSON.stringify(expectedKeys)) {
    throw new Error(`enabled candidate managed action keys mismatch: ${JSON.stringify(result)}`);
  }
  for (const key of expectedKeys) {
    if (result.previewActionAvailability[key] !== 'enabled' || result.previewActionEnabled[key] !== true) {
      throw new Error(`enabled candidate preview action mismatch: ${JSON.stringify(result)}`);
    }
  }
  if (
    result.enabledStates.some((state) => state !== 'true')
    || result.availabilityStates.some((state) => state !== 'enabled')
    || result.disabledProperties.some((disabled) => disabled !== false)
    || result.disabledReasons.some((reason) => reason !== '')
    || result.policyFailedChecks.some((checks) => checks !== '')
    || JSON.stringify(result.initialReadiness.map((item) => item.key)) !== JSON.stringify(expectedKeys)
    || result.initialReadiness.some((item) => item.readinessState !== 'candidate_ready')
    || result.initialReadiness.some((item) => item.availabilityCandidate !== 'true')
    || result.initialReadiness.some((item) => item.canSubmit !== 'false')
    || result.initialReadiness.some((item) => item.executorConfigStatus !== 'disabled')
    || JSON.stringify(result.previewReadiness.map((item) => item.key)) !== JSON.stringify(expectedKeys)
    || result.previewReadiness.some((item) => item.readinessState !== 'candidate_ready')
    || result.previewReadiness.some((item) => item.availabilityCandidate !== true)
    || result.previewReadiness.some((item) => item.canSubmit !== false)
    || result.previewReadiness.some((item) => item.executorConfigStatus !== 'disabled')
    || result.forbiddenAvailability.some((item) => item.availability === 'enabled' || item.enabled === 'true')
    || !result.guardedClickProbe
    || result.guardedClickProbe.key !== 'create_task_card'
    || result.guardedClickProbe.disabled !== false
    || (!config.runtimeUiAuthorityStubProbe && result.guardedClickProbe.lastSubmitResult !== 'blocked')
    || (!config.runtimeUiAuthorityStubProbe && result.guardedClickProbe.lastFailureCode !== config.expected.managedActionFailClosedResult)
    || (!config.runtimeUiAuthorityStubProbe && result.guardedClickProbe.feedbackState !== 'blocked')
    || (!config.runtimeUiAuthorityStubProbe && result.guardedClickProbe.feedbackResult !== 'blocked')
    || (!config.runtimeUiAuthorityStubProbe && result.guardedClickProbe.feedbackFailureCode !== config.expected.managedActionFailClosedResult)
    || (!config.runtimeUiAuthorityStubProbe && !result.guardedClickProbe.feedbackText.includes(config.expected.managedActionFailClosedResult))
    || (!config.runtimeUiAuthorityStubProbe && !result.submitProbe.fetchCalled)
    || (!config.runtimeUiAuthorityStubProbe && result.submitProbe.method !== 'POST')
    || (!config.runtimeUiAuthorityStubProbe && result.submitProbe.credentials !== 'same-origin')
  ) {
    throw new Error(`enabled candidate managed action surface mismatch: ${JSON.stringify(result)}`);
  }

  return result;
}

async function probeRuntimeDataInitialUrlReplay(page, targetUrl, config) {
  if (
    config.statusProbe !== 'real'
    || config.executionBinding !== 'required'
    || !config.expected.searchQuery
    || !config.expected.filterField
    || !config.expected.filterValue
    || !config.expected.sortField
    || !config.expected.sortDirection
  ) {
    return { skipped: true };
  }

  const replayUrl = new URL(targetUrl);
  replayUrl.searchParams.set('q', String(config.expected.searchQuery));
  replayUrl.searchParams.set(`filter[${config.expected.filterField}]`, String(config.expected.filterValue));
  replayUrl.searchParams.set(`filter_op[${config.expected.filterField}]`, 'contains');
  if (config.expected.secondFilterField && config.expected.secondFilterValue) {
    replayUrl.searchParams.set(`filter[${config.expected.secondFilterField}]`, String(config.expected.secondFilterValue));
    replayUrl.searchParams.set(`filter_op[${config.expected.secondFilterField}]`, 'eq');
  }
  if (config.expected.thirdFilterField && config.expected.thirdFilterValue) {
    replayUrl.searchParams.set(`filter[${config.expected.thirdFilterField}]`, String(config.expected.thirdFilterValue));
    replayUrl.searchParams.set(`filter_op[${config.expected.thirdFilterField}]`, 'contains');
  }
  replayUrl.searchParams.set(`sort[${config.expected.sortField}]`, String(config.expected.sortDirection));
  if (config.expected.secondSortField && config.expected.secondSortDirection) {
    replayUrl.searchParams.set(`sort[${config.expected.secondSortField}]`, String(config.expected.secondSortDirection));
  }
  if (config.expected.thirdSortField && config.expected.thirdSortDirection) {
    replayUrl.searchParams.set(`sort[${config.expected.thirdSortField}]`, String(config.expected.thirdSortDirection));
  }
  replayUrl.searchParams.set('page', '1');
  replayUrl.searchParams.set('page_size', '1');

  await page.goto(replayUrl.toString(), { waitUntil: 'load' });
  await page.waitForFunction(
    (expected) => {
      const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
      const statusText = list?.querySelector('[data-runtime-result-refresh-status]')?.textContent || '';
      const firstRow = list?.querySelector('tbody tr:not(.no-code-empty-row)');
      return statusText.includes('Fresh runtime data loaded from') && firstRow;
    },
    config.expected,
    { timeout: 5000 },
  );

  const result = await page.evaluate((expected) => {
    const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
    const firstRow = list?.querySelector('tbody tr:not(.no-code-empty-row)');
    return {
      skipped: false,
      locationSearch: window.location.search || '',
      statusText: list?.querySelector('[data-runtime-result-refresh-status]')?.textContent?.trim() || '',
      retainedSearchValue: list?.querySelector('[data-runtime-search-input]')?.value || '',
      retainedFilterField: list?.querySelector('[data-runtime-filter-field]')?.value || '',
      retainedFilterOperator: list?.querySelector('[data-runtime-filter-operator]')?.value || '',
      retainedFilterValue: list?.querySelector('[data-runtime-filter-value]')?.value || '',
      retainedSecondFilterField: list?.querySelector('[data-runtime-filter-field-secondary]')?.value || '',
      retainedSecondFilterOperator: list?.querySelector('[data-runtime-filter-operator-secondary]')?.value || '',
      retainedSecondFilterValue: list?.querySelector('[data-runtime-filter-value-secondary]')?.value || '',
      retainedThirdFilterField: list?.querySelector('[data-runtime-filter-field-tertiary]')?.value || '',
      retainedThirdFilterOperator: list?.querySelector('[data-runtime-filter-operator-tertiary]')?.value || '',
      retainedThirdFilterValue: list?.querySelector('[data-runtime-filter-value-tertiary]')?.value || '',
      retainedSortField: list?.querySelector('[data-runtime-sort-field]')?.value || '',
      retainedSortDirection: list?.querySelector('[data-runtime-sort-direction]')?.value || '',
      retainedSecondSortField: list?.querySelector('[data-runtime-sort-field-secondary]')?.value || '',
      retainedSecondSortDirection: list?.querySelector('[data-runtime-sort-direction-secondary]')?.value || '',
      retainedThirdSortField: list?.querySelector('[data-runtime-sort-field-tertiary]')?.value || '',
      retainedThirdSortDirection: list?.querySelector('[data-runtime-sort-direction-tertiary]')?.value || '',
      retainedPageSize: list?.querySelector('[data-runtime-page-size-input]')?.value || '',
      firstRowKey: firstRow?.getAttribute('data-runtime-row-key') || '',
      renderedRowCount: list?.querySelectorAll('tbody tr:not(.no-code-empty-row)').length || 0,
    };
  }, config.expected);

  if (
    !result.locationSearch.includes('q=')
    || !result.locationSearch.includes('filter%5B')
    || !result.locationSearch.includes('filter_op%5B')
    || (config.expected.secondFilterField && !result.locationSearch.includes(`filter%5B${config.expected.secondFilterField}%5D`))
    || (config.expected.thirdFilterField && !result.locationSearch.includes(`filter%5B${config.expected.thirdFilterField}%5D`))
    || !result.locationSearch.includes('sort%5B')
    || (config.expected.secondSortField && !result.locationSearch.includes(`sort%5B${config.expected.secondSortField}%5D`))
    || (config.expected.thirdSortField && !result.locationSearch.includes(`sort%5B${config.expected.thirdSortField}%5D`))
    || !result.locationSearch.includes('page=1')
    || !result.locationSearch.includes('page_size=1')
  ) {
    throw new Error(`runtime data initial URL replay did not preserve browser query: ${JSON.stringify(result)}`);
  }
  if (
    result.retainedSearchValue !== String(config.expected.searchQuery || '')
    || result.retainedFilterField !== String(config.expected.filterField || '')
    || result.retainedFilterOperator !== 'contains'
    || result.retainedFilterValue !== String(config.expected.filterValue || '')
    || (config.expected.secondFilterField && result.retainedSecondFilterField !== String(config.expected.secondFilterField || ''))
    || (config.expected.secondFilterField && result.retainedSecondFilterOperator !== 'eq')
    || (config.expected.secondFilterValue && result.retainedSecondFilterValue !== String(config.expected.secondFilterValue || ''))
    || (config.expected.thirdFilterField && result.retainedThirdFilterField !== String(config.expected.thirdFilterField || ''))
    || (config.expected.thirdFilterField && result.retainedThirdFilterOperator !== 'contains')
    || (config.expected.thirdFilterValue && result.retainedThirdFilterValue !== String(config.expected.thirdFilterValue || ''))
    || result.retainedSortField !== String(config.expected.sortField || '')
    || result.retainedSortDirection !== String(config.expected.sortDirection || '')
    || (config.expected.secondSortField && result.retainedSecondSortField !== String(config.expected.secondSortField || ''))
    || (config.expected.secondSortDirection && result.retainedSecondSortDirection !== String(config.expected.secondSortDirection || ''))
    || (config.expected.thirdSortField && result.retainedThirdSortField !== String(config.expected.thirdSortField || ''))
    || (config.expected.thirdSortDirection && result.retainedThirdSortDirection !== String(config.expected.thirdSortDirection || ''))
    || result.retainedPageSize !== '1'
  ) {
    throw new Error(`runtime data initial URL replay did not retain controls: ${JSON.stringify(result)}`);
  }
  if (result.renderedRowCount !== 1 || result.firstRowKey !== String(config.expected.selectedKeyValue || config.expected.keyValue)) {
    throw new Error(`runtime data initial URL replay row mismatch: ${JSON.stringify(result)}`);
  }

  return result;
}

async function probeRuntimeDataBrowserHistoryReplay(page, targetUrl, config) {
  if (
    config.statusProbe !== 'real'
    || config.executionBinding !== 'required'
    || !config.expected.searchQuery
    || !config.expected.filterField
    || !config.expected.filterValue
    || !config.expected.secondFilterField
    || !config.expected.secondFilterValue
    || !config.expected.thirdFilterField
    || !config.expected.thirdFilterValue
  ) {
    return { skipped: true };
  }

  await page.goto(targetUrl, { waitUntil: 'load' });
  await page.waitForSelector(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] [data-runtime-search-input]`, { timeout: 5000 });
  const historyLengthBefore = await page.evaluate(() => window.history.length);

  await page.evaluate((expected) => {
    const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
    const searchInput = list?.querySelector('[data-runtime-search-input]');
    const searchButton = list?.querySelector('[data-runtime-search-submit]');
    searchInput.value = expected.searchQuery;
    searchInput.dispatchEvent(new Event('input', { bubbles: true }));
    searchButton.click();
  }, config.expected);

  await page.waitForFunction(
    (expected) => {
      const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
      const firstRow = list?.querySelector('tbody tr:not(.no-code-empty-row)');
      return window.location.search.includes('q=')
        && !window.location.search.includes('filter%5B')
        && list?.querySelector('[data-runtime-search-input]')?.value === expected.searchQuery
        && firstRow?.getAttribute('data-runtime-row-key') === String(expected.selectedKeyValue || expected.keyValue);
    },
    config.expected,
    { timeout: 5000 },
  );

  const rowBuilderProbe = await page.evaluate((expected) => {
    const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
    const controls = list?.querySelector('[data-runtime-data-controls]');
    const secondFilterRow = controls?.querySelector('[data-runtime-filter-extra="secondary"]');
    const thirdFilterRow = controls?.querySelector('[data-runtime-filter-extra="tertiary"]');
    const secondSortRow = controls?.querySelector('[data-runtime-sort-extra="secondary"]');
    const thirdSortRow = controls?.querySelector('[data-runtime-sort-extra="tertiary"]');
    const addFilter = controls?.querySelector('[data-runtime-filter-add]');
    const addSort = controls?.querySelector('[data-runtime-sort-add]');
    const removeSecondFilter = controls?.querySelector('[data-runtime-filter-remove="secondary"]');
    const removeSecondSort = controls?.querySelector('[data-runtime-sort-remove="secondary"]');
    const secondFilterField = controls?.querySelector('[data-runtime-filter-field-secondary]');
    const secondFilterValue = controls?.querySelector('[data-runtime-filter-value-secondary]');
    const thirdFilterField = controls?.querySelector('[data-runtime-filter-field-tertiary]');
    const thirdFilterValue = controls?.querySelector('[data-runtime-filter-value-tertiary]');
    const secondSortField = controls?.querySelector('[data-runtime-sort-field-secondary]');
    const thirdSortField = controls?.querySelector('[data-runtime-sort-field-tertiary]');
    const before = {
      secondFilterHidden: !!secondFilterRow?.hidden,
      thirdFilterHidden: !!thirdFilterRow?.hidden,
      secondSortHidden: !!secondSortRow?.hidden,
      thirdSortHidden: !!thirdSortRow?.hidden,
    };
    addFilter?.click();
    const afterAddFilter2 = {
      secondFilterHidden: !!secondFilterRow?.hidden,
      thirdFilterHidden: !!thirdFilterRow?.hidden,
      addFilterDisabled: !!addFilter?.disabled,
    };
    addFilter?.click();
    const afterAddFilter3 = {
      secondFilterHidden: !!secondFilterRow?.hidden,
      thirdFilterHidden: !!thirdFilterRow?.hidden,
      addFilterDisabled: !!addFilter?.disabled,
    };
    if (secondFilterField && secondFilterValue && thirdFilterField && thirdFilterValue) {
      secondFilterField.value = expected.secondFilterField;
      secondFilterValue.value = expected.secondFilterValue;
      thirdFilterField.value = expected.thirdFilterField;
      thirdFilterValue.value = expected.thirdFilterValue;
    }
    removeSecondFilter?.click();
    const afterRemoveFilter2 = {
      secondFilterHidden: !!secondFilterRow?.hidden,
      thirdFilterHidden: !!thirdFilterRow?.hidden,
      secondFilterField: secondFilterField?.value || '',
      secondFilterValue: secondFilterValue?.value || '',
      thirdFilterField: thirdFilterField?.value || '',
      thirdFilterValue: thirdFilterValue?.value || '',
      addFilterDisabled: !!addFilter?.disabled,
    };
    addSort?.click();
    const afterAddSort2 = {
      secondSortHidden: !!secondSortRow?.hidden,
      thirdSortHidden: !!thirdSortRow?.hidden,
      addSortDisabled: !!addSort?.disabled,
    };
    addSort?.click();
    const afterAddSort3 = {
      secondSortHidden: !!secondSortRow?.hidden,
      thirdSortHidden: !!thirdSortRow?.hidden,
      addSortDisabled: !!addSort?.disabled,
    };
    if (secondSortField && thirdSortField) {
      secondSortField.value = expected.secondSortField;
      thirdSortField.value = expected.thirdSortField;
    }
    removeSecondSort?.click();
    const afterRemoveSort2 = {
      secondSortHidden: !!secondSortRow?.hidden,
      thirdSortHidden: !!thirdSortRow?.hidden,
      secondSortField: secondSortField?.value || '',
      thirdSortField: thirdSortField?.value || '',
      addSortDisabled: !!addSort?.disabled,
    };
    return {
      before,
      afterAddFilter2,
      afterAddFilter3,
      afterRemoveFilter2,
      afterAddSort2,
      afterAddSort3,
      afterRemoveSort2,
    };
  }, config.expected);
  if (
    !rowBuilderProbe.before.secondFilterHidden
    || !rowBuilderProbe.before.thirdFilterHidden
    || !rowBuilderProbe.before.secondSortHidden
    || !rowBuilderProbe.before.thirdSortHidden
    || rowBuilderProbe.afterAddFilter2.secondFilterHidden
    || rowBuilderProbe.afterAddFilter3.thirdFilterHidden
    || !rowBuilderProbe.afterAddFilter3.addFilterDisabled
    || !rowBuilderProbe.afterRemoveFilter2.secondFilterHidden
    || !rowBuilderProbe.afterRemoveFilter2.thirdFilterHidden
    || rowBuilderProbe.afterRemoveFilter2.secondFilterField !== ''
    || rowBuilderProbe.afterRemoveFilter2.secondFilterValue !== ''
    || rowBuilderProbe.afterRemoveFilter2.thirdFilterField !== ''
    || rowBuilderProbe.afterRemoveFilter2.thirdFilterValue !== ''
    || rowBuilderProbe.afterAddSort2.secondSortHidden
    || rowBuilderProbe.afterAddSort3.thirdSortHidden
    || !rowBuilderProbe.afterAddSort3.addSortDisabled
    || !rowBuilderProbe.afterRemoveSort2.secondSortHidden
    || !rowBuilderProbe.afterRemoveSort2.thirdSortHidden
    || rowBuilderProbe.afterRemoveSort2.secondSortField !== ''
    || rowBuilderProbe.afterRemoveSort2.thirdSortField !== ''
  ) {
    throw new Error(`runtime data row builder progressive disclosure mismatch: ${JSON.stringify(rowBuilderProbe)}`);
  }

  await page.evaluate((expected) => {
    const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
    const filterField = list?.querySelector('[data-runtime-filter-field]');
    const filterValue = list?.querySelector('[data-runtime-filter-value]');
    const secondFilterField = list?.querySelector('[data-runtime-filter-field-secondary]');
    const secondFilterValue = list?.querySelector('[data-runtime-filter-value-secondary]');
    const thirdFilterField = list?.querySelector('[data-runtime-filter-field-tertiary]');
    const thirdFilterValue = list?.querySelector('[data-runtime-filter-value-tertiary]');
    const filterButton = list?.querySelector('[data-runtime-filter-submit]');
    filterField.value = expected.filterField;
    filterField.dispatchEvent(new Event('change', { bubbles: true }));
    filterValue.value = expected.filterValue;
    filterValue.dispatchEvent(new Event('input', { bubbles: true }));
    secondFilterField.value = expected.secondFilterField;
    secondFilterField.dispatchEvent(new Event('change', { bubbles: true }));
    secondFilterValue.value = expected.secondFilterValue;
    secondFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
    thirdFilterField.value = expected.thirdFilterField;
    thirdFilterField.dispatchEvent(new Event('change', { bubbles: true }));
    thirdFilterValue.value = expected.thirdFilterValue;
    thirdFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
    filterButton.click();
  }, config.expected);

  await page.waitForFunction(
    (expected) => {
      const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
      const firstRow = list?.querySelector('tbody tr:not(.no-code-empty-row)');
      return window.location.search.includes(`filter%5B${expected.filterField}%5D`)
        && window.location.search.includes(`filter%5B${expected.secondFilterField}%5D`)
        && window.location.search.includes(`filter%5B${expected.thirdFilterField}%5D`)
        && list?.querySelector('[data-runtime-filter-field-secondary]')?.value === expected.secondFilterField
        && list?.querySelector('[data-runtime-filter-field-tertiary]')?.value === expected.thirdFilterField
        && firstRow?.getAttribute('data-runtime-row-key') === String(expected.selectedKeyValue || expected.keyValue);
    },
    config.expected,
    { timeout: 5000 },
  );

  const historyLengthAfterFilter = await page.evaluate(() => window.history.length);
  await page.evaluate(() => window.history.back());
  await page.waitForFunction(
    (expected) => {
      const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
      return window.location.search.includes('q=')
        && !window.location.search.includes('filter%5B')
        && list?.querySelector('[data-runtime-search-input]')?.value === expected.searchQuery
        && list?.querySelector('[data-runtime-filter-field]')?.value === ''
        && list?.querySelector('[data-runtime-filter-field-secondary]')?.value === ''
        && list?.querySelector('[data-runtime-filter-field-tertiary]')?.value === '';
    },
    config.expected,
    { timeout: 5000 },
  );
  const historyLengthAfterBack = await page.evaluate(() => window.history.length);

  await page.evaluate(() => window.history.forward());
  await page.waitForFunction(
    (expected) => {
      const list = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"]`);
      return window.location.search.includes(`filter%5B${expected.filterField}%5D`)
        && window.location.search.includes(`filter%5B${expected.secondFilterField}%5D`)
        && window.location.search.includes(`filter%5B${expected.thirdFilterField}%5D`)
        && list?.querySelector('[data-runtime-filter-field]')?.value === expected.filterField
        && list?.querySelector('[data-runtime-filter-value]')?.value === expected.filterValue
        && list?.querySelector('[data-runtime-filter-field-secondary]')?.value === expected.secondFilterField
        && list?.querySelector('[data-runtime-filter-value-secondary]')?.value === expected.secondFilterValue
        && list?.querySelector('[data-runtime-filter-field-tertiary]')?.value === expected.thirdFilterField
        && list?.querySelector('[data-runtime-filter-value-tertiary]')?.value === expected.thirdFilterValue;
    },
    config.expected,
    { timeout: 5000 },
  );
  const historyLengthAfterForward = await page.evaluate(() => window.history.length);
  if (historyLengthAfterFilter < historyLengthBefore + 2) {
    throw new Error(`runtime data browser history replay did not create query history entries: before=${historyLengthBefore}, after=${historyLengthAfterFilter}`);
  }
  if (historyLengthAfterBack !== historyLengthAfterFilter || historyLengthAfterForward !== historyLengthAfterFilter) {
    throw new Error(`runtime data browser history replay created extra entries during popstate: filter=${historyLengthAfterFilter}, back=${historyLengthAfterBack}, forward=${historyLengthAfterForward}`);
  }

  return {
    skipped: false,
    historyLengthBefore,
    historyLengthAfterFilter,
    historyLengthAfterBack,
    historyLengthAfterForward,
    locationSearch: await page.evaluate(() => window.location.search || ''),
    retainedFilterField: await page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] [data-runtime-filter-field]`).inputValue(),
    retainedSecondFilterField: await page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] [data-runtime-filter-field-secondary]`).inputValue(),
    retainedThirdFilterField: await page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] [data-runtime-filter-field-tertiary]`).inputValue(),
  };
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
  const mobileScreenshotPath = path.join(config.outputDir, `no-code-runtime-preview-mobile-${timestamp()}.png`);
  try {
    const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
    const targetUrl = config.url !== '' ? config.url : `file://${config.htmlPath}`;
    const guardedSubmitRequests = [];
    if (config.runtimeUiAuthorityStubProbe) {
      await page.route('**/samples/sample18-task-board/no-code/generated-submit', async (route) => {
        guardedSubmitRequests.push({ method: route.request().method(), url: route.request().url() });
        await route.fulfill({
          status: 200,
          contentType: 'application/json',
          body: JSON.stringify({ ok: true, result: 'executed', transaction_result: { transaction_status: 'committed' } }),
        });
      });
    }
    if (config.submitProbe === 'enabled-real-fetch' || config.runtimeUiAuthorityStubProbe || config.runtimeManagedOutboxAuthority) {
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

    if (config.runtimeFilterDomOnly) {
      const runtimeFilterDomProbe = await probeRuntimeFilterDomOnly(page, config);
      const runtimeDisabledActionSurfaceProbe = await probeRuntimeDisabledActionSurface(page, config);
      await page.screenshot({ path: screenshotPath, fullPage: true });
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(targetUrl, { waitUntil: 'load' });
      await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.listScreenKey}"] [data-runtime-data-controls]`), 'mobile runtime data controls');
      await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.formScreenKey}"] .no-code-actions button[data-action-key]`), 'mobile guarded managed action surface');
      await page.screenshot({ path: mobileScreenshotPath, fullPage: true });
      return {
        ok: true,
        profile: config.expected.profile,
        runtime_filter_dom_only: true,
        runtime_filter_dom_probe: runtimeFilterDomProbe,
        runtime_disabled_action_surface_probe: runtimeDisabledActionSurfaceProbe,
        screenshot: screenshotPath,
        mobile_screenshot: mobileScreenshotPath,
      };
    }

    if (config.runtimeEnabledCandidateSurface) {
      const runtimeEnabledCandidateSurfaceProbe = await probeRuntimeEnabledCandidateSurface(page, config);
      let runtimeUiAuthorityProbe = { skipped: true };
      if (config.runtimeUiAuthorityStubProbe) {
        const createControl = page.locator('[data-action-control="create_task_card"]').first();
        await createControl.locator('[data-server-action-availability-diagnostic]').waitFor({ state: 'visible' });
        const diagnostic = await createControl.locator('[data-server-action-availability-diagnostic]').textContent();
        if (diagnostic !== 'Server availability: enabled') {
          throw new Error(`live create availability was not enabled: ${diagnostic}`);
        }
        await createControl.locator('button[data-action-key="create_task_card"]').click();
        await page.waitForTimeout(100);
        if (guardedSubmitRequests.length !== 1 || guardedSubmitRequests[0].method !== 'POST') {
          throw new Error(`live UI authority did not issue exactly one stubbed POST: ${JSON.stringify(guardedSubmitRequests)}`);
        }
        await page.locator('button[data-action-key="complete_task_card"]').first().click();
        await page.waitForTimeout(50);
        if (guardedSubmitRequests.length !== 1) {
          throw new Error('excluded complete action issued an additional POST');
        }
        runtimeUiAuthorityProbe = { skipped: false, create_availability: 'enabled', guarded_post_count: 1, excluded_complete_post_count: 0 };
      }
      await page.screenshot({ path: screenshotPath, fullPage: true });
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(targetUrl, { waitUntil: 'load' });
      await requireVisible(page.locator(`.no-code-screen[data-screen-key="${config.expected.formScreenKey}"] .no-code-actions button[data-action-key]`), 'mobile enabled-candidate managed action surface');
      const mobileRuntimeEnabledCandidateSurfaceProbe = await probeRuntimeEnabledCandidateSurface(page, config);
      await page.screenshot({ path: mobileScreenshotPath, fullPage: true });
      return {
        ok: true,
        profile: config.expected.profile,
        runtime_enabled_candidate_surface: true,
        runtime_enabled_candidate_surface_probe: runtimeEnabledCandidateSurfaceProbe,
        runtime_ui_authority_probe: runtimeUiAuthorityProbe,
        mobile_runtime_enabled_candidate_surface_probe: mobileRuntimeEnabledCandidateSurfaceProbe,
        screenshot: screenshotPath,
        mobile_screenshot: mobileScreenshotPath,
      };
    }

    if (config.runtimeManagedOutboxAuthority) {
      const availabilityDiagnostic = page.locator(`[data-server-action-availability-diagnostic="${config.expected.actionKey}"]`).first();
      await availabilityDiagnostic.waitFor({ state: 'visible', timeout: 10000 });
      const diagnostic = (await availabilityDiagnostic.textContent() || '').trim();
      if (diagnostic !== 'Server availability: enabled') {
        throw new Error(`managed-outbox live availability was not enabled: ${diagnostic}`);
      }
    }

    const evaluateExpected = {
      ...config.expected,
      submitProbe: config.submitProbe,
      statusProbe: config.statusProbe,
      demoProcessing: config.demoProcessing,
      managedOutboxAuthority: config.runtimeManagedOutboxAuthority,
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
        if (!expected.managedOutboxAuthority) {
          previewActions.forEach((action) => {
            if (action.action_key === expected.actionKey) {
              action.enabled = true;
              action.availability = 'enabled';
              action.failed_checks = [];
            }
          });
        }
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
        if (!expected.managedOutboxAuthority) {
          const forcedDispatch = typeof window.noCodeRuntimeDispatchAction === 'function'
            ? window.noCodeRuntimeDispatchAction(expected.actionKey, expected.payload)
            : { ok: false };
          if (executeButton && forcedDispatch.ok) {
            // Legacy probes isolate endpoint handoff from authority; managed-outbox authority probes must not mutate state.
            executeButton.disabled = false;
            executeButton.setAttribute('data-runtime-execute-state', 'ready');
          }
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
          requestSelectedKey: '',
          requestSearchQuery: '',
          requestFilterField: '',
          requestFilterValue: '',
          requestFilterOperator: '',
          requestSecondFilterField: '',
          requestSecondFilterValue: '',
          requestSecondFilterOperator: '',
          requestThirdFilterField: '',
          requestThirdFilterValue: '',
          requestThirdFilterOperator: '',
          requestSortField: '',
          requestSortDirection: '',
          requestSecondSortField: '',
          requestSecondSortDirection: '',
          requestThirdSortField: '',
          requestThirdSortDirection: '',
          responseStatus: 0,
          responseOk: null,
          contractVersion: '',
          screenCount: 0,
          firstRowKey: '',
          selectedKey: '',
        };
        const nativeFetch = window.fetch.bind(window);
        window.fetch = async (url, options = {}) => {
          const method = String(options.method || 'GET');
          let runtimeDataPathname = '';
          let runtimeDataSelectedKey = '';
          let runtimeDataPage = '';
          let runtimeDataPageSize = '';
          let runtimeDataSearchQuery = '';
          let runtimeDataFilterField = '';
          let runtimeDataFilterValue = '';
          let runtimeDataFilterOperator = '';
          let runtimeDataSecondFilterField = '';
          let runtimeDataSecondFilterValue = '';
          let runtimeDataSecondFilterOperator = '';
          let runtimeDataThirdFilterField = '';
          let runtimeDataThirdFilterValue = '';
          let runtimeDataThirdFilterOperator = '';
          let runtimeDataSortField = '';
          let runtimeDataSortDirection = '';
          let runtimeDataSecondSortField = '';
          let runtimeDataSecondSortDirection = '';
          let runtimeDataThirdSortField = '';
          let runtimeDataThirdSortDirection = '';
          try {
            const parsedRuntimeDataUrl = new URL(String(url), window.location.href);
            runtimeDataPathname = parsedRuntimeDataUrl.pathname;
            runtimeDataSelectedKey = parsedRuntimeDataUrl.searchParams.get('selected_key') || '';
            runtimeDataPage = parsedRuntimeDataUrl.searchParams.get('page') || '';
            runtimeDataPageSize = parsedRuntimeDataUrl.searchParams.get('page_size') || '';
            runtimeDataSearchQuery = parsedRuntimeDataUrl.searchParams.get('q') || '';
            for (const [paramKey, paramValue] of parsedRuntimeDataUrl.searchParams.entries()) {
              const operatorMatch = /^filter_op\[(.+)\]$/.exec(paramKey);
              if (operatorMatch) {
                const operatorField = operatorMatch[1] || '';
                if (operatorField === runtimeDataFilterField || (!runtimeDataFilterField && !runtimeDataFilterOperator)) {
                  runtimeDataFilterOperator = paramValue || '';
                } else if (operatorField === runtimeDataSecondFilterField || (!runtimeDataSecondFilterField && !runtimeDataSecondFilterOperator)) {
                  runtimeDataSecondFilterOperator = paramValue || '';
                } else if (operatorField === runtimeDataThirdFilterField || (!runtimeDataThirdFilterField && !runtimeDataThirdFilterOperator)) {
                  runtimeDataThirdFilterOperator = paramValue || '';
                }
              }
            }
            for (const [paramKey, paramValue] of parsedRuntimeDataUrl.searchParams.entries()) {
              const filterMatch = /^filter\[(.+)\]$/.exec(paramKey);
              if (filterMatch && !runtimeDataFilterField) {
                runtimeDataFilterField = filterMatch[1] || '';
                runtimeDataFilterValue = paramValue || '';
                runtimeDataFilterOperator = parsedRuntimeDataUrl.searchParams.get(`filter_op[${runtimeDataFilterField}]`) || runtimeDataFilterOperator || 'contains';
              } else if (filterMatch && !runtimeDataSecondFilterField) {
                runtimeDataSecondFilterField = filterMatch[1] || '';
                runtimeDataSecondFilterValue = paramValue || '';
                runtimeDataSecondFilterOperator = parsedRuntimeDataUrl.searchParams.get(`filter_op[${runtimeDataSecondFilterField}]`) || runtimeDataSecondFilterOperator || 'contains';
              } else if (filterMatch && !runtimeDataThirdFilterField) {
                runtimeDataThirdFilterField = filterMatch[1] || '';
                runtimeDataThirdFilterValue = paramValue || '';
                runtimeDataThirdFilterOperator = parsedRuntimeDataUrl.searchParams.get(`filter_op[${runtimeDataThirdFilterField}]`) || runtimeDataThirdFilterOperator || 'contains';
              }
              const sortMatch = /^sort\[(.+)\]$/.exec(paramKey);
              if (sortMatch && !runtimeDataSortField) {
                runtimeDataSortField = sortMatch[1] || '';
                runtimeDataSortDirection = paramValue || '';
              } else if (sortMatch && !runtimeDataSecondSortField) {
                runtimeDataSecondSortField = sortMatch[1] || '';
                runtimeDataSecondSortDirection = paramValue || '';
              } else if (sortMatch && !runtimeDataThirdSortField) {
                runtimeDataThirdSortField = sortMatch[1] || '';
                runtimeDataThirdSortDirection = paramValue || '';
              }
            }
          } catch (_error) {
            runtimeDataPathname = String(url).split('?')[0];
          }
          if (method.toUpperCase() === 'GET' && runtimeDataPathname.endsWith('/runtime-data.json')) {
            const dataProbe = {
              fetchCalled: true,
              url: String(url),
              method,
              credentials: String(options.credentials || ''),
              requestSelectedKey: runtimeDataSelectedKey,
              requestPage: runtimeDataPage,
              requestPageSize: runtimeDataPageSize,
              requestSearchQuery: runtimeDataSearchQuery,
              requestFilterField: runtimeDataFilterField,
              requestFilterValue: runtimeDataFilterValue,
              requestFilterOperator: runtimeDataFilterOperator,
              requestSecondFilterField: runtimeDataSecondFilterField,
              requestSecondFilterValue: runtimeDataSecondFilterValue,
              requestSecondFilterOperator: runtimeDataSecondFilterOperator,
              requestThirdFilterField: runtimeDataThirdFilterField,
              requestThirdFilterValue: runtimeDataThirdFilterValue,
              requestThirdFilterOperator: runtimeDataThirdFilterOperator,
              requestSortField: runtimeDataSortField,
              requestSortDirection: runtimeDataSortDirection,
              requestSecondSortField: runtimeDataSecondSortField,
              requestSecondSortDirection: runtimeDataSecondSortDirection,
              requestThirdSortField: runtimeDataThirdSortField,
              requestThirdSortDirection: runtimeDataThirdSortDirection,
              responseStatus: 0,
              responseOk: null,
              contractVersion: '',
              screenCount: 0,
              firstRowKey: '',
              selectedKey: '',
              pagination: {},
            };
            window.__noCodeRuntimeDataProbe = dataProbe;
            if (window.__noCodeRuntimeDataForceError) {
              window.__noCodeRuntimeDataForceError = false;
              const payload = {
                ok: false,
                contract_version: 'no-code-runtime-data-v0',
                error: 'forced_runtime_data_error',
              };
              dataProbe.responseStatus = 503;
              dataProbe.responseOk = false;
              dataProbe.contractVersion = payload.contract_version;
              window.__noCodeRuntimeDataProbe = dataProbe;
              return {
                json: async () => payload,
              };
            }
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
              const queryFilter = runtimeDataFilterField ? { [runtimeDataFilterField]: runtimeDataFilterValue } : {};
              const queryFilterOp = runtimeDataFilterField ? { [runtimeDataFilterField]: runtimeDataFilterOperator || 'contains' } : {};
              if (runtimeDataSecondFilterField) {
                queryFilter[runtimeDataSecondFilterField] = runtimeDataSecondFilterValue;
                queryFilterOp[runtimeDataSecondFilterField] = runtimeDataSecondFilterOperator || 'contains';
              }
              if (runtimeDataThirdFilterField) {
                queryFilter[runtimeDataThirdFilterField] = runtimeDataThirdFilterValue;
                queryFilterOp[runtimeDataThirdFilterField] = runtimeDataThirdFilterOperator || 'contains';
              }
              const querySort = runtimeDataSortField ? { [runtimeDataSortField]: runtimeDataSortDirection } : {};
              if (runtimeDataSecondSortField) {
                querySort[runtimeDataSecondSortField] = runtimeDataSecondSortDirection;
              }
              if (runtimeDataThirdSortField) {
                querySort[runtimeDataThirdSortField] = runtimeDataThirdSortDirection;
              }
              const readModelFields = {
                [expected.keyField]: {
                  field_key: expected.keyField,
                  label: expected.keyField,
                  type: 'integer',
                },
              };
              for (const fieldKey of Object.keys(expected.payload || {})) {
                readModelFields[fieldKey] = {
                  field_key: fieldKey,
                  label: fieldKey,
                  type: 'string',
                };
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
                read_model: {
                  contracts: {
                    [expected.contractKey]: {
                      contract_key: expected.contractKey,
                      fields: readModelFields,
                    },
                  },
                },
                query: {
                  selected_key: runtimeDataSelectedKey,
                  q: runtimeDataSearchQuery,
                  filter: queryFilter,
                  filter_op: queryFilterOp,
                  sort: querySort,
                  page: runtimeDataPage,
                  page_size: runtimeDataPageSize,
                },
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
              dataProbe.selectedKey = String(runtimeDataSelectedKey || expected.keyValue);
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
            const detailScreen = Array.isArray(payload?.screens) ? payload.screens.find((screen) => screen?.screen_key === expected.detailScreenKey) : null;
            dataProbe.selectedKey = detailScreen?.metadata?.selected_key?.display_value || payload?.query?.selected_key || '';
            const listScreen = Array.isArray(payload?.screens) ? payload.screens.find((screen) => screen?.screen_key === expected.listScreenKey) : null;
            dataProbe.pagination = listScreen?.metadata?.pagination || {};
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
        const dataProbeResult = { ...(window.__noCodeRuntimeDataProbe || {}) };
        const resultRefreshDisabledAfterDataRefresh = !!resultRefreshButton?.disabled;
        const resultRefreshStateAfterDataRefresh = resultRefreshButton?.getAttribute('data-runtime-result-refresh-state') || '';
        const resultRefreshStatusAfterDataRefresh = formScreen?.querySelector('[data-runtime-result-refresh-status]')?.textContent?.trim() || '';
        let runtimeDataSearch = {
          skipped: true,
          inputCount: document.querySelectorAll('[data-runtime-search-input]').length,
          buttonCount: document.querySelectorAll('[data-runtime-search-submit]').length,
          url: '',
          requestSearchQuery: '',
          requestPage: '',
          requestPageSize: '',
          responseStatus: 0,
          responseOk: null,
          firstRowKey: '',
          selectedKey: '',
          renderedRowCount: 0,
        };
        let runtimeDataEmptySearch = {
          skipped: true,
          inputCount: document.querySelectorAll('[data-runtime-search-input]').length,
          buttonCount: document.querySelectorAll('[data-runtime-search-submit]').length,
          url: '',
          requestSearchQuery: '',
          responseStatus: 0,
          responseOk: null,
          firstRowKey: '',
          selectedKey: '',
          renderedRowCount: 0,
          emptyRowCount: 0,
          querySummaryText: '',
          querySummaryAriaLabel: '',
          querySummaryTokenCount: 0,
        };
        let runtimeDataErrorRefresh = {
          skipped: true,
          beforeRowCount: 0,
          afterRowCount: 0,
          requestSearchQuery: '',
          responseStatus: 0,
          responseOk: null,
          statusText: '',
          state: '',
        };
        if (expected.statusProbe === 'real' && expected.searchQuery) {
          const searchInput = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-input]`);
          const searchButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-submit]`);
          if (searchInput && searchButton) {
            runtimeDataSearch.skipped = false;
            runtimeDataEmptySearch.skipped = false;
            searchInput.value = expected.searchQuery;
            searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            searchButton.click();
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              const firstSearchedRow = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`);
              if (latestDataProbe.requestSearchQuery === expected.searchQuery && firstSearchedRow?.getAttribute('data-runtime-row-key') === String(expected.selectedKeyValue || expected.keyValue)) {
                break;
              }
            }
            const searchProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            const retainedSearchInput = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-input]`);
            runtimeDataSearch = {
              ...runtimeDataSearch,
              inputCount: document.querySelectorAll('[data-runtime-search-input]').length,
              buttonCount: document.querySelectorAll('[data-runtime-search-submit]').length,
              retainedSearchValue: retainedSearchInput?.value || '',
              url: searchProbe.url || '',
              requestSearchQuery: searchProbe.requestSearchQuery || '',
              requestPage: searchProbe.requestPage || '',
              requestPageSize: searchProbe.requestPageSize || '',
              responseStatus: searchProbe.responseStatus || 0,
              responseOk: searchProbe.responseOk,
              firstRowKey: searchProbe.firstRowKey || '',
              selectedKey: searchProbe.selectedKey || '',
              renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
            };
            const emptySearchQuery = '__no_runtime_data_match__';
            const emptySearchInput = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-input]`);
            const emptySearchButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-submit]`);
            if (emptySearchInput && emptySearchButton) {
              emptySearchInput.value = emptySearchQuery;
              emptySearchInput.dispatchEvent(new Event('input', { bubbles: true }));
              emptySearchButton.click();
            }
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              const rowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length;
              const emptyRowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr.no-code-empty-row`).length;
              if (latestDataProbe.requestSearchQuery === emptySearchQuery && rowCount === 0 && emptyRowCount >= 1) {
                break;
              }
            }
            const emptySearchProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            const emptyQuerySummary = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-query-summary]`);
            runtimeDataEmptySearch = {
              ...runtimeDataEmptySearch,
              inputCount: document.querySelectorAll('[data-runtime-search-input]').length,
              buttonCount: document.querySelectorAll('[data-runtime-search-submit]').length,
              url: emptySearchProbe.url || '',
              requestSearchQuery: emptySearchProbe.requestSearchQuery || '',
              responseStatus: emptySearchProbe.responseStatus || 0,
              responseOk: emptySearchProbe.responseOk,
              firstRowKey: emptySearchProbe.firstRowKey || '',
              selectedKey: emptySearchProbe.selectedKey || '',
              renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
              emptyRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr.no-code-empty-row`).length,
              querySummaryText: emptyQuerySummary?.textContent?.trim() || '',
              querySummaryAriaLabel: emptyQuerySummary?.getAttribute('aria-label') || '',
              querySummaryTokenCount: emptyQuerySummary?.querySelectorAll('.no-code-runtime-data-query-token').length || 0,
            };
            const searchResetButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh]`) || resultRefreshButton;
            if (searchResetButton && !searchResetButton.disabled) {
              searchResetButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const rowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length;
                if (!latestDataProbe.requestSearchQuery && rowCount >= 2) {
                  break;
                }
              }
            }
            const errorRefreshButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh]`) || resultRefreshButton;
            if (errorRefreshButton && !errorRefreshButton.disabled) {
              const beforeRowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length;
              window.__noCodeRuntimeDataForceError = true;
              errorRefreshButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const latestStatus = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh-status]`)?.textContent?.trim() || '';
                if (latestDataProbe.responseOk === false && latestStatus.includes('Current preview data was left unchanged.')) {
                  break;
                }
              }
              const errorProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
              const errorStatus = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh-status]`);
              runtimeDataErrorRefresh = {
                ...runtimeDataErrorRefresh,
                skipped: false,
                beforeRowCount,
                afterRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
                requestSearchQuery: errorProbe.requestSearchQuery || '',
                responseStatus: errorProbe.responseStatus || 0,
                responseOk: errorProbe.responseOk,
                statusText: errorStatus?.textContent?.trim() || '',
                state: errorStatus?.getAttribute('data-state') || '',
              };
            }
          }
        }
        let runtimeDataFilter = {
          skipped: true,
          fieldControlCount: document.querySelectorAll('[data-runtime-filter-field], [data-runtime-filter-field-secondary], [data-runtime-filter-field-tertiary]').length,
          valueInputCount: document.querySelectorAll('[data-runtime-filter-value], [data-runtime-filter-value-secondary], [data-runtime-filter-value-tertiary]').length,
          buttonCount: document.querySelectorAll('[data-runtime-filter-submit]').length,
          url: '',
          requestFilterField: '',
          requestFilterValue: '',
          requestFilterOperator: '',
          requestSecondFilterField: '',
          requestSecondFilterValue: '',
          requestSecondFilterOperator: '',
          requestThirdFilterField: '',
          requestThirdFilterValue: '',
          requestThirdFilterOperator: '',
          requestPage: '',
          requestPageSize: '',
          retainedSecondFilterField: '',
          retainedSecondFilterValue: '',
          retainedThirdFilterField: '',
          retainedThirdFilterValue: '',
          retainedFilterOperator: '',
          retainedSecondFilterOperator: '',
          retainedThirdFilterOperator: '',
          typeDrivenStringOperatorValues: '',
          typeDrivenDateOperatorValues: '',
          typeDrivenDateOperatorSelected: '',
          typeDrivenStringValuePlaceholder: '',
          typeDrivenDateValuePlaceholder: '',
          typeDrivenDateValueTitle: '',
          typeDrivenStringValueInputType: '',
          typeDrivenNumberValueInputType: '',
          typeDrivenDateValueInputType: '',
          typeDrivenDatetimeValuePlaceholder: '',
          typeDrivenDatetimeValueTitle: '',
          typeDrivenDatetimeValueInputType: '',
          typeDrivenTimeValuePlaceholder: '',
          typeDrivenTimeValueTitle: '',
          typeDrivenTimeValueInputType: '',
          invalidFilterStatusText: '',
          invalidFilterFetchUnchanged: null,
          responseStatus: 0,
          responseOk: null,
          firstRowKey: '',
          selectedKey: '',
          renderedRowCount: 0,
        };
        if (expected.statusProbe === 'real' && expected.filterField && expected.filterValue) {
          const filterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field]`);
          const filterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value]`);
          const secondFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-secondary]`);
          const secondFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-secondary]`);
          const thirdFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-tertiary]`);
          const thirdFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-tertiary]`);
          const filterButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-submit]`);
          if (filterField && filterValue && filterButton) {
            runtimeDataFilter.skipped = false;
            const filterOperator = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-operator]`);
            const visibleOperatorValues = (operatorSelect) => Array.from(operatorSelect?.options || [])
              .filter((option) => !option.hidden && !option.disabled)
              .map((option) => option.value)
              .join(',');
            if (filterOperator && expected.typedFilterField && expected.typedFilterOperator) {
              filterField.value = expected.filterField;
              filterField.dispatchEvent(new Event('change', { bubbles: true }));
              runtimeDataFilter.typeDrivenStringOperatorValues = visibleOperatorValues(filterOperator);
              runtimeDataFilter.typeDrivenStringValuePlaceholder = filterValue.getAttribute('placeholder') || '';
              runtimeDataFilter.typeDrivenStringValueInputType = filterValue.getAttribute('type') || '';
              filterField.value = expected.typedFilterField;
              filterField.dispatchEvent(new Event('change', { bubbles: true }));
              runtimeDataFilter.typeDrivenDateOperatorValues = visibleOperatorValues(filterOperator);
              runtimeDataFilter.typeDrivenDateValuePlaceholder = filterValue.getAttribute('placeholder') || '';
              runtimeDataFilter.typeDrivenDateValueTitle = filterValue.getAttribute('title') || '';
              runtimeDataFilter.typeDrivenDateValueInputType = filterValue.getAttribute('type') || '';
              const typedOption = filterField.options?.[filterField.selectedIndex] || null;
              const originalTypedFieldType = typedOption?.getAttribute('data-runtime-field-type') || '';
              if (typedOption) {
                typedOption.setAttribute('data-runtime-field-type', 'datetime');
                filterField.dispatchEvent(new Event('change', { bubbles: true }));
                runtimeDataFilter.typeDrivenDatetimeValuePlaceholder = filterValue.getAttribute('placeholder') || '';
                runtimeDataFilter.typeDrivenDatetimeValueTitle = filterValue.getAttribute('title') || '';
                runtimeDataFilter.typeDrivenDatetimeValueInputType = filterValue.getAttribute('type') || '';
                typedOption.setAttribute('data-runtime-field-type', 'time');
                filterField.dispatchEvent(new Event('change', { bubbles: true }));
                runtimeDataFilter.typeDrivenTimeValuePlaceholder = filterValue.getAttribute('placeholder') || '';
                runtimeDataFilter.typeDrivenTimeValueTitle = filterValue.getAttribute('title') || '';
                runtimeDataFilter.typeDrivenTimeValueInputType = filterValue.getAttribute('type') || '';
                typedOption.setAttribute('data-runtime-field-type', originalTypedFieldType || 'date');
                filterField.dispatchEvent(new Event('change', { bubbles: true }));
              }
              if (expected.secondFilterField) {
                filterField.value = expected.secondFilterField;
                filterField.dispatchEvent(new Event('change', { bubbles: true }));
                runtimeDataFilter.typeDrivenNumberValueInputType = filterValue.getAttribute('type') || '';
                filterField.value = expected.typedFilterField;
                filterField.dispatchEvent(new Event('change', { bubbles: true }));
              }
              filterOperator.value = expected.typedFilterOperator;
              filterOperator.dispatchEvent(new Event('change', { bubbles: true }));
              runtimeDataFilter.typeDrivenDateOperatorSelected = filterOperator.value || '';
            }
            filterField.value = expected.filterField;
            filterField.dispatchEvent(new Event('change', { bubbles: true }));
            filterValue.value = expected.filterValue;
            filterValue.dispatchEvent(new Event('input', { bubbles: true }));
            if (secondFilterField && secondFilterValue && expected.secondFilterField && expected.secondFilterValue) {
              secondFilterField.value = expected.secondFilterField;
              secondFilterField.dispatchEvent(new Event('change', { bubbles: true }));
              secondFilterValue.value = expected.secondFilterValue;
              secondFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (thirdFilterField && thirdFilterValue && expected.thirdFilterField && expected.thirdFilterValue) {
              thirdFilterField.value = expected.thirdFilterField;
              thirdFilterField.dispatchEvent(new Event('change', { bubbles: true }));
              thirdFilterValue.value = expected.thirdFilterValue;
              thirdFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
            }
            filterButton.click();
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              const firstFilteredRow = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`);
              if (
                latestDataProbe.requestFilterField === expected.filterField
                && latestDataProbe.requestFilterValue === expected.filterValue
                && (!expected.secondFilterField || latestDataProbe.requestSecondFilterField === expected.secondFilterField)
                && (!expected.secondFilterValue || latestDataProbe.requestSecondFilterValue === expected.secondFilterValue)
                && (!expected.thirdFilterField || latestDataProbe.requestThirdFilterField === expected.thirdFilterField)
                && (!expected.thirdFilterValue || latestDataProbe.requestThirdFilterValue === expected.thirdFilterValue)
                && firstFilteredRow?.getAttribute('data-runtime-row-key') === String(expected.selectedKeyValue || expected.keyValue)
              ) {
                break;
              }
            }
            const filterProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            let invalidFilterStatusText = '';
            let invalidFilterFetchUnchanged = null;
            if (expected.typedFilterField && expected.secondFilterField) {
              const invalidFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field]`);
              const invalidFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value]`);
              const invalidSecondFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-secondary]`);
              const invalidSecondFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-secondary]`);
              const invalidThirdFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-tertiary]`);
              const invalidThirdFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-tertiary]`);
              const invalidFilterButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-submit]`);
              invalidFilterField.value = expected.secondFilterField;
              invalidFilterField.dispatchEvent(new Event('change', { bubbles: true }));
              invalidFilterValue.value = '1.5';
              invalidFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
              if (invalidSecondFilterField && invalidSecondFilterValue) {
                invalidSecondFilterField.value = '';
                invalidSecondFilterField.dispatchEvent(new Event('change', { bubbles: true }));
                invalidSecondFilterValue.value = '';
                invalidSecondFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
              }
              if (invalidThirdFilterField && invalidThirdFilterValue) {
                invalidThirdFilterField.value = '';
                invalidThirdFilterField.dispatchEvent(new Event('change', { bubbles: true }));
                invalidThirdFilterValue.value = '';
                invalidThirdFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
              }
              invalidFilterButton.click();
              await new Promise((resolve) => setTimeout(resolve, 200));
              const invalidProbe = window.__noCodeRuntimeDataProbe || {};
              invalidFilterStatusText = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh-status]`)?.textContent?.trim() || '';
              invalidFilterFetchUnchanged = (invalidProbe.url || '') === (filterProbe.url || '')
                && (invalidProbe.requestFilterField || '') === (filterProbe.requestFilterField || '')
                && (invalidProbe.requestFilterValue || '') === (filterProbe.requestFilterValue || '');
              invalidFilterField.value = expected.filterField;
              invalidFilterField.dispatchEvent(new Event('change', { bubbles: true }));
              invalidFilterValue.value = expected.filterValue;
              invalidFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
              if (invalidSecondFilterField && invalidSecondFilterValue && expected.secondFilterField && expected.secondFilterValue) {
                invalidSecondFilterField.value = expected.secondFilterField;
                invalidSecondFilterField.dispatchEvent(new Event('change', { bubbles: true }));
                invalidSecondFilterValue.value = expected.secondFilterValue;
                invalidSecondFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
              }
              if (invalidThirdFilterField && invalidThirdFilterValue && expected.thirdFilterField && expected.thirdFilterValue) {
                invalidThirdFilterField.value = expected.thirdFilterField;
                invalidThirdFilterField.dispatchEvent(new Event('change', { bubbles: true }));
                invalidThirdFilterValue.value = expected.thirdFilterValue;
                invalidThirdFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
              }
            }
            const retainedFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field]`);
            const retainedFilterOperator = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-operator]`);
            const retainedFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value]`);
            const retainedSecondFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-secondary]`);
            const retainedSecondFilterOperator = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-operator-secondary]`);
            const retainedSecondFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-secondary]`);
            const retainedThirdFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-tertiary]`);
            const retainedThirdFilterOperator = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-operator-tertiary]`);
            const retainedThirdFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-tertiary]`);
            runtimeDataFilter = {
              ...runtimeDataFilter,
              fieldControlCount: document.querySelectorAll('[data-runtime-filter-field], [data-runtime-filter-field-secondary], [data-runtime-filter-field-tertiary]').length,
              valueInputCount: document.querySelectorAll('[data-runtime-filter-value], [data-runtime-filter-value-secondary], [data-runtime-filter-value-tertiary]').length,
              buttonCount: document.querySelectorAll('[data-runtime-filter-submit]').length,
              retainedFilterField: retainedFilterField?.value || '',
              retainedFilterOperator: retainedFilterOperator?.value || '',
              retainedFilterValue: retainedFilterValue?.value || '',
              retainedSecondFilterField: retainedSecondFilterField?.value || '',
              retainedSecondFilterOperator: retainedSecondFilterOperator?.value || '',
              retainedSecondFilterValue: retainedSecondFilterValue?.value || '',
              retainedThirdFilterField: retainedThirdFilterField?.value || '',
              retainedThirdFilterOperator: retainedThirdFilterOperator?.value || '',
              retainedThirdFilterValue: retainedThirdFilterValue?.value || '',
              url: filterProbe.url || '',
              requestFilterField: filterProbe.requestFilterField || '',
              requestFilterValue: filterProbe.requestFilterValue || '',
              requestFilterOperator: filterProbe.requestFilterOperator || '',
              requestSecondFilterField: filterProbe.requestSecondFilterField || '',
              requestSecondFilterValue: filterProbe.requestSecondFilterValue || '',
              requestSecondFilterOperator: filterProbe.requestSecondFilterOperator || '',
              requestThirdFilterField: filterProbe.requestThirdFilterField || '',
              requestThirdFilterValue: filterProbe.requestThirdFilterValue || '',
              requestThirdFilterOperator: filterProbe.requestThirdFilterOperator || '',
              requestPage: filterProbe.requestPage || '',
              requestPageSize: filterProbe.requestPageSize || '',
              invalidFilterStatusText,
              invalidFilterFetchUnchanged,
              responseStatus: filterProbe.responseStatus || 0,
              responseOk: filterProbe.responseOk,
              firstRowKey: filterProbe.firstRowKey || '',
              selectedKey: filterProbe.selectedKey || '',
              renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
            };
            const filterResetButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh]`) || resultRefreshButton;
            if (filterResetButton && !filterResetButton.disabled) {
              filterResetButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const rowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length;
                if (!latestDataProbe.requestFilterField && rowCount >= 2) {
                  break;
                }
              }
            }
          }
        }
        let runtimeDataSort = {
          skipped: true,
          fieldControlCount: document.querySelectorAll('[data-runtime-sort-field]').length,
          directionControlCount: document.querySelectorAll('[data-runtime-sort-direction]').length,
          buttonCount: document.querySelectorAll('[data-runtime-sort-submit]').length,
          url: '',
          requestSortField: '',
          requestSortDirection: '',
          requestSecondSortField: '',
          requestSecondSortDirection: '',
          requestThirdSortField: '',
          requestThirdSortDirection: '',
          headerButtonCount: document.querySelectorAll('[data-runtime-sort-header]').length,
          headerUrl: '',
          headerRequestSortField: '',
          headerRequestSortDirection: '',
          headerRequestSecondSortField: '',
          headerRequestThirdSortField: '',
          headerRetainedSortField: '',
          headerRetainedSortDirection: '',
          headerRetainedSecondSortField: '',
          headerRetainedThirdSortField: '',
          headerAriaSort: '',
          headerSortState: '',
          headerOtherAriaSort: '',
          headerOtherSortState: '',
          requestPage: '',
          requestPageSize: '',
          responseStatus: 0,
          responseOk: null,
          firstRowKey: '',
          selectedKey: '',
          renderedRowCount: 0,
        };
        if (expected.statusProbe === 'real' && expected.sortField && expected.sortDirection) {
          const sortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field]`);
          const sortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction]`);
          const secondSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-secondary]`);
          const secondSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-secondary]`);
          const thirdSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-tertiary]`);
          const thirdSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-tertiary]`);
          const sortButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-submit]`);
          if (sortField && sortDirection && sortButton) {
            runtimeDataSort.skipped = false;
            sortField.value = expected.sortField;
            sortField.dispatchEvent(new Event('change', { bubbles: true }));
            sortDirection.value = expected.sortDirection;
            sortDirection.dispatchEvent(new Event('change', { bubbles: true }));
            if (secondSortField && secondSortDirection && expected.secondSortField && expected.secondSortDirection) {
              secondSortField.value = expected.secondSortField;
              secondSortField.dispatchEvent(new Event('change', { bubbles: true }));
              secondSortDirection.value = expected.secondSortDirection;
              secondSortDirection.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (thirdSortField && thirdSortDirection && expected.thirdSortField && expected.thirdSortDirection) {
              thirdSortField.value = expected.thirdSortField;
              thirdSortField.dispatchEvent(new Event('change', { bubbles: true }));
              thirdSortDirection.value = expected.thirdSortDirection;
              thirdSortDirection.dispatchEvent(new Event('change', { bubbles: true }));
            }
            sortButton.click();
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              const firstSortedRow = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`);
              if (
                latestDataProbe.requestSortField === expected.sortField
                && latestDataProbe.requestSortDirection === expected.sortDirection
                && (!expected.secondSortField || latestDataProbe.requestSecondSortField === expected.secondSortField)
                && (!expected.secondSortDirection || latestDataProbe.requestSecondSortDirection === expected.secondSortDirection)
                && (!expected.thirdSortField || latestDataProbe.requestThirdSortField === expected.thirdSortField)
                && (!expected.thirdSortDirection || latestDataProbe.requestThirdSortDirection === expected.thirdSortDirection)
                && firstSortedRow?.getAttribute('data-runtime-row-key') === String(expected.sortFirstKeyValue || expected.selectedKeyValue || expected.keyValue)
              ) {
                break;
              }
            }
            const sortProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            const retainedSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field]`);
            const retainedSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction]`);
            const retainedSecondSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-secondary]`);
            const retainedSecondSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-secondary]`);
            const retainedThirdSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-tertiary]`);
            const retainedThirdSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-tertiary]`);
            runtimeDataSort = {
              ...runtimeDataSort,
              fieldControlCount: document.querySelectorAll('[data-runtime-sort-field]').length,
              directionControlCount: document.querySelectorAll('[data-runtime-sort-direction]').length,
              buttonCount: document.querySelectorAll('[data-runtime-sort-submit]').length,
              headerButtonCount: document.querySelectorAll('[data-runtime-sort-header]').length,
              retainedSortField: retainedSortField?.value || '',
              retainedSortDirection: retainedSortDirection?.value || '',
              retainedSecondSortField: retainedSecondSortField?.value || '',
              retainedSecondSortDirection: retainedSecondSortDirection?.value || '',
              retainedThirdSortField: retainedThirdSortField?.value || '',
              retainedThirdSortDirection: retainedThirdSortDirection?.value || '',
              url: sortProbe.url || '',
              requestSortField: sortProbe.requestSortField || '',
              requestSortDirection: sortProbe.requestSortDirection || '',
              requestSecondSortField: sortProbe.requestSecondSortField || '',
              requestSecondSortDirection: sortProbe.requestSecondSortDirection || '',
              requestThirdSortField: sortProbe.requestThirdSortField || '',
              requestThirdSortDirection: sortProbe.requestThirdSortDirection || '',
              requestPage: sortProbe.requestPage || '',
              requestPageSize: sortProbe.requestPageSize || '',
              responseStatus: sortProbe.responseStatus || 0,
              responseOk: sortProbe.responseOk,
              firstRowKey: sortProbe.firstRowKey || '',
              selectedKey: sortProbe.selectedKey || '',
              renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
            };
            const sortHeaderButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-header][data-runtime-sort-field-key="${expected.sortField}"]`);
            if (sortHeaderButton) {
              const expectedHeaderDirection = expected.sortDirection === 'asc' ? 'desc' : 'asc';
              sortHeaderButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                if (
                  latestDataProbe.requestSortField === expected.sortField
                  && latestDataProbe.requestSortDirection === expectedHeaderDirection
                  && !latestDataProbe.requestSecondSortField
                  && !latestDataProbe.requestThirdSortField
                ) {
                  break;
                }
              }
              const headerProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
              const headerRetainedSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field]`);
              const headerRetainedSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction]`);
              const headerRetainedSecondSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-secondary]`);
              const headerRetainedThirdSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-tertiary]`);
              const headerCell = sortHeaderButton.closest('th');
              const otherSortHeaderButton = Array.from(document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-header]`))
                .find((button) => button.getAttribute('data-runtime-sort-field-key') !== expected.sortField);
              const otherHeaderCell = otherSortHeaderButton?.closest('th') || null;
              runtimeDataSort = {
                ...runtimeDataSort,
                headerUrl: headerProbe.url || '',
                headerRequestSortField: headerProbe.requestSortField || '',
                headerRequestSortDirection: headerProbe.requestSortDirection || '',
                headerRequestSecondSortField: headerProbe.requestSecondSortField || '',
                headerRequestThirdSortField: headerProbe.requestThirdSortField || '',
                headerRetainedSortField: headerRetainedSortField?.value || '',
                headerRetainedSortDirection: headerRetainedSortDirection?.value || '',
                headerRetainedSecondSortField: headerRetainedSecondSortField?.value || '',
                headerRetainedThirdSortField: headerRetainedThirdSortField?.value || '',
                headerAriaSort: headerCell?.getAttribute('aria-sort') || '',
                headerSortState: sortHeaderButton.getAttribute('data-runtime-sort-state') || '',
                headerOtherAriaSort: otherHeaderCell?.getAttribute('aria-sort') || '',
                headerOtherSortState: otherSortHeaderButton?.getAttribute('data-runtime-sort-state') || '',
              };
            }
            const sortResetButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh]`) || resultRefreshButton;
            if (sortResetButton && !sortResetButton.disabled) {
              sortResetButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const rowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length;
                if (!latestDataProbe.requestSortField && rowCount >= 2) {
                  break;
                }
              }
            }
          }
        }
        let runtimeDataCombined = {
          skipped: true,
          url: '',
          requestSearchQuery: '',
          requestFilterField: '',
          requestFilterValue: '',
          requestSortField: '',
          requestSortDirection: '',
          requestPage: '',
          requestPageSize: '',
          retainedSearchValue: '',
          retainedFilterField: '',
          retainedFilterValue: '',
          retainedSecondFilterField: '',
          retainedSecondFilterValue: '',
          retainedThirdFilterField: '',
          retainedThirdFilterValue: '',
          retainedSortField: '',
          retainedSortDirection: '',
          retainedSecondSortField: '',
          retainedSecondSortDirection: '',
          retainedThirdSortField: '',
          retainedThirdSortDirection: '',
          retainedPageSize: '',
          querySummaryText: '',
          querySummaryAriaLabel: '',
          querySummaryTokenCount: 0,
          locationSearch: '',
          responseStatus: 0,
          responseOk: null,
          firstRowKey: '',
          renderedRowCount: 0,
        };
        let runtimeDataQueryReset = {
          skipped: true,
          url: '',
          requestSearchQuery: '',
          requestFilterField: '',
          requestFilterValue: '',
          requestSortField: '',
          requestSortDirection: '',
          requestPage: '',
          requestPageSize: '',
          retainedSearchValue: '',
          retainedFilterField: '',
          retainedFilterValue: '',
          retainedSortField: '',
          retainedSortDirection: '',
          retainedPageSize: '',
          querySummaryText: '',
          querySummaryAriaLabel: '',
          querySummaryTokenCount: 0,
          locationSearch: '',
          responseStatus: 0,
          responseOk: null,
          renderedRowCount: 0,
        };
        if (expected.statusProbe === 'real' && expected.searchQuery && expected.filterField && expected.filterValue && expected.sortField && expected.sortDirection) {
          const combinedSearchInput = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-input]`);
          const combinedFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field]`);
          const combinedFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value]`);
          const combinedSecondFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-secondary]`);
          const combinedSecondFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-secondary]`);
          const combinedThirdFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-tertiary]`);
          const combinedThirdFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-tertiary]`);
          const combinedSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field]`);
          const combinedSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction]`);
          const combinedSecondSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-secondary]`);
          const combinedSecondSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-secondary]`);
          const combinedThirdSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-tertiary]`);
          const combinedThirdSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-tertiary]`);
          const combinedPageSize = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-size-input]`);
          const combinedSortButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-submit]`);
          if (combinedSearchInput && combinedFilterField && combinedFilterValue && combinedSortField && combinedSortDirection && combinedPageSize && combinedSortButton) {
            runtimeDataCombined.skipped = false;
            combinedSearchInput.value = expected.searchQuery;
            combinedSearchInput.dispatchEvent(new Event('input', { bubbles: true }));
            combinedFilterField.value = expected.filterField;
            combinedFilterField.dispatchEvent(new Event('change', { bubbles: true }));
            combinedFilterValue.value = expected.filterValue;
            combinedFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
            if (combinedSecondFilterField && combinedSecondFilterValue && expected.secondFilterField && expected.secondFilterValue) {
              combinedSecondFilterField.value = expected.secondFilterField;
              combinedSecondFilterField.dispatchEvent(new Event('change', { bubbles: true }));
              combinedSecondFilterValue.value = expected.secondFilterValue;
              combinedSecondFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (combinedThirdFilterField && combinedThirdFilterValue && expected.thirdFilterField && expected.thirdFilterValue) {
              combinedThirdFilterField.value = expected.thirdFilterField;
              combinedThirdFilterField.dispatchEvent(new Event('change', { bubbles: true }));
              combinedThirdFilterValue.value = expected.thirdFilterValue;
              combinedThirdFilterValue.dispatchEvent(new Event('input', { bubbles: true }));
            }
            combinedSortField.value = expected.sortField;
            combinedSortField.dispatchEvent(new Event('change', { bubbles: true }));
            combinedSortDirection.value = expected.sortDirection;
            combinedSortDirection.dispatchEvent(new Event('change', { bubbles: true }));
            if (combinedSecondSortField && combinedSecondSortDirection && expected.secondSortField && expected.secondSortDirection) {
              combinedSecondSortField.value = expected.secondSortField;
              combinedSecondSortField.dispatchEvent(new Event('change', { bubbles: true }));
              combinedSecondSortDirection.value = expected.secondSortDirection;
              combinedSecondSortDirection.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (combinedThirdSortField && combinedThirdSortDirection && expected.thirdSortField && expected.thirdSortDirection) {
              combinedThirdSortField.value = expected.thirdSortField;
              combinedThirdSortField.dispatchEvent(new Event('change', { bubbles: true }));
              combinedThirdSortDirection.value = expected.thirdSortDirection;
              combinedThirdSortDirection.dispatchEvent(new Event('change', { bubbles: true }));
            }
            combinedPageSize.value = '1';
            combinedPageSize.dispatchEvent(new Event('input', { bubbles: true }));
            combinedSortButton.click();
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              const firstCombinedRow = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`);
              if (
                latestDataProbe.requestSearchQuery === expected.searchQuery
                && latestDataProbe.requestFilterField === expected.filterField
                && latestDataProbe.requestFilterValue === expected.filterValue
                && (!expected.secondFilterField || latestDataProbe.requestSecondFilterField === expected.secondFilterField)
                && (!expected.secondFilterValue || latestDataProbe.requestSecondFilterValue === expected.secondFilterValue)
                && (!expected.thirdFilterField || latestDataProbe.requestThirdFilterField === expected.thirdFilterField)
                && (!expected.thirdFilterValue || latestDataProbe.requestThirdFilterValue === expected.thirdFilterValue)
                && latestDataProbe.requestSortField === expected.sortField
                && latestDataProbe.requestSortDirection === expected.sortDirection
                && (!expected.secondSortField || latestDataProbe.requestSecondSortField === expected.secondSortField)
                && (!expected.secondSortDirection || latestDataProbe.requestSecondSortDirection === expected.secondSortDirection)
                && (!expected.thirdSortField || latestDataProbe.requestThirdSortField === expected.thirdSortField)
                && (!expected.thirdSortDirection || latestDataProbe.requestThirdSortDirection === expected.thirdSortDirection)
                && latestDataProbe.requestPage === '1'
                && latestDataProbe.requestPageSize === '1'
                && firstCombinedRow?.getAttribute('data-runtime-row-key') === String(expected.selectedKeyValue || expected.keyValue)
              ) {
                break;
              }
            }
            const combinedProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            const retainedCombinedSearch = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-input]`);
            const retainedCombinedFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field]`);
            const retainedCombinedFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value]`);
            const retainedCombinedSecondFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-secondary]`);
            const retainedCombinedSecondFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-secondary]`);
            const retainedCombinedThirdFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field-tertiary]`);
            const retainedCombinedThirdFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value-tertiary]`);
            const retainedCombinedSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field]`);
            const retainedCombinedSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction]`);
            const retainedCombinedSecondSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-secondary]`);
            const retainedCombinedSecondSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-secondary]`);
            const retainedCombinedThirdSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field-tertiary]`);
            const retainedCombinedThirdSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction-tertiary]`);
            const retainedCombinedPageSize = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-size-input]`);
            const combinedQuerySummary = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-query-summary]`);
            runtimeDataCombined = {
              ...runtimeDataCombined,
              url: combinedProbe.url || '',
              requestSearchQuery: combinedProbe.requestSearchQuery || '',
              requestFilterField: combinedProbe.requestFilterField || '',
              requestFilterValue: combinedProbe.requestFilterValue || '',
              requestSecondFilterField: combinedProbe.requestSecondFilterField || '',
              requestSecondFilterValue: combinedProbe.requestSecondFilterValue || '',
              requestThirdFilterField: combinedProbe.requestThirdFilterField || '',
              requestThirdFilterValue: combinedProbe.requestThirdFilterValue || '',
              requestSortField: combinedProbe.requestSortField || '',
              requestSortDirection: combinedProbe.requestSortDirection || '',
              requestSecondSortField: combinedProbe.requestSecondSortField || '',
              requestSecondSortDirection: combinedProbe.requestSecondSortDirection || '',
              requestThirdSortField: combinedProbe.requestThirdSortField || '',
              requestThirdSortDirection: combinedProbe.requestThirdSortDirection || '',
              requestPage: combinedProbe.requestPage || '',
              requestPageSize: combinedProbe.requestPageSize || '',
              retainedSearchValue: retainedCombinedSearch?.value || '',
              retainedFilterField: retainedCombinedFilterField?.value || '',
              retainedFilterValue: retainedCombinedFilterValue?.value || '',
              retainedSecondFilterField: retainedCombinedSecondFilterField?.value || '',
              retainedSecondFilterValue: retainedCombinedSecondFilterValue?.value || '',
              retainedThirdFilterField: retainedCombinedThirdFilterField?.value || '',
              retainedThirdFilterValue: retainedCombinedThirdFilterValue?.value || '',
              retainedSortField: retainedCombinedSortField?.value || '',
              retainedSortDirection: retainedCombinedSortDirection?.value || '',
              retainedSecondSortField: retainedCombinedSecondSortField?.value || '',
              retainedSecondSortDirection: retainedCombinedSecondSortDirection?.value || '',
              retainedThirdSortField: retainedCombinedThirdSortField?.value || '',
              retainedThirdSortDirection: retainedCombinedThirdSortDirection?.value || '',
              retainedPageSize: retainedCombinedPageSize?.value || '',
              querySummaryText: combinedQuerySummary?.textContent?.trim() || '',
              querySummaryAriaLabel: combinedQuerySummary?.getAttribute('aria-label') || '',
              querySummaryTokenCount: combinedQuerySummary?.querySelectorAll('.no-code-runtime-data-query-token')?.length || 0,
              locationSearch: window.location.search || '',
              responseStatus: combinedProbe.responseStatus || 0,
              responseOk: combinedProbe.responseOk,
              firstRowKey: combinedProbe.firstRowKey || '',
              renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
            };
            const combinedResetButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-query-reset]`);
            if (combinedResetButton && !combinedResetButton.disabled) {
              runtimeDataQueryReset.skipped = false;
              combinedResetButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const rowCount = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length;
                if (!latestDataProbe.requestSearchQuery && !latestDataProbe.requestFilterField && !latestDataProbe.requestSortField && !latestDataProbe.requestPage && !latestDataProbe.requestPageSize && rowCount >= 2) {
                  break;
                }
              }
              const resetProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
              const resetSearch = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-search-input]`);
              const resetFilterField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-field]`);
              const resetFilterValue = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-filter-value]`);
              const resetSortField = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-field]`);
              const resetSortDirection = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-sort-direction]`);
              const resetPageSize = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-size-input]`);
              const resetQuerySummary = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-query-summary]`);
              runtimeDataQueryReset = {
                ...runtimeDataQueryReset,
                url: resetProbe.url || '',
                requestSearchQuery: resetProbe.requestSearchQuery || '',
                requestFilterField: resetProbe.requestFilterField || '',
                requestFilterValue: resetProbe.requestFilterValue || '',
                requestSortField: resetProbe.requestSortField || '',
                requestSortDirection: resetProbe.requestSortDirection || '',
                requestPage: resetProbe.requestPage || '',
                requestPageSize: resetProbe.requestPageSize || '',
                retainedSearchValue: resetSearch?.value || '',
                retainedFilterField: resetFilterField?.value || '',
                retainedFilterValue: resetFilterValue?.value || '',
                retainedSortField: resetSortField?.value || '',
                retainedSortDirection: resetSortDirection?.value || '',
                retainedPageSize: resetPageSize?.value || '',
                querySummaryText: resetQuerySummary?.textContent?.trim() || '',
                querySummaryAriaLabel: resetQuerySummary?.getAttribute('aria-label') || '',
                querySummaryTokenCount: resetQuerySummary?.querySelectorAll('.no-code-runtime-data-query-token')?.length || 0,
                locationSearch: window.location.search || '',
                responseStatus: resetProbe.responseStatus || 0,
                responseOk: resetProbe.responseOk,
                renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
              };
            }
          }
        }
        let runtimeDataPagination = {
          skipped: true,
          controlGroupCount: document.querySelectorAll('[data-runtime-data-controls]').length,
          labelledGroupCount: Array.from(document.querySelectorAll('[data-runtime-data-controls]')).filter((element) => element.getAttribute('role') === 'group' && element.getAttribute('aria-label') === 'Runtime data controls').length,
          pageSizeButtonCount: document.querySelectorAll('[data-runtime-page-size-submit]').length,
          pageSizeInputCount: document.querySelectorAll('[data-runtime-page-size-input]').length,
          pageSubmitButtonCount: document.querySelectorAll('[data-runtime-page-submit]').length,
          pageInputCount: document.querySelectorAll('[data-runtime-page-input]').length,
          queryResetButtonCount: document.querySelectorAll('[data-runtime-query-reset]').length,
          pageButtonCount: document.querySelectorAll('[data-runtime-page]').length,
          entryUrl: '',
          entryPage: '',
          entryPageSize: '',
          nextUrl: '',
          nextPage: '',
          nextPageSize: '',
          directUrl: '',
          directPage: '',
          directPageSize: '',
          renderedRowCount: 0,
          firstRowKey: '',
          pageText: '',
          totalRowsAttribute: '',
          pagination: {},
        };
        if (expected.statusProbe === 'real') {
          const pageSizeInput = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-size-input]`);
          const pageSizeButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-size-submit]`);
          if (pageSizeButton) {
            runtimeDataPagination.skipped = false;
            if (pageSizeInput) {
              pageSizeInput.value = '1';
              pageSizeInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            pageSizeButton.click();
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              const latestNextButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page="2"][data-runtime-page-size="1"]`);
              if (latestDataProbe.requestPage === '1' && latestDataProbe.requestPageSize === '1' && latestNextButton) {
                break;
              }
            }
            const entryProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            const nextButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page="2"][data-runtime-page-size="1"]`);
            if (nextButton) {
              nextButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const latestPagination = latestDataProbe.pagination || {};
                const activePagination = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] .no-code-pagination[data-runtime-pagination-page="2"]`);
                if (latestDataProbe.requestPage === '2' && latestDataProbe.requestPageSize === '1' && Number(latestPagination.page || 0) === 2 && activePagination) {
                  break;
                }
              }
            }
            const nextProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
            const firstPaginatedRow = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`);
            const paginationElement = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] .no-code-pagination`);
            runtimeDataPagination = {
              ...runtimeDataPagination,
              controlGroupCount: document.querySelectorAll('[data-runtime-data-controls]').length,
              labelledGroupCount: Array.from(document.querySelectorAll('[data-runtime-data-controls]')).filter((element) => element.getAttribute('role') === 'group' && element.getAttribute('aria-label') === 'Runtime data controls').length,
              pageSizeButtonCount: document.querySelectorAll('[data-runtime-page-size-submit]').length,
              pageSizeInputCount: document.querySelectorAll('[data-runtime-page-size-input]').length,
              pageSubmitButtonCount: document.querySelectorAll('[data-runtime-page-submit]').length,
              pageInputCount: document.querySelectorAll('[data-runtime-page-input]').length,
              queryResetButtonCount: document.querySelectorAll('[data-runtime-query-reset]').length,
              pageButtonCount: document.querySelectorAll('[data-runtime-page]').length,
              entryUrl: entryProbe.url || '',
              entryPage: entryProbe.requestPage || '',
              entryPageSize: entryProbe.requestPageSize || '',
              nextUrl: nextProbe.url || '',
              nextPage: nextProbe.requestPage || '',
              nextPageSize: nextProbe.requestPageSize || '',
              renderedRowCount: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
              firstRowKey: firstPaginatedRow?.getAttribute('data-runtime-row-key') || '',
              pageText: paginationElement?.textContent?.trim() || '',
              totalRowsAttribute: paginationElement?.getAttribute('data-runtime-pagination-total-rows') || '',
              pagination: nextProbe.pagination || {},
            };
            const directPageInput = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-input]`);
            const directPageButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-page-submit]`);
            if (directPageInput && directPageButton) {
              directPageInput.value = '1';
              directPageInput.dispatchEvent(new Event('input', { bubbles: true }));
              directPageButton.click();
              for (let attempt = 0; attempt < 30; attempt += 1) {
                await new Promise((resolve) => setTimeout(resolve, 100));
                const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                const latestPagination = latestDataProbe.pagination || {};
                const activePagination = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] .no-code-pagination[data-runtime-pagination-page="1"]`);
                if (latestDataProbe.requestPage === '1' && latestDataProbe.requestPageSize === '1' && Number(latestPagination.page || 0) === 1 && activePagination) {
                  break;
                }
              }
              const directProbe = { ...(window.__noCodeRuntimeDataProbe || {}) };
              runtimeDataPagination = {
                ...runtimeDataPagination,
                pageSubmitButtonCount: document.querySelectorAll('[data-runtime-page-submit]').length,
                pageInputCount: document.querySelectorAll('[data-runtime-page-input]').length,
                directUrl: directProbe.url || '',
                directPage: directProbe.requestPage || '',
                directPageSize: directProbe.requestPageSize || '',
              };
              if (expected.selectedKeyValue && String(expected.selectedKeyValue) !== String(expected.keyValue)) {
                const runtimeFullRefreshButton = document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh]`) || resultRefreshButton;
                runtimeFullRefreshButton?.click();
                for (let attempt = 0; attempt < 30; attempt += 1) {
                  await new Promise((resolve) => setTimeout(resolve, 100));
                  const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
                  const selectableRows = document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-row-select]`);
                  if (!latestDataProbe.requestPage && selectableRows.length >= 2) {
                    break;
                  }
                }
              }
            }
          }
        }
        let runtimeDataRowSelection = {
          skipped: true,
          selectedKey: '',
          buttonCount: document.querySelectorAll('[data-runtime-row-select]').length,
          url: '',
          requestSelectedKey: '',
          responseStatus: 0,
          responseOk: null,
          responseSelectedKey: '',
          hiddenKeyValue: '',
          draftChecks: [],
          statusText: '',
        };
        const expectedSelectedKey = expected.selectedKeyValue ? String(expected.selectedKeyValue) : '';
        if (expectedSelectedKey && expectedSelectedKey !== String(expected.keyValue)) {
          const selectedButton = document.querySelector(`[data-runtime-row-select][data-runtime-selected-key="${expectedSelectedKey}"]`);
          if (selectedButton) {
            runtimeDataRowSelection.skipped = false;
            runtimeDataRowSelection.selectedKey = expectedSelectedKey;
            selectedButton.click();
            for (let attempt = 0; attempt < 30; attempt += 1) {
              await new Promise((resolve) => setTimeout(resolve, 100));
              const refreshState = formScreen?.querySelector('[data-runtime-result-refresh]')?.getAttribute('data-runtime-result-refresh-state') || '';
              const latestDataProbe = window.__noCodeRuntimeDataProbe || {};
              if ((refreshState === 'success' || refreshState === 'error') && latestDataProbe.requestSelectedKey === expectedSelectedKey) {
                break;
              }
            }
            const selectedDraftText = formScreen?.querySelector('[data-intent-draft-output]')?.textContent || '';
            let selectedDraft = {};
            try {
              selectedDraft = selectedDraftText ? JSON.parse(selectedDraftText) : {};
            } catch (error) {
              selectedDraft = { parse_error: String(error && error.message ? error.message : error) };
            }
            const selectedDataProbe = window.__noCodeRuntimeDataProbe || {};
            runtimeDataRowSelection = {
              ...runtimeDataRowSelection,
              buttonCount: document.querySelectorAll('[data-runtime-row-select]').length,
              url: selectedDataProbe.url || '',
              requestSelectedKey: selectedDataProbe.requestSelectedKey || '',
              responseStatus: selectedDataProbe.responseStatus || 0,
              responseOk: selectedDataProbe.responseOk,
              responseSelectedKey: selectedDataProbe.selectedKey || '',
              hiddenKeyValue: formScreen?.querySelector(`[data-runtime-hidden-action-key="${expected.keyField}"]`)?.getAttribute('value') || '',
              draftChecks: Array.isArray(selectedDraft.draft_checks) ? selectedDraft.draft_checks : [],
              statusText: document.querySelector(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] [data-runtime-result-refresh-status]`)?.textContent?.trim() || '',
            };
          }
        }
        const probeResult = window.__noCodeRuntimeSubmitProbe || {};
        const statusProbeResult = window.__noCodeRuntimeStatusProbe || {};
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
          resultRefreshDisabledAfterClick: resultRefreshDisabledAfterDataRefresh,
          resultRefreshStateAfterClick: resultRefreshStateAfterDataRefresh,
          resultRefreshStatusAfterClick: resultRefreshStatusAfterDataRefresh,
          runtimeDataFetchCalled: !!dataProbeResult.fetchCalled,
          runtimeDataFetchUrl: dataProbeResult.url || '',
          runtimeDataFetchResponseStatus: dataProbeResult.responseStatus || 0,
          runtimeDataFetchResponseOk: dataProbeResult.responseOk,
          runtimeDataFetchContractVersion: dataProbeResult.contractVersion || '',
          runtimeDataFetchScreenCount: Number(dataProbeResult.screenCount || 0),
          runtimeDataFetchFirstRowKey: dataProbeResult.firstRowKey || '',
          runtimeDataFetchSelectedKey: dataProbeResult.selectedKey || '',
          runtimeDataListRowCountAfterRefresh: document.querySelectorAll(`.no-code-screen[data-screen-key="${expected.listScreenKey}"] tbody tr:not(.no-code-empty-row)`).length,
          runtimeDataHiddenKeyValueAfterRefresh: runtimeDataHiddenKeyAfterRefresh?.getAttribute('value') || '',
          runtimeDataDraftSummaryAfterRefresh: formScreen?.querySelector('[data-intent-draft-summary]')?.textContent?.trim() || '',
          runtimeDataDraftChecksAfterRefresh: Array.isArray(parsedRuntimeDataDraftAfterRefresh.draft_checks) ? parsedRuntimeDataDraftAfterRefresh.draft_checks : [],
          runtimeDataPagination,
          runtimeDataSearch,
          runtimeDataEmptySearch,
          runtimeDataErrorRefresh,
          runtimeDataFilter,
          runtimeDataSort,
          runtimeDataCombined,
          runtimeDataQueryReset,
          runtimeDataRowSelection,
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
      const runtimeDataRefreshApplied = !!(metrics.submitProbe && metrics.submitProbe.runtimeDataFetchCalled);
      if (metrics.listRowCount < 3 && !runtimeDataRefreshApplied) {
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
        if (expectsRuntimeDataRefresh) {
          const search = probe.runtimeDataSearch || {};
          if (search.skipped || search.inputCount < 1 || search.buttonCount < 1) {
            throw new Error(`real submit probe did not expose runtime data search controls: ${JSON.stringify(probe)}`);
          }
          if (!String(search.url || '').includes('q=') || search.requestSearchQuery !== String(config.expected.searchQuery || '')) {
            throw new Error(`real submit probe did not request searched runtime data: ${JSON.stringify(probe)}`);
          }
          if (search.retainedSearchValue !== String(config.expected.searchQuery || '')) {
            throw new Error(`real submit probe did not retain searched runtime data controls: ${JSON.stringify(probe)}`);
          }
          if (search.responseStatus !== 200 || search.responseOk !== true || search.renderedRowCount !== 1 || search.firstRowKey !== String(config.expected.selectedKeyValue || config.expected.keyValue)) {
            throw new Error(`real submit probe searched runtime data row mismatch: ${JSON.stringify(probe)}`);
          }
          const emptySearch = probe.runtimeDataEmptySearch || {};
          if (emptySearch.skipped || emptySearch.inputCount < 1 || emptySearch.buttonCount < 1) {
            throw new Error(`real submit probe did not exercise empty runtime data search controls: ${JSON.stringify(probe)}`);
          }
          if (!String(emptySearch.url || '').includes('q=') || emptySearch.requestSearchQuery !== '__no_runtime_data_match__') {
            throw new Error(`real submit probe did not request empty searched runtime data: ${JSON.stringify(probe)}`);
          }
          if (emptySearch.responseStatus !== 200 || emptySearch.responseOk !== true || emptySearch.renderedRowCount !== 0 || emptySearch.emptyRowCount < 1 || emptySearch.firstRowKey || emptySearch.selectedKey) {
            throw new Error(`real submit probe empty searched runtime data row mismatch: ${JSON.stringify(probe)}`);
          }
          if (
            !String(emptySearch.querySummaryText || '').includes('Active query:')
            || !String(emptySearch.querySummaryText || '').includes('__no_runtime_data_match__')
            || !String(emptySearch.querySummaryText || '').includes('Rows: 0')
            || !String(emptySearch.querySummaryAriaLabel || '').includes('Rows: 0')
            || Number(emptySearch.querySummaryTokenCount || 0) < 2
          ) {
            throw new Error(`real submit probe did not keep empty search query summary visible: ${JSON.stringify(probe)}`);
          }
          const errorRefresh = probe.runtimeDataErrorRefresh || {};
          if (errorRefresh.skipped) {
            throw new Error(`real submit probe did not exercise runtime data error refresh: ${JSON.stringify(probe)}`);
          }
          if (errorRefresh.responseStatus !== 503 || errorRefresh.responseOk !== false || errorRefresh.state !== 'error') {
            throw new Error(`real submit probe runtime data error refresh response mismatch: ${JSON.stringify(probe)}`);
          }
          if (
            !String(errorRefresh.statusText || '').includes('Fresh runtime data could not be loaded from the read-only runtime-data endpoint')
            || !String(errorRefresh.statusText || '').includes('forced_runtime_data_error')
            || !String(errorRefresh.statusText || '').includes('Current preview data was left unchanged.')
          ) {
            throw new Error(`real submit probe runtime data error wording mismatch: ${JSON.stringify(probe)}`);
          }
          if (Number(errorRefresh.beforeRowCount || 0) < 1 || errorRefresh.afterRowCount !== errorRefresh.beforeRowCount || errorRefresh.requestSearchQuery) {
            throw new Error(`real submit probe runtime data error refresh did not preserve current rows: ${JSON.stringify(probe)}`);
          }
          const filter = probe.runtimeDataFilter || {};
          const expectedFilterControlCount = config.expected.thirdFilterField ? 3 : (config.expected.secondFilterField ? 2 : 1);
          if (filter.skipped || filter.fieldControlCount < expectedFilterControlCount || filter.valueInputCount < expectedFilterControlCount || filter.buttonCount < 1) {
            throw new Error(`real submit probe did not expose runtime data filter controls: ${JSON.stringify(probe)}`);
          }
          if (
            !String(filter.url || '').includes('filter%5B')
            || !String(filter.url || '').includes('filter_op%5B')
            || filter.requestFilterField !== String(config.expected.filterField || '')
            || filter.requestFilterValue !== String(config.expected.filterValue || '')
            || filter.requestFilterOperator !== 'contains'
            || (config.expected.secondFilterField && filter.requestSecondFilterField !== String(config.expected.secondFilterField || ''))
            || (config.expected.secondFilterValue && filter.requestSecondFilterValue !== String(config.expected.secondFilterValue || ''))
            || (config.expected.secondFilterField && filter.requestSecondFilterOperator !== 'contains')
            || (config.expected.thirdFilterField && filter.requestThirdFilterField !== String(config.expected.thirdFilterField || ''))
            || (config.expected.thirdFilterValue && filter.requestThirdFilterValue !== String(config.expected.thirdFilterValue || ''))
            || (config.expected.thirdFilterField && filter.requestThirdFilterOperator !== 'contains')
          ) {
            throw new Error(`real submit probe did not request filtered runtime data: ${JSON.stringify(probe)}`);
          }
          if (
            filter.retainedFilterField !== String(config.expected.filterField || '')
            || filter.retainedFilterOperator !== 'contains'
            || filter.retainedFilterValue !== String(config.expected.filterValue || '')
            || (config.expected.secondFilterField && filter.retainedSecondFilterField !== String(config.expected.secondFilterField || ''))
            || (config.expected.secondFilterField && filter.retainedSecondFilterOperator !== 'contains')
            || (config.expected.secondFilterValue && filter.retainedSecondFilterValue !== String(config.expected.secondFilterValue || ''))
            || (config.expected.thirdFilterField && filter.retainedThirdFilterField !== String(config.expected.thirdFilterField || ''))
            || (config.expected.thirdFilterField && filter.retainedThirdFilterOperator !== 'contains')
            || (config.expected.thirdFilterValue && filter.retainedThirdFilterValue !== String(config.expected.thirdFilterValue || ''))
          ) {
            throw new Error(`real submit probe did not retain filtered runtime data controls: ${JSON.stringify(probe)}`);
          }
          if (filter.responseStatus !== 200 || filter.responseOk !== true || filter.renderedRowCount !== 1 || filter.firstRowKey !== String(config.expected.selectedKeyValue || config.expected.keyValue)) {
            throw new Error(`real submit probe filtered runtime data row mismatch: ${JSON.stringify(probe)}`);
          }
          if (config.expected.typedFilterField && config.expected.typedFilterOperator) {
            const stringOperators = String(filter.typeDrivenStringOperatorValues || '').split(',').filter(Boolean);
            const dateOperators = String(filter.typeDrivenDateOperatorValues || '').split(',').filter(Boolean);
            if (stringOperators.includes(config.expected.typedFilterOperator)) {
              throw new Error(`real submit probe exposed ordered operator for string filter field: ${JSON.stringify(probe)}`);
            }
            if (!dateOperators.includes(config.expected.typedFilterOperator) || filter.typeDrivenDateOperatorSelected !== String(config.expected.typedFilterOperator || '')) {
              throw new Error(`real submit probe did not expose ordered operator for typed date filter field: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenStringValuePlaceholder !== 'Text value') {
              throw new Error(`real submit probe did not keep text placeholder for string filter field: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenStringValueInputType !== 'text') {
              throw new Error(`real submit probe did not keep text input type for string filter field: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenNumberValueInputType !== 'number') {
              throw new Error(`real submit probe did not expose native number input type for typed numeric filter field: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenDateValuePlaceholder !== 'YYYY-MM-DD' || !String(filter.typeDrivenDateValueTitle || '').includes('YYYY-MM-DD')) {
              throw new Error(`real submit probe did not expose date value hint for typed date filter field: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenDateValueInputType !== 'date') {
              throw new Error(`real submit probe did not expose native date input type for typed date filter field: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenDatetimeValueInputType !== 'datetime-local' || filter.typeDrivenDatetimeValuePlaceholder !== 'YYYY-MM-DDTHH:MM:SS' || !String(filter.typeDrivenDatetimeValueTitle || '').includes('YYYY-MM-DDTHH:MM:SS')) {
              throw new Error(`real submit probe did not expose native datetime filter control metadata: ${JSON.stringify(probe)}`);
            }
            if (filter.typeDrivenTimeValueInputType !== 'time' || filter.typeDrivenTimeValuePlaceholder !== 'HH:MM:SS' || !String(filter.typeDrivenTimeValueTitle || '').includes('HH:MM:SS')) {
              throw new Error(`real submit probe did not expose native time filter control metadata: ${JSON.stringify(probe)}`);
            }
            if (filter.invalidFilterFetchUnchanged !== true || !String(filter.invalidFilterStatusText || '').includes('Runtime data filter was not fetched')) {
              throw new Error(`real submit probe did not stop invalid typed filter before fetch: ${JSON.stringify(probe)}`);
            }
            if (!String(filter.invalidFilterStatusText || '').includes('QuantityNeeded') || !String(filter.invalidFilterStatusText || '').includes('Expected format: Integer value')) {
              throw new Error(`real submit probe did not expose field-aware validation copy: ${JSON.stringify(probe)}`);
            }
          }
          const sort = probe.runtimeDataSort || {};
          if (sort.skipped || sort.fieldControlCount < 1 || sort.directionControlCount < 1 || sort.buttonCount < 1 || sort.headerButtonCount < 1) {
            throw new Error(`real submit probe did not expose runtime data sort controls: ${JSON.stringify(probe)}`);
          }
          if (!String(sort.url || '').includes('sort%5B') || sort.requestSortField !== String(config.expected.sortField || '') || sort.requestSortDirection !== String(config.expected.sortDirection || '')) {
            throw new Error(`real submit probe did not request sorted runtime data: ${JSON.stringify(probe)}`);
          }
          if (
            (config.expected.secondSortField && !String(sort.url || '').includes(`sort%5B${config.expected.secondSortField}%5D`))
            || (config.expected.secondSortField && sort.requestSecondSortField !== String(config.expected.secondSortField || ''))
            || (config.expected.secondSortDirection && sort.requestSecondSortDirection !== String(config.expected.secondSortDirection || ''))
            || (config.expected.thirdSortField && !String(sort.url || '').includes(`sort%5B${config.expected.thirdSortField}%5D`))
            || (config.expected.thirdSortField && sort.requestThirdSortField !== String(config.expected.thirdSortField || ''))
            || (config.expected.thirdSortDirection && sort.requestThirdSortDirection !== String(config.expected.thirdSortDirection || ''))
          ) {
            throw new Error(`real submit probe did not request additional sorted runtime data: ${JSON.stringify(probe)}`);
          }
          if (
            sort.retainedSortField !== String(config.expected.sortField || '')
            || sort.retainedSortDirection !== String(config.expected.sortDirection || '')
            || (config.expected.secondSortField && sort.retainedSecondSortField !== String(config.expected.secondSortField || ''))
            || (config.expected.secondSortDirection && sort.retainedSecondSortDirection !== String(config.expected.secondSortDirection || ''))
            || (config.expected.thirdSortField && sort.retainedThirdSortField !== String(config.expected.thirdSortField || ''))
            || (config.expected.thirdSortDirection && sort.retainedThirdSortDirection !== String(config.expected.thirdSortDirection || ''))
          ) {
            throw new Error(`real submit probe did not retain sorted runtime data controls: ${JSON.stringify(probe)}`);
          }
          if (sort.responseStatus !== 200 || sort.responseOk !== true || sort.renderedRowCount < 1 || sort.firstRowKey !== String(config.expected.sortFirstKeyValue || config.expected.selectedKeyValue || config.expected.keyValue)) {
            throw new Error(`real submit probe sorted runtime data row mismatch: ${JSON.stringify(probe)}`);
          }
          const expectedHeaderSortDirection = config.expected.sortDirection === 'asc' ? 'desc' : 'asc';
          if (
            !String(sort.headerUrl || '').includes(`sort%5B${config.expected.sortField}%5D`)
            || sort.headerRequestSortField !== String(config.expected.sortField || '')
            || sort.headerRequestSortDirection !== expectedHeaderSortDirection
            || sort.headerRequestSecondSortField
            || sort.headerRequestThirdSortField
          ) {
            throw new Error(`real submit probe did not request header-sorted runtime data: ${JSON.stringify(probe)}`);
          }
          if (
            sort.headerRetainedSortField !== String(config.expected.sortField || '')
            || sort.headerRetainedSortDirection !== expectedHeaderSortDirection
            || sort.headerRetainedSecondSortField
            || sort.headerRetainedThirdSortField
          ) {
            throw new Error(`real submit probe did not retain header-sorted runtime data controls: ${JSON.stringify(probe)}`);
          }
          const expectedHeaderAriaSort = expectedHeaderSortDirection === 'desc' ? 'descending' : 'ascending';
          if (
            sort.headerAriaSort !== expectedHeaderAriaSort
            || sort.headerSortState !== expectedHeaderAriaSort
            || sort.headerOtherAriaSort !== 'none'
            || sort.headerOtherSortState !== 'none'
          ) {
            throw new Error(`real submit probe did not expose header sorted state: ${JSON.stringify(probe)}`);
          }
          const combined = probe.runtimeDataCombined || {};
          if (combined.skipped) {
            throw new Error(`real submit probe did not exercise combined runtime data controls: ${JSON.stringify(probe)}`);
          }
          if (
            !String(combined.url || '').includes('q=')
            || !String(combined.url || '').includes('filter%5B')
            || !String(combined.url || '').includes('sort%5B')
            || !String(combined.url || '').includes('page=1')
            || !String(combined.url || '').includes('page_size=1')
            || combined.requestSearchQuery !== String(config.expected.searchQuery || '')
            || combined.requestFilterField !== String(config.expected.filterField || '')
            || combined.requestFilterValue !== String(config.expected.filterValue || '')
            || (config.expected.secondFilterField && combined.requestSecondFilterField !== String(config.expected.secondFilterField || ''))
            || (config.expected.secondFilterValue && combined.requestSecondFilterValue !== String(config.expected.secondFilterValue || ''))
            || (config.expected.thirdFilterField && combined.requestThirdFilterField !== String(config.expected.thirdFilterField || ''))
            || (config.expected.thirdFilterValue && combined.requestThirdFilterValue !== String(config.expected.thirdFilterValue || ''))
            || combined.requestSortField !== String(config.expected.sortField || '')
            || combined.requestSortDirection !== String(config.expected.sortDirection || '')
            || (config.expected.secondSortField && combined.requestSecondSortField !== String(config.expected.secondSortField || ''))
            || (config.expected.secondSortDirection && combined.requestSecondSortDirection !== String(config.expected.secondSortDirection || ''))
            || (config.expected.thirdSortField && combined.requestThirdSortField !== String(config.expected.thirdSortField || ''))
            || (config.expected.thirdSortDirection && combined.requestThirdSortDirection !== String(config.expected.thirdSortDirection || ''))
            || combined.requestPage !== '1'
            || combined.requestPageSize !== '1'
          ) {
            throw new Error(`real submit probe did not request combined runtime data query: ${JSON.stringify(probe)}`);
          }
          if (
            !String(combined.locationSearch || '').includes('q=')
            || !String(combined.locationSearch || '').includes('filter%5B')
            || (config.expected.secondFilterField && !String(combined.locationSearch || '').includes(`filter%5B${config.expected.secondFilterField}%5D`))
            || (config.expected.thirdFilterField && !String(combined.locationSearch || '').includes(`filter%5B${config.expected.thirdFilterField}%5D`))
            || !String(combined.locationSearch || '').includes('sort%5B')
            || (config.expected.secondSortField && !String(combined.locationSearch || '').includes(`sort%5B${config.expected.secondSortField}%5D`))
            || (config.expected.thirdSortField && !String(combined.locationSearch || '').includes(`sort%5B${config.expected.thirdSortField}%5D`))
            || !String(combined.locationSearch || '').includes('page=1')
            || !String(combined.locationSearch || '').includes('page_size=1')
          ) {
            throw new Error(`real submit probe did not mirror combined runtime data query into browser URL: ${JSON.stringify(probe)}`);
          }
          if (
            combined.retainedSearchValue !== String(config.expected.searchQuery || '')
            || combined.retainedFilterField !== String(config.expected.filterField || '')
            || combined.retainedFilterValue !== String(config.expected.filterValue || '')
            || (config.expected.secondFilterField && combined.retainedSecondFilterField !== String(config.expected.secondFilterField || ''))
            || (config.expected.secondFilterValue && combined.retainedSecondFilterValue !== String(config.expected.secondFilterValue || ''))
            || (config.expected.thirdFilterField && combined.retainedThirdFilterField !== String(config.expected.thirdFilterField || ''))
            || (config.expected.thirdFilterValue && combined.retainedThirdFilterValue !== String(config.expected.thirdFilterValue || ''))
            || combined.retainedSortField !== String(config.expected.sortField || '')
            || combined.retainedSortDirection !== String(config.expected.sortDirection || '')
            || (config.expected.secondSortField && combined.retainedSecondSortField !== String(config.expected.secondSortField || ''))
            || (config.expected.secondSortDirection && combined.retainedSecondSortDirection !== String(config.expected.secondSortDirection || ''))
            || (config.expected.thirdSortField && combined.retainedThirdSortField !== String(config.expected.thirdSortField || ''))
            || (config.expected.thirdSortDirection && combined.retainedThirdSortDirection !== String(config.expected.thirdSortDirection || ''))
            || combined.retainedPageSize !== '1'
          ) {
            throw new Error(`real submit probe did not retain combined runtime data controls: ${JSON.stringify(probe)}`);
          }
          if (combined.responseStatus !== 200 || combined.responseOk !== true || combined.renderedRowCount !== 1 || combined.firstRowKey !== String(config.expected.selectedKeyValue || config.expected.keyValue)) {
            throw new Error(`real submit probe combined runtime data row mismatch: ${JSON.stringify(probe)}`);
          }
          if (
            !String(combined.querySummaryText || '').includes('Active query:')
            || !String(combined.querySummaryText || '').includes(String(config.expected.searchQuery || ''))
            || !String(combined.querySummaryText || '').includes(String(config.expected.filterLabel || config.expected.filterField || ''))
            || !String(combined.querySummaryText || '').includes(String(config.expected.filterOperatorLabel || 'Contains'))
            || !String(combined.querySummaryText || '').includes(String(config.expected.sortLabel || config.expected.sortField || ''))
            || !String(combined.querySummaryText || '').includes(String(config.expected.sortDirectionLabel || config.expected.sortDirection || ''))
            || !String(combined.querySummaryText || '').includes('Page size: 1')
            || !String(combined.querySummaryText || '').includes('Rows:')
          ) {
            throw new Error(`real submit probe did not expose combined runtime data query summary: ${JSON.stringify(probe)}`);
          }
          if (!combined.querySummaryAriaLabel.includes(' | ') || Number(combined.querySummaryTokenCount || 0) < 5) {
            throw new Error(`real submit probe did not expose tokenized runtime data query summary: ${JSON.stringify(probe)}`);
          }
          const queryReset = probe.runtimeDataQueryReset || {};
          if (queryReset.skipped) {
            throw new Error(`real submit probe did not exercise runtime data query reset control: ${JSON.stringify(probe)}`);
          }
          if (
            String(queryReset.url || '').includes('?')
            || queryReset.locationSearch
            || queryReset.requestSearchQuery
            || queryReset.requestFilterField
            || queryReset.requestFilterValue
            || queryReset.requestSortField
            || queryReset.requestSortDirection
            || queryReset.requestPage
            || queryReset.requestPageSize
          ) {
            throw new Error(`real submit probe query reset did not request no-query runtime data: ${JSON.stringify(probe)}`);
          }
          if (
            queryReset.retainedSearchValue
            || queryReset.retainedFilterValue
            || queryReset.retainedSortField !== String(config.expected.defaultSortFieldAfterReset || '')
            || queryReset.retainedSortDirection !== String(config.expected.defaultSortDirectionAfterReset || '')
          ) {
            throw new Error(`real submit probe query reset did not clear runtime data controls: ${JSON.stringify(probe)}`);
          }
          if (queryReset.responseStatus !== 200 || queryReset.responseOk !== true || queryReset.renderedRowCount < 2) {
            throw new Error(`real submit probe query reset runtime row mismatch: ${JSON.stringify(probe)}`);
          }
          if (queryReset.querySummaryText !== 'No runtime data query applied.') {
            throw new Error(`real submit probe query reset did not clear runtime data query summary: ${JSON.stringify(probe)}`);
          }
          if (queryReset.querySummaryAriaLabel !== queryReset.querySummaryText || Number(queryReset.querySummaryTokenCount || 0) !== 0) {
            throw new Error(`real submit probe query reset did not clear tokenized runtime data query summary: ${JSON.stringify(probe)}`);
          }
          const pagination = probe.runtimeDataPagination || {};
          if (pagination.skipped || pagination.controlGroupCount < 1 || pagination.labelledGroupCount < 1 || pagination.pageSizeButtonCount < 1 || pagination.pageSizeInputCount < 1 || pagination.pageSubmitButtonCount < 1 || pagination.pageInputCount < 1 || pagination.queryResetButtonCount < 1 || pagination.pageButtonCount < 2) {
            throw new Error(`real submit probe did not expose runtime data pagination controls: ${JSON.stringify(probe)}`);
          }
          if (!String(pagination.entryUrl || '').includes('page=1') || !String(pagination.entryUrl || '').includes('page_size=1') || pagination.entryPage !== '1' || pagination.entryPageSize !== '1') {
            throw new Error(`real submit probe did not request first paginated runtime data page: ${JSON.stringify(probe)}`);
          }
          if (!String(pagination.nextUrl || '').includes('page=2') || !String(pagination.nextUrl || '').includes('page_size=1') || pagination.nextPage !== '2' || pagination.nextPageSize !== '1') {
            throw new Error(`real submit probe did not request next paginated runtime data page: ${JSON.stringify(probe)}`);
          }
          if (!String(pagination.directUrl || '').includes('page=1') || !String(pagination.directUrl || '').includes('page_size=1') || pagination.directPage !== '1' || pagination.directPageSize !== '1') {
            throw new Error(`real submit probe did not request direct paginated runtime data page: ${JSON.stringify(probe)}`);
          }
          if (pagination.renderedRowCount !== 1 || pagination.firstRowKey !== String(config.expected.selectedKeyValue || config.expected.keyValue)) {
            throw new Error(`real submit probe paginated runtime row mismatch: ${JSON.stringify(probe)}`);
          }
          if (Number((pagination.pagination || {}).page || 0) !== 2 || Number((pagination.pagination || {}).page_size || 0) !== 1) {
            throw new Error(`real submit probe pagination metadata mismatch: ${JSON.stringify(probe)}`);
          }
          const totalRows = Number((pagination.pagination || {}).total_rows || 0);
          if (totalRows < 1 || pagination.totalRowsAttribute !== String(totalRows) || !String(pagination.pageText || '').includes(`${totalRows} total rows`)) {
            throw new Error(`real submit probe pagination total row label mismatch: ${JSON.stringify(probe)}`);
          }
        }
        if (expectsRuntimeDataRefresh && config.expected.selectedKeyValue && String(config.expected.selectedKeyValue) !== String(config.expected.keyValue)) {
          const rowSelection = probe.runtimeDataRowSelection || {};
          if (rowSelection.skipped || rowSelection.buttonCount < 2) {
            throw new Error(`real submit probe did not expose selectable runtime data rows: ${JSON.stringify(probe)}`);
          }
          if (!rowSelection.url.includes(`selected_key=${encodeURIComponent(String(config.expected.selectedKeyValue))}`) || rowSelection.requestSelectedKey !== String(config.expected.selectedKeyValue)) {
            throw new Error(`real submit probe did not request selected runtime data row: ${JSON.stringify(probe)}`);
          }
          if (rowSelection.responseStatus !== 200 || rowSelection.responseOk !== true || rowSelection.responseSelectedKey !== String(config.expected.selectedKeyValue)) {
            throw new Error(`real submit probe selected runtime data response mismatch: ${JSON.stringify(probe)}`);
          }
          if (rowSelection.hiddenKeyValue !== String(config.expected.selectedKeyValue)) {
            throw new Error(`real submit probe selected runtime data did not update hidden form key: ${JSON.stringify(probe)}`);
          }
          if (Array.isArray(rowSelection.draftChecks) && rowSelection.draftChecks.includes(`key.missing:${config.expected.keyField}`)) {
            throw new Error(`real submit probe selected runtime data lost the form key: ${JSON.stringify(probe)}`);
          }
          if (!String(rowSelection.statusText || '').includes('Fresh runtime data loaded from')) {
            throw new Error(`real submit probe selected runtime data did not update refresh status: ${JSON.stringify(probe)}`);
          }
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
    if ((!config.runtimeManagedOutboxAuthority && !metrics.draftSummaryAfterEdit.includes('action.disabled')) || !metrics.draftSummaryAfterEdit.includes(`key.missing:${config.expected.keyField}`)) {
      throw new Error(`intent draft summary did not include expected checks: ${metrics.draftSummaryAfterEdit}`);
    }
    if (!config.runtimeManagedOutboxAuthority && (!metrics.draftSummaryAfterEdit.includes('policy:') || !metrics.draftSummaryAfterEdit.includes('principal.missing'))) {
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

    const runtimeDataInitialUrlReplay = await probeRuntimeDataInitialUrlReplay(page, targetUrl, config);
    const runtimeDataBrowserHistoryReplay = await probeRuntimeDataBrowserHistoryReplay(page, targetUrl, config);

    await page.screenshot({ path: screenshotPath, fullPage: true });
    await page.setViewportSize({ width: 390, height: 900 });
    await page.waitForTimeout(100);
    const mobileRuntimeDataControls = await page.evaluate(() => {
      const visible = (element) => !!(element && element.getClientRects().length > 0);
      const controls = Array.from(document.querySelectorAll('[data-runtime-data-controls]')).filter(visible);
      const rowGroups = Array.from(document.querySelectorAll('.no-code-runtime-data-row-group')).filter(visible);
      const tokens = Array.from(document.querySelectorAll('.no-code-runtime-data-query-token')).filter(visible);
      const overflows = controls.filter((element) => element.scrollWidth > element.clientWidth + 1).length;
      const narrowRowGroups = rowGroups.filter((element) => {
        const parent = element.closest('[data-runtime-data-controls]');
        return parent && element.getBoundingClientRect().width < parent.getBoundingClientRect().width * 0.85;
      }).length;
      const tokenOverflows = tokens.filter((element) => element.scrollWidth > element.clientWidth + 1).length;
      return {
        skipped: controls.length === 0,
        viewportWidth: window.innerWidth,
        controlCount: controls.length,
        rowGroupCount: rowGroups.length,
        overflowCount: overflows,
        narrowRowGroupCount: narrowRowGroups,
        tokenOverflowCount: tokenOverflows,
      };
    });
    if (!mobileRuntimeDataControls.skipped && (mobileRuntimeDataControls.controlCount < 1 || mobileRuntimeDataControls.rowGroupCount < 1 || mobileRuntimeDataControls.overflowCount !== 0 || mobileRuntimeDataControls.narrowRowGroupCount !== 0 || mobileRuntimeDataControls.tokenOverflowCount !== 0)) {
      throw new Error(`mobile runtime data controls overflow or density mismatch: ${JSON.stringify(mobileRuntimeDataControls)}`);
    }
    await page.screenshot({ path: mobileScreenshotPath, fullPage: true });

    return {
      ok: true,
      html: config.htmlPath,
      url: config.url,
      screenshot: screenshotPath,
      mobileScreenshot: mobileScreenshotPath,
      metrics,
      mobileRuntimeDataControls,
      runtimeDataInitialUrlReplay,
      runtimeDataBrowserHistoryReplay,
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
