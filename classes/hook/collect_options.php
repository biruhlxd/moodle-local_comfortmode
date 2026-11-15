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

namespace local_comfortmode\hook;

use local_comfortmode\option;

/**
 * Lets other plugins add their own comfort settings.
 *
 * An added option appears on the Comfort Mode page and its value is written to the html tag. The plugin that adds it
 * supplies the strings, the styles that act on the attribute, the admin settings (options::add_admin_settings) and
 * the privacy declarations (options::add_privacy_metadata and options::export_user_preferences).
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Lets other plugins add their own settings to Comfort Mode')]
#[\core\attribute\tags('user', 'output')]
final class collect_options {
    /** @var option[] Options by name. */
    private array $options = [];

    /**
     * Add an option. The first option with a given name wins.
     *
     * @param option $option
     */
    public function add_option(option $option): void {
        if (!isset($this->options[$option->name])) {
            $this->options[$option->name] = $option;
        }
    }

    /**
     * The options added so far.
     *
     * @return option[] By name.
     */
    public function get_options(): array {
        return $this->options;
    }
}
