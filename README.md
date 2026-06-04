# Etechflow_AiSeo

AI-generated **SEO meta titles & descriptions** for Magento 2 / Adobe Commerce products.
Part of the Etechflow SEO Suite. Supports **Anthropic (Claude)** and **OpenAI (GPT)**.

## Features

- **Bulk generate** AI meta titles + descriptions straight from the **catalog product grid**
  — select products → *Generate AI Meta (Etechflow)*.
- **Review before publish** — suggestions land in an admin grid (current vs AI), where you
  Apply individually, mass-apply, or delete. Or flip **Auto-apply** to write them immediately.
- **Provider-agnostic** — Anthropic Messages API or OpenAI Chat Completions; pick the model.
- **Length-aware** — clamps to your configured meta title / description limits.
- **Brand voice** — a configurable tone/instruction is injected into every prompt.
- **Encrypted API key**, flag-gated master switch (off by default), CLI command for testing.

## How it works

`Service\AiClient` calls the LLM → `Service\MetaGenerator` builds the prompt from the product
(name, SKU, description) and parses the JSON `{title, description}` → `Service\SuggestionProcessor`
stores a reviewable suggestion and, on approval, writes `meta_title` / `meta_description` back to
the product.

## Where it lives

- **Catalog → Products** → mass action **"Generate AI Meta (Etechflow)"**
- **Marketing → AI SEO → AI SEO Suggestions** — review & apply
- **Stores → Configuration → Etechflow → AI SEO** — provider, key, model, lengths, tone

## Configuration

| Path | Default | Notes |
|------|---------|-------|
| `etechflow_aiseo/general/enabled` | `0` | Master switch |
| `etechflow_aiseo/general/provider` | `anthropic` | `anthropic` or `openai` |
| `etechflow_aiseo/general/model` | `claude-sonnet-4-6` | e.g. `gpt-4o-mini` for OpenAI |
| `etechflow_aiseo/general/api_key` | – | Encrypted |
| `etechflow_aiseo/general/auto_apply` | `0` | Write straight to product vs review queue |
| `etechflow_aiseo/output/title_max` | `60` | Meta title char limit |
| `etechflow_aiseo/output/description_max` | `160` | Meta description char limit |
| `etechflow_aiseo/output/brand_tone` | – | Tone/voice added to the prompt |

## CLI

```bash
bin/magento etechflow:aiseo:generate --product-id=135
```

## Notes

Grid mass-generation runs **synchronously** and is capped at 25 products per run (LLM calls take
a few seconds each). For large catalogs, run in smaller batches (a queue/cron worker is on the
roadmap).

## Install

```bash
bin/magento module:enable Etechflow_AiSeo
bin/magento setup:upgrade
bin/magento setup:static-content:deploy <locale> -f --area frontend   # prod: setup:upgrade clears var/view_preprocessed
rm -rf generated/code/* generated/metadata/*
bin/magento setup:di:compile
bin/magento cache:flush
```

## Tests

```bash
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Etechflow/AiSeo/Test/Unit
```
