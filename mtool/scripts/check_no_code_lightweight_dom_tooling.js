#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_no_code_lightweight_dom_tooling.js [options]

Options:
  --fixture=PATH  sample32 no-code UI contract fixture
                  (default: sample/tutorials/sample32-no-code-ui-test-lab/fixtures/no-code-ui-contract-fixtures.json)
  --output=PATH   output JSON path
                  (default: work/tmp/no-code-lightweight-dom-tooling-check.json)
  --help          show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function parseArgs(argv) {
  const root = repoRoot();
  const config = {
    fixturePath: path.join(root, 'sample', 'tutorials', 'sample32-no-code-ui-test-lab', 'fixtures', 'no-code-ui-contract-fixtures.json'),
    outputPath: path.join(root, 'work', 'tmp', 'no-code-lightweight-dom-tooling-check.json'),
    help: false,
  };

  for (const argument of argv.slice(2)) {
    if (argument === '--help' || argument === '-h') {
      config.help = true;
      continue;
    }
    if (!argument.startsWith('--') || !argument.includes('=')) {
      throw new Error(`Unknown argument: ${argument}`);
    }

    const body = argument.slice(2);
    const separatorIndex = body.indexOf('=');
    const name = body.slice(0, separatorIndex).trim();
    const value = body.slice(separatorIndex + 1).trim();
    if (name === 'fixture') {
      config.fixturePath = path.resolve(value);
    } else if (name === 'output') {
      config.outputPath = path.resolve(value);
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  return config;
}

function readJson(filePath) {
  if (!fs.existsSync(filePath)) {
    throw new Error(`required JSON file was not found: ${filePath}`);
  }

  return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

function optionalResolve(packageName, root) {
  try {
    return {
      available: true,
      resolvedPath: require.resolve(packageName, { paths: [root] }),
    };
  } catch (error) {
    return {
      available: false,
      resolvedPath: '',
    };
  }
}

function existingManifestFiles(root) {
  return ['package.json', 'package-lock.json', 'pnpm-lock.yaml', 'yarn.lock']
    .filter((fileName) => fs.existsSync(path.join(root, fileName)));
}

function candidateSummary(packageName, root) {
  const resolution = optionalResolve(packageName, root);

  if (packageName === 'linkedom') {
    return {
      package: packageName,
      available: resolution.available,
      resolved_path: resolution.resolvedPath,
      fit: 'first choice for a narrow generated-runtime event probe when only DOM parsing, querySelector, and basic events are required',
      risk: 'does not aim to emulate every browser API, so runtime helpers using clipboard, layout, or fetch still need stubs or browser smoke',
    };
  }

  return {
    package: packageName,
    available: resolution.available,
    resolved_path: resolution.resolvedPath,
    fit: 'fallback when a probe needs broader Web API coverage than linkedom can provide',
    risk: 'larger dependency surface for an inner-loop test, so it should be justified by a concrete interaction gap',
  };
}

function buildReport(config) {
  const root = repoRoot();
  const fixture = readJson(config.fixturePath);
  const manifests = existingManifestFiles(root);
  const candidates = [
    candidateSummary('linkedom', root),
    candidateSummary('happy-dom', root),
  ];
  const hasInstalledCandidate = candidates.some((candidate) => candidate.available);
  const hasPackageManifest = manifests.length > 0;

  return {
    check_version: 'no-code-lightweight-dom-tooling-check-v0',
    fixture: {
      path: path.relative(root, config.fixturePath),
      fixture_version: String(fixture.fixture_version || ''),
      contract_key: String(fixture.contract_key || ''),
      disabled_action_count: Array.isArray(fixture.disabled_managed_actions)
        ? fixture.disabled_managed_actions.length
        : 0,
    },
    repository_node_dependency_state: {
      manifest_files: manifests,
      has_package_manifest: hasPackageManifest,
    },
    candidates,
    concrete_interaction_gaps: [
      'generated action button click updates data-action-state and action feedback',
      'generated form input/change events refresh the local intent draft',
      'copy-draft and runtime execute helpers need browser API stubs if tested outside Chrome',
    ],
    recommendation: hasInstalledCandidate
      ? 'Use the installed lightweight DOM candidate only for a narrow generated-runtime event probe.'
      : 'Do not add linkedom or happy-dom yet; keep PHP DOM fixture contracts as the fast loop and add a temporary dependency-backed probe only when a concrete interaction gap is promoted.',
    selected_tool: hasInstalledCandidate
      ? (candidates.find((candidate) => candidate.available) || candidates[0]).package
      : 'defer-external-dom-runtime',
    should_add_root_package_manifest: false,
  };
}

function main() {
  const config = parseArgs(process.argv);
  if (config.help) {
    process.stdout.write(`${usage()}\n`);
    return;
  }

  const report = buildReport(config);
  fs.mkdirSync(path.dirname(config.outputPath), { recursive: true });
  fs.writeFileSync(config.outputPath, `${JSON.stringify(report, null, 2)}\n`);
  process.stdout.write(`wrote ${config.outputPath}\n`);
  process.stdout.write(`selected_tool=${report.selected_tool}\n`);
  process.stdout.write(`recommendation=${report.recommendation}\n`);
}

try {
  main();
} catch (error) {
  process.stderr.write(`${error && error.message ? error.message : String(error)}\n`);
  process.exit(1);
}
