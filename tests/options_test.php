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
        unset($_COOKIE[options::COOKIE]);
        options::reset();
        parent::tearDown();
    }

    public function test_nothing_is_set_by_default(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame(['textsize', 'font', 'easyread', 'contrast', 'reducemotion', 'focus'], array_keys(options::all()));
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

        $this->assertSame(['font', 'contrast', 'reducemotion', 'focus'], array_keys(options::enabled()));
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
                'local_comfortmode_font',
                'local_comfortmode_easyread',
                'local_comfortmode_contrast',
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

    public function test_new_options_reach_the_html_tag(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        options::save(['local_comfortmode_font' => 'dyslexic', 'local_comfortmode_contrast' => '1']);
        $this->assertSame(
            ['data-comfortmode-font' => 'dyslexic', 'data-comfortmode-contrast' => '1'],
            options::html_attributes()
        );
        options::save(['local_comfortmode_font' => 'comicsans']);
        $this->assertSame('dyslexic', get_user_preferences('local_comfortmode_font'));
    }

    public function test_a_profile_sets_its_values_and_leaves_the_others(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        options::save(['local_comfortmode_focus' => '1']);
        options::save(options::profile('lowvision'));
        $this->assertSame([
            'data-comfortmode-textsize' => '150',
            'data-comfortmode-font' => 'hyperlegible',
            'data-comfortmode-contrast' => '1',
            'data-comfortmode-focus' => '1',
        ], options::html_attributes());

        // Options the site does not offer are left out of a profile.
        set_config('enable_font', '0', 'local_comfortmode');
        options::reset();
        $this->assertSame(
            ['local_comfortmode_easyread' => '1', 'local_comfortmode_textsize' => '115'],
            options::profile('dyslexia')
        );
        $this->assertSame([], options::profile('nosuchprofile'));
    }

    public function test_visitors_choices_are_kept_in_their_cookie(): void {
        $this->setUser(null);
        options::save(['local_comfortmode_textsize' => '130', 'local_comfortmode_contrast' => '1']);
        $this->assertSame('local_comfortmode_textsize=130&local_comfortmode_contrast=1', $_COOKIE[options::COOKIE]);
        $this->assertSame(
            ['data-comfortmode-textsize' => '130', 'data-comfortmode-contrast' => '1'],
            options::html_attributes()
        );

        // A later choice adds to the earlier ones; a bad value in the cookie is ignored.
        options::save(['local_comfortmode_focus' => '1']);
        $this->assertCount(3, options::visitor_choices());
        $_COOKIE[options::COOKIE] .= '&local_comfortmode_font=comicsans';
        $this->assertArrayNotHasKey('data-comfortmode-font', options::html_attributes());

        // A guest is a visitor too, and the reset removes the cookie.
        $this->setGuestUser();
        $this->assertSame('1', options::html_attributes()['data-comfortmode-focus']);
        options::reset_user();
        $this->assertArrayNotHasKey(options::COOKIE, $_COOKIE);
        $this->assertSame([], options::html_attributes());
    }

    public function test_visitors_get_the_site_defaults_when_not_offered(): void {
        set_config('visitors', '0', 'local_comfortmode');
        set_config('default_textsize', '115', 'local_comfortmode');
        $this->setUser(null);
        $_COOKIE[options::COOKIE] = 'local_comfortmode_textsize=150';
        $this->assertSame(['data-comfortmode-textsize' => '115'], options::html_attributes());
        options::save(['local_comfortmode_focus' => '1']);
        $this->assertSame('local_comfortmode_textsize=150', $_COOKIE[options::COOKIE]);

        // A visitor's cookie never applies to a logged-in person.
        set_config('visitors', '1', 'local_comfortmode');
        $this->setUser($this->getDataGenerator()->create_user());
        $this->assertSame(['data-comfortmode-textsize' => '115'], options::html_attributes());
    }
}
