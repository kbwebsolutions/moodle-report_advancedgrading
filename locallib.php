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
 * Lang strings.
 *
 * Language strings to be used by report/rubrics
 *
 * @package    report_rubrics
 * @copyright  2021 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;

/**
 * Get all students given the course/module details
 * @param \context_module $modcontext
 * @param \stdClass $cm
 * @return array
 */
function report_componentgrades_get_students($modcontext, $cm) :array {
    global $DB;
    $assign = new assign($modcontext, $cm, $cm->course);
    $params = ['courseid' => $cm->course];
    $sql = 'SELECT stu.id AS userid, stu.idnumber AS idnumber, stu.firstname, stu.lastname,
                   stu.username AS username
              FROM {user} stu
              JOIN {user_enrolments} ue
                ON ue.userid = stu.id
              JOIN {enrol} enr
                ON ue.enrolid = enr.id
             WHERE enr.courseid = :courseid
          ORDER BY lastname ASC, firstname ASC, userid ASC';
         $users = $DB->get_records_sql($sql, $params);

    if ($assign->is_blind_marking()) {
        foreach ($users as &$user) {
            $user->firstname = '';
            $user->lastname = '';
            $user->student = get_string('participant', 'assign') .
             ' ' . \assign::get_uniqueid_for_user_static($cm->instance, $user->userid);
        }
    }
    return $users;
}
function get_grading_definition(int $assignid) {
    global $DB;
    $sql = "SELECT ga.activemethod, gdef.name as definition from {assign} assign
            JOIN {course_modules} cm ON cm.instance = assign.id
            JOIN {context} ctx ON ctx.instanceid = cm.id
            JOIN {grading_areas} ga ON ctx.id=ga.contextid
            JOIN {grading_definitions} gdef ON ga.id = gdef.areaid
            WHERE assign.id = :assignid";
    $definition = $DB->get_record_sql($sql, ['assignid' => $assignid]);
    return $definition;
}
/**
 * Add header text to report, name of course etc

 */
function report_advancedgrading_get_header($coursename, $assignmentname, $method, $definition) {

    $cells[]  = [
        'row' => 0,
        'col' => 0,
        'value' => $coursename
    ];
    $cells[]  = [
        'row' => 1,
        'col' => 0,
        'value' => $assignmentname
    ];
    $cells[]  = [
        'row' => 2,
        'col' => 0,
        'value' => get_string($method, 'report_advancedgrading').":"
    ];
    $cells[]  = [
        'row' => 2,
        'col' => 1,
        'value' => $definition
    ];
    return $cells;

    // $sheet->write_string(1, 0, $modname, $format);
    // $methodname = ($method == 'rubric' ? 'Rubric: ' : 'Marking guide: ') . $methodname;
    // $sheet->write_string(2, 0, $methodname, $format);

    // $sheet->write_string(HEADINGSROW, 0, get_string('student', 'report_componentgrades'), $format);
    // $sheet->merge_cells(HEADINGSROW, 0, HEADINGSROW, 2, $format);
    // $sheet->write_string(5, $col++, get_string('firstname', 'report_componentgrades'), $format2);
    // $sheet->write_string(5, $col++, get_string('lastname', 'report_componentgrades'), $format2);
    // $sheet->write_string(5, $col++, get_string('username', 'report_componentgrades'), $format2);
    // if (get_config('report_componentgrades', 'showstudentid')) {
    //     $sheet->write_string(5, $col, get_string('studentid', 'report_componentgrades'), $format2);
    //     $col++;
    // }
    // return $col;

}

class cell {
    private $text = "";
    public function get_text() :string {
        return $this->text;
    }
    public function set_text(string $textvalue) {
        $this->text = $textvalue;
    }


}
