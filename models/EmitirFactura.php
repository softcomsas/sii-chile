<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\traits\DteTrait;
use yii\base\DynamicModel;
use sasco\LibreDTE\Sii\Folios;

class EmitirFactura extends Model
{
    use DteTrait;
    public $fecha;
    public $codigo_documento;
    public $rut_empresa;
    public $rut_receptor;
    public $rsocial_receptor;
    public $giro_receptor;
    public $direccion_receptor;
    public $ciudad_receptor;
    public $productos = [];
    public $ambiente;

    /** @var Folios */
    private $_folios;

    /** @var MantenedorFolio */
    private $_mantenedor;

    /** @var Caf */
    private $_caf;

    /** @var Empresa */
    private $_empresa;

    /** @var FacturaEmitida */
    private $_registro;

    public function rules()
    {
        return [
            [
                [
                    'codigo_documento',
                    'rut_empresa',
                    'rut_receptor',
                    'rsocial_receptor',
                    'giro_receptor',
                    'direccion_receptor',
                    'ciudad_receptor',
                    'productos',
                    'ambiente',
                    'fecha'
                ],
                'required'
            ],
            [['codigo_documento'], 'integer'],
            [['codigo_documento'], 'in', 'range' => array_keys(MantenedorFolio::TIPOS_DOCUMENTOS)],
            [
                [
                    'rut_empresa',
                    'rut_receptor',
                ],
                'string',
                'max' => 10
            ],
            [
                [
                    'rsocial_receptor',
                    'giro_receptor',
                    'direccion_receptor',
                    'ciudad_receptor',
                ],
                'string'
            ],
            ['rut_empresa', 'validarRutEmpresa'],
            ['productos', 'validarProducto'],
            ['codigo_documento', 'validarFolios'],
        ];
    }

    public function validarProducto($attribute, $params)
    {
        $productos = $this->$attribute;
        foreach ($productos as $key => $producto) {
            $model = new DynamicModel(['codigo', 'producto', 'cantidad', 'precio']);
            $model->addRule(['producto', 'cantidad', 'precio'], 'required')
                ->addRule(['codigo'], 'integer')
                ->addRule(['producto'], 'string', ['max' => 45])
                ->addRule(['cantidad', 'precio'], 'number', ['min' => 0.01]);
            $model->load($producto, '');
            if (!$model->validate()) {
                foreach ($model->errors as $attr => $errors) {
                    $this->addError("$attribute.$key.$attr", $errors[0]);
                }
            }
        }
        if (!$this->getEmpresa()) {
            $this->addError($attribute, "La empresa no está configurada.");
        }
    }
    public function validarRutEmpresa($attribute, $params)
    {
        if (!$this->getEmpresa()) {
            $this->addError($attribute, "La empresa no está configurada.");
        }
    }
    public function validarFolios($attribute, $params)
    {
        if (!$this->getMantenedor()) {
            return $this->addError($attribute, "La empresa no tiene mantenedor para el codigo de documento enviado.");
        }
        if (!$this->getCaf()) {
            return $this->addError($attribute, "El mantenedor del codigo de documento enviado no tiene Caf activos.");
        }
        if (!$this->getFolios()) {
            return $this->addError($attribute, "Error al recuperar los folios.");
        }
    }

    public function emitir()
    {
        $this->setAmbienteDesarrollo();
        $factura = $this->generarFactura();
        $caratula = $this->generarCaratula();
        // return [
        //     'factura' => $factura,
        //     'caratula' => $caratula,
        // ];
        $firma = $this->getFirma();
        $folios = $this->getFolios();

        // generar XML del DTE timbrado y firmado
        $dte = new \sasco\LibreDTE\Sii\Dte($factura);
        $dte->timbrar($folios);
        $dte->firmar($firma);

        // generar sobre con el envío del DTE y enviar al SII
        $envioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
        $envioDTE->agregar($dte);
        $envioDTE->setFirma($firma);
        $envioDTE->setCaratula($caratula);
        $xml = $envioDTE->generar();
        $this->guardarRegistro($xml);
        if ($envioDTE->schemaValidate()) {
            $envioDTE->generar();
            $track_id = $envioDTE->enviar();
            if ($track_id) {
                $this->_registro->track_id = $track_id;
                $this->_registro->save(false);
                $this->getMantenedor()->correrFolio();
            }
            return $track_id;
        }

        $messageError = '';
        foreach (\sasco\LibreDTE\Log::readAll() as $error) {
            $messageError .= $error->msg . '\n';
        }
        throw new \Exception($messageError, 1);
    }

    private function generarFactura()
    {
        $detalle = [];
        foreach ($this->productos as $key => $producto) {
            $detalle[] = [
                'NmbItem' => $producto['producto'],
                'QtyItem' => $producto['cantidad'],
                'PrcItem' => $producto['precio'],
            ];
        }
        return [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $this->codigo_documento,
                    'Folio' => $this->getMantenedor()->siguiente_folio,
                ],
                'Emisor' => [
                    'RUTEmisor' => $this->getEmpresa()->rut,
                    'RznSoc' =>  $this->getEmpresa()->razon_social,
                    'GiroEmis' =>  $this->getEmpresa()->giro,
                    'Acteco' =>  $this->getEmpresa()->ateco,
                    'DirOrigen' =>  $this->getEmpresa()->direccion,
                    'CmnaOrigen' =>   $this->getEmpresa()->ciudad,
                ],
                'Receptor' => [
                    'RUTRecep' => $this->rut_receptor,
                    'RznSocRecep' => $this->rsocial_receptor,
                    'GiroRecep' => $this->giro_receptor,
                    'DirRecep' => $this->direccion_receptor,
                    'CmnaRecep' => $this->ciudad_receptor,
                ],
            ],
            'Detalle' => $detalle
        ];
    }
    private function generarCaratula()
    {
        return [
            //'RutEnvia' => '11222333-4', // se obtiene de la firma
            'RutReceptor' => $this->rut_receptor,
            'FchResol' => $this->fecha,
            'NroResol' => 0,
        ];
    }
    public function guardarRegistro($xml)
    {
        $this->_registro = new FacturaEmitida();
        $this->_registro->rut_empresa = $this->rut_empresa;
        $this->_registro->rut_receptor = $this->rut_receptor;
        $this->_registro->fecha = $this->fecha;
        $fileName = Yii::$app->security->generateRandomString(32) . ".xml";
        file_put_contents($this->_registro->getPath() . $fileName, $xml);
        $this->_registro->folio = $this->getMantenedor()->siguiente_folio;
        $this->_registro->tipo = $this->getMantenedor()->codigo_documento;
        $this->_registro->save();
    }

    public function getEmpresa()
    {
        if (!$this->_empresa) {
            $this->_empresa = Empresa::findOne([
                'rut' => $this->rut_empresa
            ]);
        }
        return $this->_empresa;
    }
    public function getMantenedor()
    {
        if (!$this->_mantenedor) {
            $this->_mantenedor = MantenedorFolio::findOne([
                'rut_empresa' => $this->rut_empresa,
                'codigo_documento' => $this->codigo_documento,
                'ambiente' => $this->ambiente
            ]);
        }
        return $this->_mantenedor;
    }
    public function getCaf()
    {
        if (!$this->_caf) {
            $this->_caf =  $this->getMantenedor()->cafEnUso;
        }
        return $this->_caf;
    }
    public function getFolios()
    {
        if (!$this->_folios) {
            $this->_folios =  $this->getCaf()->folios;
        }
        return $this->_folios;
    }
}