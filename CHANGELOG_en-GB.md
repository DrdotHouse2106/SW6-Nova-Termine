# 1.0.0

- New entity “event”: translatable title and programme, location, country, start, end (optional), link (http/https only), participation (with booth / without booth / not yet decided), sales channels (empty = all), status (active / inactive / suggestion), image, internal note
- Administration: Content → Events with filters (all, upcoming, past, suggestions), search, create, edit, duplicate (+1 year), delete and “Accept” for suggestions; roles viewer/editor/creator/deleter
- Shopping experiences: block and element “Event list” with section, fallback text, maximum number and “Is your meet missing?”
- “Report a meet” form: creates a suggestion and notifies the shop by email; protected by Shopware captchas, honeypot, origin check and rate limits (5 per IP and hour, 50 per sales channel and day); suggestions not accepted are deleted after 180 days
- schema.org event data (JSON-LD) for every displayed event
- Cache: pages with an event list are rebuilt when an event is saved/deleted and shortly after midnight
