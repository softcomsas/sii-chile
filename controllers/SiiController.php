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
                'file' => Yii::getAlias('@app/certificados').DIRECTORY_SEPARATOR.$params['sii_cert_file'],
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
        $cliente = new SiiClient();
        $cliente->ObtenerSemilla();
        $token = $cliente->ObtenerToken();
                // consultar estado dte
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => '',
            'DvConsultante'     => '',
            'RutCompania'       => '',
            'DvCompania'        => '',
            'RutReceptor'       => '',
            'DvReceptor'        => '',
            'TipoDte'           => '',
            'FolioDte'          => '',
            'FechaEmisionDte'   => '',
            'MontoDte'          => '',
            'token'             => $token,
        ]);

        // si el estado se pudo recuperar se muestra
        if ($xml!==false) {
            return (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
        }
        return "no esntró";
    }
}
