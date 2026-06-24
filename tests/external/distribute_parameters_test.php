<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests for the distribute_parameters external function.
 *
 * @package    catquizcentralhub_host
 * @copyright  2024 Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace catquizcentralhub_host\external;

use advanced_testcase;
use local_catquiz\hash\question_hasher;

/**
 * Tests for distribute_parameters external function.
 *
 * @package    catquizcentralhub_host
 * @covers     \catquizcentralhub_host\external\distribute_parameters
 */
final class distribute_parameters_test extends advanced_testcase {
    /** @var int admin user ID */
    private int $adminid;

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        global $USER;
        $this->adminid = $USER->id;
    }

    public function test_unknown_scale_label_returns_status_false(): void {
        $result = distribute_parameters::execute('nonexistent_label');
        $this->assertFalse($result['status']);
        $this->assertSame(0, $result['contextid']);
        $this->assertEmpty($result['parameters']);
    }

    public function test_result_contains_required_keys(): void {
        $result = distribute_parameters::execute('nonexistent_label');
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('contextid', $result);
        $this->assertArrayHasKey('parameters', $result);
    }

    public function test_scale_with_no_items_returns_empty_parameters_and_status_true(): void {
        global $DB;
        $contextid = $this->create_context();
        $DB->insert_record('local_catquiz_catscales', (object) [
            'name' => 'Empty Scale',
            'label' => 'emptyscale',
            'parentid' => 0,
            'contextid' => $contextid,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $result = distribute_parameters::execute('emptyscale');
        $this->assertTrue($result['status']);
        $this->assertEmpty($result['parameters']);
        $this->assertSame($contextid, $result['contextid']);
    }

    public function test_returns_parameter_for_item_in_scale(): void {
        $this->setup_scale_with_item();
        $result = distribute_parameters::execute('testscale');
        $this->assertTrue($result['status']);
        $this->assertCount(1, $result['parameters']);
        $param = $result['parameters'][0];
        // DB drivers may return numeric strings; assertEquals covers float/string equality.
        $this->assertEquals(1.5, $param['difficulty']);
        $this->assertSame('rasch', $param['model']);
        $this->assertSame('testscale', $param['scalelabel']);
    }

    public function test_parameter_entry_contains_required_fields(): void {
        $this->setup_scale_with_item();
        $result = distribute_parameters::execute('testscale');
        $param = $result['parameters'][0];
        $this->assertArrayHasKey('questionhash', $param);
        $this->assertArrayHasKey('model', $param);
        $this->assertArrayHasKey('difficulty', $param);
        $this->assertArrayHasKey('discrimination', $param);
        $this->assertArrayHasKey('guessing', $param);
        $this->assertArrayHasKey('status', $param);
        $this->assertArrayHasKey('scalelabel', $param);
    }

    public function test_filter_by_questionhash_includes_matching(): void {
        ['hash' => $hash] = $this->setup_scale_with_item();
        $result = distribute_parameters::execute('testscale', [$hash]);
        $this->assertCount(1, $result['parameters']);
    }

    public function test_filter_by_questionhash_excludes_nonmatching(): void {
        $this->setup_scale_with_item();
        $result = distribute_parameters::execute('testscale', ['0000000000000000000000000000000000000000000000000000000000000000']);
        $this->assertEmpty($result['parameters']);
    }

    public function test_filter_by_model_includes_matching(): void {
        $this->setup_scale_with_item();
        $result = distribute_parameters::execute('testscale', [], ['rasch']);
        $this->assertCount(1, $result['parameters']);
    }

    public function test_filter_by_model_excludes_nonmatching(): void {
        $this->setup_scale_with_item();
        $result = distribute_parameters::execute('testscale', [], ['2pl']);
        $this->assertEmpty($result['parameters']);
    }

    /**
     * Creates a context record and returns its ID.
     */
    private function create_context(): int {
        global $DB;
        return $DB->insert_record('local_catquiz_catcontext', (object) [
            'name' => 'Test Context',
            'usermodified' => $this->adminid,
            'timecreated' => time(),
            'timemodified' => time(),
            'timecalculated' => 0,
        ]);
    }

    /**
     * Creates a scale, question, and item parameter for execute() to return.
     *
     * @return array{contextid: int, scaleid: int, hash: string}
     */
    private function setup_scale_with_item(): array {
        global $DB;

        $contextid = $this->create_context();

        $scaleid = $DB->insert_record('local_catquiz_catscales', (object) [
            'name' => 'Test Scale',
            'label' => 'testscale',
            'parentid' => 0,
            'contextid' => $contextid,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        /** @var \core_question_generator $qgenerator */
        $qgenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $qcat = $qgenerator->create_question_category();
        $question = $qgenerator->create_question('shortanswer', null, ['category' => $qcat->id]);

        // Pre-generate the hash so filter tests can reference it.
        $hash = question_hasher::generate_hash($question->id);

        $itemid = $DB->insert_record('local_catquiz_items', (object) [
            'componentname' => 'question',
            'componentid' => $question->id,
            'catscaleid' => $scaleid,
            'contextid' => $contextid,
            'status' => 0,
        ]);

        $DB->insert_record('local_catquiz_itemparams', (object) [
            'itemid' => $itemid,
            'componentid' => $question->id,
            'componentname' => 'question',
            'contextid' => $contextid,
            'model' => 'rasch',
            'difficulty' => 1.5,
            'discrimination' => 0.0,
            'guessing' => 0.0,
            'json' => null,
            'status' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        return ['contextid' => $contextid, 'scaleid' => $scaleid, 'hash' => $hash];
    }
}
