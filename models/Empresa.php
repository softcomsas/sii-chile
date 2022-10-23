<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "empresa".
 *
 * @property int $id
 * @property string $rut
 * @property string $razon_social
 * @property string $giro
 * @property int|null $ateco
 * @property string $direccion
 * @property string $ciudad
 */
class Empresa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'empresa';
    }

    public function rules()
    {
        return [
            [['rut', 'razon_social', 'giro', 'direccion', 'ciudad'], 'required'],
            [['ateco'], 'integer'],
            [['rut'], 'string', 'max' => 10],
            [['razon_social', 'giro'], 'string', 'max' => 100],
            [['direccion'], 'string', 'max' => 150],
            [['ciudad'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rut' => 'Rut',
            'razon_social' => 'Razon Social',
            'giro' => 'Giro',
            'ateco' => 'Ateco',
            'direccion' => 'Direccion',
            'ciudad' => 'Ciudad',
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

    public static function query(array $requestParams)
    {
        $query = self::find();

        $columns = [
            'id' => ['id'],
            'rut' => ['like', 'rut'],
            'razon_social' => ['like', 'razon_social'],
            'giro' => ['like', 'giro'],
            'ateco' => ['ateco'],
            'direccion' => ['like', 'direccion'],
            'ciudad' => ['like', 'ciudad'],
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

    public static function selectAll(array $requestParams)
    {
        return  self::query($requestParams)->all();
    }

    public static function selectOne(array $requestParams)
    {
        return  self::query($requestParams)->one();
    }
}
