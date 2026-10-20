# Changelog

All notable changes to Magna Forms are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2026-10-21

Requires Magna 1.6.2 or later.

### Fixed

- **Forms submit from cached pages.** Magna Pages caches public pages for guests, and the form block
  rendered the CSRF token of whichever visitor's page was cached. Every later guest posted a token
  their own session never issued, so the submission failed with a 419 (on any browser that does not
  send `Sec-Fetch-Site: same-origin`), and the first visitor's token was served to everyone. The
  form now renders an empty token field and fills it with the visitor's own token from
  `GET /_magna/form-token` when they focus or submit the form.

### Added

- **Forms travel in the site kit** under the `forms:forms` section. The form block selects a form by
  handle, so a kit whose pages use the block now carries the forms they reference instead of
  applying cleanly and rendering a gap. Forms are upserted by handle and their fields are normalised
  on the way in and out; submissions never travel.

### Changed

- The signed render timestamp that drives the timing trap is now core's shared signed form
  timestamp. Stamps issued by earlier versions, including ones already stored in cached pages, keep
  passing.
- Requires `magna-cms/plugin-sdk` ^1.7, for the site-kit section contract.

## [0.1.1] - 2026-10-13

Requires Magna 1.4.8 or later.

### Changed

- The manifest's compatibility floor is the core version this release was tested on (1.4.8),
  replacing the open `^1.0`.
- A README that says what the plugin is and how to install it.

## [0.1.0] - 2026-10-13

First release: a form builder in the admin, the `form` page-builder block, and a submissions inbox,
with a honeypot and a signed timing trap against spam and email notification of each submission.
