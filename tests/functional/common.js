var
    siteUrl = 'http://example.org',
    env     = require('system').env
;

casper.test.setUp(function globalSetUp() {
    phantom.clearCookies();
});
