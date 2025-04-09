<?php
	chdir(dirname(__FILE__));
	chdir('..');

	require_once "./include/inc.header.php";
	global $ilUser;

	function print_r2($val){
        	echo '<pre>';
	        print_r($val);
        	echo  '</pre>';
	}

	print_r2($_SERVER["HTTP_USER_AGENT"]);

	$d = new DateTime();
	$s = $ilUser->getId().";".$d->format('Y-m-d')."T".$d->format('H:i:s');
	echo "<br>".$s."<br>";
	$thehash = hash('sha256', $s);
	echo $thehash."<br>";

	$url = "https://next.bps-system.de/blok/sso";		//"https://liv.bfe-elearning.de/exad/jmpto_blok_debug.php";
	$client = "BFE";
	$userid = $ilUser->getId(); // $ilUser->getLogin()
	$companyid = "BFE_BLok_SSO_a98aScN2Nc8c52jkQwlnw8dmn423mlc3jk_";
	$role = "2"; //oder Ausbilder
	$first = $ilUser->getFirstname();
	$last = $ilUser->getLastname();
	$login = $ilUser->getLogin();
	$mail = $ilUser->getEmail();
	$hash = ";".$thehash;


/*
# Weiterleitung in die Lernplattform hinein
header("Content-Type: text/html; charset=UTF-8");
header('Pragma: no-cache');
header('Expires: Thu, 01-Jan-70 00:00:01 GMT');
	echo "<html><head><title>Weiterleitung zur ILIAS-Plattform...</title></head>\n".
	"<body><form method='post' action='http://broetje.bfe-elearning.de/ilias.php?lang=de&client_id=broe_01&cmd=post&cmdClass=ilstartupgui&cmdNode=21&baseClass=ilStartUpGUI&rtoken='".
	" name='formlogin'>\n".
	"<input type='hidden' name='cmd[showLogin]' value='Anmelden' />\n".
	"<input type='hidden' name='username' value='".htmlspecialchars($kennung)."' />\n".
	"<input type='hidden' name='password' value='".$hashpasswort."' />\n".
	"<p>ILIAS-Plattform wird geladen...</p>\n".
	"<input type='submit' id='s01' value='Bitte klicken Sie hier...' />\n</form>\n".
	"<script type='text/javascript'>document.getElementById('s01').style.display='none';".
	" document.formlogin.submit();</script>\n</body></html>\n";
exit (0);
*/

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "client=" . $client . "&userid=" . $userid . "&companyid=" . $companyid . "&role=" . $role . "&first=" . $first . "&last=" . $last . "&login=" . $login . "&mail=" . $mail . "&hash=" . $hash);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
	$data = curl_exec($ch);

	echo $data;

/*	echo "url: ". $url . "<br>";
	echo "client: ". $client . "<br>";
	echo "userid: ". $userid . "<br>";
	echo "companyid: ". $companyid . "<br>";
	echo "role: ". $role . "<br>";
	echo "first: ". $first . "<br>";
	echo "last: ". $last . "<br>";
	echo "login: ". $login . "<br>";
	echo "mail: ". $mail . "<br>";
	echo "hash: ". $hash . "<br>";
*/

//	echo "<br>";
//	echo $ilUser->getFullname();


//ToDo:
// - 3  Parameter:  url, client, companyid
// - zu klären: Account existiert in BLok noch nicht

/*
<form action="https://blok.bps-system.de/blok/sso" enctype="application/x-www-form-urlencoded" method="post" accept-charset="UTF-8">
<input type="text" name="client" value="BPS" /><br>
<input type="text" name="userid" value="id123" /><br>
<input type="text" name="companyid" value="BPS123" /><br>
<input type="text" name="role" value="2" /><br>
<input type="text" name="first" value="Charlie" /><br>
<input type="text" name="last" value="Smith" /><br>
<input type="text" name="login" value="id123" /><br>
<input type="text" name="mail" value="mail@client.tld" /><br><input type="text" name="hash" value=";E22EE687987EFBEAA16EC674B127C4D4B7B64A4B93B824C0006E08E6419307B" /><br>
<input type="submit" value="SSO" /><br>
</form>
*/
?>
	