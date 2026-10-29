# Nova Termine

Shopware-6.7-Plugin für Veranstaltungstermine – Oldtimertreffen, Teilemärkte, Messen. Termine werden
bequem in der Administration gepflegt und über das Erlebniswelten-Element **„Terminliste“** im Shop
angezeigt. Vergangene Termine verschwinden automatisch, Besucher können fehlende Treffen melden.

*English version: [README.en.md](README.en.md)*

## Funktionen

- **Termine in der Administration** unter *Inhalte → Termine*: Titel, Ort, Land, Beginn, Ende
  (optional), Programm/Uhrzeiten, Link, Teilnahme, Verkaufskanäle, Status, Bild, interne Notiz.
  Titel und Programm sind übersetzbar (Sprachumschalter oben).
- **Teilnahme** bestimmt den Bereich im Shop:
  - *Mit Stand* → „Hier sind wir dabei“
  - *Ohne Stand* → „Weitere Treffen in der Szene“
  - *Noch offen* → wie „Ohne Stand“, mit dem Hinweis „Teilnahme noch offen“
- **Liste** mit Filtern *Alle / Kommend / Vergangen / Vorschläge* und Suche. Kommende Termine stehen
  oben, vergangene darunter ausgegraut.
- **Duplizieren** für jährliche Treffen: Die Kopie bekommt das Datum +1 Jahr und den Status
  „Inaktiv“, damit Sie sie vor dem Veröffentlichen prüfen.
- **Erlebniswelten-Block „Terminliste“** (Kategorie *Text*): Bereich (alle / mit Stand / ohne Stand),
  Text bei leerer Liste, Höchstzahl.
- **„Ihr Treffen fehlt?“**: Unter der Liste ein Knopf „Treffen melden“ mit kleinem Formular. Meldungen
  werden als *Vorschlag* gespeichert (nie automatisch sichtbar), der Shop bekommt eine kurze E-Mail.
- **SEO**: Für jeden angezeigten Termin schema.org-`Event`-Daten (JSON-LD).
- **Automatisch aktuell**: Seiten mit Terminliste werden beim Speichern eines Termins und kurz nach
  Mitternacht neu aufgebaut.
- **Abholung auf Treffen/Messe** (ab 1.1.0): Kunden wählen in der Kasse eine Abhol-Versandart und das
  Treffen, auf dem sie ihre Bestellung abholen. Die Bestellungen je Treffen stehen als Packliste am
  Termin.

## Installation

**Über den Nova Updater:** *Erweiterungen → Meine Erweiterungen → Plugin-Katalog* → „Nova Termine“
→ *Installieren*. Danach aktivieren.

