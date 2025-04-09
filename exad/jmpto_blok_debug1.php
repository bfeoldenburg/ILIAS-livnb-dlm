<?php
	function print_r2($val){
        	echo '<pre>';
	        print_r($val);
        	echo  '</pre>';
	}


	echo "Array: \$_POST<br>";
	print_r2($_POST);

	echo "Funktion: getallheaders<br>";
	$headers =  getallheaders();
	print_r2($headers);

	echo "Funktion: apache_request_headers<br>";
	$headers =  apache_request_headers();
	print_r2($headers);
//	foreach($headers as $key=>$val){
//	  echo $key . ': ' . $val . '<br>';
//	}

	echo "Array: \$_SERVER<br>";
	print_r2($_SERVER);

	echo "Array: \$_GLOBALS<br>";
	print_r2($GLOBALS);

//	echo "tpl:: " . $GLOBALS["tpl"]
?>