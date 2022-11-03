<?php

namespace  app\controllers;

use Yii;
use yii\rest\Controller;
use app\models\ProcessDTE;
use app\components\sii\SiiClient;
use app\models\Factura;

class SiiController extends Controller
{
    public function actionProcess()
    {
        ProcessDTE::descargarAdjuntos();
        return ProcessDTE::runProcess();
    }
    public function actionEstadoDte()
    {
        \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::PRODUCCION);

        // solicitar token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken(Yii::$app->params['config']);

        $respuesta = ['token' => $token];
        $params = Yii::$app->params;

        // consultar estado dte
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => $params['sii_rut'],
            // 'RutConsultante'    => '',
            'DvConsultante'     => $params['sii_dv'],
            // 'DvConsultante'     => '',
            // 'RutCompania'       => $params['sii_rut'],
            'RutCompania'       => '',
            // 'DvCompania'     => $params['sii_dv'],
            'DvCompania'        => '',
            'RutReceptor'       => '',
            'DvReceptor'        => '',
            'TipoDte'           => '',
            'FolioDte'          => '16865',
            'FechaEmisionDte'   => '',
            'MontoDte'          => '',
            'token'             => $token,
        ]);

        // si el estado se pudo recuperar se muestra
        if ($xml !== false) {
            $respuesta['result'] = (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
        }
        return $respuesta;
    }
    public function actionEstadoDteEnviado($trackID = '')
    {
        $cliente = new SiiClient();
        $cliente->ObtenerSemilla();
        $token = $cliente->ObtenerToken();

        $params = Yii::$app->params;
        $rut = $params['sii_rut'];
        $dv = $params['sii_dv'];
        $estado = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$rut, $dv, $trackID, $token]);
        Yii::error($estado, '$estado');
        // si el estado se pudo recuperar se muestra estado y glosa
        if ($estado !== false) {
            return [
                'codigo' => (string)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0],
                'glosa' => (string)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0],
            ];
        }

        return "no esntró";
    }
    public function actionObtenerToken()
    {
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken(Yii::$app->params['config']);

        return [
            'token' => $token
        ];
    }
}