**Per ZIP:** Das ZIP `NovaTermine-<Version>.zip` aus den
[Releases](https://github.com/DrdotHouse2106/SW6-Nova-Termine/releases) unter *Erweiterungen → Meine
Erweiterungen → Erweiterung hochladen* hochladen, installieren und aktivieren.

Nach dem Aktivieren die Administration einmal neu laden (Strg+F5). Für die Darstellung im Shop das
Theme neu kompilieren lassen (passiert beim Aktivieren automatisch; sonst *Inhalte → Themes* →
Theme speichern).

## Schritt für Schritt

### 1. Termin anlegen

1. *Inhalte → Termine* → **Termin anlegen**.
2. Titel (z. B. „Oldtimertreffen Musterstadt“), Ort, ggf. Land eintragen.
3. **Beginn** wählen. **Ende** nur bei mehrtägigen Terminen – leer bedeutet „eintägig“.
4. Optional **Programm / Uhrzeiten** (z. B. „Sa 9–18 Uhr, So 9–16 Uhr“) und **Link** (muss mit
   `https://` oder `http://` beginnen; erscheint im Shop als „Mehr erfahren“).
5. **Teilnahme**: *Mit Stand*, wenn Sie selbst ausstellen; sonst *Ohne Stand* oder *Noch offen*.
6. **Status** *Aktiv* (sichtbar) oder *Inaktiv* (vorbereitet, noch nicht sichtbar).
7. **Verkaufskanäle** leer lassen = in allen Shops; sonst die gewünschten Shops auswählen.
8. **Speichern**.

### 2. Terminliste auf einer Seite einbauen

1. *Inhalte → Erlebniswelten* → Ihre Seite öffnen (z. B. „Termine“).
2. Links **Blöcke** → Kategorie **Text** → Block **„Terminliste“** auf die Seite ziehen.
3. Block anklicken → Zahnrad am Element → Einstellungen:
   - **Bereich**: *Alle Termine* zeigt beide Bereiche mit Überschrift. Wollen Sie die Bereiche selbst
     überschreiben, legen Sie zwei Blöcke an (einmal *Mit Stand*, einmal *Ohne Stand*) und darüber
     je einen Textblock mit Ihrer Überschrift.
   - **Text, wenn keine Termine anstehen** (Standard: „Die nächsten Termine stehen bald fest.“)
   - **Höchstens anzeigen** (0 = alle)
   - **„Ihr Treffen fehlt?“ anzeigen** und den Text dazu. Haben Sie zwei Terminlisten auf einer Seite,
     schalten Sie es bei einer davon aus.
4. Speichern. Den bisherigen Text mit den Platzhaltern `[[bis …]] … [[ende]]` können Sie löschen.

Im Editor sehen Sie Beispieldaten; die echten Termine erscheinen im Shop.

### 3. Vorschläge übernehmen

1. Meldet ein Besucher ein Treffen, kommt eine E-Mail an den Shop (Empfänger: Plugin-Konfiguration,
   sonst die Shop-E-Mail aus *Einstellungen → Stammdaten*). „Antworten“ geht direkt an den Melder,
   falls er eine E-Mail-Adresse angegeben hat.
2. *Inhalte → Termine* → Reiter **Vorschläge** (die Zahl zeigt offene Vorschläge).
3. Vorschlag öffnen, Angaben prüfen/ergänzen (Teilnahme, Programm, Verkaufskanäle) und
   **Übernehmen** klicken – der Termin wird aktiv. Die E-Mail-Adresse des Melders wird dabei
   entfernt; seine Nachricht steht in der internen Notiz.
4. Unpassende Vorschläge **löschen**. Nicht übernommene Vorschläge löscht das Plugin nach 180 Tagen
   automatisch.

## Abholung auf Treffen/Messe (ab 1.1.0)

Kunden können ihre Bestellung auf einem Treffen abholen, auf dem Sie mit Stand vertreten sind.

### Einrichten

1. **Versandart anlegen**: *Einstellungen → Versand → Versandart hinzufügen*, z. B. „Abholung auf
   Treffen/Messe“, Preis 0 €, aktiv, Verfügbarkeitsregel „Immer gültig“ (bzw. Ihre übliche Regel).
   Unter *Verkaufskanäle* den Shop zuordnen (Verkaufskanal → Versandarten).
2. **Plugin konfigurieren** (je Verkaufskanal möglich): *Versandart für Abholung auf Treffen* = die neue
   Versandart, *Vorlauf in Tagen* = wie viele Tage vor Beginn spätestens bestellt werden muss
   (Standard 7).
3. **Termine pflegen**: Für die Abholung zählen nur Termine mit Status *Aktiv*, Teilnahme *Mit Stand*,
   passendem Verkaufskanal und **Beginn ≥ heute + Vorlauf**.

### So funktioniert es in der Kasse

- Steht kein passender Termin an, wird die Versandart **nicht angeboten** (das Plugin filtert sie aus
  Shopwares Versandarten-Liste – Warenkorb, Kasse und Store-API).
- Ist sie gewählt, erscheint darunter die Pflicht-Auswahl **„Auf welchem Treffen möchten Sie
  abholen?“** (Anzeige „Titel – Ort – Datum“). Die Wahl wird in der Sitzung gemerkt und bleibt beim
  Neuladen oder beim Wechsel der Zahlungsart erhalten.
- Läuft die Frist ab, während die Versandart schon gewählt ist, meldet das Plugin sie wie eine gesperrte
  Versandart: Shopware wechselt automatisch auf eine andere Versandart und zeigt den üblichen Hinweis.
- Beim **Bestellabschluss** prüft der Server erneut (Termin vorhanden, aktiv, Verkaufskanal, Mit Stand,
  Frist). Sonst bleibt der Kunde mit Hinweis auf der Bestätigungsseite.

**Warum kein Rule-Builder-Baustein?** Regeln werden bei jeder Warenkorb-Berechnung für alle Regeln
ausgewertet und zwischengespeichert; ein Baustein „Abhol-Treffen verfügbar“ müsste dafür jedes Mal die
Termine laden. Das Ausfiltern direkt an der Versandarten-Liste wirkt nur dort, wo Versandarten
angezeigt werden, und braucht keine Regel-Pflege.

### An der Bestellung

- Bestellkommentar beginnt mit `ABHOLUNG: <Titel>, <Ort>, <Datum>`; ein Kommentar des Kunden bleibt
  dahinter erhalten. So kommt die Information auch in Warenwirtschaften an, die eigene Felder nicht
  übernehmen.
- Zusätzlich als Schnappschuss in den Zusatzfeldern der Bestellung (bleibt erhalten, auch wenn der
  Termin später gelöscht wird):

  | Feld | Inhalt |
  |---|---|
  | `nova_termine_pickup_id` | ID des Termins |
  | `nova_termine_pickup_title` | Titel |
  | `nova_termine_pickup_location` | Ort |
  | `nova_termine_pickup_date` | Datum, z. B. „17.–18.07.2027“ |
  | `nova_termine_pickup_start` | Beginn `JJJJ-MM-TT` |
  | `nova_termine_pickup_text` | „Titel, Ort, Datum“ |

- Anzeige: Abschlussseite, Kundenkonto → Bestellungen → Details, Administration → Bestellung →
  *Details* (Karte „Abholung auf Treffen/Messe“) und am Termin die Liste **„Abholungen“**
  (Bestellnummer, Kunde, Bestelldatum, Status, Betrag, Link zur Bestellung) – benötigt das Recht
  „Bestellungen ansehen“.

### Bestellbestätigung (E-Mail)

Das Standard-Template wird nicht verändert. Um das Treffen in der Mail zu nennen: *Einstellungen →
E-Mail-Templates → Bestellbestätigung* öffnen und an passender Stelle (z. B. nach der Versandart)
einfügen – im HTML-Teil:

```twig
{% if order.customFields.nova_termine_pickup_text is defined %}
    <p><strong>Abholung auf:</strong> {{ order.customFields.nova_termine_pickup_text }}</p>
{% endif %}
```

im Text-Teil:

```twig
{% if order.customFields.nova_termine_pickup_text is defined %}
Abholung auf: {{ order.customFields.nova_termine_pickup_text }}
{% endif %}
```

### Zahlung bei Abholung (bar, EC, Kreditkarte)

Das Plugin enthält keine eigene Zahlungslogik. So legen Sie eine Zahlungsart nur für die Abholung an:

1. *Einstellungen → Rule Builder → Regel anlegen*: Name „Versandart Abholung auf Treffen“, Bedingung
   **Versandart** *ist eine von* „Abholung auf Treffen/Messe“, speichern.
2. *Einstellungen → Zahlungsarten → Zahlungsart hinzufügen*: Name „Bezahlung bei Abholung (bar, EC,
   Kreditkarte)“, aktiv, als **Verfügbarkeitsregel** die neue Regel wählen.
3. Die Zahlungsart dem Verkaufskanal zuordnen (Verkaufskanal → Zahlungsarten).

Vorkasse oder PayPal bleiben daneben möglich – sie gelten für alle Versandarten, sofern Sie sie nicht
ebenfalls per Regel einschränken.

## Konfiguration

*Erweiterungen → Meine Erweiterungen → Nova Termine → Konfigurieren* (je Verkaufskanal möglich):

| Einstellung | Standard | Bedeutung |
|---|---|---|
| Empfänger für neue Vorschläge | leer | Leer = E-Mail-Adresse des Shops aus den Stammdaten |
| Zeitzone des Shops | Europe/Berlin | Bestimmt, wann „heute“ beginnt |
| Versandart für Abholung auf Treffen | leer | Leer = Abholung aus (ab 1.1.0) |
| Vorlauf in Tagen | 7 | Bestellung mindestens so viele Tage vor Beginn (ab 1.1.0) |

## Wie die Liste aktuell bleibt (Caching)

Shopware speichert fertige Seiten im HTTP-Cache. Damit die Terminliste trotzdem stimmt:

1. **Cache-Tag**: Jede Seite mit Terminliste wird beim Aufbau mit dem Tag `nova-termine-list`
   markiert.
2. **Beim Speichern/Löschen** eines Termins (Administration, API, Formular) werden alle Seiten mit
   diesem Tag aus dem Cache entfernt und beim nächsten Aufruf neu gebaut. Bei aktiviertem
   „verzögerten Invalidieren“ geschieht das mit Shopwares Aufgabe `shopware.invalidate_cache`
   (alle 5 Minuten).
3. **Nach Mitternacht**: Die geplante Aufgabe `nova_termine.refresh_list` läuft stündlich und
   entfernt die Seiten einmal pro Tag, sobald in der Shop-Zeitzone ein neuer Tag begonnen hat.
   So verschwinden abgelaufene Termine spätestens etwa eine Stunde nach Mitternacht.

Voraussetzung für Punkt 3 sind laufende geplante Aufgaben (Cron für `bin/console
scheduled-task:run` bzw. Admin-Worker). Angezeigt werden Termine mit **Ende ≥ heute** – ein Termin am
17.–18.07. ist also am 18.07. noch zu sehen und ab dem 19.07. nicht mehr.

## Sicherheit und Datenschutz (Formular „Treffen melden“)

- **Captcha**: Die Route ist wie Shopwares Kontaktformular mit Captcha-Prüfung versehen; es gelten
  die unter *Einstellungen → Grundinformationen → Captcha* aktivierten Captchas (Honeypot, einfaches
  Captcha, Google reCAPTCHA …).
- **CSRF**: Shopware 6.7 nutzt keine CSRF-Tokens mehr, sondern SameSite-Cookies. Zusätzlich nimmt das
  Plugin Meldungen nur an, wenn `Origin` bzw. `Referer` auf den eigenen Shop zeigt.
- **Honeypot**: verstecktes Feld; Bots, die es ausfüllen, bekommen eine Scheinbestätigung.
- **Mengenbegrenzung** (Symfony RateLimiter): 5 Meldungen je IP-Adresse und Stunde, 50 je
  Verkaufskanal und Tag. Im Cache steht nur ein Hash der IP.
- **Eingaben**: Längenbegrenzung aller Felder, Link nur `http`/`https`, Datum geprüft (nicht in der
  Vergangenheit, höchstens 3 Jahre voraus, höchstens 31 Tage Dauer).
- **Gespeichert** werden nur die Formulardaten – keine IP-Adresse. Die optionale E-Mail-Adresse des
  Melders wird beim Übernehmen entfernt; Vorschläge werden nach 180 Tagen gelöscht.
- Unter dem Formular steht Shopwares Datenschutzhinweis mit Link zur Datenschutzseite (bzw. die
  Pflicht-Checkbox, wenn diese in den Login-/Registrierungs-Einstellungen aktiviert ist).
- In der Ausgabe wird jeder Text escaped (kein `|raw` mit Nutzertext). Die JSON-LD-Daten werden
  mit `JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT` kodiert.

**Datenschutzerklärung**: Ergänzen Sie, dass über das Formular „Treffen melden“ freiwillig Angaben
zu einer Veranstaltung und optional eine E-Mail-Adresse für Rückfragen übermittelt werden, die zur
Prüfung gespeichert und nach Übernahme bzw. spätestens nach 180 Tagen gelöscht wird.

## Rechte

*Einstellungen → Benutzer & Rechte → Rollen → Inhalte → Termine*: Ansehen, Bearbeiten (inkl.
Übernehmen), Anlegen (inkl. Duplizieren), Löschen.

## Deinstallation

Beim Deinstallieren **ohne** „Daten behalten“ werden die Tabellen `nova_termin`,
`nova_termin_translation`, `nova_termin_sales_channel` und die Plugin-Konfiguration gelöscht. Die
Erlebniswelten-Blöcke bleiben auf den Seiten stehen und zeigen dann nichts mehr an – bitte
entfernen.

## Entwicklung

Template-Blöcke zum Anpassen im eigenen Theme:

| Template | Blöcke |
|---|---|
| `storefront/element/cms-element-nova-termine.html.twig` | `element_nova_termine`, `element_nova_termine_empty`, `element_nova_termine_lists`, `element_nova_termine_json_ld`, `element_nova_termine_report` |
| `storefront/nova-termine/item.html.twig` | `nova_termine_item_date`, `…_title`, `…_place`, `…_program`, `…_open`, `…_link`, `…_media` |
| `storefront/nova-termine/report-form.html.twig` | `nova_termine_report_teaser`, `…_fields`, `…_captcha`, `…_privacy`, `…_submit` |
| `storefront/nova-termine/pickup-select.html.twig` | `nova_termine_pickup`, `nova_termine_pickup_none`, `nova_termine_pickup_form` |
| `storefront/nova-termine/pickup-info.html.twig` | `nova_termine_pickup_info` |

Die Administration liegt fertig gebaut unter `src/Resources/public/administration` (wird
eingecheckt). Nach Änderungen an `src/Resources/app/administration` neu bauen – innerhalb einer
Shopware-Installation mit `bin/build-administration.sh`, oder nur dieses Plugin mit dem
Administration-Quellcode von Shopware 6.7:

```bash
# PROJECT_ROOT enthält var/plugins.json (Eintrag „NovaTermine“ mit basePath custom/plugins/NovaTermine/src/)
# und custom/plugins/NovaTermine (Kopie dieses Repositorys)
cd <shopware>/src/Administration/Resources/app/administration
PROJECT_ROOT=<projekt> SHOPWARE_ADMIN_BUILD_ONLY_EXTENSIONS=1 VITE_MODE=production npx ts-node -T build.ts
# danach custom/plugins/NovaTermine/src/Resources/public zurückkopieren
```

Das Storefront-Plugin braucht kein eigenes JavaScript (Formular über Shopwares `FormCmsHandler`,
Aufklappen über Bootstrap); das SCSS wird beim Theme-Kompilieren übernommen.

Unit-Tests der Datumslogik und Eingabeprüfung laufen ohne Shopware-Kernel:

```bash
php phpunit.phar   # oder vendor/bin/phpunit
```

## Häufige Probleme

### Die Abhol-Versandart erscheint nicht in der Kasse

**Ursache:** Kein aktiver Termin „Mit Stand“ für diesen Verkaufskanal, der mindestens „Vorlauf in Tagen“
nach heute beginnt; Versandart nicht in der Plugin-Konfiguration gewählt oder nicht dem Verkaufskanal
zugeordnet

**Lösung:** Termin und Plugin-Konfiguration prüfen; Versandart unter Verkaufskanal → Versandarten
zuordnen

### Ein abgelaufener Termin steht noch im Shop

**Ursache:** Geplante Aufgaben laufen nicht, die Seite kommt noch aus dem Cache

**Lösung:** Cron für `scheduled-task:run` einrichten bzw. Admin-Worker aktivieren; sofort hilft
*Einstellungen → Caches & Indizes → Caches leeren*

### Ein neuer Termin erscheint nicht

**Ursache:** Status nicht „Aktiv“, falscher Verkaufskanal, Ende liegt in der Vergangenheit, oder das
verzögerte Invalidieren wartet noch auf die nächste Aufgabe (bis zu 5 Minuten)

**Lösung:** Termin prüfen; ggf. kurz warten oder Cache leeren

### Keine E-Mail bei neuen Vorschlägen

**Ursache:** Kein Empfänger (Plugin-Konfiguration und Shop-E-Mail leer) oder Mailversand gestört

**Lösung:** Empfänger in der Plugin-Konfiguration eintragen, Mailversand unter *Einstellungen →
Mailer* prüfen. Der Vorschlag ist trotzdem gespeichert (Reiter „Vorschläge“).

### „Termine“ fehlt im Menü

**Ursache:** Administration nicht neu geladen oder Benutzerrolle ohne Recht „Termine ansehen“

**Lösung:** Strg+F5; Rolle unter *Benutzer & Rechte* ergänzen

## Lizenz

MIT – siehe [LICENSE](LICENSE).
