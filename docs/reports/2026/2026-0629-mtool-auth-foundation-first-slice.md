# 2026-06-29 Mtool auth foundation first slice

Status: `COMPLETED`

## Summary

Mtool auth foundation first slice is complete.

This slice is the foundation that the App-local DB / sync / no-code roadmap needs before Gate 0 feasibility studies. It does not revive the old user-specific `ProjectUser` read/write bit model. Instead, it inventories the legacy permission units and collapses them into the current role-based permission key model.

## Implemented

- Added `mtool/app/auth_foundation.php`.
- Inventoried the 16 legacy `ProjectUser` permission fields:
  - `dbtoolRead` / `dbtoolWrite`
  - `htmlRead` / `htmlWrite`
  - `testtoolRead` / `testtoolWrite`
  - `spectoolRead` / `spectoolWrite`
  - `ReqRead` / `ReqWrite`
  - `ChatRead` / `ChatWrite`
  - `MinutesRead` / `MinutesWrite`
  - `UploadRead` / `UploadWrite`
- Collapsed legacy units into role-based permission keys:
  - read-like units -> `project.read`
  - write-like metadata units -> `project.edit`
  - upload read -> `source_output.download`
  - upload write -> `source_output.publish`
- Defined a normalized principal shape with:
  - stable identity fields
  - normalized auth source / site
  - known site roles only
  - normalized project keys and known project roles only
- Added minimal authorization evaluator:
  - requires at least one permission key
  - requires all permission keys to pass
  - fails closed on unknown permission keys
  - fails closed on missing project scope for project permissions
  - keeps site admin as a break-glass project permission source, matching the existing `project_permission.php` behavior

## Verification

- `AuthFoundationContractTest`
  - legacy permission unit inventory is stable
  - legacy fields are inventory-only and are not permission requirement keys
  - principal normalization keeps only known roles
  - evaluator requires all permission keys
  - evaluator fails closed on unknown or unscoped input
  - site admin break-glass behavior is covered
- Existing related contracts still pass:
  - `ProjectIdentityMembershipPermissionTest`
  - `ProjectRouteAuthorizationContractTest`

## Next

Gate 0 feasibility studies are now the active next work. Start with either:

- Shared Contract Manifest Spike
- App Local SQLite Schema Spike
