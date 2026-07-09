#!/usr/bin/env node
'use strict';

const childProcess = require('child_process');
const fs = require('fs');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_no_code_react_bridge_build_smoke.js [options]

Options:
  --bridge=PATH       generated NO-CODE-REACT-BRIDGE directory
                      (default: work/source-outputs/SAMPLE28/NO-CODE-REACT-BRIDGE)
  --work-dir=PATH     temporary build directory
                      (default: work/tmp/no-code-react-bridge-build-smoke)
  --cache=PATH        npm cache directory
                      (default: work/tmp/npm-cache-react-bridge)
  --keep-work-dir     keep temporary build directory
  --help              show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function parseArgs(argv) {
  const root = repoRoot();
  const config = {
    bridgeDir: path.join(root, 'work', 'source-outputs', 'SAMPLE28', 'NO-CODE-REACT-BRIDGE'),
    workDir: path.join(root, 'work', 'tmp', 'no-code-react-bridge-build-smoke'),
    cacheDir: path.join(root, 'work', 'tmp', 'npm-cache-react-bridge'),
    keepWorkDir: false,
    help: false,
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--help' || argument === '-h') {
      config.help = true;
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
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  return config;
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

function readBridgeSummary(workDir) {
  const contract = JSON.parse(fs.readFileSync(path.join(workDir, 'bridge-contract.json'), 'utf8'));
  return {
    contractSchemaVersion: String(contract.contract_schema_version || ''),
    bridgeVersion: String(contract.bridge_version || ''),
    framework: String(contract.framework?.name || ''),
    language: String(contract.framework?.language || ''),
    actionIntentVersion: String(contract.action_intent_version || ''),
    invariantRuntimePreviewVersion: String(contract.contract_invariants?.runtime_preview_version || ''),
    invariantActionIntentVersion: String(contract.contract_invariants?.action_intent_version || ''),
    requiredFiles: Array.isArray(contract.contract_invariants?.required_files)
      ? contract.contract_invariants.required_files
      : [],
    screenCount: Array.isArray(contract.runtime_preview?.screens)
      ? contract.runtime_preview.screens.length
      : 0,
    customOperationHandoffCount: Array.isArray(contract.custom_operation_handoffs)
      ? contract.custom_operation_handoffs.length
      : 0,
    customOperationAdapterHandoffs: Array.isArray(contract.custom_operation_handoffs)
      ? contract.custom_operation_handoffs.map((handoff) => String(handoff.adapter_handoff || '')).filter(Boolean)
      : [],
  };
}

function main() {
  const config = parseArgs(process.argv);
  if (config.help) {
    console.log(usage());
    return;
  }

  assertRequiredFiles(config.bridgeDir);
  copyBridgeToWorkDir(config.bridgeDir, config.workDir);
  fs.mkdirSync(config.cacheDir, { recursive: true });

  const env = {
    ...process.env,
    npm_config_cache: config.cacheDir,
  };
  run('npm', ['install'], config.workDir, env);
  run('npm', ['run', 'build'], config.workDir, env);

  const summary = readBridgeSummary(config.workDir);
  if (summary.contractSchemaVersion !== 'no-code-react-bridge-contract-v0') {
    throw new Error(`unexpected contract schema version: ${summary.contractSchemaVersion}`);
  }
  if (summary.bridgeVersion !== 'no-code-react-bridge-v0') {
    throw new Error(`unexpected bridge version: ${summary.bridgeVersion}`);
  }
  if (summary.framework !== 'react' || summary.language !== 'typescript') {
    throw new Error(`unexpected framework target: ${summary.framework}/${summary.language}`);
  }
  if (summary.actionIntentVersion !== 'no-code-runtime-action-intent-v0') {
    throw new Error(`unexpected action intent version: ${summary.actionIntentVersion}`);
  }
  if (summary.invariantRuntimePreviewVersion !== 'no-code-runtime-v0') {
    throw new Error(`unexpected runtime preview invariant: ${summary.invariantRuntimePreviewVersion}`);
  }
  if (summary.invariantActionIntentVersion !== summary.actionIntentVersion) {
    throw new Error('contract invariant action intent version does not match top-level action intent version.');
  }
  for (const requiredFile of ['bridge-contract.json', 'src/mtoolNoCodeBridge.ts', 'src/MtoolNoCodeRuntime.tsx', 'CONSUMER-NOTES.md']) {
    if (!summary.requiredFiles.includes(requiredFile)) {
      throw new Error(`contract invariant required file is missing: ${requiredFile}`);
    }
  }
  if (summary.screenCount < 1) {
    throw new Error('bridge contract has no runtime screens.');
  }
  if (!fs.existsSync(path.join(config.workDir, 'dist', 'index.html'))) {
    throw new Error('React bridge build did not emit dist/index.html.');
  }

  if (!config.keepWorkDir) {
    fs.rmSync(config.workDir, { recursive: true, force: true });
  }

  console.log(JSON.stringify({
    ok: true,
    bridge_dir: config.bridgeDir,
    work_dir: config.keepWorkDir ? config.workDir : '',
    summary,
  }, null, 2));
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
