<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "factura_detalle".
 *
 * @property int $id
 * @property int $id_factura
 * @property int|null $TpoCodigo
 * @property int|null $VlrCodigo
 * @property string|null $NmbItem
 * @property float|null $QtyItem
 * @property string|null $UnmdItem
 * @property float|null $PrcItem
 * @property float|null $DescuentoPct
 * @property float|null $DescuentoMonto
 * @property float|null $MontoItem
 *
 * @property Factura $factura
 */
class FacturaDetalle extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'factura_detalle';
    }

    /**
     * Validaciones
     */
    public function rules()
    {
        return [
            [['id_factura'], 'required'],
            [['id_factura', 'TpoCodigo', 'VlrCodigo'], 'integer'],
            [['QtyItem', 'PrcItem', 'DescuentoPct', 'DescuentoMonto', 'MontoItem'], 'number'],
            [['NmbItem'], 'string', 'max' => 255],
            [['UnmdItem'], 'string', 'max' => 10],
            [['id_factura'], 'exist', 'skipOnError' => true, 'targetClass' => Factura::class, 'targetAttribute' => ['id_factura' => 'id']],
        ];
    }

    /**
     * Presentación
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_factura' => 'Id Factura',
            'TpoCodigo' => 'Tpo Codigo',
            'VlrCodigo' => 'Vlr Codigo',
            'NmbItem' => 'Nmb Item',
            'QtyItem' => 'Qty Item',
            'UnmdItem' => 'Unmd Item',
            'PrcItem' => 'Prc Item',
            'DescuentoPct' => 'Descuento Pct',
            'DescuentoMonto' => 'Descuento Monto',
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
        //$extra['factura'] = 'factura';
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
            'id_factura' => ['id_factura'],
            'TpoCodigo' => ['TpoCodigo'],
            'VlrCodigo' => ['VlrCodigo'],
            'NmbItem' => ['like', 'NmbItem'],
            'QtyItem' => ['QtyItem'],
            'UnmdItem' => ['like', 'UnmdItem'],
            'PrcItem' => ['PrcItem'],
            'DescuentoPct' => ['DescuentoPct'],
            'DescuentoMonto' => ['DescuentoMonto'],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFactura()
    {
        return $this->hasOne(Factura::class, ['id' => 'id_factura']);
    }
}
