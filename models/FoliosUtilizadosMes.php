<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "folios_utilizados_mes".
 *
 * @property int $anno
 * @property int $mes
 * @property int $cantidad
 */
class FoliosUtilizadosMes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'folios_utilizados_mes';
    }

    public function rules()
    {
        return [
            [['anno', 'mes'], 'required'],
            [['anno'], 'integer', 'max' => 9999],
            [['mes'], 'integer', 'min' => 1, 'max' => 12],
            [['cantidad'], 'integer'],
            [['anno', 'mes'], 'unique', 'targetAttribute' => ['anno', 'mes']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'anno' => 'Anno',
            'mes' => 'Mes',
            'cantidad' => 'Cantidad',
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
            'anno' => ['anno'],
            'mes' => ['mes'],
            'cantidad' => ['cantidad'],
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
