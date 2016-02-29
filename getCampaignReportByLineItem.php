<?php

require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/soapClient.php');

$result = $client->getCampaignReportByLineItem(array(
	"fromDate" => date('Y-m-d', time() - 86400 * 31),
	"toDate" => date('Y-m-d', time())
));

$fn = "./delivery.csv";
$fp = fopen($fn, "a");

fwrite($fp, implode(',', array(
	'lineItemId',
	'deliveryDate',
	'impressions',
	'clicks'
)) . "\n");


foreach ($result->getCampaignReportByLineItemResult->item as $index => $item) {

	$deliveryDate = new DateTime($item->date);
	$deliveryDate->setTimezone(new DateTimeZone('Europe/Amsterdam'));

        $columns = array(
                $item->lineItemId,
		$deliveryDate->format('Y-m-d H:i:s'),
		$item->impressions,
                $item->clicks
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
$shell = shell_exec("/usr/local/bin/gsutil cp delivery*.csv gs://api-hub-output/mocean-reports/delivery/");

printf("Deleting downloaded files\n");
shell_exec("rm delivery*.csv");

?>
