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
 * Exports an Excel spreadsheet of the  grades in a Pass Fail Rubric-graded assignment.
 *
 * @package    report_advancedgrading
 * @copyright  2022 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '../../../config.php');
require_once(__DIR__ . '/../../report/advancedgrading/locallib.php');
require_once(__DIR__ . '/../../lib/excellib.class.php');
require_once(__DIR__ . '/../../grade/lib.php');

$data['courseid'] = required_param('id', PARAM_INT); // Course ID.
require_login($data['courseid']);
use report_advancedgrading\pfrbric;

$dload = optional_param("dload", '', PARAM_BOOL);

$data['headerstyle'] = 'style="background-color:#D2D2D2;"';
$data['reportname'] = get_string('rubricreportname', 'report_advancedgrading');
$data['grademethod'] = 'pfrbric';
$data['modid'] = required_param('modid', PARAM_INT); // CM ID.

$data = init($data);
$cfg  = get_config('report_advancedgrading');
require_capability('mod/assign:grade', $data['context']);

$rubric = new pfrbric();
$data['dbrecords'] = $rubric->get_data($data['assign'], $data['cm']);

$data = user_fields($data, $data['dbrecords']);
if (isset($data['students'])) {
    $data = add_groups($data, $data['courseid']);
    $data = get_grades($data, $data['dbrecords']);
}

$form = $OUTPUT->render_from_template('report_advancedgrading/form', $data);

// Each rubric criteria has a score,and feedback column.
$data['colcount'] += count($data['criteria']) * 2;
$data['rows'] = $rubric->get_rows($data);
$table = $OUTPUT->render_from_template('report_advancedgrading/pfrbric', $data);

send_output($form, $dload, $data, $table);