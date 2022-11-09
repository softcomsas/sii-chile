<?php

namespace app\components;

use Yii;
use yii\base\Component;

class PdfGenerator extends Component
{
    public function byDte( $DTE, $Caratula)
    {
        $dir = sys_get_temp_dir() . '/dte_' . $Caratula['RutEmisor'] . '_' . $Caratula['RutReceptor'] . '_' . str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
        if (is_dir($dir))
            \sasco\LibreDTE\File::rmdir($dir);
        if (!mkdir($dir))
            die('No fue posible crear directorio temporal para DTEs');
            
        $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte(80); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)
        //$pdf->setFooterText();
        //$pdf->setLogo('/home/delaf/www/localhost/dev/pages/sasco/website/webroot/img/logo_mini.png'); // debe ser PNG!
        $pathLogo = Yii::getAlias('@app/upload')
            . DIRECTORY_SEPARATOR . 'logos-empresas'
            . DIRECTORY_SEPARATOR;
        //$pdf->setLogo($pathLogo.'logo_ayala.jpg',1); // debe ser PNG!
        //$pdf->setLogo($pathLogo.'7555986-0.jpg','C'); // debe ser PNG!
        $pdf->setLogo($pathLogo . 'logo_ar.png', 'C'); // debe ser PNG!
        $pdf->setResolucion(['FchResol' => $Caratula['FchResol'], 'NroResol' => $Caratula['NroResol']]);
        //$pdf->setCedible(true);
        $pdf->agregar($DTE->getDatos(), $DTE->getTED());
        $path = $dir . '/dte_' . $Caratula['RutEmisor'] . '_' . $DTE->getID() . '.pdf';
        $pdf->Output($path, 'F');
        return $path;
    }
}
