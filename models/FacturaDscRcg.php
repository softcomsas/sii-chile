<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "factura_dsc_rcg".
 *
 * @property int $id
 * @property int $id_factura
 * @property string $TpoMov
 * @property string $TpoValor
 * @property float $ValorDR
 *
 * @property Factura $factura
 */
class FacturaDscRcg extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'factura_dsc_rcg';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_factura', 'TpoMov', 'TpoValor', 'ValorDR'], 'required'],
            [['id_factura'], 'integer'],
            [['ValorDR'], 'number'],
            [['TpoMov', 'TpoValor'], 'string', 'max' => 1],
            [['id_factura'], 'exist', 'skipOnError' => true, 'targetClass' => Factura::class, 'targetAttribute' => ['id_factura' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_factura' => 'Id Factura',
            'TpoMov' => 'Tpo Mov',
            'TpoValor' => 'Tpo Valor',
            'ValorDR' => 'Valor Dr',
        ];
    }

    /**
     * Gets query for [[Factura]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFactura()
    {
        return $this->hasOne(Factura::class, ['id' => 'id_factura']);
    }
}
