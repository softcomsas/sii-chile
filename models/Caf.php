<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "caf".
 *
 * @property int $id
 * @property int $id_mantenedor
 * @property int $desde
 * @property int $hasta
 * @property string $fecha_autorizacion
 * @property int $meses_autorizados
 * @property int $estado
 * @property string|null $url_xml
 *
 * @property MantenedorFolio $mantenedor
 */
class Caf extends \yii\db\ActiveRecord
{
    const ESTADO_DISPONIBLE = 0;
    const ESTADO_EN_USO = 1;
    const ESTADO_USADO = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'caf';
    }

    public function rules()
    {
        return [
            [['id_mantenedor', 'desde', 'hasta', 'fecha_autorizacion', 'meses_autorizados'], 'required'],
            [['id_mantenedor', 'desde', 'hasta', 'meses_autorizados', 'estado'], 'integer'],
            [['fecha_autorizacion'], 'safe'],
            [['url_xml'], 'string', 'max' => 100],
            [['id_mantenedor'], 'exist', 'skipOnError' => true, 'targetClass' => MantenedorFolio::class, 'targetAttribute' => ['id_mantenedor' => 'id']],
        ];
    }
    public function beforeSave($insert)
    {
        if(!parent::beforeSave($insert))
            return false;

            
        
        
        return true;
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'id_mantenedor' => 'Id Mantenedor',
            'desde' => 'Desde',
            'hasta' => 'Hasta',
            'fecha_autorizacion' => 'Fecha Autorizacion',
            'meses_autorizados' => 'Meses Autorizados',
            'estado' => 'Estado',
            'url_xml' => 'Url Xml',
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
        //$extra['mantenedor'] = 'mantenedor';
        return $extra;
    }

    public static function query(array $requestParams)
    {
        $query = self::find()
            ->innerJoinWith('mantenedor m');

        $columns = [
            'id' => ['id'],
            'id_mantenedor' => ['id_mantenedor'],
            'desde' => ['desde'],
            'hasta' => ['hasta'],
            'fecha_autorizacion' => ['like', 'fecha_autorizacion'],
            'meses_autorizados' => ['meses_autorizados'],
            'estado' => ['estado'],
            'url_xml' => ['like', 'url_xml'],
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
    public function getMantenedor()
    {
        return $this->hasOne(MantenedorFolio::class, ['id' => 'id_mantenedor']);
    }
}
