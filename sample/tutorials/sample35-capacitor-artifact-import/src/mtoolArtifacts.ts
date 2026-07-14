import bridgeContract from './mtool-artifacts/bridge-contract.sample.json';
import reactWrapperAppHandoff from './mtool-artifacts/react-wrapper-app-handoff.sample.json';
import mobileWrapperBundleManifest from './mtool-artifacts/mobile-wrapper-bundle-manifest.sample.json';

export const mtoolArtifacts = {
  bridgeContract,
  reactWrapperAppHandoff,
  mobileWrapperBundleManifest
};

export type MtoolArtifacts = typeof mtoolArtifacts;

