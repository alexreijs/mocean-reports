<?php

ini_set("soap.wsdl_cache_enabled", "0");

// Create the SoapClient instance
$wsdl = "http://sanomamedia.moceanmobile.net/soap/reporting/wsdl";
$ns = "http://sanomamedia.moceanmobile.net/soap/reporting";
$client = new SoapClient($wsdl, array(
        "trace" => 1,
        "exceptions" => 0
));

$client = new SoapClient($wsdl, array('trace' => 1));

$headerBody = array('apiKey' => $apiKey);
$header = new SoapHeader($ns, 'authHeader', $headerBody);

$client->__setSoapHeaders($header);


?>
