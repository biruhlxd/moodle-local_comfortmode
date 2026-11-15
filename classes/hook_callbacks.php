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

namespace local_comfortmode;

use core\hook\output\before_html_attributes;
use core_user\hook\extend_user_menu;

/**
 * Hook callbacks: the attributes on the html tag and the link in the user menu.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Write the comfort settings in effect to the html tag.
     *
     * @param before_html_attributes $hook
     */
    public static function before_html_attributes(before_html_attributes $hook): void {
        if (during_initial_install()) {
            return;
        }
        foreach (options::html_attributes() as $name => $value) {
            $hook->add_attribute($name, $value);
        }
    }

    /**
     * Add the link to the Comfort Mode page to the user menu.
     *
     * @param extend_user_menu $hook
     */
    public static function extend_user_menu(extend_user_menu $hook): void {
        if (!isloggedin() || isguestuser() || !options::enabled()) {
            return;
        }
        $url = new \core\url('/local/comfortmode/index.php');
        $title = get_string('pluginname', options::COMPONENT);
        if (method_exists($hook, 'add_menu_item')) {
            $hook->add_menu_item(new \core_user\output\user_action_menu\link($url, $title));
            return;
        }
        // Before Moodle 5.3 the hook takes a plain object.
        $hook->add_navitem((object) [
            'itemtype' => 'link',
            'url' => $url,
            'title' => $title,
            'titleidentifier' => 'pluginname,' . options::COMPONENT,
        ]);
    }
}
