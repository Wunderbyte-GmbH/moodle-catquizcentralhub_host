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

namespace catquizcentralhub_host\repository;

use local_catquiz\catscale;

/**
 * Data access for remote responses stored by the central hub.
 *
 * @package    catquizcentralhub_host
 * @copyright  2024 Wunderbyte GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class remote_responses {
    /**
     * Count unprocessed remote responses for the given scales and context.
     *
     * @param array $catscaleids
     * @param int $contextid
     * @return int
     */
    public function count_unprocessed(array $catscaleids, int $contextid): int {
        global $DB;

        [$insql, $params] = $DB->get_in_or_equal($catscaleids, SQL_PARAMS_NAMED, 'incatscales');
        $params['contextid'] = $contextid;

        $sql = "SELECT COUNT(*)
            FROM {local_catquiz_rresponses} rr
            JOIN {local_catquiz_qhashmap} qh ON rr.questionhash = qh.questionhash
            JOIN {local_catquiz_items} lci ON lci.componentid = qh.questionid
            WHERE lci.catscaleid $insql
            AND lci.contextid = :contextid
            AND rr.timeprocessed IS NULL";

        return $DB->count_records_sql($sql, $params);
    }

    /**
     * Get remote responses for a main scale and its subscales.
     *
     * @param int $mainscale
     * @param int|null $contextid
     * @return array
     */
    public function get_for_scale(int $mainscale, ?int $contextid): array {
        global $DB;

        if (!$contextid) {
            $contextid = catscale::get_context_id($mainscale);
        }
        $subscales = catscale::get_subscale_ids($mainscale);
        $selectedscales = [$mainscale, ...$subscales];
        [$insql, $inparams] = $DB->get_in_or_equal($selectedscales, SQL_PARAMS_NAMED, 'selectedscales');

        $sql = "SELECT rr.id, rr.questionhash, attempthash, response
            FROM {local_catquiz_rresponses} rr
            JOIN {local_catquiz_qhashmap} qh ON rr.questionhash = qh.questionhash
            JOIN {local_catquiz_items} lci ON lci.componentid = qh.questionid AND lci.catscaleid $insql
            AND lci.contextid = :contextid";

        $params = array_merge(['contextid' => $contextid], $inparams);
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Mark remote responses as processed for the given scales and context.
     *
     * @param array $catscaleids
     * @param int $contextid
     * @return void
     */
    public function mark_processed(array $catscaleids, int $contextid): void {
        global $DB;

        [$insql, $params] = $DB->get_in_or_equal($catscaleids, SQL_PARAMS_NAMED, 'incatscales');
        $params['contextid'] = $contextid;
        $params['now'] = time();
        $params['info'] = json_encode(['status' => 'success', 'contextid' => $contextid]);

        $DB->execute(
            "UPDATE {local_catquiz_rresponses}
            SET timeprocessed = :now, processinginfo = :info
            WHERE questionhash IN (
                SELECT qh.questionhash
                FROM {local_catquiz_qhashmap} qh
                JOIN {local_catquiz_items} lci ON lci.componentid = qh.questionid
                WHERE lci.catscaleid $insql
                AND lci.contextid = :contextid
        )
        AND timeprocessed IS NULL",
            $params
        );
    }
}
