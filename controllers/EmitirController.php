<?php

namespace  app\controllers;

use app\models\EmitirFactura;
use app\models\EmitirNotaCredito;
use Yii;
use yii\rest\Controller;
use app\models\FacturaEmitida;
use app\models\MantenedorFolio;
use yii\web\NotFoundHttpException;

class EmitirController extends Controller
{
    public function actions()
    {
        $actions = parent::actions();
        $actions['options'] = [
            'class' => 'yii\rest\OptionsAction',
        ];
        return $actions;
    }
    public function actionPendientes($ambiente = 'DEV', $rut_empresa = '7555986-0')
    {
        Yii::$app->sii->setEmpresa($rut_empresa);
        Yii::$app->sii->setAmbiente($ambiente);

        $registros = FacturaEmitida::find()
            ->where(
                [
                    'rut_empresa' => $rut_empresa,
                    'tipo' => [39, 61],
                    'estado' => FacturaEmitida::ESTADO_CREADO
                ]
            )
            ->orderBy(['tipo' => SORT_ASC])
            ->all();

        foreach ($registros as $row) {
            Yii::$app->sii->agregar($row->getDte());
        }
        return Yii::$app->sii->send();
    }
    public function actionFacturaBoleta()
    {
        $model = new EmitirFactura();
        $model->load(Yii::$app->request->post(), '');
        $model->ambiente = $this->getAmbiente();
        if (!$model->validate())
            return $model;

        return $model->emitir();
    }
    public function actionNotaCredito()
    {
        $model = new EmitirNotaCredito();
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
