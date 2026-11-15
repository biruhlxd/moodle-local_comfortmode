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

/**
 * One comfort setting: a switch or a choice that a person sets for themselves.
 *
 * The value in effect is written to the html tag as data-comfortmode-{name}="{value}", and the styles of the plugin
 * that owns the option act on that attribute. This is what lets a setting work in any theme.
 *
 * The owning plugin supplies these strings: option_{name}, option_{name}_desc, privacy:metadata:preference:{name}
 * and, for a choice, option_{name}_{choice} for every choice.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class option {
    /** @var string A switch that is on ("1") or off ("0"). */
    const TYPE_TOGGLE = 'toggle';

    /** @var string One of several named choices. */
    const TYPE_CHOICE = 'choice';

    /** @var string[] The values of a switch. */
    const TOGGLE_CHOICES = ['0', '1'];

    /** @var string[] The values a person can pick. */
    public readonly array $choices;

    /** @var string The value that changes nothing on the page. It is also the site default until an admin sets one. */
    public readonly string $default;

    /**
     * Describe an option.
     *
     * @param string $component The plugin that owns the option, its strings and its styles.
     * @param string $name Lower case letters only.
     * @param string $type One of the TYPE_ constants.
     * @param string[] $choices For a choice: its values, lower case letters and digits. Ignored for a switch.
     * @param string $default For a choice: the value that changes nothing. Ignored for a switch.
     */
    public function __construct(
        /** @var string The plugin that owns the option. */
        public readonly string $component,
        /** @var string The name of the option. */
        public readonly string $name,
        /** @var string One of the TYPE_ constants. */
        public readonly string $type = self::TYPE_TOGGLE,
        array $choices = [],
        string $default = '',
    ) {
        if (!preg_match('/^[a-z]+$/', $name)) {
            throw new \coding_exception('Comfort Mode option names are lower case letters only: ' . $name);
        }
        if ($type === self::TYPE_TOGGLE) {
            $choices = self::TOGGLE_CHOICES;
            $default = '0';
        } else if ($type !== self::TYPE_CHOICE) {
            throw new \coding_exception('Unknown Comfort Mode option type: ' . $type);
        }
        $choices = array_map('strval', array_values($choices));
        foreach ($choices as $choice) {
            if (!preg_match('/^[a-z0-9]+$/', $choice)) {
                throw new \coding_exception('Comfort Mode choices are lower case letters and digits only: ' . $choice);
            }
        }
        if (!in_array($default, $choices, true)) {
            throw new \coding_exception('The default of the Comfort Mode option ' . $name . ' is not one of its choices');
        }
        $this->choices = $choices;
        $this->default = $default;
    }

    /**
     * The name of the user preference that holds a person's choice.
     *
     * @return string
     */
    public function preference(): string {
        return $this->component . '_' . $this->name;
    }

    /**
     * The name of the attribute on the html tag.
     *
     * @return string
     */
    public function attribute(): string {
        return 'data-comfortmode-' . $this->name;
    }

    /**
     * The name shown to people.
     *
     * @return string
     */
    public function label(): string {
        return get_string('option_' . $this->name, $this->component);
    }

    /**
     * One sentence on what the option does.
     *
     * @return string
     */
    public function description(): string {
        return get_string('option_' . $this->name . '_desc', $this->component);
    }

    /**
     * The name of one value, as shown to people.
     *
     * @param string $choice
     * @return string
     */
    public function choice_label(string $choice): string {
        if ($this->type === self::TYPE_TOGGLE) {
            return $choice === '1' ? get_string('yes') : get_string('no');
        }
        return get_string('option_' . $this->name . '_' . $choice, $this->component);
    }

    /**
     * Whether the site offers this option. It does until an admin switches it off.
     *
     * @return bool
     */
    public function is_enabled(): bool {
        $enabled = get_config($this->component, 'enable_' . $this->name);
        return $enabled === false || !empty($enabled);
    }

    /**
     * The value people get until they choose for themselves.
     *
     * @return string
     */
    public function site_default(): string {
        $default = (string) get_config($this->component, 'default_' . $this->name);
        return in_array($default, $this->choices, true) ? $default : $this->default;
    }

    /**
     * The value in effect for a person: their own choice, or else the site default.
     *
     * @param \stdClass|null $user Defaults to the current user.
     * @return string
     */
    public function value(?\stdClass $user = null): string {
        global $USER;
        if (!$this->is_enabled()) {
            return $this->default;
        }
        $user = $user ?? $USER;
        if (!empty($user->id) && !isguestuser($user)) {
            $choice = get_user_preferences($this->preference(), null, $user);
            if ($choice !== null && in_array((string) $choice, $this->choices, true)) {
                return (string) $choice;
            }
        }
        return $this->site_default();
    }
}
