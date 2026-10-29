# Nova Events (Nova Termine)

Shopware 6.7 plugin for events – classic car meets, parts fairs, trade shows. Events are maintained in
the administration and displayed in the storefront with the shopping experience element
**“Event list”**. Past events disappear automatically, visitors can report missing meets.

*Deutsche Fassung: [README.md](README.md)*

## Features

- **Events in the administration** under *Content → Events*: title, location, country, start, end
  (optional), programme/times, link, participation, sales channels, status, image, internal note.
  Title and programme are translatable.
- **Participation** decides the section in the storefront: *with booth* → “Where you can meet us”,
  *without booth* → “More meets in the scene”, *not yet decided* → like without booth, with a note.
- **List** with filters *all / upcoming / past / suggestions* and search; upcoming events first,
  past events greyed out below.
- **Duplicate** for annual meets: the copy gets the date +1 year and status “inactive”.
- **Shopping experience block “Event list”** (category *Text*): section, empty text, maximum number.
- **“Is your meet missing?”**: a “Report a meet” button with a small form below the list. Reports are
  stored as *suggestions* (never shown automatically) and the shop gets a short email.
- **SEO**: schema.org `Event` data (JSON-LD) for every displayed event.
- **Always up to date**: pages with an event list are rebuilt when an event is saved and shortly
  after midnight.

## Installation

Via the Nova Updater (*Extensions → My extensions → Plugin catalog* → “Nova Termine”) or by uploading
`NovaTermine-<version>.zip` from the
[releases](https://github.com/DrdotHouse2106/SW6-Nova-Termine/releases). Activate, then reload the
administration (Ctrl+F5).

## Step by step

1. **Create an event**: *Content → Events* → *Add event*, fill in title, location, start (end only for
   multi-day events), optional programme and link (`https://…`), participation, status *Active*,
   sales channels (empty = all), save.
2. **Add the list to a page**: *Content → Shopping Experiences* → your page → *Blocks* → category
   *Text* → drag “Event list” onto the page → element settings (section, empty text, maximum,
   “Is your meet missing?”) → save.
3. **Accept suggestions**: *Content → Events* → tab *Suggestions* → open, check, *Accept* (the
   reporter's email address is removed) or delete. Suggestions not accepted are deleted after 180 days.

## Configuration

| Setting | Default | Meaning |
|---|---|---|
| Recipient for new suggestions | empty | Empty = shop email from the basic information |
| Time zone of the shop | Europe/Berlin | Decides when “today” starts |

## Caching

Pages containing an event list are tagged `nova-termine-list`. Saving or deleting an event
invalidates this tag. The scheduled task `nova_termine.refresh_list` runs hourly and invalidates the
tag once per day as soon as a new day has started in the shop time zone, so past events disappear
about one hour after midnight at the latest. Events are shown while **end ≥ today**.

## Security and privacy

Captcha check like Shopware's contact form, origin check (Shopware 6.7 relies on SameSite cookies
instead of CSRF tokens), honeypot, rate limits (5 per IP and hour, 50 per sales channel and day; only
a hash of the IP is cached), length limits on all fields, links only `http`/`https`. No IP address is
stored; the reporter's email address is removed on acceptance. Output is always escaped; JSON-LD is
encoded with `JSON_HEX_*` flags.

## Uninstall

Uninstalling **without** “keep data” drops the tables `nova_termin`, `nova_termin_translation`,
`nova_termin_sales_channel` and the plugin configuration.

## Development

The built administration is committed under `src/Resources/public/administration`; rebuild after
changes with `bin/build-administration.sh` (see the German README for building only this plugin).
Unit tests run without the Shopware kernel: `php phpunit.phar`.

## License

MIT – see [LICENSE](LICENSE).
