<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "factura_emitida".
 *
 * @property int $id
 * @property string $rut_empresa
 * @property string $rut_receptor
 * @property string $fecha
 * @property string|null $url_xml
 * @property int|null $track_id
 */
class FacturaEmitida extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'factura_emitida';
    }

    public function rules()
    {
        return [
            [['rut_empresa', 'rut_receptor', 'fecha'], 'required'],
            [['fecha'], 'safe'],
            [['track_id'], 'integer'],
            [['rut_empresa', 'rut_receptor'], 'string', 'max' => 10],
            [['url_xml'], 'string', 'max' => 45],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rut_empresa' => 'Rut Empresa',
            'rut_receptor' => 'Rut Receptor',
            'fecha' => 'Fecha',
            'url_xml' => 'Url Xml',
            'track_id' => 'Track ID',
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
            'rut_empresa' => ['like', 'rut_empresa'],
            'rut_receptor' => ['like', 'rut_receptor'],
            'fecha' => ['like', 'fecha'],
            'url_xml' => ['like', 'url_xml'],
            'track_id' => ['track_id'],
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
    public function getPath()
    {
        $path = Yii::getAlias('@app/upload')
            . DIRECTORY_SEPARATOR . 'envios'
            . DIRECTORY_SEPARATOR . $this->rut_empresa
            . DIRECTORY_SEPARATOR . $this->fecha
            . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        return $path;
    }
}
