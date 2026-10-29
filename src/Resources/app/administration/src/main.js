import './module/nova-termine';
import './cms/elements/nova-termine';
import './cms/blocks/nova-termine';

import deDE from './module/nova-termine/snippet/de-DE.json';
import enGB from './module/nova-termine/snippet/en-GB.json';

Shopware.Locale.extend('de-DE', deDE);
Shopware.Locale.extend('en-GB', enGB);
