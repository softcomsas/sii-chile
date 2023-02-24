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
        $Docs = [];
        $Caratula = null;
        try {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDte->loadXML(file_get_contents($file));
            $Caratula = $EnvioDte->getCaratula();
            $Documentos = $EnvioDte->getDocumentos();

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
                    'TpoTranCompra'  => isset($datos['Encabezado']['IdDoc']['TpoTranCompra'])
                        ? $datos['Encabezado']['IdDoc']['TpoTranCompra']
                        : null,
                    'TpoTranVenta'  => isset($datos['Encabezado']['IdDoc']['TpoTranVenta'])
                        ? $datos['Encabezado']['IdDoc']['TpoTranVenta']
                        : null,
                    'FmaPago'  => isset($datos['Encabezado']['IdDoc']['FmaPago'])
                        ? $datos['Encabezado']['IdDoc']['FmaPago']
                        : null,
                    'RUTEmisor'  => isset($datos['Encabezado']['Emisor']['RUTEmisor'])
                        ? $datos['Encabezado']['Emisor']['RUTEmisor']
                        : null,
                    'RznSocEmisor'  => isset($datos['Encabezado']['Emisor']['RznSoc'])
                        ? $datos['Encabezado']['Emisor']['RznSoc']
                        : null,
                    'GiroEmis'  => isset($datos['Encabezado']['Emisor']['GiroEmis'])
                        ? $datos['Encabezado']['Emisor']['GiroEmis']
                        : null,
                    'TelefonoEmisor'  => isset($datos['Encabezado']['Emisor']['Telefono'])
                        ? $datos['Encabezado']['Emisor']['Telefono']
                        : null,
                    'CorreoEmisor'  => isset($datos['Encabezado']['Emisor']['CorreoEmisor'])
                        ? $datos['Encabezado']['Emisor']['CorreoEmisor']
                        : null,
                    'Acteco'  => isset($datos['Encabezado']['Emisor']['Acteco'])
                        ? $datos['Encabezado']['Emisor']['Acteco']
                        : null,
                    'CdgSIISucur'  => isset($datos['Encabezado']['Emisor']['CdgSIISucur'])
                        ? $datos['Encabezado']['Emisor']['CdgSIISucur']
                        : null,
                    'DirOrigen'  => isset($datos['Encabezado']['Emisor']['DirOrigen'])
                        ? $datos['Encabezado']['Emisor']['DirOrigen']
                        : null,
                    'CmnaOrigen'  => isset($datos['Encabezado']['Emisor']['CmnaOrigen'])
                        ? $datos['Encabezado']['Emisor']['CmnaOrigen']
                        : null,
                    'CiudadOrigen'  => isset($datos['Encabezado']['Emisor']['CiudadOrigen'])
                        ? $datos['Encabezado']['Emisor']['CiudadOrigen']
                        : null,
                    'RUTRecep'  => isset($datos['Encabezado']['Receptor']['RUTRecep'])
                        ? $datos['Encabezado']['Receptor']['RUTRecep']
                        : null,
                    'RznSocRecep'  => isset($datos['Encabezado']['Receptor']['RznSocRecep'])
                        ? $datos['Encabezado']['Receptor']['RznSocRecep']
                        : null,
                    'GiroRecep'  => isset($datos['Encabezado']['Receptor']['GiroRecep'])
                        ? $datos['Encabezado']['Receptor']['GiroRecep']
                        : null,
                    'ContactoRecep'  => isset($datos['Encabezado']['Receptor']['Contacto'])
                        ? $datos['Encabezado']['Receptor']['Contacto']
                        : null,
                    'DirRecep'  => isset($datos['Encabezado']['Receptor']['DirRecep'])
                        ? $datos['Encabezado']['Receptor']['DirRecep']
                        : null,
                    'CmnaRecep'  => isset($datos['Encabezado']['Receptor']['CmnaRecep'])
                        ? $datos['Encabezado']['Receptor']['CmnaRecep']
                        : null,
                    'CiudadRecep'  => isset($datos['Encabezado']['Receptor']['CiudadRecep'])
                        ? $datos['Encabezado']['Receptor']['CiudadRecep']
                        : null,
                    'RUTSolicita'  => isset($datos['Encabezado']['RUTSolicita'])
                        ? $datos['Encabezado']['RUTSolicita']
                        : null,
                    'MntNeto'  => $datos['Encabezado']['Totales']['MntNeto'],
                    'TasaIVA'  => isset($datos['Encabezado']['Totales']['TasaIVA'])
                        ? $datos['Encabezado']['Totales']['TasaIVA']
                        : null,
                    'IVA'  => $datos['Encabezado']['Totales']['IVA'],
                    'MntTotal'  => $datos['Encabezado']['Totales']['MntTotal'],
                    'Detalles'  => $datos['Detalle']
                ]);
                //$factura->Detalles = 
                if(!$factura->save()){
                    print_r($factura->errors). " \n";
                    throw new \Exception("Factura inválida", 1);
                    ;
                }
            }
            $dir = Yii::getAlias('@processed') . DIRECTORY_SEPARATOR
                . substr($Caratula['TmstFirmaEnv'], 0, 10) . '_' . $Caratula['RutEmisor'] . '_' . $Caratula['RutReceptor'];

            if (!is_dir($dir)) {
                mkdir($dir);
            }
            if (copy($file,  $dir . DIRECTORY_SEPARATOR  . 'dte_' . $Caratula['RutEmisor'] . '_' . $DTE->getID() . '.xml'))
                FileHelper::unlink($file);
        } catch (\Throwable $th) {
            echo "File " . $file . " \n";
            echo $th->getMessage() . " \n";

            $dir = Yii::getAlias('@skiped') . DIRECTORY_SEPARATOR;

            if (copy($file,  $dir . DIRECTORY_SEPARATOR  . basename($file)))
                FileHelper::unlink($file);
            }
        return [
            'Caratula' => $Caratula,
            'Documentos' => $Docs
        ];
    }
}
