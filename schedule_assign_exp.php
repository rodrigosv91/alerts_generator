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
global $DB, $COURSE, $USER;

$course_id = required_param('course_id', PARAM_INT); 
$days = required_param('days', PARAM_INT); 
$hm_time = required_param('hm_time', PARAM_TEXT); 
$assign_id = required_param('assign_id', PARAM_INT);
$subject = required_param('subject', PARAM_TEXT);
$messagetext = required_param('texto', PARAM_TEXT);
$messagehtml = $messagetext;

$customized = required_param('customized', PARAM_INT);

sscanf($hm_time, "%d:%d:", $hours, $minutes);
$alert_time =  $days*86400 + $hours * 3600 + $minutes * 60 ;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$messageid  = -1;
$ag_assign = -1; 

$recordmsg = new stdClass();
$recordmsg->fromid = $USER->id;
$recordmsg->subject = $subject;
$recordmsg->message = $messagetext;
$recordmsg->courseid = $course_id;

$recordmsg->customized = $customized;

//$recordmsg->timecreated = time();
$messageid = $DB->insert_record('block_alerts_generator_msg', $recordmsg, true);


$recordassign = new stdClass();
$recordassign->assignid = $assign_id;
$recordassign->messageid = $messageid;
$recordassign->alerttime = $alert_time;
$recordassign->sent = 0;
$ag_assign = $DB->insert_record('block_alerts_generator_assig', $recordassign, true);


header('Content-type: application/json');
$mensagem = array('ag_assign' => $ag_assign, 'msg_id' => $messageid );
echo json_encode($mensagem);

?>