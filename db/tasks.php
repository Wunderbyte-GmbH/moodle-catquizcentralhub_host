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
 * Task definitions for the catquizcentralhub_host subplugin.
 *
 * The adhoc_recalculate_remote_item_parameters task is an adhoc task and
 * does not require a scheduled task entry here.
 *
 * @package     catquizcentralhub_host
 * @copyright   2024 Wunderbyte GmbH <info@wunderbyte.at>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => \catquizcentralhub_host\task\recalculate_remote_item_parameters::class,
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '1',
        'month' => '*',
        'dayofweek' => '*',
        'disabled' => 0,
    ],
];
