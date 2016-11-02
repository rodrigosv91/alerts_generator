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
global $DB;

$messageid = $SESSION->block_alerts_generator_spvrs->id_msg_abs_spvrs; 
$absenceid = $SESSION->block_alerts_generator_spvrs->id_abs_spvrs; 
$course_id = $SESSION->block_alerts_generator_spvrs->course_id_abs_spvrs;

//$course_id = required_param('course_id', PARAM_INT); 

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

//$abs_count = -1;
//$msg_count = -1;	

$abs_count = $DB->count_records('block_alerts_generator_abs_s', array('id' => $absenceid));
$msg_count = $DB->count_records('block_alerts_generator_msg', array('id' => $messageid, 'courseid' => $course_id));

if($abs_count > 0 && $msg_count > 0 ){

	$DB->delete_records('block_alerts_generator_abs_s', array('id' => $absenceid));
	$DB->delete_records('block_alerts_generator_msg', array('id' => $messageid));
	$DB->delete_records('block_alerts_generator_abs_z', array('courseid' => $course_id));
	
	$abs_count = $DB->count_records('block_alerts_generator_abs_s', array('id' => $absenceid));
	$msg_count = $DB->count_records('block_alerts_generator_msg', array('id' => $messageid, 'courseid' => $course_id));

}
else{
	$abs_count = -1;
	$msg_count = -1;	
}

header('Content-type: application/json');

$mensagem = array( 'abs_count' => $abs_count, 'msg_count' => $msg_count ); //, 'msg_id' => $messageid, 'abs_id' => $absenceid
echo json_encode($mensagem);

?>