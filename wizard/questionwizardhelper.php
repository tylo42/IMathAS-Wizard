<?php
/*
 * Author:	Tyler Hyndman
 * Description:	This php file brings together all the questions and
 *		creates functions to be used in questionwizard.php.
 */

require_once("create_questions.php");
require_once("question_factory.php");

global $questions;
$xml = new DOMDocument();
$xml->load("wizard/questions.xml");

$factory = new question_set_factory;

$questions = $factory->create($xml);

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
