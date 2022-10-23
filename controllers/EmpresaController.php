<?php

namespace app\controllers;

use Yii;
use yii\web\ServerErrorHttpException;
use app\models\Empresa;

class EmpresaController extends \yii\rest\Controller
{
    /** @var Empresa  */
    public $modelClass = Empresa::class;
    
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
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
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
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


    public function findModel($id)
    {
        $model = $this->modelClass::findOne($id);        

        if (!isset($model)) 
            throw new \yii\web\NotFoundHttpException("No existe la fila: $id");

        return $model;
    }

}