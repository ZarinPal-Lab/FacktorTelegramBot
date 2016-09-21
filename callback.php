<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head id="Head1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<title>پرداخت</title>
<?php
	
	include('include/config.php');
	include('include/function.php');
	
	$list_log 	= mysql_query("SELECT * FROM `log` WHERE `id`='".$_GET['id']."' ");
	$item_log 		= mysql_fetch_array($list_log);
	if($item_log['step'] == 4)
	{
		$Authority = $_GET['Authority'];
		if ($_GET['Status'] == 'OK')
		{
			$client = new SoapClient('https://www.zarinpal.com/pg/services/WebGate/wsdl');

			$result = $client->PaymentVerification(array(
				'MerchantID'     => $Merchant,
				'Authority'      => $Authority,
				'Amount'         => $item_log['price'],
			));
			
			if ($result->Status == 100)
			{
				echo 'Transation success. RefID:'.$result->RefID;
				
				$update['step'] = 5;
				$update['res1'] = $result->RefID;
			
				$sql = queryUpdate('log', $update, 'WHERE `id` = '.$item_log['id'].';');
				execute($sql);
				
				$text_reply = 'پرداخت شما با موفقیت به پایان رسید.
کد پیگیری شما: '.$result->RefID.'
شناسه پرداخت: '.$item_log['id'] ;
				
				$replyMarkup = array(
					'keyboard' => array(
						array("شروع پرداخت جدید")
					)
				);
				$encodedMarkup = json_encode($replyMarkup);
				$url = 'https://api.telegram.org/bot'.$Token.'/sendMessage';

				$ch = curl_init( );
				curl_setopt( $ch, CURLOPT_URL, $url );
				curl_setopt( $ch, CURLOPT_POST, 1 );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, "text=".$text_reply."&chat_id=".$item_log['user']."&reply_markup=" .$encodedMarkup);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
				$agent = $_SERVER["HTTP_USER_AGENT"];
				curl_setopt($ch, CURLOPT_USERAGENT, $agent);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

				$check = curl_exec( $ch );
			} else {
				echo 'Transation failed. Status:'.$result->Status;
			}
		} else {
			echo 'Transaction canceled by user';
		}
	}else
		echo('این پرداخت قبلا انجام شده است');
?>