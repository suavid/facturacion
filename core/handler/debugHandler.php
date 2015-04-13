<?php

class debugHandler{

	

	public function __construct(){

		
	}

	public static function setAssert($assertion, $description){
		
		$options = array(ASSERT_ACTIVE => 1,ASSERT_WARNING => 0,ASSERT_QUIET_EVAL => 1);

		if(is_array($options)){
			
			foreach ($options as $key => $value) {
				
				assert_options($key, $value);
			}
		}


		assert_options(ASSERT_CALLBACK, function($file, $line, $code, $descrp){
			die("Assertion failed at $file:$line: $code [$descrp]");
		});

		return assert($assertion, $description);
	}

}

?>