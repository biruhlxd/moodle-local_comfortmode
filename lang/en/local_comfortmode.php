<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * English strings for local_comfortmode.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['intro'] = 'Choose how this site looks and feels for you. Your choices follow you to every device you log in from.';
$string['intro_visitor'] = 'Choose how this site looks and feels for you. Your choices are kept in this browser.';
$string['nooptions'] = 'The site administrator has switched off all comfort settings.';
$string['option_contrast'] = 'High contrast';
$string['option_contrast_desc'] = 'Black text on white (white on black in dark mode), stronger borders, underlined links and a clearly visible focus.';
$string['option_easyread'] = 'Easier reading';
$string['option_easyread_desc'] = 'A plain typeface with more space between letters, words and lines, and shorter lines of text.';
$string['option_focus'] = 'Focus mode';
$string['option_focus_desc'] = 'Hides the side panels and the footer and narrows the page, so only the content is left.';
$string['option_font'] = 'Typeface';
$string['option_font_desc'] = 'A typeface made to be easier to read. It is used for all text on the site.';
$string['option_font_dyslexic'] = 'OpenDyslexic, for dyslexia';
$string['option_font_hyperlegible'] = 'Atkinson Hyperlegible, for low vision';
$string['option_font_standard'] = 'The site\'s own';
$string['option_reducemotion'] = 'Reduce motion';
$string['option_reducemotion_desc'] = 'Stops animations and sliding effects.';
$string['option_textsize'] = 'Text size';
$string['option_textsize_100'] = 'Standard';
$string['option_textsize_115'] = 'Large';
$string['option_textsize_130'] = 'Larger';
$string['option_textsize_150'] = 'Largest';
$string['option_textsize_desc'] = 'Makes all text on the site larger.';
$string['ownchoices'] = 'Your own choices';
$string['pluginname'] = 'Comfort Mode';
$string['privacy:metadata:preference:contrast'] = 'Whether the user chose high contrast.';
$string['privacy:metadata:preference:easyread'] = 'Whether the user chose easier reading.';
$string['privacy:metadata:preference:focus'] = 'Whether the user chose focus mode.';
$string['privacy:metadata:preference:font'] = 'The typeface the user chose.';
$string['privacy:metadata:preference:reducemotion'] = 'Whether the user chose to reduce motion.';
$string['privacy:metadata:preference:textsize'] = 'The text size the user chose.';
$string['privacy:preference'] = 'Your choice for "{$a->label}" is "{$a->value}".';
$string['profile_concentration'] = 'Concentration';
$string['profile_dyslexia'] = 'Dyslexia';
$string['profile_lowvision'] = 'Low vision';
$string['profiledone'] = 'The {$a} settings are on. You can change each of them below.';
$string['profiles'] = 'Quick start';
$string['profiles_desc'] = 'Turn on a set of settings in one click, then adjust them below. Dyslexia: OpenDyslexic, easier reading and large text. Low vision: the largest text, Atkinson Hyperlegible and high contrast. Concentration: focus mode and reduced motion.';
$string['reset'] = 'Use the site defaults';
$string['resetdone'] = 'Your comfort settings are back to the site defaults.';
$string['saved'] = 'Your comfort settings are saved.';
$string['setting_default'] = 'Site default for "{$a}"';
$string['setting_default_desc'] = 'What people get until they choose for themselves. It also applies to guests and to pages shown before login.';
$string['setting_enable'] = 'Offer "{$a}"';
$string['setting_enable_desc'] = 'When ticked, people can choose this setting for themselves. When not ticked, it is hidden and has no effect.';
$string['setting_visitors'] = 'Offer to visitors';
$string['setting_visitors_desc'] = 'People who are not logged in, and guests, can choose comfort settings too, from a Comfort Mode button in the bottom corner of every page. Their choices are kept in a cookie in their own browser (MoodleComfortMode), not on the site.';
