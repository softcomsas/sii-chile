<?php
namespace app\traits;

use sasco\LibreDTE\FirmaElectronica;
use Yii;

/**
 * DteTrait
 */
trait DteTrait
{
    /** @var string */
    private $_token;

    /** @var FirmaElectronica */
    private $_firma;

    public function getToken()
    {
        if (!$this->_token) {
            $this->_token = \sasco\LibreDTE\Sii\Autenticacion::getToken( $this->getFirma());
            if (!$this->_token) {
                throw new \Exception("Error al obtener el token DTE.", 1);
                
            }
        }
        return $this->_token;
    }

    public function getFirma()
    {
        if (!$this->_firma) {
            $params = Yii::$app->params;
            $firma = [
                'cert' => $params['sii_cert'],
                'pkey' => $params['sii_pkey'],
            ];
            $this->_firma = new FirmaElectronica( $firma);
            if (!$this->_firma) {
                throw new \Exception("Error al obtener la Firma.", 1);
                
            }
        }
        return $this->_firma;
    }

    public function setAmbienteDesarrollo()
    {
        \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::CERTIFICACION);
    }
    public function setAmbienteProduccion()
    {
        \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::PRODUCCION);
    }
}