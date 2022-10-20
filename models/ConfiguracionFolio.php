<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "configuracion_folio".
 *
 * @property int $id
 * @property int $multiplicador
 * @property string $rut_empresa
 * @property string $tipo_documento
 * @property int $rango_maximo
 * @property int $total_utilizado
 */
class ConfiguracionFolio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'configuracion_folio';
    }

    public function rules()
    {
        return [
            [['multiplicador', 'rango_maximo', 'total_utilizado'], 'integer'],
            [['rut_empresa', 'tipo_documento', 'rango_maximo', 'total_utilizado'], 'required'],
            [['rut_empresa'], 'string', 'max' => 10],
            [['tipo_documento'], 'string', 'max' => 45],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'multiplicador' => 'Multiplicador',
            'rut_empresa' => 'Rut Empresa',
            'tipo_documento' => 'Tipo Documento',
            'rango_maximo' => 'Rango Maximo',
            'total_utilizado' => 'Total Utilizado',
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
            'multiplicador' => ['multiplicador'],
            'rut_empresa' => ['like', 'rut_empresa'],
            'tipo_documento' => ['like', 'tipo_documento'],
            'rango_maximo' => ['rango_maximo'],
            'total_utilizado' => ['total_utilizado'],
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
