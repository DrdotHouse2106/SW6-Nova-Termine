# 1.0.0

- Neue Entität „Termin“: Titel und Programm übersetzbar, Ort, Land, Beginn, Ende (optional), Link (nur http/https), Teilnahme (Mit Stand / Ohne Stand / Noch offen), Verkaufskanäle (leer = alle), Status (aktiv / inaktiv / Vorschlag), Bild, interne Notiz
- Administration: Inhalte → Termine mit Filtern (Alle, Kommend, Vergangen, Vorschläge), Suche, Anlegen, Bearbeiten, Duplizieren (+1 Jahr), Löschen und „Übernehmen“ für Vorschläge; Rollen Ansehen/Bearbeiten/Anlegen/Löschen
- Erlebniswelten: Block und Element „Terminliste“ mit Bereich, Ersatztext, Höchstzahl und „Ihr Treffen fehlt?“
- Formular „Treffen melden“: legt einen Vorschlag an und informiert den Shop per E-Mail; geschützt durch Shopware-Captchas, Honeypot, Herkunftsprüfung und Mengenbegrenzung (5 je IP und Stunde, 50 je Verkaufskanal und Tag); nicht übernommene Vorschläge werden nach 180 Tagen gelöscht
- schema.org-Event-Daten (JSON-LD) für jeden angezeigten Termin
- Cache: Seiten mit Terminliste werden beim Speichern/Löschen eines Termins und kurz nach Mitternacht neu aufgebaut
