# WP Tweaks

This is a WordPress must-use plugin, aiming to centralize some tweaks and
helpers that I use on 99% of the WordPress websites that I build. It's not
registered on [wordpress.org](https://wordpress.org/plugins/), because I don't
think that people would actually want to use this as is, but I'm putting it on
GitHub, because GitHub is pretty cool (plus, free code hosting, yay!). It's also
published on Packagist, just to make things a little easier for me in
`composer.json`.

The DocBlocks and inline comments for the various helpers and actions/filters
are hopefully self-explanatory.

This was also my first experiment in unit testing a WordPress plugin. I was
expecting the process to be a pain in the ass, but it was surprisingly easy to
set up thanks to the `scaffold plugin-tests` command of [wp-cli](http://wp-cli.org/)
and some tweaking. However, testing a WordPress plugin remains inherently
tedious because of the extensive use of procedural programming and global scope
in WP core and plugins.

## @todo

- [ ] Finish to write unit tests
- [ ] Finish to write DocBlocks

## License

This plugin is licensed under the [MIT License](http://opensource.org/licenses/MIT).

## Disclaimer

This plugin is meaningful and useful to me; it may not be (and certainly isn't
at all) the case for everyone.
