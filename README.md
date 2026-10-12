<h1 align="center">Magna Forms</h1>

<p align="center">
  <strong>Forms for Magna CMS.</strong><br>
  Build a form in the admin, drop it on any page with the form block, collect submissions —
  with spam protection and email notification built in.
</p>

<p align="center">
  <img alt="Version 0.1.0" src="https://img.shields.io/badge/version-0.1.0-6366f1.svg">
  <img alt="PHP ^8.3" src="https://img.shields.io/badge/php-%5E8.3-777bb4.svg">
  <a href="https://github.com/Magna-CMS"><img alt="Magna CMS ^1.0" src="https://img.shields.io/badge/Magna%20CMS-%5E1.0-6366f1.svg"></a>
  <img alt="License: proprietary" src="https://img.shields.io/badge/license-proprietary-orange.svg">
</p>

---

Magna Forms is a plugin for [Magna CMS](https://github.com/Magna-CMS). It adds three things:

- **A form builder** in the admin panel — name a form, add typed fields, choose who gets
  notified and whether submissions are stored.
- **A `form` page-builder block** — place any form on any page built with
  [Magna Pages](https://github.com/Magna-CMS/Magna-Pages). The form renders through the active
  theme like every other block; the plugin ships no pages or layout of its own.
- **A submissions inbox** — read what came in, delete what you no longer need.

There is no JavaScript shipped to the visitor. The form is plain HTML, posted with the page's
CSRF token, rendered and validated server-side.

## The form block

The plugin registers one block, `form`, with two fields: an optional heading and a picker
listing every form on the site. Editors choose a form by name; the block stores its handle.

```php
BlockDefinition::fromArray([
    'handle' => 'form',
    'label' => 'Form',
    'icon' => 'core:envelope',
    'category' => 'site',
    'fields' => [
        ['handle' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => false],
        [
            'handle' => 'form',
            'type' => 'select',
            'label' => 'Form',
            'required' => true,
            'optionsFrom' => FormOptions::class,
        ],
    ],
]),
```

A form that has been deleted resolves to nothing: the page renders a gap, never an error.

## Fields and validation

Eight field types, each with validation derived from the type:

| Type | Validation |
|---|---|
| `text` | string, max 255 |
| `email` | RFC email, max 255 |
| `tel` | string, max 40 |
| `url` | URL, max 2048 |
| `number` | numeric |
| `textarea` | string, max 5000 |
| `select` | must be one of the form's own options |
| `checkbox` | boolean; `accepted` when required |

The form definition is the validation — rules are derived from the declared fields and nowhere
else, so a submitted key that no declared field claims is dropped before it can be stored. A
select may only submit what it offers: the options are an allowlist, not a suggestion.

## Submissions

The public endpoint is `POST /forms/{handle}`, throttled to 10 requests per minute per IP. It
answers JSON for JSON requests and redirects back (with errors or a success flash) for plain
form posts.

Two spam checks run before validation, and both fail **silently** — a bot that learns which
trap it tripped defeats the trap, so a caught submission receives the same success message a
visitor would see, and nothing is stored:

- **Honeypot** — a visually hidden field only a bot fills.
- **Timing** — the page embeds an HMAC-signed render timestamp; a submission arriving less
  than two seconds after render, or carrying a forged or missing stamp, is discarded.

Clean submissions are stored (unless the form's *store submissions* toggle is off) and each
address on the form's notification list receives an email. A mail outage never loses a
submission or shows the visitor an error — the submission is already stored before mail is
attempted.

The visitor's IP is stored **hashed** (HMAC with the app key) — enough to spot a flood, never
a stored personal identifier. Submissions in the admin are read-and-delete: nothing edits a
visitor's words.

## Admin

A **Forms** navigation group with two screens, each behind its own permission:

| Screen | Permission | What it does |
|---|---|---|
| Forms | `forms.manage` | Create forms, edit fields, set notification addresses, submit label, success message, storage toggle |
| Submissions | `forms.submissions` | Read and delete submissions, per form |

## Site kits

Forms travel in the site kit under the `forms:forms` section. The form block selects a form by
handle, so a kit whose contact page uses the block carries the forms its pages reference —
without this, the kit would apply cleanly and render a hole until someone hand-created the form
on the target site. Kits upsert forms by handle; submissions deliberately do not travel — they
are environment data, not the site.

## Requirements

- PHP 8.3+
- Magna CMS ^1.0

## Install

### From the admin panel

On a site with marketplace installs available, install **Magna Forms** from the Plugins screen
in the admin panel and enable it. Enabling runs the plugin's migrations — no artisan step
needed.

### Dev-path install

From the root of a Magna CMS checkout:

```bash
git clone https://github.com/Magna-CMS/Magna-Forms.git plugins-dev/magna/forms
```

Register the path repository in the root `composer.json` and require the package:

```json
{
    "repositories": [
        { "type": "path", "url": "plugins-dev/magna/forms" }
    ]
}
```

```bash
composer require magna/forms:@dev
php artisan magna:plugin:enable magna/forms
```

## Quick start

1. In the admin panel, open **Forms → Forms** and create a form — add fields, set the
   notification addresses, adjust the submit label and success message.
2. In the page builder, add a **Form** block to a page and pick the form.
3. Publish. Submissions land in **Forms → Submissions**, and each notification address gets
   an email per submission.

## Uninstall

Uninstalling the plugin drops its two tables, `forms_forms` and `forms_submissions` —
submissions go with them, so export anything you need first.

## License

Proprietary. © Magna Team. Not open source — do not redistribute.

## Links

- [Magna CMS](https://github.com/Magna-CMS) — the core
- [Magna Pages](https://github.com/Magna-CMS/Magna-Pages) — the page builder the form block renders in
