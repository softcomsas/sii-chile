<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "facturas".
 *
 * @property int $id
 * @property string|null $id_doc
 * @property string|null $TipoDTE
 * @property string|null $Folio
 * @property string|null $FchEmis
 * @property string|null $TpoTranCompra
 * @property string|null $TpoTranVenta
 * @property string|null $FmaPago
 * @property string|null $RUTEmisor
 * @property string|null $RznSocEmisor
 * @property string|null $GiroEmis
 * @property string|null $TelefonoEmisor
 * @property string|null $CorreoEmisor
 * @property string|null $Acteco
 * @property string|null $CdgSIISucur
 * @property string|null $DirOrigen
 * @property string|null $CmnaOrigen
 * @property string|null $CiudadOrigen
 * @property string|null $RUTRecep
 * @property string|null $RznSocRecep
 * @property string|null $GiroRecep
 * @property string|null $ContactoRecep
 * @property string|null $DirRecep
 * @property string|null $CmnaRecep
 * @property string|null $CiudadRecep
 * @property string|null $RUTSolicita
 * @property float|null $MntNeto
 * @property float|null $TasaIVA
 * @property float|null $IVA
 * @property float|null $MntTotal
 * @property int $estado
 *
 * @property FacturaDetalle[] $facturaDetalles
 * @property FacturaDscRcg[] $facturaDscRcgs
 */
