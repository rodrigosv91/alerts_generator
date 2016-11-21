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
					sch_a.id AS ag_sched_alert_id,
					sch_a.alertdate, 
					msg.message, 
					msg.subject,
					msg.customized
					FROM {block_alerts_generator_msg} AS msg 
					INNER JOIN {block_alerts_generator_sch_a} AS sch_a ON msg.id = sch_a.messageid 					
					AND msg.courseid = ". $course_id . " 
					AND sch_a.sent = 0 
					ORDER BY msg.subject ASC, sch_a.alertdate DESC " ; 
				
$result = $DB->get_recordset_sql( $query );

//echo '<pre>'; print_r($result); echo '</pre>';
?>

<!DOCTYPE html>
<html>
    <head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo get_string('show_scheduled_alerts_title', 'block_alerts_generator');?></title>

		<link rel="stylesheet" href="externalref/jquery-ui-1.12.1/jquery-ui.css">
		<script src="externalref/jquery-1.12.4.js"></script> 
		<script src="externalref/jquery-ui-1.12.1/jquery-ui.js"></script>
	</head>	
	<body>	
		
		<?php 		
			$url = $CFG->wwwroot . '/blocks/alerts_generator/show_scheduled_alerts.php?id=' . $course_id;
			$PAGE->set_url($url);
			$PAGE->set_heading($COURSE->fullname);
			echo $OUTPUT->header(); 		
		?>
	
		<style>
			body {/*
				color: #333;
				font-family: Trebuchet MS, Tahoma, Verdana, Arial, sans-serif;  */
			}
			
			.ui-widget { /* */ font-size: 12px; }
			
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
			
			.ui-datepicker-trigger { margin-left:0.2em; width: 1.5em; }
			
			.container_alerts{					
				margin-top: 2em; /*
				font-size: 100%;	 */				
			}
					
			.footer_page_link{
				margin-top: 2em; 				
			}
			
			.ui-selectmenu-open{
				max-height: 380px;
				overflow-y: scroll;
			}	/* */
			
		</style>
		
	
		<script>
			$(document).ready(function(){
				
				$( ".container_alerts" ).accordion({  header: "h3", collapsible: true, active: false });
				$( ".button" ).button();
				
				$(".container_alerts").on('click', '.btnDelete', function() { 
								
					if (confirm('<?php echo get_string('confirmation_message', 'block_alerts_generator');?>')) {
						
						var course_id  = <?php echo json_encode($course_id); ?>;
						var ag_sched_alert_id = $(this).closest('.alert_unit').find("input:hidden[name='ag_sched_alert_id']").val();
						var msg_id = $(this).closest('.alert_unit').find("input:hidden[name='msg_id']").val();				
						var url = "del_sch_alert.php";
						
						var posting = $.post( url, { ag_sched_alert_id: ag_sched_alert_id, msg_id: msg_id, course_id: course_id } );
						
						posting.done(function( data ) {
							if(data){	
								alert('<?php echo get_string('alert_deleted', 'block_alerts_generator');?>'); 
								//alert("Alerta Deletado!");
								location.reload(); 
							} else {							
								alert('<?php echo get_string('alert_not_deleted', 'block_alerts_generator');?>'); 
								//alert("Erro ao deletar alerta!");
							}						
						});				
					}	
				});
				
				$(".container_alerts").on('click', '.btnEdit', function() { 
					//adjust etf_form default values								
					var ag_sched_alert_id = $(this).closest('.alert_unit').find("input:hidden[name='ag_sched_alert_id']").val();
					var msg_id = $(this).closest('.alert_unit').find("input:hidden[name='msg_id']").val();
					var asgn_name = $(this).closest('.alert_unit').find("h3").text(); 
					
					var msg_subject = $(this).closest('.alert_unit').find("input:hidden[name='msg_subject']").val();			
					var msg_message = $(this).closest('.alert_unit').find("input:hidden[name='msg_message']").val();
					var ag_alertdate = $(this).closest('.alert_unit').find("input:hidden[name='ag_alertdate']").val();
					var msg_customized = $(this).closest('.alert_unit').find("input:hidden[name='msg_customized']").val();
					
					
					var edtform = $( "#dialog_edt_form" );				
					edtform.find("input[name='edt_subject']").val(msg_subject);
					edtform.find("textarea[name='edt_texto']").val(msg_message);
					edtform.find("span.edt_asgn_name").text(asgn_name);				
					
					var ag_alertdate_formated = getformatedDate(ag_alertdate);
					
					edtform.find("input[name='input_date']").val( ag_alertdate_formated );
					/*										
					var days = Math.floor(ag_alertdate/86400);
									
					var fullmin = (ag_alertdate-(days*86400))/60;
								
					var m = fullmin % 60;
					var h = (fullmin-m)/60;
					*/
					
					var date = new Date(ag_alertdate*1000);
					
					var m = date.getMinutes();
					var h = date.getHours();				
					
					var hm_time = (h<10?"0":"") + h + ":" + (m<10?"0":"") + m;
					
					//alert(hm_time);
					
					edtform.find("input[name='check_custom_message']").prop('checked', (msg_customized > 0 ? true : false) );
							
					edtform.find("select[name='edt_hm_time'] option").filter(function() {
						return $(this).text() == hm_time; 
					}).prop('selected', true);

					//$('.selectmenu').val(hm_time).selectmenu("refresh");
					$('.selectmenu').selectmenu("refresh");
					
					//open dialog form
					$( "#dialog_edt_form" ) 
					.data({ ag_sched_alert_id: ag_sched_alert_id, msg_id: msg_id })
					.dialog( "open" );							
				});
				
				$( "#dialog_edt_form" ).dialog({
					autoOpen: false,
					width: 500,
					//height: 400,
					modal: true,
					buttons: [
						{
							text: "Ok",
							click: function() {
								
								var course_id  = <?php echo json_encode($course_id);?>;	
								
								var input_date = $(".input_date").datepicker('getDate') ; 
							
								var ag_sched_alert_id = $( "#dialog_edt_form" ).data('ag_sched_alert_id');
								var msg_id = $( "#dialog_edt_form" ).data('msg_id'); 
		
								var $form = $( this ).closest('#dialog_edt_form').find("form[name='edtform']"),
								
								hm_time = $form.find( "select[name='edt_hm_time']" ).val(),
								subjectval = $form.find( "input[name='edt_subject']" ).val(),
								textoval = $form.find( "textarea[name='edt_texto']" ).val(),
								
								customized = $form.find( "input[name='check_custom_message']" ).is(":checked")== true ? 1 : 0,
								
								url = $form.attr( "action" );

								//alert(url);
								
								if($.trim(input_date) == '' || $.trim(input_date) == null){		
									alert('<?php echo get_string('invalid_date', 'block_alerts_generator');?>'); 
								}
								else{
									
									var str_hm_time = hm_time;
									var str_hm_time_splited = str_hm_time.split(':',2);
									var str_input_date = new Date(input_date);
									
									str_input_date.setHours(str_hm_time_splited[0]);
									str_input_date.setMinutes(str_hm_time_splited[1]);
									
									//alert(str_input_date);
									
									if(str_input_date < new Date()){
										alert('<?php echo get_string('invalid_date_2', 'block_alerts_generator');?>'); 
									}else{	
									/*  */
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
													
													var posting = $.post( url, { hm_time: hm_time, customized: customized, 
																					ag_sched_alert_id: ag_sched_alert_id,
																					input_date: input_date, msg_id: msg_id, 
																					subject: subjectval, texto: textoval, course_id: course_id} );																				
													posting.done(function( data ) {
														if(data){																							
															alert("<?php echo get_string('alert_updated', 'block_alerts_generator');?>");												 
															//alert("Alerta Atualizado");	
															$( "#dialog_edt_form" ).dialog( "close" );
															location.reload();
															
														} else {
															alert("<?php echo get_string('alert_not_updated', 'block_alerts_generator');?>");
															//alert("Alerta Não Atualizado");
														}
													});																					
												}
											}
										}
									}
								}//end else
								//$( this ).dialog( "close" );						
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
								
				$( ".selectmenu" ).selectmenu({width: 100});
				
				$( ".dialog_custom_message_help" ).dialog({
					autoOpen: false,
					width: 300,
					modal: true,
					resizable: false,
					buttons: [
						{
							text: "Ok",
							click: function() {
								$( this ).dialog( "close" );
							}
						}
					]
				});
				
				$( ".helpButton" ).click(function( event ) {
					$( ".dialog_custom_message_help" ).dialog( "open" );
					event.preventDefault();
				});
				
				//datepicker validation
				var min_date2 = new Date();
				min_date2.setHours(00);
				min_date2.setMinutes(00);
				min_date2.setSeconds(00);
				min_date2.setMilliseconds(00);
								
				$( ".input_date" )
				.attr("placeholder", "dd/mm/yyyy")
				.datepicker({
					dateFormat: "dd/mm/yy",
					//defaultDate: "+1w",
					changeMonth: true,
					changeYear: true,
					//numberOfMonths: 1
					minDate: min_date2,
					//maxDate: "10Y",	
					showOn: "both",
					buttonImage: "images/calendar.gif",
					buttonImageOnly: true,
					buttonText: "Select date"
				}).on( "change", function() {
					if(  getDate( this ) < min_date2  ){				
						$( this ).datepicker('setDate', null) ;
						//alert("Data inválida");
					}				
					//from.datepicker('setDate', from.datepicker('getDate'));				  
				});
						
				var dateFormat = "dd/mm/yy";

				function getDate( element ) {
					var date;
					try {
						date = $.datepicker.parseDate( dateFormat, element.value );
					} catch( error ) {
					date = null;
					}		 
					return date;
				}			
				
				function getformatedDate( fulltimestamp ) {
					var date = new Date(fulltimestamp * 1000);
					var d  = date.getDate();
					var m = date.getMonth() + 1;
					var y = date.getFullYear();				
					var formatedDate =  (d<10?"0":"") + d + "/" + (m<10?"0":"") + m  + "/" + y;
				 
					return formatedDate;
				}
				
				$(".ui-datepicker").draggable() ;
			});
		</script>
	
		
		<div class="container_body_ag">
		
		<h2><?php echo get_string('scheduled_alert_title', 'block_alerts_generator');?></h2>
		
		<?php if($result->valid()): ?>
		
		<div id="" class="container_alerts"> 		
			<?php foreach ($result  as  $rs) :?>
				
				<div class="alert_unit">	
					<h3><?php echo get_string('subject', 'block_alerts_generator');?>: <?php echo $rs->subject ?></h3>
					
					<div>
						<p>Data do Alerta: <?php echo userdate($rs->alertdate) ?></p>
						<p>Mensagem: </p>
						<p><?php echo $rs->message ?></p>			
						<div>			
							<input type="hidden" value="<?php echo $rs->ag_sched_alert_id ?>" class="ag_sched_alert_id" name="ag_sched_alert_id" />						
							<input type="hidden" value="<?php echo $rs->msgid ?>" class="msg_id" name="msg_id" />
							<input type="hidden" value="<?php echo $rs->subject ?>" class="msg_subject" name="msg_subject" />
							<input type="hidden" value="<?php echo $rs->alertdate ?>" class="ag_alertdate" name="ag_alertdate" />
							<input type="hidden" value="<?php echo $rs->message ?>" class="msg_message" name="msg_message" />
							<input type="hidden" value="<?php echo $rs->customized ?>" class="msg_customized" name="msg_customized" />							
							
							<button class="button btnEdit" name="btnEdit">Edit</button>
							<button class="button btnDelete" name="btnDelete">Delete</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>		
		</div>
		
		<div id="dialog_edt_form" >
		
			<form action="edt_sch_alert.php" method="post" name="edtform">
				<input type="hidden" id="course_id" value="<?php echo $course_id ?>" name="course_id" form="edtform"/> 
					
						
				<p>Em
				<input type="text" class="input_date"  name="input_date" style="width: 100px; height:18px; margin: 0;"> às
					
				<select class="selectmenu" name="edt_hm_time"> 
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
				<p>Enviar para alunos a mensagem:</p>
				
				<p class="subject_paragraph"><?php echo get_string('subject', 'block_alerts_generator');?>:
					<input class="input_subject text ui-widget-content ui-corner-all  " type='text' name='edt_subject' form="edtform" >
				</p>
				<textarea autofocus class="text_message text ui-widget-content ui-corner-all " rows="6" cols="60" name="edt_texto" form="edtform"></textarea><br><br>
				
				<fieldset>
					<legend></legend>
					<input type="checkbox" name="check_custom_message" id="check_custom_message" class="check_custom_message" />
					<label for="check_custom_message" class="" style="font-size: 12px;">Usar nome personalizado na mensagem. </label>
					<button class=" button helpButton" style="masrgin:0px; "></span>Ajuda</a>
				</fieldset>
				
			</form>
		</div>
		
		<div class="dialog_custom_message_help" style="text-align:center">
			<?php echo get_string('custom_checkbox_help_message', 'block_alerts_generator');?> 
			</br></br> 
			<?php echo get_string('custom_checkbox_help_example', 'block_alerts_generator');?>   
		</div>
		
		<div class="footer_page_link">
			<p><a href="scheduled_alert.php?id=<?php echo $course_id;?>" class="">Inserir novo alerta</a></p>
		</div>
		
		<?php else:  ?>
		
		<div class="no_results"><p>Não há alertas cadastrados</p></div>
		
		<p><a href="scheduled_alert.php?id=<?php echo $course_id;?>" class="">Inserir novo alerta</a></p>
		
		<?php endif;  ?>	
		
		</div>
		
		<?php 
		$result->close() ;// close database connection
		echo $OUTPUT->footer();
		?>		
	</body>
</html>		

		