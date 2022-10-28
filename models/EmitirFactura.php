<?php

namespace app\models;

use app\traits\DteTrait;
use sasco\LibreDTE\Sii\Folios;
use yii\base\DynamicModel;
use yii\base\Model;

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
            ['productos', 'validarProducto']
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

    public function emitir()
    {
        $factura = $this->generarFactura();
        $caratula = $this->generarCaratula();
        return [
            'factura' => $factura,
            'caratula' => $caratula,
        ];
        $Firma = $this->getFirma();
        $Folios = $this->getFolios();

        // generar XML del DTE timbrado y firmado
        $DTE = new \sasco\LibreDTE\Sii\Dte($factura);
        $DTE->timbrar($Folios);
        $DTE->firmar($Firma);

        // generar sobre con el envío del DTE y enviar al SII
        $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDTE->agregar($DTE);
        $EnvioDTE->setFirma($Firma);
        $EnvioDTE->setCaratula($caratula);
        $track_id = $EnvioDTE->enviar();
        if (!$track_id) {
            //Ocurrió un error
            throw new \Exception("Error al Emitir", 1);
        }
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
}
