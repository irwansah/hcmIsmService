<?php
namespace serviceism\helpers;

/**
 * 
 */
class PushNotif
{
	//hit API notification
    public function sendNotification($title,$body,$email){
        $url = 'https://taskmgmt.telkomsel.co.id/api/integrate/remindernotification?title='.urlencode($title).'&body='.urlencode($body).'&username='.urlencode($email).'&type=HOME';
    
        // $header = array(
        // 	"Authorization: Bearer ".Yii::$app->params['notification-bearer']
        // );
        $bearer_token="861c138b-43d0-43bc-9dcb-dceacf30f9fc";
        $header = array(
            "Authorization: Bearer ".$bearer_token
        );
    
        $ch = curl_init();
        $opt_array = array(
            CURLOPT_URL => $url,
            CURLOPT_PROXY => false,
            CURLOPT_PROXYPORT => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => $header
        );
    
        curl_setopt_array($ch, $opt_array);
    
            //execute post
            $result = curl_exec($ch);
            $err    = curl_error($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($err) {
                return "cURL Error #:" . $err;
            } else {
                return json_decode($result, true);
            }
        }

}