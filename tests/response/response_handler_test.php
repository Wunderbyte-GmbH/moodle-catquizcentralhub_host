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
 * Tests for the response_handler class.
 *
 * @package    catquizcentralhub_host
 * @copyright  2024 Wunderbyte GmbH <info@wunderbyte.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace catquizcentralhub_host\response;

use advanced_testcase;

/**
 * Tests for response_handler.
 *
 * @package    catquizcentralhub_host
 * @covers     \catquizcentralhub_host\response\response_handler
 */
final class response_handler_test extends advanced_testcase {
    /** @var string reusable question hash */
    private const HASH = 'aabbccdd11223344aabbccdd11223344aabbccdd11223344aabbccdd11223344';
    /** @var int reusable attempt hash */
    private const ATTEMPT = 123456;
    /** @var string reusable source url */
    private const SOURCE = 'https://node.example.com';

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_store_response_returns_stored_for_new_record(): void {
        $result = response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $this->assertSame(response_handler::RESPONSE_STORED, $result);
    }

    public function test_store_response_inserts_db_record(): void {
        global $DB;
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $this->assertSame(1, $DB->count_records('local_catquiz_rresponses', ['questionhash' => self::HASH]));
    }

    public function test_store_response_returns_exists_for_duplicate(): void {
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $result = response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $this->assertSame(response_handler::RESPONSE_EXISTS, $result);
    }

    public function test_store_response_does_not_insert_duplicate(): void {
        global $DB;
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $this->assertSame(1, $DB->count_records('local_catquiz_rresponses', ['questionhash' => self::HASH]));
    }

    public function test_store_response_allows_same_hash_from_different_source(): void {
        global $DB;
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, 'https://node1.example.com');
        $result = response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, 'https://node2.example.com');
        $this->assertSame(response_handler::RESPONSE_STORED, $result);
        $this->assertSame(2, $DB->count_records('local_catquiz_rresponses', ['questionhash' => self::HASH]));
    }

    public function test_store_response_stores_fraction_in_response_field(): void {
        global $DB;
        response_handler::store_response(self::HASH, '0.75', self::ATTEMPT, self::SOURCE);
        $record = $DB->get_record('local_catquiz_rresponses', ['questionhash' => self::HASH]);
        $this->assertSame('0.75', $record->response);
    }

    public function test_store_response_leaves_timeprocessed_null(): void {
        global $DB;
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $record = $DB->get_record('local_catquiz_rresponses', ['questionhash' => self::HASH]);
        $this->assertNull($record->timeprocessed);
    }

    public function test_store_response_sets_timecreated(): void {
        global $DB;
        $before = time();
        response_handler::store_response(self::HASH, '0.5', self::ATTEMPT, self::SOURCE);
        $after = time();
        $record = $DB->get_record('local_catquiz_rresponses', ['questionhash' => self::HASH]);
        $this->assertGreaterThanOrEqual($before, $record->timecreated);
        $this->assertLessThanOrEqual($after, $record->timecreated);
    }

    public function test_response_stored_constant_is_nonzero(): void {
        $this->assertNotSame(0, response_handler::RESPONSE_STORED);
        $this->assertNotSame(0, response_handler::RESPONSE_EXISTS);
        $this->assertNotSame(response_handler::RESPONSE_STORED, response_handler::RESPONSE_EXISTS);
    }
}
