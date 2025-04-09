<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('soap.wsdl_cache_enabled', 1); // prevent WSDL cache

//require_once("./webservice/soap/lib/nusoap.php"); // Path to nusoap.php file of ILIAS

$soapdb = "c-il-livnb";
$soapuser = "root";  //"usr_soap";
$soappass = "Liv_42ILnb!";  //"i42soap";

$ilias_base_url = 'https://liv.bfe-elearning.de';
$wsdl = $ilias_base_url . '/webservice/soap/server.php?wsdl';
//$GLOBALS['ilias_installation_path'] = 'il_0_';
//$client = new nusoap_client($ilias_base_url . '/webservice/soap/server.php?wsdl&client_id=' . 'default', true);
//$client->soap_defencoding = 'UTF-8';
$err = null;

$sid = "000";
$client = new SoapClient($wsdl, array('trace' => 1, 'encoding' => 'UTF-8', 'cache_wsdl' => WSDL_CACHE_MEMORY));
//var_dump($client);
$sid=$client->login($soapdb,$soapuser,$soappass);
echo $sid;
exit;

if ($err = $client->getError()) {
    echo "Fehler bei der Verbindung zum Webservice:<br>";
    echo $err;
}
else {
    echo "Verbindung zum Webservice erfolgreich.<br>";
    var_dump($client->getFunctions());
}

