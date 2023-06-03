<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class SubirCaf extends Model
{
    public $ambiente;
    /** @var UploadedFile */
    public $file;

    private $_folio;
    private $_fileName;

    public function beforeValidate()
    {
        $this->file = UploadedFile::getInstanceByName('file');

        return parent::beforeValidate();
    }

    public function rules()
    {
        return [
            [['ambiente', 'file'], 'required'],
            ['ambiente', 'in', 'range' => [MantenedorFolio::AMBIENTE_DEV, MantenedorFolio::AMBIENTE_PROD]],
            ['file', 'file'],
            ['file', 'validarFolio']
        ];
    }
    public function validarFolio($attr, $params)
    {
        Yii::error(print_r($this->file, true));
        $this->_fileName = Yii::$app->security->generateRandomString(32) . "." . $this->file->extension;
        $fullPath = Caf::getPath() . $this->_fileName;
        if (!$this->file->saveAs($fullPath)) {
            return false;
        };
        $this->_folio = new \sasco\LibreDTE\Sii\Folios(
            file_get_contents($fullPath)
        );
        if (!$this->_folio->check()) {
            $this->addError($attr, "El folio es inválido");
        }
        Yii::error($this->_folio->getDesde(), 'desde');
        Yii::error($this->_folio->getHasta(), 'getHasta');
        Yii::error($this->_folio->getFechaAutorizacion(), 'getFechaAutorizacion');
        Yii::error($this->_folio->getMesesAutorizacion(), 'getMesesAutorizacion');
        Yii::error($this->_folio->getTipo(), 'getTipo');
    }

    public function subir()
    {
        $mantenedor = $this->getMantenedor();
        $model = new Caf();
        $model->id_mantenedor = $mantenedor->id;
        $model->desde = $this->_folio->getDesde();
        $model->hasta = $this->_folio->getHasta();
        $model->fecha_autorizacion = $this->_folio->getFechaAutorizacion();
        $model->meses_autorizados = (int) $this->_folio->getMesesAutorizacion();
        $model->url_xml = $this->_fileName;
        $model->estado = $mantenedor->cafEnUso ? Caf::ESTADO_DISPONIBLE : Caf::ESTADO_EN_USO;
        if($model->save()){
            if (!$mantenedor->cafEnUso) {
                $mantenedor->siguiente_folio = $model->desde;
            }
            $mantenedor->total_disponible += $model->hasta - $model->desde;
            $mantenedor->save();
        }
        Yii::error($model->errors);
    }

    public function getMantenedor()
    {
        static $mantenedor;
        if (!$mantenedor) {
            $data = [
                'rut_empresa'  => $this->_folio->getEmisor(),
                'codigo_documento'  => $this->_folio->getTipo(),
                'ambiente'  => $this->ambiente
            ];
            $mantenedor = MantenedorFolio::findOne($data);
            if (!$mantenedor) {
                $mantenedor = new MantenedorFolio($data);
                $mantenedor->tipo_documento = MantenedorFolio::TIPOS_DOCUMENTOS[$this->_folio->getTipo()];
                $mantenedor->multiplicador = 5;
                $mantenedor->rango_maximo = 100;
                $mantenedor->total_utilizado = 0;
                $mantenedor->total_disponible = 0;
                $mantenedor->alerta = 50;
                $mantenedor->save();
                Yii::error($mantenedor->errors, 'mantenedor->errors');
            }
        }
        return $mantenedor;
    }
}
