<?php
namespace app\components\rest;

use Yii;

class ApiRestController extends \yii\rest\Controller
{
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        //'collectionEnvelope'=> 'data'
    ];
	public function behaviors()
    {
        $behaviors = parent::behaviors();
        // eliminar autenticación y verbos
        $verb = $behaviors['verbFilter'];
        unset($behaviors['authenticator'], $behaviors['verbFilter'], $behaviors['rateLimiter']);
        // añadir CORS
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => ['*'],
				//'Access-Control-Allow-Origin' => ['*','http://localhost:4200'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Expose-Headers' => ['*'],
               // 'Access-Control-Allow-Credentials' => true,
            ],
        ];
        // añadir los verbos
        $behaviors['verbFilter'] =  $verb;
        // añadir autenticacion
        $behaviors['authenticator'] = [
            'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
        ];
        return $behaviors;
    }
    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }
}
