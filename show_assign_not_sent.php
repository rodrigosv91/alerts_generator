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
global $DB;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);

$query = "SELECT 	msg.id AS msgid, 
					aga.id AS ag_assignid, 
					a.name, 
					a.duedate, 
					msg.message, 
					msg.subject
					FROM {block_alerts_generator_msg} AS msg 
					INNER JOIN {block_alerts_generator_ans} AS aga ON msg.id = aga.messageid 			
					INNER JOIN {assign} AS a ON a.id = aga.assignid
					WHERE msg.courseid = ". $course_id . " 
					AND aga.sent = 0 
					AND  a.course = " . $course_id . " 
					ORDER BY a.name" ; 
				
$result = $DB->get_recordset_sql( $query );

?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo get_string('show_assign_not_sent_alert', 'block_alerts_generator');?></title>

		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
	</head>	
	<body>	
		<?php 
			$PAGE->set_url('/show_assign_not_sent.php');
			$PAGE->set_heading($COURSE->fullname);
			echo $OUTPUT->header(); 		
		?>
	
		<style>
			body {
				color: #333;
				font-family: Trebuchet MS, Tahoma, Verdana, Arial, sans-serif; 
			}
			
			.ui-widget { font-size: 12px; }
			
			.container_body_ag{			
				text-align:center;
				margin: auto;
				margin-top: 1em;
				width: 850px;/*
				height: 430px;				 
				padding: 20px 20px 0px 20px;				
				border: 2px solid;
				border-radius: 25px;   */		
			}
			
			.no_results{	
				margin: auto;	
				margin-top: 3em; 
				margin-bottom: 3em; 
			}
								
			.footer_page_link{
				margin-top: 3em; 
				margin-bottom: 2em;
			}
			
		</style>	
		<script>
			$(document).ready(function(){
				
				$( "#container_alerts" ).accordion({  header: "h3", collapsible: true, active: false });
				$( ".button" ).button();
				
				$("#container_alerts").on('click', '.btnDelete', function() { 
								
					if (confirm('<?php echo get_string('confirmation_message', 'block_alerts_generator');?>')) {
						
						var course_id  = <?php echo json_encode($course_id); ?>;
						var ag_assignid = $(this).closest('.alert_unit').find("input:hidden[name='ag_assignid']").val();				
						var msg_id = $(this).closest('.alert_unit').find("input:hidden[name='msg_id']").val();				
						var url = "del_sch_assign_not_sent.php";
						
						var posting = $.post( url, { ag_assignid: ag_assignid, msg_id: msg_id, course_id: course_id } );
						
						posting.done(function( data ) {
							if(data){	
								alert('<?php echo get_string('alert_deleted', 'block_alerts_generator');?>'); 
								location.reload(); 
							} else {							
								alert('<?php echo get_string('alert_not_deleted', 'block_alerts_generator');?>'); 
							}						
						});				
					}	
				});
				
				$("#container_alerts").on('click', '.btnEdit', function() { 
					//adjust etf_form default values
					var ag_assignid = $(this).closest('.alert_unit').find("input:hidden[name='ag_assignid']").val();						
					var msg_id = $(this).closest('.alert_unit').find("input:hidden[name='msg_id']").val();
					var asgn_name = $(this).closest('.alert_unit').find("h3").text(); 
					
					var msg_subject = $(this).closest('.alert_unit').find("input:hidden[name='msg_subject']").val();			
					var msg_message = $(this).closest('.alert_unit').find("input:hidden[name='msg_message']").val();
												
					var edtform = $( "#dialog_edt_form" );				
					edtform.find("input[name='edt_subject']").val(msg_subject);
					edtform.find("textarea[name='edt_texto']").val(msg_message);
					//edtform.find("span.edt_asgn_name").text(asgn_name);
					
					edtform.dialog( "option", "title", "Se aluno não enviar " + asgn_name + " enviar a mensagem:" );
					
					//open dialog form
					$( "#dialog_edt_form" ) 
					.data({ ag_assignid: ag_assignid, msg_id: msg_id })
					.dialog( "open" );							
				});
				
				$( "#dialog_edt_form" ).dialog({
					autoOpen: false,
					width: 450,
					//height: 400,
					modal: true,
					buttons: [
						{
							text: "Ok",
							click: function() {
								
								var course_id  = <?php echo json_encode($course_id);?>;
								var ag_assignid = $( "#dialog_edt_form" ).data('ag_assignid');
								var msg_id = $( "#dialog_edt_form" ).data('msg_id'); 
		
								var $form = $( this ).closest('#dialog_edt_form').find("form[name='edtform']"),
								subjectval = $form.find( "input[name='edt_subject']" ).val(),
								textoval = $form.find( "textarea[name='edt_texto']" ).val(),
								url = 'edt_sch_assign_not_sent.php';

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
											var posting = $.post( url, { ag_assignid: ag_assignid, msg_id: msg_id, subject: subjectval, 
														texto: textoval, course_id: course_id} );																				
											posting.done(function( data ) {
												if(data){																							
													alert("<?php echo get_string('alert_updated', 'block_alerts_generator');?>");						
													$( "#dialog_edt_form" ).dialog( "close" );
													location.reload();
													
												} else {
													alert("<?php echo get_string('alert_not_updated', 'block_alerts_generator');?>");
												}
											});																					
										}
									}
								}				
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
			});
		</script>

		<div class="container_body_ag">
		
		<h2><?php echo get_string('assign_not_sent_alert', 'block_alerts_generator');?></h2>
		
		<?php if($result->valid()): ?>
		
		<div id="container_alerts"> 		
			<?php foreach ($result  as  $rs) :?>
				
				<div class="alert_unit">	
					<h3><?php echo $rs->name ?></h3>
					
					<div>
						<p>Data de Entrega: <?php echo userdate($rs->duedate) ?></p>
						<p>Mensagem a ser enviada: </p>
						<p><?php echo $rs->message ?></p>			
						<div>
							<input type="hidden" value="<?php echo $rs->ag_assignid ?>" class="ag_assignid" name="ag_assignid" />
							<input type="hidden" value="<?php echo $rs->msgid ?>" class="msg_id" name="msg_id" />
							<input type="hidden" value="<?php echo $rs->subject ?>" class="msg_subject" name="msg_subject" />
							<input type="hidden" value="<?php echo $rs->message ?>" class="msg_message" name="msg_message" />
							
							<button class="button btnEdit" name="btnEdit">Edit</button>
							<button class="button btnDelete" name="btnDelete">Delete</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>		
		</div>
		
		<div id="dialog_edt_form" >
		
			<form action="edt_sch_assign_not_sent.php" method="post" name="edtform">
				<input type="hidden" id="course_id" value="<?php echo $course_id ?>" name="course_id" form="edtform"/> 
				<!--											
				<p>Se aluno não enviar tarefa: <span class="edt_asgn_name"> </span> enviar a mensagem:</p>
				-->
		
				
				<p class="subject_paragraph"><?php echo get_string('subject', 'block_alerts_generator');?>:
					<input class="input_subject text ui-widget-content ui-corner-all  " type='text' name='edt_subject' form="edtform" >
				</p>
				<textarea class="text_message text ui-widget-content ui-corner-all " rows="6" cols="60" name='edt_texto' form="edtform"></textarea><br><br>
				
			</form>
		</div>
		
		
		<div class="footer_page_link">
			<p><a href="assign_not_sent.php?id=<?php echo $course_id;?>" class="">Inserir novo alerta</a></p>
		</div>
		<?php else:  ?>
		
		<div class="no_results"><p>Não há alertas cadastrados</p></div>
		
		<div class="footer_page_link">
			<p><a href="assign_not_sent.php?id=<?php echo $course_id;?>" class="">Inserir novo alerta</a></p>
		</div>
		<?php endif;  ?>	
		
		</div>
		
		<?php 
		
		$result->close() ;
		echo $OUTPUT->footer();
		
		?>
	</body>
</html>		

		