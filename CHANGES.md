# Changes

## 1.1.0 (2026-11-26)

- Typeface: Atkinson Hyperlegible for low vision or OpenDyslexic for dyslexia, bundled with the plugin.
- High contrast: black on white (white on black in dark mode), underlined links, stronger borders and focus ring. It also
  overrules themes that colour text directly, such as Academi.
- Quick start profiles: Dyslexia, Low vision and Concentration, each a set of settings in one click.
- Visitors who are not logged in, and guests, can choose comfort settings too; their choices are kept in a cookie in their
  browser. A link to the page is in the footer. New setting *Offer to visitors* (on by default).
- Tested with Moove and Academi on Moodle 5.3.

## 1.0.0 (2026-11-15)

First stable version.

- Focus mode keeps the site menu on small screens, and also hides the block columns in Classic.
- Easier reading shortens lines of text only; course sections and other lists keep their full width.
- Reduce motion no longer stops scripts that wait for an animation to end.
- Behat tests.

## 0.1.0 (2026-11-15)

First version, beta.

- Text size, easier reading, reduce motion and focus mode, chosen by each person on the Comfort Mode page.
- Link in the user menu and on the Preferences page.
- Admin settings to offer or hide each setting and to set a site default.
- `collect_options` hook for settings added by other plugins.
