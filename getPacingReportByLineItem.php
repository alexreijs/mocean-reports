<?php

require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/soapClient.php');

$result = $client->getPacingReportByLineItem();

$fn = "./line-items.csv";
$fp = fopen($fn, "a");

fwrite($fp, implode(',', array(
	'orderId',
	'orderName',
	'lineItemId',
	'lineItemName',
	'externalId',
	'startDateTime',
	'endDateTime',
	'impressionsDelivered',
	'clicksDelivered',
	'expectedDeliveryPercentage',
	'actualDeliveryPercentage',
	'status',
	'primaryGoalUnitType',
	'primaryGoalUnits'
)) . "\n");


foreach ($result->getPacingReportByLineItemResult->item as $index => $item) {

	$startDate = new DateTime($item->startDate);
	$startDate->setTimezone(new DateTimeZone('Europe/Amsterdam'));

	$endDate = new DateTime($item->endDate);
	$endDate->setTimezone(new DateTimeZone('Europe/Amsterdam'));

	preg_match("/0[0-9]{9}/", $item->orderName, $paIds);
	preg_match("/0[0-9]{5}/", $item->lineItemName, $paLineIds);

        $columns = array(
                $item->orderId,
                '"' . str_replace('"', '""', $item->orderName) . '"',
                $item->lineItemId,
                '"' . str_replace('"', '""', $item->lineItemName) . '"',
		((count($paIds) == 1 and count($paLineIds) == 1) ? $paIds[0] . '-' . $paLineIds[0] : ''),
                $item->startDate == '' ? null : $startDate->format('Y-m-d H:i:s'),
                $item->endDate == '' ? null : $endDate->format('Y-m-d H:i:s'),
                $item->units == "IMPRESSIONS" ? $item->totalDelivered : 0,
                $item->units == "CLICKS" ? $item->totalDelivered : 0,
		$item->optimalPercent,
		$item->percentDelivered,
		$item->currentStatus,
                $item->units,
                $item->booked
        );
        foreach ($columns as $i => $column) {
                $line = $column . ($i == count($columns) - 1 ? "\n" : ",");
                fwrite($fp, $line);
        }

/*
   [lineItemId] => 431812
    [lineItemName] => tb_jumbo_f7_nuipad_130713
    [orderId] => 147569
    [orderName] => Jumbo BBQ 2013 - 100016724 - 040613
    [startDate] => 2013-07-13T03:00:00-05:00
    [endDate] => 2013-07-13T07:00:00-05:00
    [booked] => 30000
    [units] => IMPRESSIONS
    [todayDelivered] => 0
    [optimalDailyDelivery] => 144000
    [totalDelivered] => 30000
    [optimalTotalDelivery] => 30000
    [percentDelivered] => 100
    [optimalPercent] => 100
    [onSchedule] => OK
    [currentStatus] => ENDED
*/

}

fclose($fp);

printf("Uploading to Google Storage\n");
$shell = shell_exec("/usr/local/bin/gsutil cp line-items*.csv gs://api-hub-output/mocean-reports/line-items/");

printf("Deleting downloaded files\n");
shell_exec("rm line-items*.csv");

?>
