<?php

namespace  app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\ProcessDTE;
use app\components\sii\SiiClient;
use app\models\Factura;

class FacturasController extends Controller
{
    public function actionIndex()
    {
        return Factura::search(Yii::$app->request->queryParams);
    }
}
