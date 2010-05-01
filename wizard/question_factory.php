<?php
/*
 * Author:	Tyler Hyndman
 * TODO:
 * 	
 */

require_once("variable.php");
require_once("question.php");

/*
$xml = new DOMDocument();
$xml->load("questions.xml");

$factory = new question_set_factory;

$question_set = $factory->create($xml);
/**/
////////////////////////////////////////////////////////

// Make this a singleton
class question_set_factory {
	/**
	 * @param[in] $xml   An XML object
	 * @return				A populated question_set
	 */
	public function create($xml) {
		$questions = array();
   
		foreach($xml->getElementsByTagName("question") as $xml_question) {
			// set up the question
			$question = new Question($xml_question->getAttribute("qtype"));

			foreach($xml_question->getElementsByTagName("variable") as $xml_variable) {
				$type = $xml_variable->getAttribute("type");
				$vairable = NULL;
				switch($type) {
					case "text":
						$variable = $this->create_text($xml_variable, $question->get_pre());
						break;
					case "radio":
						$variable = $this->create_radio($xml_variable, $question->get_pre());
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
			assert(!isset($questions[$question->get_qtype()]));
			$questions[$question->get_qtype()] = $question;
		} // end foreach question
		return $questions;
	}
	
	private function create_text($variable, $pre) {
		$title = $variable->getElementsByTagName("title")->item(0)->nodeValue;
		$instructions = $variable->getElementsByTagName("instructions")->item(0)->nodeValue;  // Note: this may be moved out of the XML
		$name = $variable->getElementsByTagName("name")->item(0)->nodeValue;
		$default_value = $variable->getAttribute("default_value");
		
		if($variable->getAttribute("ignore_quotes") == "true") {
			$ignore_quotes = true;
		} else {
			$ignore_quotes = false;
		}
		
		return new text_input($title, $instructions, $name, $pre, $default_value, $ignore_quotes);
	}
	
	private function create_radio($variable, $pre) {
		$title = $variable->getElementsByTagName("title")->item(0)->nodeValue;
		$instructions = $variable->getElementsByTagName("instructions")->item(0)->nodeValue;
		$name = $variable->getElementsByTagName("name")->item(0)->nodeValue;
		$default_value = $variable->getAttribute("default_value");
		foreach($variable->getElementsByTagName("selection") as $selection) {
			$values[$selection->nodeValue] = $selection->getAttribute("name");	
		}
		
		return new radio_selection_input($title, $instructions, $name, $pre, $default_value, $values);
	}
}




?>
