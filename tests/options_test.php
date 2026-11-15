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
use local_comfortmode\hook\collect_options;

/**
 * Tests for the comfort settings: the value in effect, the html attributes, saving, and options from other plugins.
 *
 * @package    local_comfortmode
 * @copyright  2026 Biruh Alemnew <biruh@biruh.org>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(options::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(option::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(collect_options::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(privacy\provider::class)]
final class options_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        options::reset();
    }

    protected function tearDown(): void {
        options::reset();
        parent::tearDown();
    }

    public function test_nothing_is_set_by_default(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame(['textsize', 'easyread', 'reducemotion', 'focus'], array_keys(options::all()));
        $this->assertSame([], options::html_attributes());
    }

    public function test_own_choices_reach_the_html_tag(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        options::save([
            'local_comfortmode_textsize' => '130',
            'local_comfortmode_easyread' => 1,
            'local_comfortmode_focus' => 0,
        ]);
        $this->assertSame(
            ['data-comfortmode-textsize' => '130', 'data-comfortmode-easyread' => '1'],
            options::html_attributes()
        );
    }

    public function test_values_that_are_not_choices_are_not_saved(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        options::save(['local_comfortmode_textsize' => '999', 'local_comfortmode_easyread' => 'yes']);
        $this->assertNull(get_user_preferences('local_comfortmode_textsize'));
        $this->assertNull(get_user_preferences('local_comfortmode_easyread'));
        $this->assertSame([], options::html_attributes());
    }

    public function test_site_default_applies_until_a_person_chooses(): void {
        set_config('default_textsize', '115', 'local_comfortmode');
        set_config('default_reducemotion', '1', 'local_comfortmode');
        $expected = ['data-comfortmode-textsize' => '115', 'data-comfortmode-reducemotion' => '1'];

        // Not logged in, and a guest.
        $this->setUser(null);
        $this->assertSame($expected, options::html_attributes());
        $this->setGuestUser();
        $this->assertSame($expected, options::html_attributes());

        // A person's own choice wins, including the choice to switch a default off.
        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame($expected, options::html_attributes());
        options::save(['local_comfortmode_textsize' => '100', 'local_comfortmode_reducemotion' => '0']);
        $this->assertSame([], options::html_attributes());

        // Back to the site defaults.
        options::reset_user();
        $this->assertSame($expected, options::html_attributes());
    }

    public function test_an_option_the_site_does_not_offer_has_no_effect(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        options::save(['local_comfortmode_easyread' => '1', 'local_comfortmode_focus' => '1']);
        set_config('default_textsize', '130', 'local_comfortmode');
        set_config('enable_easyread', '0', 'local_comfortmode');
        set_config('enable_textsize', '0', 'local_comfortmode');

        $this->assertSame(['reducemotion', 'focus'], array_keys(options::enabled()));
        $this->assertSame(['data-comfortmode-focus' => '1'], options::html_attributes());

        // Saving leaves it alone too.
        options::save(['local_comfortmode_easyread' => '0']);
        $this->assertSame('1', get_user_preferences('local_comfortmode_easyread'));
    }

    public function test_another_plugin_can_add_an_option(): void {
        $this->redirectHook(collect_options::class, function (collect_options $hook): void {
            $hook->add_option(new option('local_comfortmode', 'scheme', option::TYPE_CHOICE, ['light', 'dark'], 'light'));
            // A name that is taken is left alone.
            $hook->add_option(new option('local_comfortmode', 'focus', option::TYPE_CHOICE, ['a', 'b'], 'a'));
        });
        options::reset();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertSame(option::TYPE_TOGGLE, options::all()['focus']->type);
        $this->assertArrayHasKey('local_comfortmode_scheme', options::preference_definitions());
        options::save(['local_comfortmode_scheme' => 'dark']);
        $this->assertSame(['data-comfortmode-scheme' => 'dark'], options::html_attributes());
    }

    public function test_preferences_are_declared_to_moodle(): void {
        global $CFG;
        require_once($CFG->dirroot . '/local/comfortmode/lib.php');
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $definitions = local_comfortmode_user_preferences();
        $this->assertSame(
            [
                'local_comfortmode_textsize',
                'local_comfortmode_easyread',
                'local_comfortmode_reducemotion',
                'local_comfortmode_focus',
            ],
            array_keys($definitions)
        );
        $this->assertSame(['100', '115', '130', '150'], $definitions['local_comfortmode_textsize']['choices']);
        $this->assertTrue(\core_user::can_edit_preference('local_comfortmode_textsize', $user));
        $this->assertSame('130', \core_user::clean_preference('130', 'local_comfortmode_textsize'));
    }

    public function test_bad_option_definitions_are_refused(): void {
        $this->expectException(\coding_exception::class);
        new option('local_comfortmode', 'text-size');
    }

    public function test_privacy_export_holds_the_choices_made(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        options::save(['local_comfortmode_textsize' => '115', 'local_comfortmode_focus' => '1']);

        privacy\provider::export_user_preferences($user->id);
        $exported = writer::with_context(\context_system::instance())->get_user_preferences('local_comfortmode');

        $this->assertSame('115', $exported->local_comfortmode_textsize->value);
        $this->assertSame('Your choice for "Text size" is "Large".', $exported->local_comfortmode_textsize->description);
        $this->assertSame('1', $exported->local_comfortmode_focus->value);
        $this->assertObjectNotHasProperty('local_comfortmode_easyread', $exported);
    }
}
