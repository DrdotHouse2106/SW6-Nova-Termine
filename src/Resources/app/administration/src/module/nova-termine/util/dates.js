/**
 * Datumshilfen für die Administration. Termine speichern nur ein Datum (JJJJ-MM-TT, ohne Uhrzeit).
 */

const pad = (n) => String(n).padStart(2, '0');

/** Heutiges Datum im Browser als JJJJ-MM-TT */
export function today() {
    const now = new Date();
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
}

/**
 * Beliebigen Datumswert (API-String, Datepicker-Wert, Date) auf JJJJ-MM-TT bringen.
 * „…T00:00:00…Z/+00:00“ (so liefert die API ein reines Datum) wird ohne Zeitzonen-Umrechnung übernommen,
 * damit kein Tag verrutscht.
 */
export function toDay(value) {
    if (!value) {
        return null;
    }
    if (typeof value === 'string') {
        if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
            return value;
        }
        if (/^\d{4}-\d{2}-\d{2}T00:00:00(\.0+)?(Z|\+00:00)$/.test(value)) {
            return value.substring(0, 10);
        }
    }
    const date = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(date.getTime())) {
        return null;
    }
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

/** JJJJ-MM-TT um ein Jahr verschieben (29.02. → 01.03.) */
export function plusOneYear(value) {
    const day = toDay(value);
    if (!day) {
        return null;
    }
    const [y, m, d] = day.split('-').map(Number);
    const date = new Date(Date.UTC(y + 1, m - 1, d));
    return `${date.getUTCFullYear()}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())}`;
}

/** Zeitraum wie im Shop: 17.07.2027 · 17.–18.07.2027 · 30.06.–02.07.2027 · 30.12.2027–02.01.2028 */
export function formatRange(startValue, endValue) {
    const start = toDay(startValue);
    if (!start) {
        return '';
    }
    let end = toDay(endValue);
    if (!end || end < start) {
        end = start;
    }
    const [sy, sm, sd] = start.split('-');
    const [ey, em, ed] = end.split('-');
    if (start === end) {
        return `${sd}.${sm}.${sy}`;
    }
    if (sy === ey && sm === em) {
        return `${sd}.–${ed}.${em}.${ey}`;
    }
    if (sy === ey) {
        return `${sd}.${sm}.–${ed}.${em}.${ey}`;
    }
    return `${sd}.${sm}.${sy}–${ed}.${em}.${ey}`;
}

/** Ist der Termin kommend bzw. läuft er noch (Ende >= heute)? */
export function isUpcoming(startValue, endValue, day = today()) {
    const start = toDay(startValue);
    if (!start) {
        return false;
    }
    const end = toDay(endValue);
    return (end && end >= start ? end : start) >= day;
}
