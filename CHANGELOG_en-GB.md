# 1.2.1

- Pickup: lines in the customer comment that start with “ABHOLUNG:” themselves are removed – customers can no longer write a different pickup event into the order comment; only the first line set by the shop counts
- “Report a meet” form: honeypot field with a meaningless name and label so autofill tools and password managers do not fill it (genuine reports were silently lost otherwise); hits are logged
- Saving via the API: “end before start” is also checked when only the start or only the end date is changed; date values are parsed more robustly
- Clear the cache after updating

# 1.2.0

- Event list now uses reading width (46rem, centred) so it lines up with text blocks; add the block CSS class `nova-full` for full width
- Compile the theme after updating

# 1.1.0

- New: **pickup at meets/trade shows** – a shipping method of your choice (plugin configuration) with a mandatory selection “At which meet would you like to pick up your order?” in the checkout; offered are active events “with booth” of the sales channel that start at least “lead time in days” (default 7) after the order
- Without such an event the shipping method is not offered; if it is already selected, Shopware switches to another shipping method with the usual notice
- Check when the order is placed (event valid, active, sales channel, with booth, deadline); the selection survives page reloads
- Order: event snapshot in `order.customFields` (`nova_termine_pickup_*`) and “ABHOLUNG: title, location, date” at the start of the order comment
- Shown on the finish page, in the order details of the customer account, as a card in the order (administration) and as a “Pickups” list per event
- README: variable for the order confirmation email, payment method “payment on pickup” via rule builder

# 1.0.0

- New entity “event”: translatable title and programme, location, country, start, end (optional), link (http/https only), participation (with booth / without booth / not yet decided), sales channels (empty = all), status (active / inactive / suggestion), image, internal note
- Administration: Content → Events with filters (all, upcoming, past, suggestions), search, create, edit, duplicate (+1 year), delete and “Accept” for suggestions; roles viewer/editor/creator/deleter
- Shopping experiences: block and element “Event list” with section, fallback text, maximum number and “Is your meet missing?”
- “Report a meet” form: creates a suggestion and notifies the shop by email; protected by Shopware captchas, honeypot, origin check and rate limits (5 per IP and hour, 50 per sales channel and day); suggestions not accepted are deleted after 180 days
- schema.org event data (JSON-LD) for every displayed event
- Cache: pages with an event list are rebuilt when an event is saved/deleted and shortly after midnight