class Factura extends \yii\db\ActiveRecord
{
    public $Detalles;
    public $dscRcgs;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'facturas';
    }

    /**
     * Validaciones
     */
    public function rules()
    {
        return [
            [['MntNeto', 'TasaIVA', 'IVA', 'MntTotal'], 'number'],
            [['estado'], 'integer'],
            [['id_doc'], 'string', 'max' => 50],
            [['TipoDTE'], 'in', 'range' => Yii::$app->params['TIPOS_DTE_PERMITIDOS']],
            [['RUTEmisor'], 'in', 'not' => true, 'range' => Yii::$app->params['RUT_OMITIDOS']],
            [['TipoDTE', 'Folio', 'FchEmis', 'RUTEmisor', 'RUTRecep', 'RUTSolicita'], 'string', 'max' => 10],
            [['TpoTranCompra', 'TpoTranVenta', 'FmaPago'], 'string', 'max' => 1],
            [['RznSocEmisor', 'GiroEmis', 'DirOrigen', 'CmnaOrigen', 'CiudadOrigen', 'RznSocRecep', 'GiroRecep', 'ContactoRecep', 'DirRecep', 'CmnaRecep', 'CiudadRecep'], 'string', 'max' => 255],
            [['TelefonoEmisor', 'Acteco', 'CdgSIISucur'], 'string', 'max' => 20],
            [['CorreoEmisor'], 'string', 'max' => 100],
            ['Detalles', 'safe'],
            ['id_doc', 'unique'],
        ];
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            if (isset($this->Detalles['NroLinDet'])) {
                $newDetalle = new FacturaDetalle();
                $newDetalle->id_factura = $this->id;
                $newDetalle->load($this->Detalles, '');
                $newDetalle->TpoCodigo = isset($this->Detalles['CdgItem']) ? $this->Detalles['CdgItem']['TpoCodigo'] : '';
                $newDetalle->VlrCodigo = isset($this->Detalles['CdgItem']) ? $this->Detalles['CdgItem']['VlrCodigo'] : '';
                $newDetalle->save();
            }else{
                foreach ($this->Detalles as $detalle) {
                    $newDetalle = new FacturaDetalle();
                    $newDetalle->id_factura = $this->id;
                    $newDetalle->load($detalle, '');
                    $newDetalle->TpoCodigo = isset($detalle['CdgItem']) ? $detalle['CdgItem']['TpoCodigo'] : '';
                    $newDetalle->VlrCodigo = isset($detalle['CdgItem']) ? $detalle['CdgItem']['VlrCodigo'] : '';
                    $newDetalle->save();
                }
            }
            if (!isset($this->dscRcgs[0]))
                $this->dscRcgs = [$this->dscRcgs];
            foreach ($this->dscRcgs as $dscRcg) {
                $newDscRcg = new FacturaDscRcg();
                $newDscRcg->id_factura = $this->id;
                $newDscRcg->load($dscRcg, '');
                $newDscRcg->save();
            }
        }
    }

    /**
     * Presentación
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_doc' => 'Id Doc',
            'TipoDTE' => 'Tipo Dte',
            'Folio' => 'Folio',
            'FchEmis' => 'Fch Emis',
            'TpoTranCompra' => 'Tpo Tran Compra',
            'TpoTranVenta' => 'Tpo Tran Venta',
            'FmaPago' => 'Fma Pago',
            'RUTEmisor' => 'Rut Emisor',
            'RznSocEmisor' => 'Rzn Soc Emisor',
            'GiroEmis' => 'Giro Emis',
            'TelefonoEmisor' => 'Telefono Emisor',
            'CorreoEmisor' => 'Correo Emisor',
            'Acteco' => 'Acteco',
            'CdgSIISucur' => 'Cdg Sii Sucur',
            'DirOrigen' => 'Dir Origen',
            'CmnaOrigen' => 'Cmna Origen',
            'CiudadOrigen' => 'Ciudad Origen',
            'RUTRecep' => 'Rut Recep',
            'RznSocRecep' => 'Rzn Soc Recep',
            'GiroRecep' => 'Giro Recep',
            'ContactoRecep' => 'Contacto Recep',
            'DirRecep' => 'Dir Recep',
            'CmnaRecep' => 'Cmna Recep',
            'CiudadRecep' => 'Ciudad Recep',
            'RUTSolicita' => 'Rut Solicita',
            'MntNeto' => 'Mnt Neto',
            'TasaIVA' => 'Tasa Iva',
            'IVA' => 'Iva',
            'MntTotal' => 'Mnt Total',
            'estado' => 'Estado',
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['Detalles'] = 'facturaDetalles';
        $fields['dscRcgs'] = 'facturaDscRcgs';
        return $fields;
    }
    public function extraFields()
    {
        $extra = [];
        return $extra;
    }

    /*
     * Consultas 
     */
    public static function query(array $requestParams)
    {
        $query = self::find();

        $columns = [
            'id' => ['id'],
            'id_doc' => ['like', 'id_doc'],
            'TipoDTE' => ['like', 'TipoDTE'],
            'Folio' => ['like', 'Folio'],
            'FchEmis' => ['like', 'FchEmis'],
            'TpoTranCompra' => ['like', 'TpoTranCompra'],
            'TpoTranVenta' => ['like', 'TpoTranVenta'],
            'FmaPago' => ['like', 'FmaPago'],
            'RUTEmisor' => ['like', 'RUTEmisor'],
            'RznSocEmisor' => ['like', 'RznSocEmisor'],
            'GiroEmis' => ['like', 'GiroEmis'],
            'TelefonoEmisor' => ['like', 'TelefonoEmisor'],
            'CorreoEmisor' => ['like', 'CorreoEmisor'],
            'Acteco' => ['like', 'Acteco'],
            'CdgSIISucur' => ['like', 'CdgSIISucur'],
            'DirOrigen' => ['like', 'DirOrigen'],
            'CmnaOrigen' => ['like', 'CmnaOrigen'],
            'CiudadOrigen' => ['like', 'CiudadOrigen'],
            'RUTRecep' => ['like', 'RUTRecep'],
            'RznSocRecep' => ['like', 'RznSocRecep'],
            'GiroRecep' => ['like', 'GiroRecep'],
            'ContactoRecep' => ['like', 'ContactoRecep'],
            'DirRecep' => ['like', 'DirRecep'],
            'CmnaRecep' => ['like', 'CmnaRecep'],
            'CiudadRecep' => ['like', 'CiudadRecep'],
            'RUTSolicita' => ['like', 'RUTSolicita'],
            'MntNeto' => ['MntNeto'],
            'TasaIVA' => ['TasaIVA'],
            'IVA' => ['IVA'],
            'MntTotal' => ['MntTotal'],
            'estado' => ['estado'],
        ];

        foreach ($columns as $key => $value) {
            if (isset($requestParams[$key])) {
                if (count($value) == 1) {
                    $query->andWhere([$value[0] => $requestParams[$key]]);
                } else {
                    $value[] = $requestParams[$key];
                    $query->andWhere($value);
                }
            }
        }

        return $query;
    }
    public static function search(array $requestParams)
    {
        return new \yii\data\ActiveDataProvider([
            'query' => self::query($requestParams),
            'pagination' => [
                'params' => $requestParams,
                'pageSizeLimit' => [1, 50],
                'defaultPageSize' => 10,
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }

    /*
     * Relaciones 
     */

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFacturaDetalles()
    {
        return $this->hasMany(FacturaDetalle::class, ['id_factura' => 'id']);
    }
    /**
     * Gets query for [[FacturaDscRcgs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFacturaDscRcgs()
    {
        return $this->hasMany(FacturaDscRcg::class, ['id_factura' => 'id']);
    }
}
