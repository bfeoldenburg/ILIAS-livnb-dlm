<?php 
//	require_once("../../Services/Init/classes/class.ilInitialisation.php");
//	ilInitialisation::initILIAS();
//	require_once "./include/inc.header.php";
	require_once './libs/composer/vendor/autoload.php';

	require_once("./exad/c1/ilias.class.php");

	// !!! Hier Variablen anpassen !!!
	// Anpassungen in ilias.class.php notwendig

	$cid = "R28312"; //Kreishandwerkerschaft
	$userCnt = 12;
	$userNamePrefix = "Raum10-User";
	$ufn = "KHS-OL - Raum10";
	$ulnp = "Benutzer ";
	$pwd = "ilias-livz";
	$howmanydays = 0;
	$imail = "name@example.org";
	$gender = "m";
	$ilang = "de";
	$accountsimmerneu = true;

if($_REQUEST['submit'] == 'Benutzer neu anlegen' && isset($_REQUEST['submit'])){
	if ((empty($_REQUEST["dtp_input1"])) and (empty($_REQUEST["dtp_input2"])) and (empty($_REQUEST["pwd_input"])) and (empty($_REQUEST["remember"]))) { 
		$divmsg = "Bitte erst alle Felder ausfüllen!";
		unset($_REQUEST["dtp_input1"]);
		unset($_REQUEST["dtp_input2"]);
		unset($_REQUEST["pwd_input"]);
		unset($_REQUEST["remember"]);
	} else if ((empty($_REQUEST["dtp_input1"])) or (empty($_REQUEST["dtp_input2"]))){
		$divmsg = "Beginn und/oder Ende nicht angegeben!";
		unset($_REQUEST["dtp_input1"]);
		unset($_REQUEST["dtp_input2"]);
		unset($_REQUEST["pwd_input"]);
		unset($_REQUEST["remember"]);
	} else if (empty($_REQUEST["pwd_input"])){
		$divmsg = "Passwort nicht angegeben!";
		unset($_REQUEST["dtp_input1"]);
		unset($_REQUEST["dtp_input2"]);
		unset($_REQUEST["pwd_input"]);
		unset($_REQUEST["remember"]);
	} else if (empty($_REQUEST["remember"])){
		$divmsg = "Bestätigung fehlt!";
		unset($_REQUEST["dtp_input1"]);
		unset($_REQUEST["dtp_input2"]);
		unset($_REQUEST["pwd_input"]);
		unset($_REQUEST["remember"]);
	} else {

		$mArr = array(1 => 'Januar', 2 => 'Februar', 3 => 'März', 4 => 'April', 5 => 'Mai', 6 => 'Juni', 7 => 'Juli', 8 => 'August', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Dezember');
		$errFl = 0;

		$startDT = 0;
		$d = explode(" ",$_REQUEST["dtp_input1"]);
		$startDT = mktime(0, 0, 0, array_search($d[1], $mArr), $d[0], $d[2]);

		$endDT = 0;
		$e = explode(" ",$_REQUEST["dtp_input2"]);
		$endDT = mktime(21, 59, 0, array_search($e[1], $mArr), $e[0], $e[2]);

		$pwd = trim($_REQUEST["pwd_input"]);

		if ((int)$endDT <= (int)$startDT) { $errFl = 1; }
		if ((int)$endDT <= time()) { $errFl = 2; }

	if ($errFl == 0) {
		// ILIAS instanzieren
		if ( ! ($ILIAS = new ILIASconnection() ) ) {
		//	fehler_intern("ILIASconnection fehlgeschlagen");
			echo "-3"; 
			exit;
		}
		try {
			$sid = $ILIAS->soap_connect();
		} catch ( Exception $e ) {
		//	fehler_intern("ILIAS soap_connect fehlgeschlagen", $e->getMessage());
			echo "-4"; 
			exit;
		}
	
		$tmpStr = "<br>";
		$tmpStr = "<br><table style=\"width:35%\">";
		$tmpStr .= "<tr><td><b>Benutzer</b></td><td><b>Passwort</b></td></tr>";

		for ($i = 1; $i <= $userCnt; $i++) {
			$kennung = $userNamePrefix . $i;

	// Prüfe, ob es schon ein Handle in ILIAS gibt:
	$uhandle = $ILIAS->finduserhandle($kennung);
	if ( ( $uhandle > 0 ) && ( $accountsimmerneu === TRUE ) ) {
		// Falls gewünscht, Account jetzt löschen
		if ( ! $ILIAS->removeuser2($uhandle) ) {
			// fehler_intern("ILIAS-account-Aktion fehlgeschlagen","removeuser(".$uhandle.")");
			echo "-5"; 
			exit;
		}
		$uhandle = 0;
	}

if ( $uhandle < 1 ) {
	// Account anlegen
	$uln = $ulnp . $i;

	try {
		$cuRes = $ILIAS->createuser( array(
			"vorname" => $ufn, "nachname" => $uln, "titel" => "",
			"passwort" => $pwd, "email" => $imail, "geschlecht" => $gender,
			"loginname" => $kennung, "gueltigtage" => $howmanydays, "startDate" => $startDT, "endDate" => $endDT, "agbok" => TRUE, "userLang" => $ilang ) );
	} catch ( Exception $e ) {
		// fehler_intern("ILIAS-account-Aktion fehlgeschlagen","createuser...: ".$e->getMessage());
		// echo "-6"; 
		// exit;
	}


	$uhandle = $ILIAS->finduserhandle($kennung);
	if ($uhandle > 0) {
		try {
			// Immer als "Member" einbuchen
			if ( ! $ILIAS->joincourse ( $uhandle, $cid, "Member" ) ) {
				// fehler_intern("Buchung fehlgeschlagen", "ohne weitere Fehlermeldung: uhandle ".$uhandle.", kurs ".$cid);
				echo "-7"; 
				exit;
			}

		} catch ( Exception $e ) {
			// fehler_intern("Buchung fehlgeschlagen", "uhandle ".$uhandle.", kurs: ".$cid.", Exception: ".$e->getMessage());
			echo "-8"; 
			exit;
		}
	} else {
		// Fehler, sollte eigentlich weiter oben in der Exception behandelt werden, funktioniert aber nicht
		// fehler_intern("ILIAS-account-Aktion fehlgeschlagen","createuser...: ".$e->getMessage());
		echo "-6"; 
		exit;

	}

} else {
	// Benutzer existiert schon, also nur im Kurs anmelden
	echo "uhandle <> 0, Löschen des users hat nicht funktioniert"; //sollte niemals ausgeführt werden
	exit;
}




 			$tmpStr .= "<tr><td>" . $kennung . "</td>" . "<td>" . $pwd . "</td></tr>";
		} //end for

		$tmpStr .= "</table>";
		$divmsg = "Benutzer erfolgreich angelegt!<br>" . "Beginn: " . $_REQUEST["dtp_input1"] . "<br>" . "Ende:  ". $_REQUEST["dtp_input2"] . "<br><br>" . "Debug: Ack " . $_REQUEST["remember"] . "<br>"  . "Debug: " . $sid . "<br>" . $tmpStr . "<br>" . date("c");

//		$url = "https://liv.bfe-elearning.de/ilias.php?ref_id=28317&cmdClass=illearningprogressgui&cmdNode=20:pd:67&baseClass=ilRepositoryGUI";
//		$url = "https://liv.bfe-elearning.de";

		$query = http_build_query([
		 'ref_id' => '28317',
		 'cmdClass' => 'illearningprogressgui',
		 'cmdNode' => '20:pd:67',
		 'baseClass' => 'ilRepositoryGUI'
		]);
		$url = "https://liv.bfe-elearning.de/?".$query;

//		$ch = curl_init();
//		curl_setopt($ch, CURLOPT_URL, $url);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
//		curl_setopt($ch, CURLOPT_CAINFO,  getcwd().'/cacert.pem'); 
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
////		$data = curl_exec($ch);
//		curl_close($ch);


		// echo $data;
//		$divmsg .= $data;


	} else {
		if ($errFl == 1) {
			$divmsg = "Ungültig: Ende-Datum liegt vor dem Start-Datum!";
		} elseif ($errFl == 2) {
			$divmsg = "Ungültig: Ende-Datum liegt in der Vergangenheit!";
		} else {
		}
	}
	}
} else {
	$divmsg = "";
}

// Aus z.B. 10 September 2020 die Zahlen 10, 9, 2020 erzeugen, dann
// echo mktime(0, 0, 0, 9, 10, 2020);  --> 1599721200
// echo date('d/m/Y H:i:s', 1599721200);  --> 10/09/2020 00:00:00

?>

<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="pragma" content="no-cache">
	<title>ILIAS Benutzer Generator</title>
	<link href="./exad/c1/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="./exad/c1/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
</head>

<body>
<div><br/><br/></div>
<div class="container">
	<form action="" class="form-horizontal" method="post" role="form">
	<legend>Lernplattform Landesinnungsverband für Elektro- und Informationstechnik Niedersachsen / Bremen<br>
Kurs "Kreishandwerkerschaft Oldenburg"<br>
Raum 10 - Benutzerverwaltung</legend>

		<div><h4>Auf dieser Seite können die Benutzer User1 bis User12 für Raum 10 neu angelegt werden.<br/>Achtung: Alle vorhandenen Lernstände und Verknüpfungen zum Persönlichen Schreibtisch gehen dabei verloren!</h4></div>
		<div><br/></div>

		<label for="dtp_input1" class="col-md-2 control-label">Beginn</label>
		<div class="input-group date form_date col-md-2" data-date="" data-date-format="dd MM yyyy" data-link-field="dtp_input1" data-link-format="dd MM yyyy">
			<input class="form-control" size="16" type="text" value="<?php echo $_REQUEST["dtp_input1"]; ?>" readonly>
			<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
		</div>
		<input type="hidden" id="dtp_input1" name="dtp_input1" value="" /><br/>

		<label for="dtp_input2" class="col-md-2 control-label">Ende</label>
		<div class="input-group date form_date col-md-2" data-date="" data-date-format="dd MM yyyy" data-link-field="dtp_input2" data-link-format="dd MM yyyy">
			<input class="form-control" size="16" type="text" value="<?php echo $_REQUEST["dtp_input2"]; ?>" readonly>
			<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
		</div>
		<input type="hidden" id="dtp_input2" name="dtp_input2" value="" /><br/>

		<!-- <div>Bitte Anfangs- und Endedatum festlegen.</div> -->
		<div><br/></div>

  		<label for="pwd_input" class="col-md-2 control-label">Passwort</label>
		<div class="input-group col-md-2">
		<input type="text" class="form-control" size="16" id="pwd_input" name="pwd_input" placeholder="min. 6 Zeichen">
		</div>
	
		<div><br/></div>

		<div class="form-check">
			<label><input type="checkbox" name="remember"> Bitte bestätigen, dass beim Anlegen neuer Benutzer der Lernfortschritt zurückgesetzt wird!</label>
		</div>

		<div><br/></div>
		<input type="submit" class="btn btn-primary" name="submit" value="Benutzer neu anlegen">
	</form>
	<div><br/></div>
	<div><h4><?php echo $divmsg."<br>"; ?></h4></div>
</div>

<script type="text/javascript" src="./exad/c1/jquery/jquery-3.3.1.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="./exad/c1/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="./exad/c1/js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
<script type="text/javascript" src="./exad/c1/js/locales/bootstrap-datetimepicker.de.js" charset="UTF-8"></script>
<script type="text/javascript">
    $('.form_date').datetimepicker({
	language:  'de',
	weekStart: 1,
	todayBtn:  1,
	autoclose: 1,
	todayHighlight: 1,
	startView: 2,
	minView: 2,
	forceParse: 0
    });
</script>
</body>
</html>
