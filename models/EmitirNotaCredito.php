<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\traits\DteTrait;
use sasco\LibreDTE\Sii\Dte;
use yii\base\DynamicModel;
use sasco\LibreDTE\Sii\Folios;

class EmitirNotaCredito extends Model
{
    use DteTrait;

    const SCENARIO_NOTA = 'nota';

    public $fecha;
    public $codigo_documento;
    public $rut_empresa;
    public $rut_receptor;
    public $rsocial_receptor;
    public $giro_receptor;
    public $direccion_receptor;
    public $ciudad_receptor;
    public $productos = [];
    public $referencia;
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
                    'productos',
                    'ambiente',
                    'fecha',
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
            ['referencia', 'validarReferencia', 'skipOnEmpty' => false],
            ['codigo_documento', 'validarFolios', 'except' => [self::SCENARIO_NOTA]],
        ];
    }

    public function validarReferencia($attribute, $params)
    {
        $model = new DynamicModel(['TpoDocRef', 'FolioRef', 'CodRef', 'RazonRef', 'FchRef']);
        $model->addRule(['TpoDocRef', 'FolioRef', 'CodRef', 'RazonRef', 'FchRef'], 'required')
            ->addRule(['TpoDocRef', 'FolioRef', 'CodRef'], 'integer', ['min' => 1])
            ->addRule(['RazonRef'], 'string')
            ->addRule(['FchRef'], 'date', ['format' => 'php:Y-m-d']);
        $model->load($this->$attribute, '');
        if (!$model->validate()) {
            foreach ($model->errors as $attr => $errors) {
                $this->addError("$attribute.$attr", $errors[0]);
            }
        }
    }
    public function validarProducto($attribute, $params)
    {
        $productos = $this->$attribute;
        foreach ($productos as $key => $producto) {
            $model = new DynamicModel(['codigo', 'producto', 'cantidad', 'precio']);
            $model->addRule(['producto', 'cantidad', 'precio'], 'required')
                ->addRule(['codigo'], 'integer')
                ->addRule(['producto'], 'string', ['max' => 300])
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
        if ($this->ambiente == 'PROD') {
            $this->setAmbienteProduccion();
        } else {
            $this->setAmbienteDesarrollo();
        }

        $cuerpo = $this->generarCuerpo();
        $firma = $this->getFirma();
        $folios = $this->getFolios();
        // generar XML del DTE timbrado y firmado
        $dte = new Dte($cuerpo);
        $dte->timbrar($folios);
        $dte->firmar($firma);
        $xml = $dte->saveXML();
        $this->guardarRegistro($xml);
        $this->getMantenedor()->correrFolio();

        if($this->referencia['TpoDocRef'] == 39) return true;

        // generar sobre con el envío del DTE y enviar al SII
        $envioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
        $envioDTE->agregar($dte);
        $envioDTE->setFirma($firma);
        $envioDTE->setCaratula($this->generarCaratula());
        $envioDTE->generar();
        if (!$envioDTE->schemaValidate()) $this->handlerError();

        $track_id = $envioDTE->enviar();
        if (!$track_id)  $this->handlerError();

        $this->_registro->track_id = $track_id;
        $this->_registro->estado = FacturaEmitida::ESTADO_ENVIADO;
        $this->_registro->save(false);
        return $track_id;
    }

    private function generarCuerpo()
    {
        $detalle = [];
        foreach ($this->productos as $key => $producto) {
            $detalle[] = [
                'CdgItem' => $producto['codigo'],
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
            'Detalle' => $detalle,
            'Referencia' => [
                'TpoDocRef' => $this->referencia['TpoDocRef'],
                'FolioRef' => $this->referencia['FolioRef'],
                "FchRef" => $this->referencia['FchRef'],
                'CodRef' => $this->referencia['CodRef'],
                'RazonRef' => $this->referencia['RazonRef'],
            ],
        ];
    }
    private function generarCaratula($ignorarTipo = false)
    {
        if (!$ignorarTipo && $this->codigo_documento == 39) {
            return [
                'RutEmisor' => $this->rut_empresa,
                'FchResol' => '2014-08-22',
                'NroResol' => 80,
            ];
        }
        //if ($this->codigo_documento == 33) {
        return [
            //'RutEnvia' => '11222333-4', // se obtiene de la firma
            'RutReceptor' => '60803000-K',
            'FchResol' => '2014-08-22',
            'NroResol' => 80,
        ];
        /*}
        return [];*/
    }

    public function guardarRegistro($xml)
    {
        $this->_registro = new FacturaEmitida();
        $this->_registro->rut_empresa = $this->rut_empresa;
        $this->_registro->rut_receptor = $this->rut_receptor;
        $this->_registro->fecha = $this->fecha;
        $fileName = Yii::$app->security->generateRandomString(32) . ".xml";
        file_put_contents($this->_registro->getPath() . $fileName, $xml);
        $this->_registro->url_xml = $fileName;
        $this->_registro->folio = $this->getMantenedor()->siguiente_folio;
        $this->_registro->tipo = $this->getMantenedor()->codigo_documento;
        $this->_registro->save();
        Yii::error($this->_registro->errors, 'guardarRegistro');
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
    public function handlerError()
    {
        $messageError = '';
        foreach (\sasco\LibreDTE\Log::readAll() as $error) {
            $messageError .= $error->msg . '\n';
        }
        throw new \Exception($messageError, 1);
    }
}
