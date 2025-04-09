<?php
/*
Array (
    [customer] => Array
        (
            [email] => name@example.org
            [firstname] => Max
            [lastname] => Mustermann
            [gender] => m
            [lang] => de
        )
    [items] => Array
        (
            [@attributes] => Array
                (
                    [quantity] => 2
                )
            [item] => Array
                (
                    [0] => Array
                        (
                            [@attributes] => Array
                                (
                                    [type] => 1
                                )

                            [crsname] => Grundlagen Elektrotechnik 1
                            [duration] => 12
                        )
                    [1] => Array
                        (
                            [@attributes] => Array
                                (
                                    [type] => 1
                                )
                            [crsname] => Drehstromtechnik
                            [duration] => 3
                        )
                )
        )
)
*/


/*	Rückgabewerte:
	 1: Benutzer erzeugt und dem Kurs zugewiesen
	 2: Benutzer existiert schon, nur dem Kurs zugewiesen

	-1: xmlRequest leer
	-2: postvar:hash stimmt nicht
	-3: kein ILIASconnection-Object
	-4: kein ILIASconnection-Connect
	-5: ILIAS-account-Aktion "removeuser" fehlgeschlagen" (falls $accountsimmerneu = TRUE)
	-6: ILIAS-account-Aktion "createuser" fehlgeschlagen" 
	-7 bis -9: ILIAS-account-Aktion "joincourse" fehlgeschlagen"
	-10: ILIAS-account-Aktion "joincourse" fehlgeschlagen", Nutzer bereits im Kurs
*/

/*	ToDo:
	Array mit refIDs der Kurse
	durch vergleich Kurs-refID ermitteln
	OK	soap: Benutzer anlegen, falls noch nicht existent, was ist, wenn user schon vorhanden
	soap: Benutzer den Kursen hinzufügen (for-schleife)
	lang auswerten (createUser?)
	OK	Passwort erzeugen
	OK	Mail gestalten und an user schicken
	eigene Mailroutine (falls Benutzer existiert/nicht existiert, 
	Fehler in createuser-exception-handler (wird immer ausgeführt)

	Datenbanktabelle anlegen mit Benutzer, refID und Dauer (timestamp)
	in cronjob Datenbanktabelle durchgehen und zeitüberschrittene Kursteilnehmer aus Kurs entfernen
	Benutzer löschen, die keinen Kurs mehr haben
*/

	require_once("ilias.class.php");
	$thesecret = "bfeShop2ilias5";


	class simple_xml_extended extends SimpleXMLElement
	{
		public function Attribute($name)
		{
			foreach($this->Attributes() as $key=>$val)
			{
				if($key == $name) 
					return (string)$val;
			}
		}
	}

	function fehler_intern($nachricht = "", $details = "") {
		echo $nachricht;
	}

	if (empty($_REQUEST['xmlRequest'])) {
		echo "-1"; 
		exit;
	} else {
		$xmlRequest = $_REQUEST['xmlRequest'];
		$hash = strtolower(@trim($_REQUEST["hash"]));
	}
	
	$checkhash = sha1($xmlRequest.$thesecret, FALSE);
	if ( $checkhash != $hash ) {
		echo "-2"; 
		exit;
	}

	# XML auslesen
        $array_data = json_decode(json_encode(simplexml_load_string($xmlRequest)), true);

	$kennung = $array_data["customer"]["email"];
	$ufn = $array_data["customer"]["firstname"];
	$uln = $array_data["customer"]["lastname"];
	$ilang = $array_data["customer"]["lang"];

	//$numOfProducts = intval($array_data["items"]["@attributes"]["quantity"]);
	$numOfProducts = (int)$array_data["items"]["@attributes"]["quantity"];

//echo((string)$numOfProducts);
//echo($array_data["items"]["item"]["0"]["crsname"]);
//echo($array_data["items"]["item"]["1"]["duration"]);
//echo($array_data["items"]["item"]["1"]["@attributes"]["type"]);
//exit;

	// $idRand = base_convert(mt_rand(2000000, 9999999), 10, 36);
	// $kennung = "bbs-".$idRand;

	$rand = '';
	for($i = 0; $i < 6; $i++)
	{
		$rand .= chr(mt_rand(97, 122));
	}
	$pwd = "s2i".$rand;
	$imail = $array_data["customer"]["email"];
	$gender = $array_data["customer"]["gender"];

	$accountsimmerneu = FALSE;
//	$cid = "R856"; //BFE EuP-Kurs (Jahresunterweisung)
	$cid = "R884"; //BFE-Kurs Grundlagen 1

	$howmanydays = 182;


	# ILIAS instanziieren
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

	# Prüfe, ob es schon ein Handle in ILIAS gibt:
	$uhandle = $ILIAS->finduserhandle($kennung);
	if ( ( $uhandle > 0 ) && ( $accountsimmerneu === TRUE ) ) {
		# Falls gewünscht, Account jetzt löschen
		if ( ! $ILIAS->removeuser($uhandle) ) {
			// fehler_intern("ILIAS-account-Aktion fehlgeschlagen","removeuser(".$uhandle.")");
			echo "-5"; 
			exit;
		}
		$uhandle = 0;
	}

if ( $uhandle < 1 ) {
	# Account anlegen
	try {
		$cuRes = $ILIAS->createuser( array(
			"vorname" => $ufn, "nachname" => $uln, "titel" => "",
			"passwort" => $pwd, "email" => $imail, "geschlecht" => $gender,
			"loginname" => $kennung, "gueltigtage" => $howmanydays, "agbok" => TRUE, "userLang" => $ilang ) );
	} catch ( Exception $e ) {
		// fehler_intern("ILIAS-account-Aktion fehlgeschlagen","createuser...: ".$e->getMessage());
		// echo "-6"; 
		// exit;
	}

	$uhandle = $ILIAS->finduserhandle($kennung);
	if ($uhandle > 0) {
		try {
			# Immer als "Member" einbuchen
			if ( ! $ILIAS->joincourse ( $uhandle, $cid, "Member" ) ) {
				// fehler_intern("Buchung fehlgeschlagen", "ohne weitere Fehlermeldung: uhandle ".$uhandle.", kurs ".$cid);
				echo "-7"; 
				exit;
			}
			echo "1";
			exit;
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

	try {
		# Immer als "Member" einbuchen
		if ( ! $ILIAS->joincourse ( $uhandle, $cid, "Member" ) ) {
			// fehler_intern("Buchung fehlgeschlagen", "ohne weitere Fehlermeldung: uhandle ".$uhandle.", kurs ".$cid);
			echo "-9"; 
			exit;
		}
		// echo '<script type="text/javascript" language="Javascript">alert("Benutzer existent, try to join.")</script> '; 
		echo "2";
		exit;
	} catch ( Exception $e ) {
		// fehler_intern("Buchung fehlgeschlagen", "uhandle ".$uhandle.", kurs: ".$cid.", Exception: ".$e->getMessage());
		echo "-10"; 
		exit;
	}

	 // per mail benachrichtigen

}

//--------------------


//      print_r('<pre>');
//	print_r($array_data);
//      print_r('</pre>');

?>