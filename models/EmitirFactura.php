<?php

namespace app\models;

use yii\base\Model;

class EmitirFactura extends Model
{
    public $codigo_documento;
    public $rut_empresa;
    public $rut_receptor;
    public $rsocial_receptor;
    public $giro_receptor;
    public $direccion_receptor;
    public $ciudad_receptor;

    public $productos = [];

    private $_folio;
    private $_caf;

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
                    'productos'
                ],
                'required'
            ],
            [['codigo_documento'], 'integer'],
            [['codigo_documento'], 'in', 'range' => array_keys( MantenedorFolio::TIPOS_DOCUMENTOS)],
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
        ];
    }

    public function emitir()
    {

    }
}
