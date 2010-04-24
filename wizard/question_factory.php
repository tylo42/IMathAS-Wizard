<?php
/*
 * Author:	Tyler Hyndman
 * Date:	March 13, 2010
 * TODO:
 * 	
 */

require_once("variable.php");
require_once("question.php");

$xml = new DOMDocument();
$xml->load("questions.xml"); //make sure path is correct 

$factory = new question_set_factory;

$question_set = $factory->create($xml);


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
	
	function get($qtype) {
		return $questions[$qtype];
	}
}


class question_set_factory {
	/**
	 * @param[in] $xml   An XML object
	 * @return				A populated question_set
	 */
	public function create($xml) {
		$questions = new question_set;
   
		foreach($xml->getElementsByTagName("question") as $xml_question) {
			// set up the question
			$question = new Question($xml_question->getAttribute("qtype"));
			
			foreach($xml_question->getElementsByTagName("variable") as $xml_variable) {
				$type = $xml_variable->getAttribute("type");
				$vairable = NULL;
				switch($type) {
					case "text":
						$variable = $this->create_text($xml_variable);
						break;
					case "radio":
						$variable = $this->create_radio($xml_variable);
						break;
					default:
						assert("Unknown variable type '".$type."'");
						break;
				} // end switch
				assert($variable != NULL);
				if($xml_variable->getAttribute("required") == true) {
					$question->insert_required($variable);
				} else {
					$question->insert_optional($variable);
				}
			} // end foreach variable
			$questions->insert($question);
		} // end foreach question
		return $questions;
	}
	
	private function create_text($variable) {
		$title = $variable->getElementsByTagName("title")->item(0)->nodeValue;
		$instructions = $variable->getElementsByTagName("instructions")->item(0)->nodeValue;  // Note: this may be moved out of the XML
		$name = $variable->getElemetnsByTagName("name")->item(0)->nodeValue;
		$pre = ???
		$default = $variable->getAttribute("default");
		$ignore_quotes = 
		
		return new text_input($title, $instructions, $name, $pre, $default, $ignore_quotes);
	}
	
	private function create_radio($variable) {
		return new radio_selection_input($title, $instructions, $name, $pre, $default_value, $required, $values);
	}
}




?>
