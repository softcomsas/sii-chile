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
 * @property int|null $folio
 * @property int|null $tipo
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
            [['track_id', 'folio', 'tipo'], 'integer'],
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
    public function getPdf()
    {
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(file_get_contents($this->path . $this->url_xml));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();

        // directorio temporal para guardar los PDF
        $dir = sys_get_temp_dir() . '/dte_' . $Caratula['RutEmisor'] . '_' . $Caratula['RutReceptor'] . '_' . str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
        if (is_dir($dir))
            \sasco\LibreDTE\File::rmdir($dir);
        if (!mkdir($dir))
            die('No fue posible crear directorio temporal para DTEs');

        // procesar cada DTEs e ir agregándolo al PDF
        foreach ($Documentos as $DTE) {
            if (!$DTE->getDatos())
                die('No se pudieron obtener los datos del DTE');
            $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte(false); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)
            $pdf->setFooterText();
            //$pdf->setLogo('/home/delaf/www/localhost/dev/pages/sasco/website/webroot/img/logo_mini.png'); // debe ser PNG!
            $pdf->setResolucion(['FchResol' => $Caratula['FchResol'], 'NroResol' => $Caratula['NroResol']]);
            //$pdf->setCedible(true);
            $pdf->agregar($DTE->getDatos(), $DTE->getTED());
            $path = $dir . '/dte_' . $Caratula['RutEmisor'] . '_' . $DTE->getID() . '.pdf';
            $pdf->Output($path, 'F');
            return $path;
        }

        // entregar archivo comprimido que incluirá cada uno de los DTEs
        //\sasco\LibreDTE\File::compress($dir, ['format' => 'zip', 'delete' => true, 'download' => false]);
    }
}
