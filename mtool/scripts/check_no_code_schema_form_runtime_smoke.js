#!/usr/bin/env node
'use strict';

const childProcess = require('child_process');
const fs = require('fs');
const path = require('path');

function usage() {
  return `Usage:
  node mtool/scripts/check_no_code_schema_form_runtime_smoke.js [options]

Options:
  --probe=PATH       generated NO-CODE-JSON-FORMS-PROBE directory
                     (default: work/source-outputs/SAMPLE28/NO-CODE-JSON-FORMS-PROBE)
  --work-dir=PATH    temporary smoke directory
                     (default: work/tmp/no-code-schema-form-runtime-smoke)
  --cache=PATH       npm cache directory
                     (default: work/tmp/npm-cache-schema-form)
  --profile=sample28 expected probe shape (default: sample28)
  --keep-work-dir    keep temporary smoke directory
  --help             show this help`;
}

function repoRoot() {
  return path.resolve(__dirname, '..', '..');
}

function expectedProfile(name) {
  if (name === 'sample28') {
    return {
      profile: name,
      contractVersion: 'no-code-json-forms-probe-contract-v0',
      probeVersion: 'no-code-json-forms-probe-v0',
      formScreenKey: 'no_code_ticket_form',
      actionKey: 'update_no_code_ticket',
      requiredField: 'title',
      editableField: 'body',
    };
  }

  throw new Error(`Unknown profile: ${name}`);
}

function parseArgs(argv) {
  const root = repoRoot();
  const config = {
    probeDir: path.join(root, 'work', 'source-outputs', 'SAMPLE28', 'NO-CODE-JSON-FORMS-PROBE'),
    workDir: path.join(root, 'work', 'tmp', 'no-code-schema-form-runtime-smoke'),
    cacheDir: path.join(root, 'work', 'tmp', 'npm-cache-schema-form'),
    expected: expectedProfile('sample28'),
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
    if (name === 'probe') {
      config.probeDir = path.resolve(value);
    } else if (name === 'work-dir') {
      config.workDir = path.resolve(value);
    } else if (name === 'cache') {
      config.cacheDir = path.resolve(value);
    } else if (name === 'profile') {
      config.expected = expectedProfile(value);
    } else {
      throw new Error(`Unknown option: --${name}`);
    }
  }

  return config;
}

function assertRequiredFiles(probeDir) {
  for (const relativePath of ['schema-form-contract.json', 'json-schema.json', 'ui-schema.json', 'README.md']) {
    const absolutePath = path.join(probeDir, relativePath);
    if (!fs.existsSync(absolutePath)) {
      throw new Error(`required schema-form probe file was not found: ${absolutePath}`);
    }
  }
}

