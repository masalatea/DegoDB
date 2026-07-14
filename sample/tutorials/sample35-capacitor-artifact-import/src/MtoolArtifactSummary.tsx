import { mtoolArtifacts } from './mtoolArtifacts';

export function MtoolArtifactSummary() {
  const handoff = mtoolArtifacts.reactWrapperAppHandoff;
  const manifest = mtoolArtifacts.mobileWrapperBundleManifest;

  return (
    <section className="card" data-mtool-operation="artifact-import-index-review">
      <h2>Artifact import / index review</h2>
      <p>
        Project: <strong>{handoff.project.project_key}</strong> — {handoff.project.title}
      </p>
      <p>
        Imported schemas: {mtoolArtifacts.bridgeContract.contract_schema_version},{' '}
        {handoff.schema_version}, {manifest.schema_version}
      </p>
      <ol>
        {manifest.artifact_order.map((artifactKey) => (
          <li key={artifactKey}>{artifactKey}</li>
        ))}
      </ol>
      <p className="boundary">{manifest.ownership_boundary.external_owner_owns}</p>
    </section>
  );
}

