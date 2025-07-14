<?php

namespace app\controllers;

use Yii;
use yii\web\ServerErrorHttpException;
use app\models\MantenedorFolio;
use app\models\SubirCaf;

class MantenedorFolioController extends \yii\rest\Controller
{
    public $serializer = [
        'class' =>  'yii\rest\Serializer',
        'collectionEnvelope' => 'items'
    ];
    
    /** @var MantenedorFolio  */
    public $modelClass = MantenedorFolio::class;

    /**
     * Configurar behaviors para CORS
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Configurar CORS específicamente para este controlador
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => [
                    'http://localhost:4200', 
                    'http://127.0.0.1:4200',
                    'https://sistema.ayalarepuestos.cl',
                    'http://sistema.ayalarepuestos.cl'
                ],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        
        return $behaviors;
    }

    /**
     * Manejar peticiones OPTIONS para CORS preflight
     */
    public function actionOptions()
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        
        // Obtener el origin de la petición
        $origin = $request->headers->get('Origin');
        $allowedOrigins = [
            'http://localhost:4200',
            'http://127.0.0.1:4200',
            'https://sistema.ayalarepuestos.cl',
            'http://sistema.ayalarepuestos.cl'
        ];
        
        // Verificar si el origin está permitido
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }
        
        $response->statusCode = 200;
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, ambiente');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');
        return '';
    }

    public function getAmbiente()
    {
        $ambiente = Yii::$app->request->getHeaders()->get('ambiente', MantenedorFolio::AMBIENTE_DEV);
        return $ambiente;
    }

    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $params['ambiente'] = $this->getAmbiente();
        $dataProvider = $this->modelClass::search($params);
        return $dataProvider;
    }


    public function actionCreate()
    {
        /** @var \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            //'scenario' => $this->modelClass::SCENARIO_DEFAULT,
        ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->ambiente = $this->getAmbiente();
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    public function actionSubirCaf()
    {
        /** @var \yii\db\ActiveRecord */
        $model = new SubirCaf();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->ambiente = $this->getAmbiente();
        if (!$model->validate()) {
            return $model;
        }

        $model->subir();
        $response = Yii::$app->getResponse();
        $response->setStatusCode(201);
        return "Caf subido correctamente";
    }

    public function actionUpdate($id)
    {
        /** @var \yii\db\ActiveRecord */
        $model = $this->findModel($id);

        $model->scenario = $this->modelClass::SCENARIO_DEFAULT;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }
    public function actionMarcarAlerta($rut_empresa, $id)
    {
        MantenedorFolio::updateAll(
            ['notif_alerta' => time()], 
            [
                'id' => explode(',', $id),
                'rut_empresa' => $rut_empresa
            ]
        );

        Yii::$app->getResponse()->setStatusCode(204);
    }

    public function actionSelect()
    {
        $params = Yii::$app->request->queryParams;
        $params['ambiente'] = $this->getAmbiente();
        $data = $this->modelClass::query($params)
            ->all();
        return $data;
    }

    public function findModel($id)
    {
        $model = $this->modelClass::findOne($id);

        if (!isset($model))
            throw new \yii\web\NotFoundHttpException("No existe la fila: $id");

        return $model;
    }
}
