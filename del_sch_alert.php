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

require('../../config.php');
global $DB, $USER;

$ag_sched_alert_id = required_param('ag_sched_alert_id', PARAM_INT); 
$msg_id = required_param('msg_id', PARAM_INT); 
$course_id = required_param('course_id', PARAM_INT);

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$DB->delete_records('block_alerts_generator_sch_a', array('id' => $ag_sched_alert_id));

$DB->delete_records('block_alerts_generator_msg', array('id' => $msg_id));

$mensagem = "Mensagem Deletada";
echo json_encode($mensagem);

?>