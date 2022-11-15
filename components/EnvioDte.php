<?php

namespace app\components;

use yii\base\Component;
use app\traits\DteTrait;
use app\models\FacturaEmitida;
use sasco\LibreDTE\Sii\Dte;
use yii\helpers\ArrayHelper;

class EnvioDte extends Component
{
    use DteTrait;

    public $ambiente = 'DEV';
    public $rut_empresa;

    private $dtes = [];

    public function setEmpresa($rut)
    {
        $this->rut_empresa = $rut;
    }
    public function setAmbiente($ambiente)
    {
        $this->ambiente = $ambiente;
    }

    public function agregar(Dte $dte)
    {
        $tipo = $dte->getTipo();
        if (!isset($this->dtes[$tipo])) {
            $this->dtes[$tipo] = [];
        }

        $this->dtes[$tipo][$dte->getFolio()] = $dte;
    }

    public function send()
    {
        if ($this->ambiente == 'PROD') {
            $this->setAmbienteProduccion();
        } else {
            $this->setAmbienteDesarrollo();
        }
        foreach ($this->dtes as $tipo => $dtes) {
            switch ($tipo) {
                case 39:
                    $this->sendBoleta(array_values($dtes));
                    break;

                default:
                    # code...
                    break;
            }
        }
    }

    private function sendFactura($dtes)
    {
        $tipo = 33;
        $firma = $this->getFirma();

        $envioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
        foreach ($dtes as $dte) {
            $envioDTE->agregar($dte);
        }
        $envioDTE->setFirma($firma);
        $envioDTE->setCaratula($this->generarCaratula($tipo));
        $xml = $envioDTE->generar();
        if (!$envioDTE->schemaValidate()) $this->handlerError();

        //$envioDTE->generar();
        $track_id = $envioDTE->enviar();
        if (!$track_id)  $this->handlerError();

        $folios = ArrayHelper::getColumn($dtes, function ($dte) {
            return $dte->getFolio();
        }, false);
        FacturaEmitida::updateAll(
            [
                'track_id' => $track_id,
                'estado' => FacturaEmitida::ESTADO_ENVIADO,
            ],
            [
                'rut_empresa' => $this->rut_empresa,
                'tipo' => $tipo,
                'folio' => $folios,
                'track_id' => null
            ]
        );
        return $track_id;
    }
    private function sendBoleta($dtes)
    {
        $firma = $this->getFirma();
        // crear objeto para consumo de folios
        $ConsumoFolio = new \sasco\LibreDTE\Sii\ConsumoFolio();
        $ConsumoFolio->setFirma($firma);
        $ConsumoFolio->setDocumentos([39, 41, 61]);

        // agregar detalle de boleta
        foreach ($dtes as $dte) {
            $ConsumoFolio->agregar($dte->getResumen());
        }
        $ConsumoFolio->setCaratula($this->generarCaratula(39));

        // generar, validar schema y mostrar XML
        $xml = $ConsumoFolio->generar();
        if (!$ConsumoFolio->schemaValidate()) $this->handlerError();

        $track_id = $ConsumoFolio->enviar();
        if ($track_id)  $this->handlerError();

        $folios = ArrayHelper::getColumn($dtes, function ($dte) {
            return $dte->getFolio();
        }, false);
        FacturaEmitida::updateAll(
            [
                'track_id' => $track_id,
                'estado' => FacturaEmitida::ESTADO_ENVIADO,
            ],
            [
                'rut_empresa' => $this->rut_empresa,
                'tipo' => 39,
                'folio' => $folios,
                'track_id' => null
            ]
        );
        return $track_id;
    }

    private $_mantenedor = [];
    public function getMantenedor($tipo)
    {
        if (!isset($this->_mantenedor[$tipo])) {
            $this->_mantenedor[$tipo] = \app\models\MantenedorFolio::findOne([
                'rut_empresa' => $this->rut_empresa,
                'codigo_documento' => $tipo,
                'ambiente' => $this->ambiente
            ]);
        }
        return $this->_mantenedor[$tipo];
    }

    private function generarCaratula($tipo)
    {
        switch ($tipo) {
            case 39:
                return [
                    'RutEmisor' => $this->rut_empresa,
                    'FchResol' => '2014-08-22',
                    'NroResol' => 80,
                    'SecEnvio' => $this->getMantenedor($tipo)->getSecuencia(),
                ];
                break;

            default:
                return [
                    'RutReceptor' => '60803000-K',
                    'FchResol' => '2014-08-22',
                    'NroResol' => 80,
                ];
                break;
        }
    }
    public function handlerError()
    {
        $messageError = '';
        foreach (\sasco\LibreDTE\Log::readAll() as $error) {
            $messageError .= $error->msg . '\n';
        }
        throw new \Exception($messageError, 1);
    }
}
