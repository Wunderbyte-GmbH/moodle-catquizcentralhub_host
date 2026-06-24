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
 * Web service definitions for the catquizcentralhub_host subplugin.
 *
 * @package catquizcentralhub_host
 * @category external
 * @copyright 2024 Wunderbyte GmbH (info@wunderbyte.at)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$services = [
    'CatQuiz Central Hub Service' => [
        'functions' => [
            'catquizcentralhub_host_collect_responses',
            'catquizcentralhub_host_distribute_parameters',
            'catquizcentralhub_host_enqueue_parameter_recalculation',
        ],
        'restrictedusers' => 0,
        'enabled' => 1,
        'shortname' => 'catquizcentralhub_host_service',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ],
];

$functions = [
    'catquizcentralhub_host_collect_responses' => [
        'classname' => 'catquizcentralhub_host\\external\\collect_responses',
        'methodname' => 'execute',
        'description' => 'Collects new responses from a client node',
        'type' => 'write',
        'capabilities' => 'moodle/site:config',
        'ajax' => true,
    ],
    'catquizcentralhub_host_distribute_parameters' => [
        'classname' => 'catquizcentralhub_host\\external\\distribute_parameters',
        'methodname' => 'execute',
        'description' => 'Allows client nodes to fetch calculated item parameters',
        'type' => 'write',
        'capabilities' => 'moodle/site:config',
        'ajax' => true,
    ],
    'catquizcentralhub_host_enqueue_parameter_recalculation' => [
        'classname' => 'catquizcentralhub_host\\external\\enqueue_parameter_recalculation',
        'methodname' => 'execute',
        'description' => 'Enqueue an adhoc task to recalculate the parameters based on submitted responses',
        'type' => 'write',
        'capabilities' => 'moodle/site:config',
        'ajax' => true,
    ],
];
