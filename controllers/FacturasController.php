<?php

namespace  app\controllers;

use app\models\EmitirFactura;
use Yii;
use yii\rest\Controller;
use app\models\Factura;
use app\models\FacturaEmitida;
use app\models\MantenedorFolio;
use yii\web\NotFoundHttpException;

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
        $factura = Factura::findOne(['id_doc' => $id]);
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
        $model->ambiente = $this->getAmbiente();
        if (!$model->validate())
            return $model;

        return $model->emitir();
    }
    public function actionPdf($id)
    {
        $model = FacturaEmitida::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Factura no encontrada');
        }
        $pdf = $model->getPdf();

        return Yii::$app->response->sendFile($pdf);
    }
    public function actionPdf2($rut, $tipo, $folio)
    {
        $model = FacturaEmitida::selectOne([
            'rut' => (string) $rut,
            'tipo' => (int) $tipo,
            'folio' => (int) $folio,
        ]);
        if (!$model) {
            throw new NotFoundHttpException('Factura no encontrada');
        }
        $pdf = $model->getPdf();

        return Yii::$app->response->sendFile($pdf, null, [
            'inline' => true
        ]);
    }
    public function actionPdf3($rut)
    {
        $params = Yii::$app->request->get();
        $params['rut'] = (string) $rut;
        $models = FacturaEmitida::selectAll($params);
        foreach ($models as $model) {
            $pdf = $model->getPdf($model->getPath());
        }

        return count($models);
    }
    public function actionGenerarPdf()
    {
        $model = new EmitirFactura(['scenario' => EmitirFactura::SCENARIO_NOTA]);
        $model->load(Yii::$app->request->post(), '');
        $model->ambiente = $this->getAmbiente();
        if (!$model->validate())
            return $model;

        $pdf = $model->generarPdf();

        return Yii::$app->response->sendFile($pdf, null, [
            'inline' => true
        ]);
    }

    public function getAmbiente()
    {
        $ambiente = Yii::$app->request->getHeaders()->get('ambiente', MantenedorFolio::AMBIENTE_DEV);
        return $ambiente;
    }
}
