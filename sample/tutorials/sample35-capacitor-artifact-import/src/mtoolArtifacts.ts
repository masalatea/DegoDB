import bridgeContract from './mtool-artifacts/bridge-contract.sample.json';
import reactWrapperAppHandoff from './mtool-artifacts/react-wrapper-app-handoff.sample.json';
import mobileWrapperBundleManifest from './mtool-artifacts/mobile-wrapper-bundle-manifest.sample.json';
import externalOutput from './mtool-artifacts/external-output.sample.json';

export const mtoolArtifacts = {
  bridgeContract,
  reactWrapperAppHandoff,
  mobileWrapperBundleManifest,
  externalOutput
};

export type MtoolArtifacts = typeof mtoolArtifacts;
