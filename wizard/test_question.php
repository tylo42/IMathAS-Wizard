<?php

require_once("question.php");

class Test_question extends Question{
	function __construct(){ 
		parent::__construct("test",true);
	}
}

?>
