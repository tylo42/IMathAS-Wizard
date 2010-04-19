<?php
/*
 * Title: 	questionwizardhelper.php
 * Author:	Tyler Hyndman
 * Date: 	August 31, 2009
 * Description:	This php file brings together all the questions and
 *		creates functions to be used in questionwizard.php.
 */

require_once("create_questions.php");

global $questions;
$questions = 	array(
		"number" => create_number_variables(),
		"calculated" => create_calculated_variables(),
		"choices" => create_choices_variables(),
		"multans" => create_multans_variables(),
		"matching" => create_matching_variables(),
		"numfunc" => create_numfunc_variables(),
		"string" => create_string_variables(),
		"essay" => create_essay_variables(),
		"draw" => create_draw_variables(),
		"ntuple" => create_ntuple_variables(),
		"calcntuple" => create_calcntuple_variables(),
		"matrix" => create_matrix_variables(),
		"calcmatrix" => create_calcmatrix_variables(),
		"interval" => create_interval_variables(),
		"calcinterval" => create_calcinterval_variables(),
		"complex" => create_complex_variables(),
		"calccomplex" => create_calccomplex_variables(),
		"multipart" => create_multipart_variables()
	);

function createDivs($myq,$line){
	global $questions;

	foreach($questions as $question) {
		$ret.=$question->display_html($myq,$line)."\n\n";
   }

	return $ret;
}

function wizardpost(&$_POST){
	global $questions;
	$questions[$_POST['qtype']]->submit_question($_POST);
}


function parseText(&$line){
	global $questions;
	$questions[$line['qtype']]->parse_question($line);
}
?>
