<?php
	chdir(dirname(__FILE__));
	chdir('..');

	require_once "./include/inc.header.php";
	global $ilUser, $ilDB, $rbacreview;

	function dbgc( $object=null, $label=null ){ ob_start(); $vType = gettype($object); $message = json_encode($object,JSON_PRETTY_PRINT); $label = ($label ? " $label: " : ': '); }
	function print_r2($val){
        	echo '<pre>';
	        print_r($val);
        	echo  '</pre>';
	}

	$blokkey = "BFE_BLok_SSO_a98aScN2Nc8c52jkQwlnw8dmn423mlc3jk_";
	$d = new DateTime();
	$data = $ilUser->getId().";".$d->format('Y-m-d')." ".$d->format('H:i:s');
	$key = $blokkey . $d->format('Ymd');

	$thehash = hash_hmac( 'sha256', $data, $key );

	$url = "https://next.bps-system.de/blok/sso";
//	$url = "https://liv.bfe-elearning.de/exad/jmpto_blok_debug.php";
	$client = "BFE";
	$userid = $ilUser->getId(); // $ilUser->getLogin()
	$role = "2"; // wird 3, wenn Ausbilder
	$first = $ilUser->getFirstname();
	$last = $ilUser->getLastname();
	$login = $ilUser->getLogin();
	$mail = $ilUser->getEmail();
	$hash = ";".$thehash;

	# $companyID ermiteln
	$query = 'SELECT udf_text.value FROM udf_definition, udf_text WHERE udf_text.usr_id = "' .$userid. '" && udf_text.field_id = udf_definition.field_id && udf_definition.field_name = "blok_compID"';
	$rec = $ilDB->fetchAssoc($ilDB->query($query));
	$companyid = (empty($rec[value])) ? 'BFE0001' : $rec[value];

	# $userRoles ermiteln
	foreach($rbacreview->assignedRoles($userid) as $role_id)
	{
		if($tmp_obj = ilObjectFactory::getInstanceByObjId($role_id,false))
		{
			$objs[] = $tmp_obj;
		}
	}

	if(count($objs))
	{
		include_once './webservice/soap/classes/class.ilObjectXMLWriter.php';
		$xml_writer = new ilObjectXMLWriter();
		$xml_writer->setObjects($objs);
		if($xml_writer->start())
		{
			$userRoles = new SimpleXMLElement($xml_writer->getXML());
		}
		$a_userRoles = json_decode(json_encode($userRoles), true);
		foreach ($a_userRoles as $arr) {
		foreach ($arr as $arr2) {
			// echo $arr2['Description'] . "<br/>";
			if (is_string($arr2['Description'])) {
			if ((strpos($arr2['Description'], 'Tutor') !== false) ||  (strpos($arr2['Description'], 'Admin') !== false)) {
				$role = 3;
				break 2;
			}
			}
		}
		}
	}

	# Weiterleitung BLok
	header("Content-Type: text/html; charset=UTF-8");
	header('Expires: Thu, 01-Jan-70 00:00:01 GMT');
	header('Pragma: no-cache');
	header("Cache-Control: no-cache");
	echo "<html><head><title></title></head>\n".
	"<body><form method='post' action='" . $url . "' enctype='application/x-www-form-urlencoded' accept-charset='UTF-8' name='bloklogin'>\n".
	"<input type='hidden' name='client' value='" . $client . "' />\n".
	"<input type='hidden' name='userid' value='" . $userid . "' />\n".
	"<input type='hidden' name='companyid' value='" . $companyid . "' />\n".
	"<input type='hidden' name='role' value='" . $role . "' />\n".
	"<input type='hidden' name='first' value='" . $first . "' />\n".
	"<input type='hidden' name='last' value='" . $last . "' />\n".
	"<input type='hidden' name='login' value='" . $login . "' />\n".
	"<input type='hidden' name='mail' value='" . $mail . "' />\n".
	"<input type='hidden' name='hash' value='" . $hash . "' />\n".
//	"<input type='hidden' name='test' value='" . $userRoles . "' />\n".
	"<input type='submit' id='s01' value='SSO' />\n</form>\n".
	"<script type='text/javascript'>document.getElementById('s01').style.display='none';".
	" document.bloklogin.submit();</script>\n</body></html>\n";
	
	exit (0);

/*
<form action="https://blok.bps-system.de/blok/sso" enctype="application/x-www-form-urlencoded" method="post" accept-charset="UTF-8">
<input type="text" name="client" value="BPS" /><br>
<input type="text" name="userid" value="id123" /><br>
<input type="text" name="companyid" value="BPS123" /><br>
<input type="text" name="role" value="2" /><br>
<input type="text" name="first" value="Charlie" /><br>
<input type="text" name="last" value="Smith" /><br>
<input type="text" name="login" value="id123" /><br>
<input type="text" name="mail" value="mail@client.tld" /><br>
<input type="text" name="hash" value=";E22EE687987EFBEAA16EC674B127C4D4B7B64A4B93B824C0006E08E6419307B" /><br>
<input type="submit" value="SSO" /><br>
</form>
*/
?>
	