function readJson(filePath) {
  return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

function writeJson(filePath, value) {
  fs.writeFileSync(filePath, `${JSON.stringify(value, null, 2)}\n`);
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

function assertProbeShape(probeDir, expected) {
  const contract = readJson(path.join(probeDir, 'schema-form-contract.json'));
  const schema = readJson(path.join(probeDir, 'json-schema.json'));
  const uiSchema = readJson(path.join(probeDir, 'ui-schema.json'));

  if (contract.schema_form_contract_version !== expected.contractVersion) {
    throw new Error(`unexpected contract version: ${contract.schema_form_contract_version || ''}`);
  }
  if (contract.probe_version !== expected.probeVersion) {
    throw new Error(`unexpected probe version: ${contract.probe_version || ''}`);
  }
  if (contract.form_screen_key !== expected.formScreenKey) {
    throw new Error(`unexpected form screen key: ${contract.form_screen_key || ''}`);
  }
  if (contract.action_key !== expected.actionKey) {
    throw new Error(`unexpected action key: ${contract.action_key || ''}`);
  }
  if (!Array.isArray(contract.schema_form_targets) || !contract.schema_form_targets.includes('rjsf')) {
    throw new Error('schema-form contract does not advertise rjsf as a comparison target.');
  }
  if (!Array.isArray(schema.required) || !schema.required.includes(expected.requiredField)) {
    throw new Error(`json schema required fields do not include ${expected.requiredField}.`);
  }
  const requiredProperty = schema.properties?.[expected.requiredField] || {};
  if (requiredProperty.minLength !== 1 || requiredProperty.pattern !== '\\S' || requiredProperty['x-mtool-blank-is-missing'] !== true) {
    throw new Error(`json schema required field does not expose blank required parity metadata for ${expected.requiredField}.`);
  }
  if (!String(contract.validation_parity?.required_blank_string_policy || '').includes('pattern \\S')) {
    throw new Error('schema-form contract does not document blank required parity policy.');
  }
  const editableProperty = schema.properties?.[expected.editableField] || {};
  if (editableProperty['x-mtool-field-key'] !== expected.editableField) {
    throw new Error(`json schema does not carry x-mtool-field-key for ${expected.editableField}.`);
  }
  if (editableProperty['x-mtool-action-field-role'] !== 'input') {
    throw new Error(`json schema does not carry action input role for ${expected.editableField}.`);
  }
  if (editableProperty['x-mtool-client-write'] !== true) {
    throw new Error(`json schema does not allow client write for ${expected.editableField}.`);
  }

  const scopes = Array.isArray(uiSchema.elements)
    ? uiSchema.elements.map((element) => String(element.scope || ''))
    : [];
  for (const fieldKey of [expected.requiredField, expected.editableField]) {
    if (!scopes.includes(`#/properties/${fieldKey}`)) {
      throw new Error(`ui schema does not include scope for ${fieldKey}.`);
    }
  }

  return {
    contract,
    schema,
    uiSchema,
    fieldCount: Object.keys(schema.properties || {}).length,
  };
}

function prepareWorkDir(config, probe) {
  fs.rmSync(config.workDir, { recursive: true, force: true });
  fs.mkdirSync(config.workDir, { recursive: true });
  fs.mkdirSync(config.cacheDir, { recursive: true });
  writeJson(path.join(config.workDir, 'package.json'), {
    name: 'mtool-schema-form-runtime-smoke',
    private: true,
    version: '0.0.0',
    type: 'module',
    scripts: {
      smoke: 'node smoke.mjs',
    },
    dependencies: {
      '@rjsf/core': '^5.24.0',
      '@rjsf/validator-ajv8': '^5.24.0',
      react: '^19.0.0',
      'react-dom': '^19.0.0',
    },
  });
  writeJson(path.join(config.workDir, 'json-schema.json'), probe.schema);
  writeJson(path.join(config.workDir, 'ui-schema.json'), probe.uiSchema);
  fs.writeFileSync(path.join(config.workDir, 'smoke.mjs'), smokeModuleText(config.expected), 'utf8');
}

function smokeModuleText(expected) {
  return `import React from 'react';
import { renderToString } from 'react-dom/server';
import RjsfCore from '@rjsf/core';
import Ajv8Validator from '@rjsf/validator-ajv8';
import fs from 'fs';

const schema = JSON.parse(fs.readFileSync(new URL('./json-schema.json', import.meta.url), 'utf8'));
const uiSchema = JSON.parse(fs.readFileSync(new URL('./ui-schema.json', import.meta.url), 'utf8'));
const Form = RjsfCore.default || RjsfCore;
const validator = Ajv8Validator.default || Ajv8Validator;
const formData = {
  ${JSON.stringify(expected.requiredField)}: 'Runtime smoke title',
  ${JSON.stringify(expected.editableField)}: 'Runtime smoke body'
};

const html = renderToString(React.createElement(Form, {
  schema,
  validator,
  formData,
  liveValidate: true
}));

if (!html.includes('Runtime smoke title')) {
  throw new Error('rjsf render output did not include required field value.');
}
if (!html.includes('Runtime smoke body')) {
  throw new Error('rjsf render output did not include editable field value.');
}
if (!Array.isArray(uiSchema.elements) || uiSchema.elements.length < 1) {
  throw new Error('ui schema did not expose render order metadata.');
}
if (typeof validator.rawValidation !== 'function') {
  throw new Error('rjsf validator does not expose rawValidation.');
}
const blankValidation = validator.rawValidation(schema, {
  ${JSON.stringify(expected.requiredField)}: '   ',
  ${JSON.stringify(expected.editableField)}: 'Runtime smoke body'
});
const blankErrors = Array.isArray(blankValidation.errors) ? blankValidation.errors : [];
if (!blankErrors.some((error) => String(error.property || '').includes(${JSON.stringify(expected.requiredField)})
  || String(error.instancePath || '').includes(${JSON.stringify(expected.requiredField)}))) {
  throw new Error('rjsf validation did not reject blank required field.');
}

console.log(JSON.stringify({
  ok: true,
  renderer: 'rjsf',
  rendered_length: html.length,
  ui_element_count: uiSchema.elements.length,
  schema_property_count: Object.keys(schema.properties || {}).length,
  blank_required_error_count: blankErrors.length
}, null, 2));
`;
}

function main() {
  const config = parseArgs(process.argv);
  if (config.help) {
    console.log(usage());
    return;
  }

  assertRequiredFiles(config.probeDir);
  const probe = assertProbeShape(config.probeDir, config.expected);
  prepareWorkDir(config, probe);

  const env = {
    ...process.env,
    npm_config_cache: config.cacheDir,
  };
  run('npm', ['install'], config.workDir, env);
  run('npm', ['run', 'smoke'], config.workDir, env);

  const summary = {
    ok: true,
    profile: config.expected.profile,
    probe_dir: config.probeDir,
    work_dir: config.keepWorkDir ? config.workDir : '',
    schema_property_count: probe.fieldCount,
    form_screen_key: probe.contract.form_screen_key,
    action_key: probe.contract.action_key,
    blank_required_field: config.expected.requiredField,
  };

  if (!config.keepWorkDir) {
    fs.rmSync(config.workDir, { recursive: true, force: true });
  }

  console.log(JSON.stringify(summary, null, 2));
}

try {
  main();
} catch (error) {
  console.error(error instanceof Error ? error.message : String(error));
  process.exit(1);
}
