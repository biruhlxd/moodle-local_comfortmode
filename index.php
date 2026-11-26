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
 * The Comfort Mode page, where a person chooses their own comfort settings.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:ignore moodle.Files.RequireLogin.Missing -- visitors may use this page when the site offers it; require_login() below.
require_once(__DIR__ . '/../../config.php');

use local_comfortmode\options;

// Visitors (not logged in, or guests) can choose too when the site allows it; their choices are kept in a cookie.
$visitor = options::is_visitor();
if (!$visitor || !options::offered_to_visitors()) {
    require_login(null, false);
    if (isguestuser()) {
        throw new \moodle_exception('noguest');
    }
    $visitor = false;
}

$url = new moodle_url('/local/comfortmode/index.php');
$returnurl = $visitor ? new moodle_url('/') : new moodle_url('/user/preferences.php');
$title = get_string('pluginname', 'local_comfortmode');
$PAGE->set_url($url);
$PAGE->set_context($visitor ? context_system::instance() : context_user::instance($USER->id));
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading($visitor ? format_string($SITE->fullname) : fullname($USER));

$enabled = options::enabled();
if (!$enabled) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    echo $OUTPUT->notification(get_string('nooptions', 'local_comfortmode'), \core\output\notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    die();
}

$form = new \local_comfortmode\form\preferences_form($url);
$current = [];
foreach ($enabled as $option) {
    $current[$option->preference()] = $option->value();
}
$form->set_data($current);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $profile = array_key_first(array_intersect_key(options::PROFILES, (array) ($data->profiles ?? [])));
    if (!empty($data->resetbutton)) {
        options::reset_user();
        $message = get_string('resetdone', 'local_comfortmode');
    } else if ($profile !== null) {
        options::save(options::profile($profile));
        $message = get_string('profiledone', 'local_comfortmode', get_string('profile_' . $profile, 'local_comfortmode'));
    } else {
        options::save((array) $data);
        $message = get_string('saved', 'local_comfortmode');
    }
    redirect($url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo html_writer::tag('p', get_string($visitor ? 'intro_visitor' : 'intro', 'local_comfortmode'));
$form->display();
echo $OUTPUT->footer();
