<?php

$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'es',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@dteEliminados' => '@app/upload/skiped',
        '@processed' => '@app/upload/processed',
        '@unprocessed' => '@app/upload/unprocessed',
    ],
    'components' => [
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key'   => $params['JWT.SECRET'],
        ],
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'multipart/form-data' => 'yii\web\MultipartFormDataParser'
            ],
            'enableCsrfValidation'   => false,
            'enableCookieValidation' => false,
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => yii\web\Response::FORMAT_JSON,
            'on beforeSend' => function ($event) {
                /* $controlador = explode('/', Yii::$app->requestedRoute);
                if (!$controlador || in_array($controlador[0], ['gii', 'debug', 'site'])) {
                    Yii::$app->getResponse()->format = yii\web\Response::FORMAT_HTML;
                    return;
                }*/
            },
            'on afterSend' => function ($event) {
                $response = Yii::$app->getResponse();
                if ($response->statusCode == 422) {
                    Yii::error($response->data, 'errors');
                }
            },
            'charset' => 'UTF-8',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'pdf' => [
            'class' => 'app\components\PdfGenerator',
        ],
        'sii' => [
            'class' => 'app\components\EnvioDte',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => false,
            'enableSession' => false,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'GET pdf-factura-emitida' => 'emitir/pdf2',
                [
                    'class' => \yii\rest\UrlRule::class,
                    'controller' => [
                        'folio/mantenedor' => 'mantenedor-folio',
                        'folio/utilizados-mes' => 'folios-utilizados-mes',
                        'empresa' => 'empresa'
                    ],
                    'extraPatterns' => [
                        'POST subir-caf' => 'subir-caf',
                        'OPTIONS subir-caf' => 'options'
                    ]
                ],
                'GET facturas/<id>/procesada' => 'facturas/procesada',
                'OPTIONS facturas/<id>/procesada' => 'facturas/options',
            ],
        ],
    ],
    'as corsFilter' => [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['*'],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'Access-Control-Request-Headers' => ['*'],
            'Access-Control-Expose-Headers' => ['*'],
        ],
    ],
    'as Authenticator' => [
        'class' => \app\components\CompositeAuth::class,
        'optional' => ['debug/*', 'emitir/pendientes'],
        'authMethods' => [
            [
                'class' => \app\components\QueryHashAuth::class,
                'key' => $params['AUTH_HASH.KEY']
            ],
            \sizeg\jwt\JwtHttpBearerAuth::class,
        ]
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
