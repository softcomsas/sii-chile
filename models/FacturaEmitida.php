<?php

namespace app\models;

use sasco\LibreDTE\Sii\Dte;
use Yii;
use yii\web\NotAcceptableHttpException;

/**
 * This is the model class for table "factura_emitida".
 *
 * @property int $id
 * @property string $rut_empresa
 * @property string $rut_receptor
 * @property string $fecha
 * @property string|null $url_xml
 * @property string|null $track_id
 * @property int|null $folio
 * @property int|null $tipo
 */
class FacturaEmitida extends \yii\db\ActiveRecord
{
    const ESTADO_CREADO = 1;
    const ESTADO_ENVIADO = 2;
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
            [['folio', 'tipo'], 'integer'],
            [['rut_empresa', 'rut_receptor'], 'string', 'max' => 10],
            [['track_id'], 'string', 'max' => 32],
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
            'track_id' => ['track_id'],
            'folio' => ['folio'],
            'tipo' => ['tipo'],
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
    public function getDte()
    {
        $xml = file_get_contents($this->path . $this->url_xml);
        $dte = new Dte($xml);
        if ($dte->getDatos()){
            return $dte;
        }
        $envioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $envioDte->loadXML($xml);
        $Documentos = $envioDte->getDocumentos();
        if (!isset($Documentos[0])) {
            throw new \Exception("XML inválido", 1);
            ;
        }
        return $Documentos[0];
    }
    public function getPdf()
    {
        // directorio temporal para guardar los PDF
        $dir = sys_get_temp_dir() . '/dte-pdf';
        if (is_dir($dir))
            \sasco\LibreDTE\File::rmdir($dir);
        if (!mkdir($dir))
            die('No fue posible crear directorio temporal para DTEs');

        $DTE = $this->getDte();
        $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte(80); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)

        $pathLogo = Yii::getAlias('@app/upload')
            . DIRECTORY_SEPARATOR . 'logos-empresas'
            . DIRECTORY_SEPARATOR;
        $pdf->setLogo($pathLogo . 'logo2.png'); // debe ser PNG!
        $pdf->setResolucion(['FchResol' => '2014-08-22', 'NroResol' => 80]);

        $pdf->agregar($DTE->getDatos(), $DTE->getTED());
        $path = $dir . '/dte_' . $DTE->getTipo(). '_' . $DTE->getFolio() . '.pdf';
        $pdf->Output($path, 'F');
        return $path;
    }
}
