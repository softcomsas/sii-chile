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
 * @property string|null $NmbItem
 * @property float|null $QtyItem
 * @property string|null $UnmdItem
 * @property float|null $PrcItem
 * @property float|null $MontoItem
 */
class Factura extends \yii\db\ActiveRecord
{
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
            [['MntNeto', 'TasaIVA', 'IVA', 'MntTotal', 'QtyItem', 'PrcItem', 'MontoItem'], 'number'],
            [['id_doc'], 'string', 'max' => 50],
            [['TipoDTE', 'Folio', 'FchEmis', 'RUTEmisor', 'RUTRecep', 'RUTSolicita', 'UnmdItem'], 'string', 'max' => 10],
            [['TpoTranCompra', 'TpoTranVenta', 'FmaPago'], 'string', 'max' => 1],
            [['RznSocEmisor', 'GiroEmis', 'DirOrigen', 'CmnaOrigen', 'CiudadOrigen', 'RznSocRecep', 'GiroRecep', 'ContactoRecep', 'DirRecep', 'CmnaRecep', 'CiudadRecep', 'NmbItem'], 'string', 'max' => 255],
            [['TelefonoEmisor', 'Acteco', 'CdgSIISucur'], 'string', 'max' => 20],
            [['CorreoEmisor'], 'string', 'max' => 100],
        ];
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
            'NmbItem' => 'Nmb Item',
            'QtyItem' => 'Qty Item',
            'UnmdItem' => 'Unmd Item',
            'PrcItem' => 'Prc Item',
            'MontoItem' => 'Monto Item',
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
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
            'NmbItem' => ['like', 'NmbItem'],
            'QtyItem' => ['QtyItem'],
            'UnmdItem' => ['like', 'UnmdItem'],
            'PrcItem' => ['PrcItem'],
            'MontoItem' => ['MontoItem'],
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
}
