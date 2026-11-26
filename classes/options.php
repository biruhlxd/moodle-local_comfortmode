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

use core_privacy\local\request\writer;

/**
 * The list of comfort settings: the ones in this plugin plus any that other plugins add through the collect_options hook.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class options {
    /** @var string */
    const COMPONENT = 'local_comfortmode';

    /** @var string The cookie that holds the choices of visitors who are not logged in. */
    const COOKIE = 'MoodleComfortMode';

    /** @var array[] One-click profiles: values by option name. Options the site does not offer are left out. */
    const PROFILES = [
        'dyslexia' => ['font' => 'dyslexic', 'easyread' => '1', 'textsize' => '115'],
        'lowvision' => ['textsize' => '150', 'font' => 'hyperlegible', 'contrast' => '1'],
        'concentration' => ['focus' => '1', 'reducemotion' => '1'],
    ];

    /** @var option[]|null Options by name, collected once per request. */
    private static ?array $options = null;

    /**
     * Every option, whether the site offers it or not.
     *
     * @return option[] By name.
     */
    public static function all(): array {
        if (self::$options === null) {
            $hook = new hook\collect_options();
            $hook->add_option(new option(self::COMPONENT, 'textsize', option::TYPE_CHOICE, ['100', '115', '130', '150'], '100'));
            $hook->add_option(new option(
                self::COMPONENT,
                'font',
                option::TYPE_CHOICE,
                ['standard', 'hyperlegible', 'dyslexic'],
                'standard'
            ));
            $hook->add_option(new option(self::COMPONENT, 'easyread'));
            $hook->add_option(new option(self::COMPONENT, 'contrast'));
            $hook->add_option(new option(self::COMPONENT, 'reducemotion'));
            $hook->add_option(new option(self::COMPONENT, 'focus'));
            \core\di::get(\core\hook\manager::class)->dispatch($hook);
            self::$options = $hook->get_options();
        }
        return self::$options;
    }

    /**
     * The options the site offers.
     *
     * @return option[] By name.
     */
    public static function enabled(): array {
        return array_filter(self::all(), fn(option $option): bool => $option->is_enabled());
    }

    /**
     * Whether a person is a visitor: not logged in, or logged in as guest. Visitors' choices are kept in a cookie.
     *
     * @param \stdClass|null $user Defaults to the current user.
     * @return bool
     */
    public static function is_visitor(?\stdClass $user = null): bool {
        global $USER;
        $user = $user ?? $USER;
        return empty($user->id) || isguestuser($user);
    }

    /**
     * Whether visitors can choose comfort settings too.
     *
     * @return bool
     */
    public static function offered_to_visitors(): bool {
        $setting = get_config(self::COMPONENT, 'visitors');
        return $setting === false || !empty($setting);
    }

    /**
     * The choices of the visitor making this request, from the cookie. They are checked against the choices of each
     * option when read.
     *
     * @return string[] Values by preference name.
     */
    public static function visitor_choices(): array {
        $raw = $_COOKIE[self::COOKIE] ?? '';
        if (!is_string($raw) || $raw === '' || strlen($raw) > 1000) {
            return [];
        }
        parse_str($raw, $values);
        return array_filter($values, fn($value, $name): bool => is_string($name) && is_string($value), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Keep the choices of the visitor making this request in the cookie, for a year. No choices removes the cookie.
     *
     * @param string[] $values Values by preference name.
     */
    private static function set_visitor_choices(array $values): void {
        global $CFG;
        $text = http_build_query($values);
        if ($values) {
            $_COOKIE[self::COOKIE] = $text;
        } else {
            unset($_COOKIE[self::COOKIE]);
        }
        if ((defined('PHPUNIT_TEST') && PHPUNIT_TEST) || headers_sent()) {
            return;
        }
        setcookie(self::COOKIE, $text, [
            'expires' => $values ? time() + YEARSECS : time() - HOURSECS,
            'path' => empty($CFG->sessioncookiepath) ? '/' : $CFG->sessioncookiepath,
            'domain' => $CFG->sessioncookiedomain ?? '',
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * The values of a one-click profile, for the options the site offers.
     *
     * @param string $profile One of the keys of PROFILES.
     * @return string[] Values by preference name.
     */
    public static function profile(string $profile): array {
        $enabled = self::enabled();
        $values = [];
        foreach (self::PROFILES[$profile] ?? [] as $name => $value) {
            if (isset($enabled[$name]) && $enabled[$name]->component === self::COMPONENT) {
                $values[$enabled[$name]->preference()] = $value;
            }
        }
        return $values;
    }

    /**
     * Forget the collected options, so the next call collects them again. For tests.
     */
    public static function reset(): void {
        self::$options = null;
    }

    /**
     * The attributes for the html tag: one for every option whose value changes the page.
     *
     * @param \stdClass|null $user Defaults to the current user.
     * @return string[] Values by attribute name.
     */
    public static function html_attributes(?\stdClass $user = null): array {
        $attributes = [];
        foreach (self::all() as $option) {
            $value = $option->value($user);
            if ($value !== $option->default) {
                $attributes[$option->attribute()] = $value;
            }
        }
        return $attributes;
    }

    /**
     * The user preferences that hold people's choices, in the form Moodle's user_preferences callback returns.
     *
     * @return array[] Definitions by preference name.
     */
    public static function preference_definitions(): array {
        $definitions = [];
        foreach (self::all() as $option) {
            $definitions[$option->preference()] = [
                'type' => PARAM_ALPHANUM,
                'null' => NULL_NOT_ALLOWED,
                'default' => $option->default,
                'choices' => $option->choices,
                'permissioncallback' => [\core_user::class, 'is_current_user'],
            ];
        }
        return $definitions;
    }

    /**
     * Store a person's choices. Values that are not one of an option's choices are left out.
     *
     * @param array $values Values by preference name.
     * @param \stdClass|null $user Defaults to the current user.
     */
    public static function save(array $values, ?\stdClass $user = null): void {
        $visitor = self::is_visitor($user);
        if ($visitor && !self::offered_to_visitors()) {
            return;
        }
        $choices = $visitor ? self::visitor_choices() : [];
        foreach (self::enabled() as $option) {
            $value = $values[$option->preference()] ?? null;
            if ($value === null || !in_array((string) $value, $option->choices, true)) {
                continue;
            }
            if ($visitor) {
                $choices[$option->preference()] = (string) $value;
            } else {
                set_user_preference($option->preference(), (string) $value, $user);
            }
        }
        if ($visitor) {
            self::set_visitor_choices($choices);
        }
    }

    /**
     * Forget a person's choices, so they get the site defaults again.
     *
     * @param \stdClass|null $user Defaults to the current user.
     */
    public static function reset_user(?\stdClass $user = null): void {
        if (self::is_visitor($user)) {
            self::set_visitor_choices([]);
            return;
        }
        foreach (self::all() as $option) {
            unset_user_preference($option->preference(), $user);
        }
    }

    /**
     * Add the "offer" and "site default" settings of one plugin's options to its admin settings page.
     *
     * @param \admin_settingpage $page
     * @param string $component The plugin whose options to add.
     */
    public static function add_admin_settings(\admin_settingpage $page, string $component): void {
        foreach (self::all() as $option) {
            if ($option->component !== $component) {
                continue;
            }
            $label = $option->label();
            $page->add(new \admin_setting_heading(
                $component . '/heading_' . $option->name,
                $label,
                $option->description()
            ));
            $page->add(new \admin_setting_configcheckbox(
                $component . '/enable_' . $option->name,
                get_string('setting_enable', self::COMPONENT, $label),
                get_string('setting_enable_desc', self::COMPONENT),
                1
            ));
            $name = $component . '/default_' . $option->name;
            $title = get_string('setting_default', self::COMPONENT, $label);
            $description = get_string('setting_default_desc', self::COMPONENT);
            if ($option->type === option::TYPE_TOGGLE) {
                $page->add(new \admin_setting_configcheckbox($name, $title, $description, 0));
            } else {
                $choices = [];
                foreach ($option->choices as $choice) {
                    $choices[$choice] = $option->choice_label($choice);
                }
                $page->add(new \admin_setting_configselect($name, $title, $description, $option->default, $choices));
            }
        }
    }

    /**
     * Declare one plugin's preferences to the privacy subsystem.
     *
     * @param \core_privacy\local\metadata\collection $collection
     * @param string $component The plugin whose options to declare.
     */
    public static function add_privacy_metadata(\core_privacy\local\metadata\collection $collection, string $component): void {
        foreach (self::all() as $option) {
            if ($option->component === $component) {
                $collection->add_user_preference($option->preference(), 'privacy:metadata:preference:' . $option->name);
            }
        }
    }

    /**
     * Export one plugin's preferences of a person for a privacy request.
     *
     * @param int $userid
     * @param string $component The plugin whose options to export.
     */
    public static function export_user_preferences(int $userid, string $component): void {
        foreach (self::all() as $option) {
            if ($option->component !== $component) {
                continue;
            }
            $value = get_user_preferences($option->preference(), null, $userid);
            if ($value === null || !in_array((string) $value, $option->choices, true)) {
                continue;
            }
            $description = get_string('privacy:preference', self::COMPONENT, (object) [
                'label' => $option->label(),
                'value' => $option->choice_label((string) $value),
            ]);
            writer::export_user_preference($component, $option->preference(), (string) $value, $description);
        }
    }
}
