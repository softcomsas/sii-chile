<?php

namespace app\controllers;

use Yii;
use yii\web\ServerErrorHttpException;
use app\models\FoliosUtilizadosMes;

class FoliosUtilizadosMesController extends \yii\rest\Controller
{
    /** @var FoliosUtilizadosMes  */
    public $modelClass = FoliosUtilizadosMes::class;
    
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $dataProvider = $this->modelClass::search($params);
        return $dataProvider;
    }

    public function actionSelect()
    {
        $params = Yii::$app->request->queryParams;
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