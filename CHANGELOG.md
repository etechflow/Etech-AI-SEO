# Changelog

All notable changes to this module are documented here.

## Security: portal-only licensing (removes forgeable key path) — 2026-07-03

Closes a licensing bypass. Previous versions shipped the HMAC signing secret
inside `LicenseValidator` (`SECRET_FRAGMENTS`/`BUNDLE_SECRET_FRAGMENTS`) and
validated a locally-computed key (`computeKey()`/`computeBundleKey()`) against
it, so anyone with the module source could compute a valid key for their own
domain and run it unlicensed. Secondary bypasses — a `production_environment`
toggle and a client-settable "locally issued" grace
(`issued_key`/`issued_at`/`ip_blocked`) — are removed too.

- Validation is now portal-only: `isValid()` honours a key only when the
  ETechFlow portal confirms it. The module ships no signing secret.
- Offline grace derives solely from a cached genuine portal success, keyed to
  host+key — nothing the customer sets can fabricate it.
- Production enforcement is always on; the sandbox toggle is gone.
- Rewrote the unit suite, incl. a hard test that a forged `SP-` key with
  attacker-set config and no portal is rejected.

## v1.0.0 — 2026-06-05

Initial public release.

AI-generated meta titles/descriptions (Anthropic/OpenAI). Admin grid of pending suggestions, apply/reject, CLI generate command, configurable provider + prompts.
