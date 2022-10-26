<?php

namespace  app\controllers;

use app\models\EmitirFactura;
use Yii;
use yii\rest\Controller;
use app\models\Factura;

class FacturasController extends Controller
{
    public function actions()
    {
        $actions = parent::actions();
        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
        ];
        return $actions;
    }
    public function actionIndex()
    {
        return Factura::search(Yii::$app->request->queryParams);
    }
    public function actionProcesada($id)
    {
        $factura = Factura::findOne(['id_doc' =>$id]);
        if ($factura) {
            $factura->estado = 1;
            $factura->save();
            return $factura;
        } else {
            throw new \yii\web\NotFoundHttpException("Factura no encontrada");
        }
    }
    public function actionEmitir()
    {
        $model = new EmitirFactura();
        $model->load(Yii::$app->request->post(), '');
        $model->validate() && $model->emitir();
        return $model;
    }
}
