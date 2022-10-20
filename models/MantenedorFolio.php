<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mantenedor_folio".
 *
 * @property int $id
 * @property int $codigo_documento
 * @property string $tipo_documento
 * @property int|null $siguiente_folio
 * @property int|null $total_disponible
 * @property int|null $alerta
 *
 * @property Caf[] $cafs
 */
class MantenedorFolio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mantenedor_folio';
    }

    public function rules()
    {
        return [
            [['codigo_documento', 'tipo_documento'], 'required'],
            [['codigo_documento', 'siguiente_folio', 'total_disponible', 'alerta'], 'integer'],
            [['tipo_documento'], 'string', 'max' => 45],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo_documento' => 'Codigo Documento',
            'tipo_documento' => 'Tipo Documento',
            'siguiente_folio' => 'Siguiente Folio',
            'total_disponible' => 'Total Disponible',
            'alerta' => 'Alerta',
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
        //$extra['cafs'] = 'cafs';
        return $extra;
    }

    public static function query(array $requestParams)
    {
        $query = self::find();

        $columns = [
            'id' => ['id'],
            'codigo_documento' => ['codigo_documento'],
            'tipo_documento' => ['like', 'tipo_documento'],
            'siguiente_folio' => ['siguiente_folio'],
            'total_disponible' => ['total_disponible'],
            'alerta' => ['alerta'],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCafs()
    {
        return $this->hasMany(Caf::class, ['id_mantenedor' => 'id']);
    }
}
