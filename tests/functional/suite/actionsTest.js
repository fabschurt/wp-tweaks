casper.test.begin('<link> tags for adjacent posts do not appear in <head>', function(test) {
    casper.start(env._casper_post_2_url, function() {
        test.assertDoesntExist(
            {type: 'xpath', path: '/html/head//link[@rel="prev"]'},
            '<link rel="prev" /> does not exist'
        );
        test.assertDoesntExist(
            {type: 'xpath', path: '/html/head//link[@rel="next"]'},
            '<link rel="next" /> does not exist'
        );
    });

    casper.run(function() {
        test.done();
    });
});
