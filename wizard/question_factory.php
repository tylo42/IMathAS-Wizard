<?php
/*
 * Author:	Tyler Hyndman
 * Date:	March 13, 2010
 * TODO:
 * 	
 */


require_once("variable.php");
require_once("question.php");




$xml = simplexml_load_file("questions.xml");

$factory = new question_set_factory;

$questions = $factory->create($xml);


////////////////////////////////////////////////////////

class question_set {
	private $questions;
	
	function __construct() {
		$this->questions = array();  // set questions to an empty array
	}
	
	function insert($question) {
		assert(!isset($questions[$question->get_qtype()]));
		$questions[$question->get_qtype()] = $question;
	}
}


class question_set_factory {
	/**
	 * @param[in] $xml   An XML object
	 * @return				A populated question_set
	 */
	public function create($xml) {
		$questions = new question_set;
   
		foreach($xml->question as $xml_question) {
			// set up the question
			$question = new Question($xml_question['qtype']->getName());
			print "Title:".$xml_question['qtype'];
			//print 
			
			// set up the required variables
			foreach($xml_question->variable as $variable) {
				switch($variable['type']) {
					case "text":
						$question->insert($this->create_text($variable));
						break;
					case "radio":
						//$question->insert(create_radio($variable));
						break;
					default:
						assert("Unknown variable type '".$variable['type']."'");
						break;
				} // end switch
			} // end foreach variable
			$questions->insert($question);
		} // end foreach question
		return $questions;
	}
	
	private function create_text($variable) {
		print "Title: ".$variable->title."<br />";
		return new text_input($variable->title,"instructions","name","pre","default_value","required","ignore_quotes");
	}
}




?>
