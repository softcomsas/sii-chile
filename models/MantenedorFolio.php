<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mantenedor_folio".
 *
 * @property int $id
 * @property string $rut_empresa
 * @property int $codigo_documento
 * @property string $tipo_documento
 * @property int|null $siguiente_folio
 * @property int|null $total_disponible
 * @property int|null $total_utilizado
 * @property int|null $alerta
 * @property int $multiplicador
 * @property int|null $rango_maximo
 * @property string $ambiente
 *
 * @property Caf[] $cafs
 */
class MantenedorFolio extends \yii\db\ActiveRecord
{
    const AMBIENTE_DEV = 'DEV';
    const AMBIENTE_PROD = 'PROD';

    const TIPOS_DOCUMENTOS = [
        33 => "Factura electrónica",
        34 => "Factura exenta electrónica",
        39 => "Boleta electrónica",
        41 => "Boleta exenta electrónica",
        46 => "Factura de compra electrónica",
        52 => "Guía de despacho electrónica",
        56 => "Nota de débito electrónica ",
        61 => "Nota de crédito electrónica",
        110 => "Factura de exportación electrónica",
        111 => "Nota de débito exportación electrónica",
        112 => "Nota de crédito exportación electrónica",
    ];

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
            [['rut_empresa', 'codigo_documento', 'tipo_documento', 'ambiente'], 'required'],
            [['codigo_documento', 'siguiente_folio', 'total_disponible', 'total_utilizado', 'alerta', 'multiplicador', 'rango_maximo'], 'integer'],
            [['rut_empresa'], 'string', 'max' => 10],
            [['tipo_documento'], 'string', 'max' => 45],
            [
                ['rut_empresa', 'codigo_documento'], 
                'unique', 'skipOnError' => true,
                'targetAttribute' => ['rut_empresa', 'codigo_documento'],
                'filter' => function(\yii\db\Query $query){
                    $query->andWhere(['ambiente' => $this->ambiente]);
                }
            ],
            [['ambiente'], 'in', 'range' => [self::AMBIENTE_DEV, self::AMBIENTE_PROD]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rut_empresa' => 'Rut Empresa',
            'codigo_documento' => 'Codigo Documento',
            'tipo_documento' => 'Tipo Documento',
            'siguiente_folio' => 'Siguiente Folio',
            'total_disponible' => 'Total Disponible',
            'total_utilizado' => 'Total Utilizado',
            'alerta' => 'Alerta',
            'multiplicador' => 'Multiplicador',
            'rango_maximo' => 'Rango Maximo',
            'ambiente' => 'Ambiente',
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
            'rut_empresa' => ['like', 'rut_empresa'],
            'codigo_documento' => ['codigo_documento'],
            'tipo_documento' => ['like', 'tipo_documento'],
            'siguiente_folio' => ['siguiente_folio'],
            'total_disponible' => ['total_disponible'],
            'total_utilizado' => ['total_utilizado'],
            'alerta' => ['alerta'],
            'multiplicador' => ['multiplicador'],
            'rango_maximo' => ['rango_maximo'],
            'ambiente' => ['like', 'ambiente'],
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCafEnUso()
    {
        return $this->hasOne(Caf::class, ['id_mantenedor' => 'id'])
            ->andOnCondition(['estado' => Caf::ESTADO_EN_USO]);
    }
    public function getSiguienteCaf()
    {
        return $this->hasOne(Caf::class, ['id_mantenedor' => 'id'])
            ->andOnCondition(['estado' => Caf::ESTADO_DISPONIBLE]);
    }

    public function correrFolio()
    {
        $caf = $this->cafEnUso;
        if ($caf) {
            if ($this->siguiente_folio < $caf->hasta) {
                $this->siguiente_folio++;
                return $this->save(false);
            }
            $caf->estado = Caf::ESTADO_USADO;
            $caf->save();
        }
        $nuevoCaf = $this->siguienteCaf;
        if ($nuevoCaf) {
            $this->siguiente_folio = $nuevoCaf->desde;
            return $this->save(false);
        }
        $this->siguiente_folio = null;
        return $this->save(false);
    }
}
