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
global $DB, $SESSION, $COURSE;

/* Access control */
require_login( $course_id );
$context = context_course::instance( $course_id );
require_capability('block/alerts_generator:viewpages', $context);
	
$query = 'SELECT 	abs.id as absid, 
					abs.messageid, 
					abs.absencetime, 
					abs.alertstatus, 
					msg.id as msgid, 
					msg.fromid, 
					msg.subject, 
					msg.message,
					msg.courseid
					FROM {block_alerts_generator_abs} abs 
					INNER JOIN {block_alerts_generator_msg} msg 
					ON abs.messageid = msg.id 
					AND msg.courseid = :courseid ' ; 	
			
$result = $DB->get_recordset_sql( $query , array('courseid' => $course_id), 0, 1);

//print_r($result);

//print_r($SESSION);

?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo get_string('absence_alert', 'block_alerts_generator');?></title>
		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
	</head>
    <body>	  <!-- style="text-align:center;" -->
		
		<!--  <div class="container_abs_alert">  -->
		<?php 
			$PAGE->set_url('/absence_alert.php');
			$PAGE->set_heading($COURSE->fullname);
			echo $OUTPUT->header(); 		
		?>	
				
		<style>
			.warning{      
				border: 1px solid red;
			} 
			
			body {/*
				color: #333333;
				margin: 1em 0;
				font-family: "Trebuchet MS", Tahoma, Verdana, Arial, sans-serif; 				 
				 margin:auto; */
			}

			input.text, textarea.text { /* font-family: Arial;  */}
				
			.ui-widget { font-size: 12px; } 
					
						
			input[type="radio"] + label, .labelStatus{
				display: inline-block;
				padding: 0.4em 1em;
			}
			
			.container_body_ag{
				text-align:center;
				width: 750px;			
				margin: auto; 
				margin-top: 1em; /*
				height: 230px;				
				padding: 20px 20px 0px 20px;				
				border: 2px solid;
				border-radius: 25px;   */		
			}
			
			.no_results{				
				margin: auto;	
				margin-top: 4em; 
			}
			
			.abs_form{
				margin-top: 2em; 
			}
			
			.deletAlert{
				margin-top: 1em; 
			}
			
			.status_alert{
				/* */ width: 14em; 			
				margin:auto;
				margin-top: 1em;
				margin-bottom: 2em; 				
			}
			
		</style>
		
	
		<script type="text/javascript">
		$(document).ready(function(){
			
			if(<?php echo( ($result->valid())==1 ? 1 : 0) ;?>){
				var abs_absencetime = $('.prop_alert').find("input:hidden[name='abs_absencetime']").val();
				var days = Math.floor(abs_absencetime/86400);
				$( ".abs_form" ).find("input[name='days']").val(days);
			}
			$( ".dialog_link" ).click(function( event ) {
				
				var msg_subject = $('.prop_alert').find("input:hidden[name='msg_subject']").val();			
				var msg_message = $('.prop_alert').find("input:hidden[name='msg_message']").val();
								
				var form = $( ".dialog" );				
				form.find("input[name='subject']").val(msg_subject);
				form.find("textarea[name='texto']").text(msg_message);
									
				$( ".dialog" ).dialog( "open" );
				event.preventDefault();			
			});
			
			$( ".dialog" ).dialog({
				autoOpen: false,
				width: 450,
				modal: true,
				buttons: [
					{
						text: '<?php echo( ($result->valid())==1 ?  'Atualizar' : 'Ok') ;?>',
						click: function() {
						
							var course_id  = <?php echo json_encode($course_id);?>; 
							
							var $form = $( this ),
							days = $("#form_abs").find( "input[name='days']" ).val(),
							subjectval = $form.find( "input[name='subject']" ).val(),
							textoval = $form.find( "textarea[name='texto']" ).val(),
							url = $("#form_abs").find("form[name='usrform']").attr( "action" ); 				
																				
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
																	
										var posting = $.post( url, { course_id: course_id, //id_msg: id_msg, id_abs: id_abs,
																		days: days, subject: subjectval, texto: textoval } );
																					
										posting.done(function( data ) {
	
											//alert("abs id: " + data.abs_id);
											//alert("asd");
											
											if(data.abs_count == 0 && data.db_result > 0 ){													
												alert("<?php echo get_string('scheduled_alert', 'block_alerts_generator');?>");
												$( "#dialog" ).dialog( "close" );
												location.reload();	
											}else{
												if(data.abs_count > 0 && data.db_result === true){																					
													alert("<?php echo get_string('alert_updated', 'block_alerts_generator');?>");
													$( "#dialog" ).dialog( "close" );
													location.reload();														
												} else {
													alert("<?php echo get_string('not_scheduled_alert', 'block_alerts_generator');?>");
												}
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
						
			$( ".selectmenu" ).selectmenu({width: 160});
			
			$( ".input_subject, .text_message" ).blur(function () { // input out of focus	
				if(  !$(this).val() )  { //!$(this).val()
					$(this).addClass('warning');

					
				}
				if( $(this).val() ){
					$(this).removeClass('warning');
					
				}	
			});
			
			$( ".spinner" ).spinner({ //allow valid entries only
				max: 999999999, 
				min: 1, 
				//value:1,
			}).on('input', function () {
				if ($(this).data('onInputPrevented')) return;
				var val = this.value,
					$this = $(this),
					max = $this.spinner('option', 'max'),
					min = $this.spinner('option', 'min');         
				if (!val.match(/^[+-]?[\d]{0,}$/)) val = $(this).data('defaultValue');
				this.value = val > max ? max : val < min ? min : val;
			}).on('keydown', function (e) {
				// we set default value for spinner.
				if (!$(this).data('defaultValue')) $(this).data('defaultValue', this.value);
				// To handle backspace
				$(this).data('onInputPrevented', e.which === 8 ? true : false);
			});	

			$( ".button" ).button();
			

			$( ".input_radio" ).checkboxradio({icon: false	});


			$('input:radio[name=status_alert_option]').change(function() {

				var course_id  = <?php echo json_encode($course_id);?>; 			
				var action =  $('input:radio[name=status_alert_option]:checked').val() ;
				var url = 'change_absence_alert_status.php';
						
				var posting = $.post( url, { course_id: course_id, action: action } );
				
				posting.done(function( data ) {
																					
					if(data.result > 0){
						if (action == 1) {
							alert("<?php echo get_string('alert_activated', 'block_alerts_generator');?>");
						}
						else if (action == 2) {
							alert("<?php echo get_string('alert_deactivated', 'block_alerts_generator');?>");
						}
						//location.reload();	
					}else{
						if(data.result == 0){							
							alert("<?php echo get_string('alert_not_updated', 'block_alerts_generator');?>");
							//location.reload();														
						} else {
							alert("<?php echo get_string('invalid_option', 'block_alerts_generator');?>");
						}
					}						
				});				
			});
			
			$( ".deletAlert" ).click(function( event ) {
				if (confirm('<?php echo get_string('confirmation_message', 'block_alerts_generator');?>')) {						
				var url = 'del_absence_alert.php';			
				var posting = $.post( url );
				
				posting.done(function( data ) {
																					
					if(data.abs_count == 0 && data.msg_count == 0){
						alert("<?php echo get_string('alert_deleted', 'block_alerts_generator');?>");					
						location.reload();	
					}else{
						//if(data.abs_count > 0 && data.msg_count > 0) // alert not deleted						
						alert("<?php echo get_string('alert_not_deleted', 'block_alerts_generator');?>");							
					}						
				});	
				}
			});
			
		});
		</script>
		
		<div class="container_body_ag">
		
		<?php // if($result->valid()): echo ' <p> Há resultados</p> '; else:  echo '<p> Não há resultados</p>'; endif;  ?>	
		
		<h2><?php echo get_string('absence_alert', 'block_alerts_generator');?></h2>
		
		<div id="form_abs" class="abs_form">
		
		<?php if($result->valid()): ?>		
		<form action="edt_absence_alert.php" method="post" name="usrform">		
		<?php else:  ?>
		<form action="schedule_absence_alert.php" method="post" name="usrform">	 
		<?php endif;  ?>
		
			<p> 
				Quando aluno se ausentar por 
				<input class="spinner" name="days" value="1" style="width: 60px; height:13px">	
			
				dia(s) do curso <?php echo $COURSE->fullname; ?> enviar mensagem:
				 <a href="#" id="dialog-link" class=" button dialog_link"><span class="ui-icon ui-icon-newwin"></span>Mensagem</a> 
			</p>
			
		<?php if($result->valid()): ?>	
			<button class="deletAlert" type="button" >Deletar Alerta</button>
		<?php endif; ?>
			<div id="dialog" class="dialog">
					<p class="subject_paragraph"><?php echo get_string('subject', 'block_alerts_generator');?>:
					<input class="input_subject text ui-widget-content ui-corner-all  " type='text' name='subject' form="usrform" ></p>
					<textarea class="text_message text ui-widget-content ui-corner-all " rows="5" cols="60" name="texto" form="usrform"></textarea>
			</div>
		</form> 
		</div>
		
		<?php if($result->valid()): ?>
		

		<?php 

			$SESSION->block_alerts_generator = new stdClass;
			$SESSION->block_alerts_generator->id_abs = 0;
			$SESSION->block_alerts_generator->id_msg_abs = 0;
			$SESSION->block_alerts_generator->course_id_abs = 0;

		foreach($result as $rs){
			$SESSION->block_alerts_generator->id_abs = $rs->absid; 
			$SESSION->block_alerts_generator->id_msg_abs = $rs->msgid; 
			$SESSION->block_alerts_generator->course_id_abs = $course_id;
		?>
		<div class="prop_alert">
			<input type="hidden" value="<?php echo $rs->subject; ?>" class="msg_subject" name="msg_subject" />
			<input type="hidden" value="<?php echo $rs->message ;?>" class="msg_message" name="msg_message" />
			<input type="hidden" value="<?php echo $rs->absencetime ;?>" class="abs_absencetime" name="abs_absencetime" />	
		</div>
		
		<div class="status_alert" >	
			<fieldset>
				<legend>Estado do Alerta: </legend>
				<label for="radio_on">Ativado</label>
				<input class="input_radio radio_on" type="radio" name="status_alert_option" id="radio_on" value=1  <?php echo($rs->alertstatus==1 ? 'checked' : '');?> >
				<label for="radio_off">Desativado</label>
				<input class="input_radio radio_off" type="radio" name="status_alert_option" id="radio_off" value=2 <?php echo($rs->alertstatus==0 ? 'checked' : '');?>>
			</fieldset>
		</div>
				
		<?php 	}  //end foreach 	?>	

		<?php else:  ?>
		
		<div class="status_alert" >		
			<fieldset>
				<legend>Estado do Alerta: </legend>
				<span class="ui-widget ui-state-default ui-corner-all labelStatus">Alerta não cadastrado</span>
			</fieldset>
		</div>
		<?php endif;  ?>
		
		</div>
		<?php 		 
		
		$result->close();
		//unset($SESSION->block_alerts_generator);
		echo $OUTPUT->footer();
		
		?>	
	</body>
</html>

