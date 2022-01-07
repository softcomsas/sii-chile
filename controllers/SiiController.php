<?php

namespace  app\controllers;

use app\components\sii\SiiClient;
use Yii;
use yii\rest\Controller;

class SiiController extends Controller
{
    public function actionLogin()
    {
        $cliente = new SiiClient();
        return [
            'semilla' => $cliente->ObtenerSemilla(),
            'token' => $cliente->ObtenerToken(),
        ];
    }
    public function actionToken()
    {
        try {
            $params = Yii::$app->params;
            $firma = [
                'file' => Yii::getAlias('@app/certificados') . DIRECTORY_SEPARATOR . $params['sii_cert_file'],
                //'file' => null,
                'pass' => $params['sii_pass'],
                'cert' => $params['sii_cert'],
                'pkey' => $params['sii_pkey'],
            ];
            /*if (isset($params['sii_data'])) {
                $firma['data'] = $params['sii_data'];
            }*/
            $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($firma);
            Yii::error($token, '$token');
            return $token;
        } catch (\Throwable $th) {
            throw $th;
            return \sasco\LibreDTE\Log::readAll();
        }
    }
    public function actionEstadoDte()
    {
        \sasco\LibreDTE\Sii::setAmbiente( \sasco\LibreDTE\Sii::CERTIFICACION);
        $cliente = new SiiClient();
        $cliente->ObtenerSemilla();
        $token = $cliente->ObtenerToken();
        $respuesta = [ 'token' => $token];
        $params = Yii::$app->params;
        // consultar estado dte
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            /*'RutConsultante'    => $params['sii_rut'],
            'DvConsultante'     => $params['sii_dv'],*/
            'RutCompania'    => $params['sii_rut'],
            'DvCompania'     => $params['sii_dv'],
            /*'RutCompania'       => '',
            'DvCompania'        => '',
            'RutReceptor'       => '',
            'DvReceptor'        => '',
            'TipoDte'           => '',
            'FolioDte'          => '',
            'FechaEmisionDte'   => '',
            'MontoDte'          => '',*/
            'token'             => $token,
        ]);

        // si el estado se pudo recuperar se muestra
        if ($xml !== false) {
            $respuesta['result'] = (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
        }
        return $respuesta;
    }
    public function actionEstadoDteEnviado($trackID = '' )
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
}
