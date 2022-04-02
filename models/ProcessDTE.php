<?php

namespace app\models;

use Yii;


class ProcessDTE
{
    public static function process()
    {
        $file = 'EnvMiPERCP1523907049.xml';
        $rutaCompleta = Yii::getAlias('@unprocessed') . DIRECTORY_SEPARATOR . $file;
        //$archivo = 'xml/certificado/set_pruebas/set_pruebas_factura_exenta.xml';
        //$archivo = 'xml/certificado/etapa_simulacion.xml';

        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(file_get_contents($rutaCompleta));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();

        $Docs = [];
        foreach ($Documentos as $DTE) {
            if (!$DTE->getDatos())
                die('No se pudieron obtener los datos del DTE');

            $datos = $DTE->getDatos();
            $Docs[] =
                [
                    'id' => $DTE->getID(),
                    'Datos' => $datos,
                    'TED' => $DTE->getTED(),
                ];

            $factura = new Factura([
                'id_doc' => $DTE->getID(),
                'TipoDTE'  => $datos['Encabezado']['IdDoc']['TipoDTE'],
                'Folio'  => $datos['Encabezado']['IdDoc']['Folio'],
                'FchEmis'  => $datos['Encabezado']['IdDoc']['FchEmis'],
                'TpoTranCompra'  => $datos['Encabezado']['IdDoc']['TpoTranCompra'],
                'TpoTranVenta'  => $datos['Encabezado']['IdDoc']['TpoTranVenta'],
                'FmaPago'  => $datos['Encabezado']['IdDoc']['FmaPago'],
                'RUTEmisor'  => $datos['Encabezado']['Emisor']['RUTEmisor'],
                'RznSocEmisor'  => $datos['Encabezado']['Emisor']['RznSoc'],
                'GiroEmis'  => $datos['Encabezado']['Emisor']['GiroEmis'],
                'TelefonoEmisor'  => $datos['Encabezado']['Emisor']['Telefono'],
                'CorreoEmisor'  => $datos['Encabezado']['Emisor']['CorreoEmisor'],
                'Acteco'  => $datos['Encabezado']['Emisor']['Acteco'],
                'CdgSIISucur'  => $datos['Encabezado']['Emisor']['CdgSIISucur'],
                'DirOrigen'  => $datos['Encabezado']['Emisor']['DirOrigen'],
                'CmnaOrigen'  => $datos['Encabezado']['Emisor']['CmnaOrigen'],
                'CiudadOrigen'  => $datos['Encabezado']['Emisor']['CiudadOrigen'],
                'RUTRecep'  => $datos['Encabezado']['Receptor']['RUTRecep'],
                'RznSocRecep'  => $datos['Encabezado']['Receptor']['RznSocRecep'],
                'GiroRecep'  => $datos['Encabezado']['Receptor']['GiroRecep'],
                'ContactoRecep'  => $datos['Encabezado']['Receptor']['Contacto'],
                'DirRecep'  => $datos['Encabezado']['Receptor']['DirRecep'],
                'CmnaRecep'  => $datos['Encabezado']['Receptor']['CmnaRecep'],
                'CiudadRecep'  => $datos['Encabezado']['Receptor']['CiudadRecep'],
                'RUTSolicita'  => $datos['Encabezado']['RUTSolicita'],
                'MntNeto'  => $datos['Encabezado']['Totales']['MntNeto'],
                'TasaIVA'  => $datos['Encabezado']['Totales']['TasaIVA'],
                'IVA'  => $datos['Encabezado']['Totales']['IVA'],
                'MntTotal'  => $datos['Encabezado']['Totales']['MntTotal'],
                'NmbItem'  => $datos['Detalle']['NmbItem'],
                'QtyItem'  => $datos['Detalle']['QtyItem'],
                'UnmdItem'  => $datos['Detalle']['UnmdItem'],
                'PrcItem'  => $datos['Detalle']['PrcItem'],
                'MontoItem'  => $datos['Detalle']['MontoItem'],
            ]);
            $factura->save();
        }
        return [
            'Caratula' => $Caratula,
            'Documentos' => $Docs
        ];
    }
}
