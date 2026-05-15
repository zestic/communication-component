# Communication Component v2 Stabilization Guide

## Goal
This document captures the changes needed in zestic/communication-component to make v2 stable for applications that:

- run on MySQL,
- and migrated from pre-v2 configuration models.

It is written as a handoff guide for the Zestic team.

## Executive Summary
Two categories need attention:

1. Configuration enforcement: v2 configuration expectations must be explicit and strict.
2. Database provider compatibility: the definition repository naming and SQL assumptions are PostgreSQL-oriented and need a first-class MySQL path.

Without these fixes, apps can fail with errors such as:

- missing/invalid communication context configuration,
- unable to resolve repository services,
- or DB-specific runtime failures.

## What Needs To Change

### 1) Enforce Communication Factory v2 Config Contract

#### Problem
Communication class creation still allows ambiguity between communication.context and communication.channelContexts.

This ambiguity increases migration risk and behavior drift.

#### Required change
Update Communication\Factory\Legacy\CommunicationFactory to enforce v2-native config only.

#### Expected behavior
- Accept `communication.channelContexts` as the primary v2 source.
- Reject `communication.context` with a clear v2 migration exception.
- Build `CommunicationContext` from channel context factories/classes consistently.
- Emit precise exception messages when config is invalid.

#### Why
This gives v2 a single supported contract and reduces long-term maintenance burden.

---

### 2) Introduce a MySQL-First Definition Repository (or portable DBAL implementation)

#### Problem
The current repository surface is PostgreSQL-branded (`PostgresCommunicationDefinitionRepository`) and may include PG-specific assumptions.

Even when functionality is DBAL-based, the naming and service contract strongly imply PG-only support and produce confusion for MySQL consumers.

#### Required change (preferred)
Create a dedicated MySQL repository implementation and expose a neutral alias contract.

Recommended classes:

- `Communication\Definition\Repository\MySqlCommunicationDefinitionRepository`
- `Communication\Definition\Repository\PostgresCommunicationDefinitionRepository` (existing)
- `Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface`

Then wire interface resolution by driver in config/factory.

#### Alternate change
If practical, replace DB-specific repositories with one portable DBAL repository:

- Communication\Definition\Repository\DbalCommunicationDefinitionRepository

and keep DB-specific classes only where needed for readability and explicitness.

#### Why
- MySQL support becomes explicit and testable.
- Naming is no longer misleading.
- Consumers can select repository implementation by DSN/driver cleanly.

---

### 3) Make Repository Selection Configurable by Driver

#### Problem
Current aliasing can hard-pin the interface to one implementation in the package config.

#### Required change
Add a repository selector factory (or conditional config provider behavior):

- Read configured DB driver (`mysql`, `pgsql`, etc.) from merged config.
- Resolve `CommunicationDefinitionRepositoryInterface` to matching implementation.
- Throw clear exception for unsupported drivers.

#### Why
It removes consumer-side override burden and avoids hidden provider mismatch.

---

### 4) Define a Strict v2 Migration Surface

#### Problem
v2 introduced structural changes, and some applications still rely on v1-era keys.

#### Required change
Publish and enforce a strict migration path:

- Legacy\CommunicationFactory supports only v2 keys.
- ConfigProvider documents key changes with explicit examples.
- Invalid v1 keys fail fast with actionable replacement guidance and exact key replacements.

#### Why
This keeps v2 behavior predictable and avoids dual-contract support cost.

## Proposed Implementation Guide

## Phase A: Repository Refactor

1. Add MySQL repository class (or DBAL-neutral repository).
2. Keep interface unchanged.
3. Add tests for CRUD/find/save behavior against MySQL and PostgreSQL.
4. Add driver-based resolver factory.
5. Update package config so interface resolves via resolver, not hard alias.

### Suggested service wiring pattern

- Factory: `CommunicationDefinitionRepositoryFactory`
- Inputs: merged `config`, `Doctrine\DBAL\Connection`
- Output: `CommunicationDefinitionRepositoryInterface`

Pseudo-flow:

1. Detect driver from DBAL connection params (`getDatabasePlatform()` or connection params).
2. Switch by normalized driver name:
   - `mysql` -> MySQL repository
   - `postgresql`/`pgsql` -> PostgreSQL repository
3. Throw `RuntimeException` for unsupported drivers.

## Phase B: Factory v2 Enforcement

1. In `Legacy\CommunicationFactory`, read `config['communication']`.
2. Resolve channel contexts from `channelContexts` only.
3. If `context` is present, throw a clear v2 migration exception.
4. If `channelContexts` is missing/invalid, throw a clear config exception.
5. Construct `CommunicationContext` consistently from resolved channels.

## Phase C: Documentation and Upgrade Notes

1. Add `UPGRADE-v2.md` section: legacy + DB repository migration.
2. Add config examples for MySQL and PostgreSQL.
3. Add explicit note: package supports MySQL and PostgreSQL in v2.x.

## Suggested Test Matrix

## Unit tests
- Factory with `channelContexts`.
- Factory rejects `context` with migration message.
- Invalid config branches with clear messages.
- Repository resolver maps each driver correctly.

## Integration tests
- MySQL: find/save definitions and template load path.
- PostgreSQL: same assertions.
- Full send path from `Communication` subclass through `SendCommunication`.

## Regression tests
- Existing communication instantiation works for v2-valid config.
- Existing CLI command behavior remains stable for v2-valid config.

## Versioning Recommendation

- Deliver as a v2 stabilization release that explicitly enforces v2-only configuration.
- Include migration notes and fail-fast guidance.
- Treat v1 key support as out of scope.

## Consumer-Side Temporary Workarounds (until release)

1. Override repository alias in app config to a local MySQL-compatible implementation.
2. Remove all `communication.context` usage and migrate to `channelContexts`.
3. Keep these overrides isolated in one app config file for easy removal after upstream fix.

## Definition of Done

The fix is complete when all are true:

1. `communication.context` is rejected with a clear migration message.
2. Interface `CommunicationDefinitionRepositoryInterface` resolves correctly on MySQL and PostgreSQL.
3. No PostgreSQL-specific SQL errors occur on MySQL.
4. Package docs include working MySQL + PostgreSQL examples.
5. CI test matrix includes both database engines.

## Suggested PR Breakdown

1. PR 1: Repository abstraction + MySQL implementation + resolver factory + tests.
2. PR 2: v2-only key enforcement in legacy factory + migration errors + tests.
3. PR 3: Docs and upgrade guide updates.

This split keeps review scope manageable and lowers release risk.
