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
$course_id = required_param('id', PARAM_INT);
global $DB, $COURSE, $USER;
//$PAGE->set_context();
//require_once($CFG->dirroot.'/lib/moodlelib.php');

//$context = get_context_instance( CONTEXT_COURSE, $COURSE->id );  
	
$query = 'SELECT id, name, duedate FROM mdl_assign WHERE course =' . $course_id . ' and duedate > UNIX_TIMESTAMP(NOW()) ORDER BY duedate';

//$duedate = $DB->get_record('assign', array('course'=>$course_id));
				
$result = $DB->get_recordset_sql( $query );
		
//foreach ($result  as  $rs) {
//	echo "id= ". $rs->id . " name= " . $rs->name . " duedate= " . date('h:i:s d-m-Y ',$rs->duedate) . " <br> "; 
//}		
//echo $query ;

require_once($CFG->dirroot.'/lib/moodlelib.php');

$touser = new stdClass();
$touser->mailformat = 0;
$touser->id = 2;
//$touser->email = "rodrigosv91@gmail.com";
$touser->email = "rodrigo.s.v.10@hotmail.com";
//$touser->email = $DB->get_field('user', 'email', array('id' => 2));

//echo $touser;
 //email_to_user($touser, "alguem", "teste subj", "msg teste 1", "msg teste 2", '', '', true);
 
 /**
$context = context_course::instance(2);
$event = \block_analytics_graphs\event\block_analytics_graphs_event_send_email::create(array(
    'objectid' => 2,
    'context' => $context,
    'other' => 'otherStringTeste',
));
$event->trigger();
*/


?>

<!--DOCTYPE HTML-->
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>TESTE</title>
	</head>
    <body>
		<p>Escolha a tarefa</p> <?php ?>
		<form action="demo_form.asp" method="post" name="usrform">
		<p>se o aluno xyz não enviou a atividade</p>
		
		<select name="assign_id">
		<?php foreach ($result  as  $rs) :?>
			<option value="<?php echo $rs->id; ?>"><?php echo  $rs->name; ?></option>
		<?php endforeach;?>
		</select>
		
		<div>e o prazo se encerra em <input value=0 name="days" type="number" min="0" max=""> dias e <!--spinner, jqueryUI-->

			<select name="hours">
			<?php 
			for($hours=0; $hours<24; $hours++) // the interval for hours is '1'
				for($mins=0; $mins<60; $mins+=60) // the interval for mins is '30'
					echo '<option>'.str_pad($hours,2,'0',STR_PAD_LEFT).':'
						.str_pad($mins,2,'0',STR_PAD_LEFT).'</option>';
			?>
			</select>
		horas
		<p>enviar mensagem para o professor</p>
		
		<textarea rows="4" cols="50" name="message" form="usrform"></textarea>
		
		</div>
		<input type="submit"  value="Send form data!">
		</form>
		
		
		
	</body>
</html>