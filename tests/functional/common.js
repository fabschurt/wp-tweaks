casper.start();

casper.test.setUp(function globalSetUp(test) {
    casper.page.clearCookies();
});
