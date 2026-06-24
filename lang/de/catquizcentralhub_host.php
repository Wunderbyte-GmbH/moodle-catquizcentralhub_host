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
 * German language strings for catquizcentralhub_host.
 *
 * @package     catquizcentralhub_host
 * @copyright   2024 Wunderbyte GmbH <info@wunderbyte.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['centralscalelabels'] = 'Zentrale Skalenbezeichnungen';
$string['centralscalelabelsdesc'] = 'Geben Sie pro Zeile eine Skalenbezeichnung ein. Nur diese Skalen werden von dieser Hub-Instanz verwaltet.';
$string['enablesyncashub'] = 'Instanz fungiert als Hub';
$string['enablesyncashubdesc'] = 'Wenn aktiviert, können Client-Nodes Antworten einreichen und berechnete Item-Parameter von dieser Instanz abrufen.';
$string['pluginname'] = 'CatQuiz Zentraler Hub (Host)';
$string['questionnotfound'] = 'Die Frage wurde nicht gefunden';
$string['responses_added'] = 'Neue Antworten wurden hinzugefügt';
$string['responses_added_desc'] = '{$a->sourceurl} hat neue Antworten übermittelt. {$a->added} neue Antworten wurden hinzugefügt, '
    . '{$a->skipped} übersprungen und {$a->errors} Fehler sind aufgetreten';
$string['taskqueued'] = 'Die entfernte Berechnungsaufgabe wurde in die Warteschlange aufgenommen und wird in Kürze ausgeführt.';
