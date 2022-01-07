<?php
namespace app\components;

use Yii;

class Metodos
{
	public static function SendSMS($numbers, $message)
	{
		$encode = base64_encode(Yii::$app->params['userSMS'].":".Yii::$app->params['passSMS']);
		if ((isset($numbers)) && (isset($message))){
			$to_sms = Yii::$app->params['codigoPais'].$numbers;
			$curl = curl_init();
			$text_sms=$message;
			curl_setopt_array($curl, array(
				  CURLOPT_URL => "http://api.infobip.com/sms/1/text/single",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => "{ \"from\":\"InfoSMS\", \"to\":\"".$to_sms."\", \"text\":\"".$text_sms."\" }",
				  CURLOPT_HTTPHEADER => array(
					"accept: application/json",
					"authorization: Basic ".$encode,
					"content-type: application/json"
				  ),
				));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			
			curl_close($curl);
		}else
			return "No puede enviar parametros vacios";
	}
	public static function formatearErrores($errors)
    {
        $formato = [];
        foreach ($errors as $campo => $errores) {
            $field = [];
            $field['field'] = $campo;
            $field['errors'] = $errores;
            $formato[] = $field;
        }
        return $formato;
    }
}
