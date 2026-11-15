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

namespace local_comfortmode\form;

use local_comfortmode\option;
use local_comfortmode\options;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * The form on the Comfort Mode page: one field for every option the site offers.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preferences_form extends \moodleform {
    /**
     * Define the fields.
     */
    protected function definition(): void {
        $mform = $this->_form;

        foreach (options::enabled() as $option) {
            $name = $option->preference();
            if ($option->type === option::TYPE_TOGGLE) {
                $mform->addElement('advcheckbox', $name, $option->label(), $option->description());
                $mform->setType($name, PARAM_BOOL);
                continue;
            }
            $choices = [];
            foreach ($option->choices as $choice) {
                $choices[$choice] = $option->choice_label($choice);
            }
            $mform->addElement('select', $name, $option->label(), $choices);
            $mform->setType($name, PARAM_ALPHANUM);
            $mform->addElement('static', $name . '_desc', '', $option->description());
        }

        $buttons = [
            $mform->createElement('submit', 'submitbutton', get_string('savechanges')),
            $mform->createElement('submit', 'resetbutton', get_string('reset', options::COMPONENT)),
            $mform->createElement('cancel'),
        ];
        $mform->addGroup($buttons, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
