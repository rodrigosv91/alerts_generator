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

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);
 

//Search not overdue assigns 	
$query = 'SELECT id, name, duedate FROM {assign} WHERE course =' . $course_id . ' AND duedate > UNIX_TIMESTAMP(NOW()) ORDER BY name'; 		
$result = $DB->get_recordset_sql( $query );

/** 
$query2 = 'SELECT UNIX_TIMESTAMP(NOW()) as unix, NOW() as date FROM DUAL ';	
$result2 = $DB->get_recordset_sql( $query2 );
		
foreach ($result2  as  $rs2) {
	$unix = $rs2->unix;
	$date_now = $rs2->date;
}			
//echo $unix . " ";
//echo $date_now;
$result2->close();
*/	
//foreach ($result  as  $rs) {
//	echo "id= ". $rs->id . " name= " . $rs->name . " duedate= " . date('h:i:s d-m-Y ',$rs->duedate) . " <br> "; 
//}		
//echo $query ;

//require_once($CFG->dirroot.'/lib/moodlelib.php');

//$touser = new stdClass();
//$touser->mailformat = 0;
//$touser->id = 2;
//$touser->email = "rodrigosv91@gmail.com";
//$touser->email = "rodrigo.s.v.10@hotmail.com";
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

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>TESTE</title>
		<!--  
		<link rel="stylesheet" href="externalref/jquery-ui-1.11.4/jquery-ui.css"> 
		<script src="externalref/jquery-ui-1.11.4/jquery-ui.js"></script>
		<script src="externalref/jquery-1.11.1.js"></script> 
		-->
			 
		 <!--
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> 
		<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
		 <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>   -->
		 
		<!-- --> 
		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
	</head>
    <body>	 
		<style>
			.warning{      
				border: 1px solid red;
			} 
			
			body {
				color: #333333;
				/*margin: 1em 0; */
				font-family: "Trebuchet MS", Tahoma, Verdana, Arial, sans-serif; 
				
			}

			input.text, textarea.text { font-family: Arial;  }
				
			.ui-widget { font-size: 12px; } 
	
		</style>
		
	
		<script type="text/javascript">
		$(document).ready(function(){
					
			//$( "body" ).accordion({  header: "h3", collapsible: true, active: false });
					
			$( "form" ).submit(function( event ) {
						// Stop form from submitting normally
						event.preventDefault();
						// Get some values from elements on the page:
						
						var course_id  = <?php echo json_encode($course_id);?>; 
						
						var $form = $( this ),
						//course_id = $form.find( "input[name='course_id']" ).val(),
						days = $form.find( "input[name='days']" ).val(),
						hm_time = $form.find( "select[name='hm_time']" ).val(),
						assign_id = $form.find( "select[name='assign_id']" ).val(),
						subjectval = $form.find( "input[name='subject']" ).val(),
						textoval = $form.find( "textarea[name='texto']" ).val(),
						url = $form.attr( "action" );
						
						//var a = hsm_hours.split(':'); // split it at the colons

						// minutes are worth 60 seconds. Hours are worth 60 minutes.
						//var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60; 
						
												
						if( ($.trim(textoval) == '') && ($.trim(subjectval) == '') ){
							
							alert('<?php echo get_string('empty_subject_message', 'block_alerts_generator');?>'); 
							
						}else{						
							if($.trim(textoval) == ''){
								
								alert('<?php echo get_string('empty_message', 'block_alerts_generator');?>'); 
							}	
							else{
								if($.trim(subjectval) == ''){ 
									alert('<?php echo get_string('empty_subject', 'block_alerts_generator');?>'); 
									
									
								}else{
									// Send the data using post			
									var posting = $.post( url, { course_id: course_id, days: days, hm_time: hm_time, assign_id: assign_id,
													subject: subjectval, texto: textoval } );
																			
									// Do something with the result
									posting.done(function( data ) {
										//alert(data);
										if(data){																					
											alert("<?php echo get_string('scheduled_alert', 'block_alerts_generator');?>");
											//alert("Alerta Cadastrado");									
										} else {
											alert("<?php echo get_string('not_scheduled_alert', 'block_alerts_generator');?>");
											//alert("Alerta Não Cadastrado");
										}
									});											
								}
							}
							//$( "#dialog" ).dialog( "open" );
						}						
			});
			
			$( "#dialog-link" ).click(function( event ) {
				$( "#dialog" ).dialog( "open" );
				event.preventDefault();
			});
			
			$( ".dialog" ).dialog({
				autoOpen: false,
				width: 450,
				buttons: [
					{
						text: "Ok",
						click: function() {
							$( this ).dialog( "close" );
						}
					},
					{
						text: "Cancel",
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});
			
			$( ".spinner" ).spinner({ //allow valid entries only
				max: 999999999, 
				min: 0, 
				value:0,
			}).on('input', function () {
				if ($(this).data('onInputPrevented')) return;
				var val = this.value,
					$this = $(this),
					max = $this.spinner('option', 'max'),
					min = $this.spinner('option', 'min');
				// We want only numbers, no alphas. 
				// We set it to previous default value.         
				if (!val.match(/^[+-]?[\d]{0,}$/)) val = $(this).data('defaultValue');
				this.value = val > max ? max : val < min ? min : val;
			}).on('keydown', function (e) {
				// we set default value for spinner.
				if (!$(this).data('defaultValue')) $(this).data('defaultValue', this.value);
				// To handle backspace
				$(this).data('onInputPrevented', e.which === 8 ? true : false);
			});
			
			$( ".selectmenu" ).selectmenu({width: 160});
			
			$( ".input_subject, .text_message, .spinner" ).blur(function () { // input out of focus	
				if(  !$(this).val() )  { //!$(this).val()
					//$(".subject_paragraph").addClass('ui-state-highlight');
					//$(".subject_paragraph").addClass('warning');	
					//$(this).parent().prev("p").addClass('ui-state-highlight'); 
					//$(this).addClass('ui-state-highlight');
					$(this).addClass('warning');

					
				}
				if( $(this).val() ){
					//$(".subject_paragraph").removeClass('ui-state-highlight');
					//$(".subject_paragraph").removeClass('warning');
					//$(this).parent().prev("p").removeClass('ui-state-highlight'); 									
					//$(this).removeClass('ui-state-highlight');
					$(this).removeClass('warning');
					
				}	
			});

			$( ".button" ).button();
			//$( "#accordion" ).accordion();
		});
		</script>
		
		<?php if($result->valid()): ?>
		
		<form action="schedule_assign_exp.php" method="post" name="usrform">
			
			<!--<input type="hidden" id="course_id" value="<?php echo $course_id ?>" name="course_id" form="usrform"/> -->
			
			<!--<textarea rows="4" cols="50" name="message" form="usrform"></textarea><br><br>-->
			
			<p> 
			Quando faltar <!-- <input value=0 name="days" type="number" min="0" max=""> dias e spinner, jqueryUI-->
			<input class="spinner" name="days" style="width: 120px"> dias e
			
			<select class="selectmenu" name="hm_time" value=0>
				<?php 
				for($hours=0; $hours<24; $hours++) // the interval for hours is '1'
					for($mins=0; $mins<60; $mins+=60) // the interval for mins is '60'
						echo '<option value=' .str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT).'>'
							.str_pad($hours,2,'0',STR_PAD_LEFT).':'
								.str_pad($mins,2,'0',STR_PAD_LEFT).'</option>';
				?>
			</select>
			horas		
			</p>
					
			<p> 
				para expirar a tarefa 
				<select class="selectmenu" name="assign_id">
				<?php foreach ($result  as  $rs) :?>
					<option value="<?php echo $rs->id; ?>"><?php echo  $rs->name; ?></option>
					
				<?php endforeach; ?>
				</select> 
			
			</p>

			<p>Enviar mensagem para alunos que não enviaram a tarefa</p>				
			
			<p class="subject_paragraph"><?php echo get_string('subject', 'block_alerts_generator');?>:
				<input class="input_subject text ui-widget-content ui-corner-all  " type='text' name='subject' form="usrform" ></p>
				<textarea class="text_message text ui-widget-content ui-corner-all " rows="5" cols="60" name="texto" form="usrform"></textarea><br><br>
			<!-- -->
			<input class="button" type="submit"  value="Cadastrar!">
		
		</form>
		
		
		
		<?php //include 'show_expire_alerts.php';?>
		
		
		<!-- <p><a href="#" id="dialog-link" class="ui-state-default ui-corner-all"><span class="ui-icon ui-icon-newwin"></span>Mensagem</a></p> -->
		
		<!-- ui-dialog -->
		<div id="dialog" class="dialog" title="Dialog Title">
			<p ><?php echo get_string('subject', 'block_alerts_generator');?>: 
			<input type='text' name='subject' ></p>
			<textarea rows="4" cols="50" name="message" form="usrform"></textarea><br><br>
		</div>
		
		<p><a href="show_expire_alerts.php?id=<?php echo $course_id;?>" class="button">Editar/Excluir Alertas Cadastrados</a></p>
		
		<?php else:  ?>
	
		<div><p>Não há tarefas disponiveis</p></div>
		<p><a href="show_expire_alerts.php?id=<?php echo $course_id;?>" class="button">Editar/Excluir Alertas Cadastrados</a></p>
		
		<?php endif;  ?>	
	
	<?php $result->close();?>
	</body>
</html>