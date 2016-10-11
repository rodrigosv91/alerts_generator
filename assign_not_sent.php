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
$query = 'SELECT id, name, duedate FROM {assign} 
			WHERE course = ' . $course_id . ' AND duedate > UNIX_TIMESTAMP(NOW())  
			AND id  NOT IN(
				SELECT distinct(assignid) from {block_alerts_generator_ans}
					WHERE sent = 0 
			) ORDER BY name'; 		
$result = $DB->get_recordset_sql( $query );

?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Assign Not Sent</title>
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
						
							var course_id  = <?php echo json_encode($course_id);?>; 
							
							var $form = $( this ),
							//var $form = $( this ),//$('body').find("form[name='usrform']"),
							assign_id = $("#form1").find( "select[name='assign_id']" ).val(),
							subjectval = $form.find( "input[name='subject']" ).val(),
							textoval = $form.find( "textarea[name='texto']" ).val(),
							url = $("#form1").find("form[name='usrform']").attr( "action" );	
							
							//alert(url);
													
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
										var posting = $.post( url, { course_id: course_id, assign_id: assign_id, subject: subjectval, texto: textoval } );
																				
										// Do something with the result
										posting.done(function( data ) {
											
											if(data.asgn_count!=0){
												alert("Alerta para esta tarefa já cadastrado.");
											}else{
												if(data.asgn_count==0){																					
													alert("<?php echo get_string('scheduled_alert', 'block_alerts_generator');?>");
													$( "#dialog" ).dialog( "close" );
													location.reload();
													//alert("Alerta Cadastrado");														
												} else {
													
													alert("<?php echo get_string('not_scheduled_alert', 'block_alerts_generator');?>");
													//alert("Alerta Não Cadastrado");
												}
											}
										});		
									}									
								}
							}
							//$( "#dialog" ).dialog( "open" );
						}						
							
							//$( this ).dialog( "close" );
						
					},
					{
						text: "Cancel",
						click: function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});
						
			$( ".selectmenu" ).selectmenu({width: 160});
			
			$( ".input_subject, .text_message" ).blur(function () { // input out of focus	
				if(  !$(this).val() )  { //!$(this).val()
					$(this).addClass('warning');

					
				}
				if( $(this).val() ){
					$(this).removeClass('warning');
					
				}	
			});

			$( ".button" ).button();
			//$( "#accordion" ).accordion();
		});
		</script>
		
		<?php if($result->valid()): ?>
		
		<div id="form1">
		<form action="schedule_assign_not_sent.php" method="post" name="usrform">	 
			<p> 
				Se aluno não enviar tarefa
				<select id="assign_id" class="selectmenu" name="assign_id">
				<?php foreach ($result  as  $rs) :?>
					<option value="<?php echo $rs->id; ?>"><?php echo  $rs->name; ?></option>				
				<?php endforeach; ?>
				</select> 
				enviar mensagem:
				<a href="#" id="dialog-link" class=" button"><span class="ui-icon ui-icon-newwin"></span>Mensagem</a>
			</p>
							
			<div id="dialog" class="dialog">
					<p class="subject_paragraph"><?php echo get_string('subject', 'block_alerts_generator');?>:
					<input class="input_subject text ui-widget-content ui-corner-all  " type='text' name='subject' form="usrform" ></p>
					<textarea class="text_message text ui-widget-content ui-corner-all " rows="5" cols="60" name="texto" form="usrform"></textarea><br><br>
				<!-- -->
				<!-- <input class="button" type="submit"  value="Cadastrar!"> -->
			</div>
		</form> 
		</div>
				
		<p><a href="show_assign_not_sent.php?id=<?php echo $course_id;?>" class="button">Editar/Excluir Alertas Cadastrados</a></p>
		
		<?php else:  ?>
	
		<div><p>Não há tarefas disponiveis</p></div>
		<p><a href="show_assign_not_sent.php?id=<?php echo $course_id;?>" class="button">Editar/Excluir Alertas Cadastrados</a></p>
		
		<?php endif;  ?>	
	
	<?php $result->close();?>
	</body>
</html>