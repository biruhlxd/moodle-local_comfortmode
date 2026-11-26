# Comfort Mode (local_comfortmode)

Lets every person choose how a Moodle site looks and feels for them. It works with any theme based on Boost, because it does not replace the theme: it adds its own styles on top. Tested with Boost, Moove and Academi on Moodle 5.3, and with Classic on 5.2.

## What people can choose

- **Text size:** standard, large, larger or largest. All text on the site grows with it.
- **Typeface:** the site's own, Atkinson Hyperlegible (made for low vision) or OpenDyslexic (made for dyslexia). Both fonts ship with the plugin.
- **Easier reading:** a plain typeface with more space between letters, words and lines, and shorter lines of text.
- **High contrast:** black text on white (white on black in dark mode), stronger borders, underlined links and a focus ring that cannot be missed.
- **Reduce motion:** stops animations and sliding effects.
- **Focus mode:** hides the side panels and the footer and narrows the page, so only the content is left.

**Quick start** profiles turn on a set of settings in one click: *Dyslexia* (OpenDyslexic, easier reading, large text), *Low vision* (largest text, Atkinson Hyperlegible, high contrast) and *Concentration* (focus mode, reduced motion). Each setting can be changed afterwards.

Choices are stored as user preferences, so they follow a person to every device they log in from. Visitors who are not logged in, and guests, can choose too, from a link in the footer: their choices are kept in a cookie in their own browser (`MoodleComfortMode`), and an administrator can switch this off.

## Using it

People open **Comfort Mode** from the user menu or from their Preferences page, choose, and save. **Use the site defaults** forgets their choices.

Administrators go to *Site administration > Plugins > Local plugins > Comfort Mode*. Each setting can be switched off for the whole site, and each has a site default that applies until a person chooses for themselves. Site defaults also apply to guests and to pages shown before login.

## How it works

The settings in effect are written to the `html` tag as attributes, for example `data-comfortmode-textsize="130"`, through Moodle's `before_html_attributes` hook. The plugin's `styles.css` acts on those attributes. No theme files are changed and no JavaScript is needed. The two fonts are loaded only when someone chooses them.

## Adding settings from another plugin

Other plugins can add their own settings through the `\local_comfortmode\hook\collect_options` hook:

```php
public static function collect_options(\local_comfortmode\hook\collect_options $hook): void {
    $hook->add_option(new \local_comfortmode\option('local_example', 'spacing'));
}
```

The plugin that adds an option supplies its strings (`option_spacing`, `option_spacing_desc`, `privacy:metadata:preference:spacing`), the styles that act on `html[data-comfortmode-spacing="1"]`, and calls `options::add_admin_settings()`, `options::add_privacy_metadata()` and `options::export_user_preferences()` for its own component.

## Requirements

Moodle 5.0 to 5.3.

## Tests

```
vendor/bin/phpunit --testsuite local_comfortmode_testsuite
```

## Third-party fonts

Atkinson Hyperlegible (Braille Institute of America) and OpenDyslexic (Abbie Gonzalez), both under the SIL Open Font License 1.1; licence texts in `fonts/`, listed in `thirdpartylibs.xml`.

## Author

Biruh Alemnew <biruh@biruh.org>

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
