<?php

	include ('include/config.php');
	include ('include/function.php');
	include ('include/jdf.php');
	
	$day_number = jdate('j'); 
	$month_number = jdate('n'); 
	$year_number = jdate('Y'); 
	$time = jdate ('H:i:s');
	
	$day = $year_number.'/'.$month_number.'/'.$day_number.' - '.$time;
	
	
	$string 	= json_decode(file_get_contents('php://input'));
	$result 	= objectToArray($string);
	$user_id 	= $result['message']['from']['id'];
	$text 		= $result['message']['text'];
	
	$list_log 	= mysql_query("SELECT * FROM `log` WHERE `user`='".$user_id."' ORDER BY `id` DESC ");
	$item_log 		= mysql_fetch_array($list_log);
	
	if($item_log['step'] > 0 AND $item_log['step'] < 4)
	{
		if($text == 'Cancel')
		{
			$update['step'] = 0;
			
			$sql = queryUpdate('log', $update, 'WHERE `id` = '.$item_log['id'].';');
			execute($sql);
			
			$text_reply = 'درخواست شما لغو شد.جهت شروع دوباره مبلغ درخواستی را به تومان ارسال نمایید.';
		}elseif($item_log['step'] == 1)
		{
			$update['name'] = $text;
			$update['step'] = 2;
			
			$sql = queryUpdate('log', $update, 'WHERE `id` = '.$item_log['id'].';');
			execute($sql);
			
			$text_reply = 'ایمیل تان را ارسال نمایید';
		}elseif($item_log['step'] == 2)
		{
			$update['email'] = $text;
			$update['step'] = 3;
			
			$sql = queryUpdate('log', $update, 'WHERE `id` = '.$item_log['id'].';');
			execute($sql);
			
			$text_reply = 'توضیحات تان راجب پرداخت را ارسال نمایید.';
		}elseif($item_log['step'] == 3)
		{
			$update['detail'] = $text;
			$update['step'] = 4;
			
			$sql = queryUpdate('log', $update, 'WHERE `id` = '.$item_log['id'].';');
			execute($sql);
			
			$pay_url = 'http://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI];
			
			$pay_url = str_replace('rec.php','pay.php?id='.$item_log['id'],$pay_url);
			
			$text_reply = 'فاکتور خرید
نام: '.$item_log['name'].'
ایمیل: '.$item_log['email'].'
مبلغ: '.$item_log['price'].' تومان
لینک پرداخت شما: 
'.$pay_url.'

لطفاً اطلاعات خرید را در فاکتور بالا بررسی نمایید و در صورت صحت اطلاعات، از طریق لینک پرداخت نسبت به پرداخت فاکتور اقدام نمایید.
توجه: تا کامل شدن عملیات پرداخت صبر کنید، پس از پرداخت اطلاعات خرید در همین ربات به شما نمایش داده خواهد شد.';
		}else
			$text_reply = 'جهت شروع پرداخت مبلغ را به تومان ارسال نمایید.';
	}else{
		if(is_numeric($text)){
			if($text >= 100)
			{
				$insert['user'] = $user_id;
				$insert['price'] = $text;
				$insert['date'] = $day;
				$insert['step'] = 1;
				
				$sql 	= queryInsert('log', $insert);
				execute($sql);
				
				$text_reply = 'نام تان را ارسال نمایید';
			}else
				$text_reply = 'حداقل مبلغ 100 تومان می باشد';
		}else
			$text_reply = 'جهت شروع پرداخت مبلغ را به تومان ارسال نمایید.';
	}
	
		
	$replyMarkup = array(
		'keyboard' => array(
			array("Cancel")
		)
	);
	$encodedMarkup = json_encode($replyMarkup);
	$url = 'https://api.telegram.org/bot'.$Token.'/sendMessage';

	$ch = curl_init( );
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, "text=".$text_reply."&chat_id=".$user_id."&reply_markup=" .$encodedMarkup);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 500 );
	$agent = $_SERVER["HTTP_USER_AGENT"];
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	$check = curl_exec( $ch );
	
	echo('OK!');
	
?>