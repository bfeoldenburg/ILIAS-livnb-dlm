<?php
	function print_r2($val){
        	echo '<pre>';
	        print_r($val);
        	echo  '</pre>';
	}

	echo "Array: \$_POST<br>";
	print_r2($_POST);
?>