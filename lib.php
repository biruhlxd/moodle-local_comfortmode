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
 * Callbacks for local_comfortmode.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The user preferences that hold people's comfort settings.
 *
 * @return array[]
 */
function local_comfortmode_user_preferences(): array {
    return \local_comfortmode\options::preference_definitions();
}

/**
 * Add the Comfort Mode page to a person's own Preferences page.
 *
 * @param navigation_node $navigation
 * @param stdClass $user
 * @param context_user $usercontext
 * @param stdClass $course
 * @param context_course $coursecontext
 */
function local_comfortmode_extend_navigation_user_settings(
    navigation_node $navigation,
    $user,
    $usercontext,
    $course,
    $coursecontext
): void {
    global $USER;
    if (isguestuser() || (int) $user->id !== (int) $USER->id || !\local_comfortmode\options::enabled()) {
        return;
    }
    $parent = $navigation->find('useraccount', navigation_node::TYPE_CONTAINER) ?: $navigation;
    $parent->add(
        get_string('pluginname', 'local_comfortmode'),
        new moodle_url('/local/comfortmode/index.php'),
        navigation_node::TYPE_SETTING,
        null,
        'local_comfortmode'
    );
}
