<?php

namespace app\models;

use Yii;
use yii\helpers\FileHelper;

class ProcessDTE
{
    public static function descargarAdjuntos()
    {
        $path = Yii::getAlias('@unprocessed');
        $mail_sii_config = Yii::$app->params['mail_sii_config'];
        $inbox = imap_open($mail_sii_config['hostname'], $mail_sii_config['username'], $mail_sii_config['password'])
            or die('Cannot connect to Gmail: ' . imap_last_error());

        $emails = imap_search($inbox, 'UNSEEN'); // 'UNSEEN' => No lidos, 'ALL' => Todos

        if ($emails) {

            rsort($emails);

            foreach ($emails as $email_number) {

                $overview = imap_fetch_overview($inbox, $email_number, 0);

                $message = imap_fetchbody($inbox, $email_number, 2);

                $structure = imap_fetchstructure($inbox, $email_number);

                $attachments = array();

                if (isset($structure->parts) && count($structure->parts)) {
                    for ($i = 0; $i < count($structure->parts); $i++) {
                        $attachments[$i] = array(
                            'is_attachment' => false,
                            'filename' => '',
                            'name' => '',
                            'attachment' => ''
                        );

                        if ($structure->parts[$i]->ifdparameters) {
                            foreach ($structure->parts[$i]->dparameters as $object) {
                                if (strtolower($object->attribute) == 'filename') {
                                    $attachments[$i]['is_attachment'] = true;
                                    $attachments[$i]['filename'] = $object->value;
                                }
                            }
                        }

                        if ($structure->parts[$i]->ifparameters) {
                            foreach ($structure->parts[$i]->parameters as $object) {
                                if (strtolower($object->attribute) == 'name') {
                                    $attachments[$i]['is_attachment'] = true;
                                    $attachments[$i]['name'] = $object->value;
                                }
                            }
                        }

                        if ($attachments[$i]['is_attachment']) {
                            $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);

                            /* 4 = QUOTED-PRINTABLE encoding */
                            if ($structure->parts[$i]->encoding == 3) {
                                $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                            }
                            /* 3 = BASE64 encoding */ elseif ($structure->parts[$i]->encoding == 4) {
                                $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                            }
                        }
                    }
                }

                foreach ($attachments as $attachment) {
                    if ($attachment['is_attachment'] == 1) {
                        $filename = $attachment['name'];
                        if (empty($filename)) $filename = $attachment['filename'];

                        if (empty($filename)) $filename = time() . ".dat";

                        $fp = fopen($path . DIRECTORY_SEPARATOR . $email_number . "-" . $filename, "w+");
                        fwrite($fp, $attachment['attachment']);
                        fclose($fp);
                    }
                }
            }
        }

        imap_close($inbox);
    }
    public static function runProcess()
    {
        $files = FileHelper::findFiles(
            Yii::getAlias('@unprocessed'),
            [
                'except' => [".gitignore"]
            ]
        );
        foreach ($files as $file) {
            self::processFile($file);
        }
        return $files;
    }
    public static function processFile($file = 'EnvMiPERCP1523907049.xml')
    {
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(file_get_contents($file));
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
                'Detalles'  => $datos['Detalle']
            ]);
            //$factura->Detalles = 
            $factura->save();
        }
        print_r($Docs);
        return 0;
        $dir = Yii::getAlias('@processed') . DIRECTORY_SEPARATOR
            . substr($Caratula['TmstFirmaEnv'], 0, 10) . '_' . $Caratula['RutEmisor'] . '_' . $Caratula['RutReceptor'];

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (copy($file,  $dir . DIRECTORY_SEPARATOR  . 'dte_' . $Caratula['RutEmisor'] . '_' . $DTE->getID() . '.xml'))
            FileHelper::unlink($file);

        return [
            'Caratula' => $Caratula,
            'Documentos' => $Docs
        ];
    }
}
