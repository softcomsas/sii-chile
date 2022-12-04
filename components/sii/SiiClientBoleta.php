<?php

namespace app\components\sii;

use Yii;
use yii\base\Component;

/**
 * Clase para el consumo de los Servicios de Impuestos Internos
 * 
 * @author Raubel
 */

class SiiClientBoleta extends Component
{
    private $_semilla;
    private $_token;

    public $produccion = 0;

    public $urlBaseProd = 'https://rahue.sii.cl/recursos/v1';
    public $urlBaseDev = 'https://pangal.sii.cl/recursos/v1';
    public $urlBaseAutorizacionProd = 'https://api.sii.cl/recursos/v1';
    public $urlBaseAutorizacionDev = 'https://apicert.sii.cl/recursos/v1';

    public function __construct($config = [])
    {
        $this->produccion = Yii::$app->params['SII.AMBIENTE'] == 'PROD' ? 1 : 0;
        parent::__construct($config);
    }

    public function getUrlBase()
    {
        return $this->produccion
            ? $this->urlBaseProd
            : $this->urlBaseDev;
    }
    public function getUrlBaseAutorizacion()
    {
        return $this->produccion
            ? $this->urlBaseAutorizacionProd
            : $this->urlBaseAutorizacionDev;
    }

    public function getSemilla()
    {
        if ($this->_semilla) {
            return $this->_semilla;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->urlBaseAutorizacion . '/boleta.electronica.semilla');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "accept: application/xml",
            "content-type: application/xml"
        ));

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!empty($err)) {
            Yii::error($err, "getSemilla");
            return;
        }

        $this->_semilla =  self::getElementByTag($response, 'SEMILLA');

        return $this->_semilla;
    }

    public function getToken()
    {
        if ($this->_token)
            return $this->_token;

        $semilla = $this->getSemilla();

        $requestFirmado = self::getTokenRequest($semilla, Yii::$app->params['config']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->urlBaseAutorizacion . "/boleta.electronica.token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "accept: application/xml",
            "content-type: application/xml"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestFirmado);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!empty($err)) {
            Yii::error($err, "getSemilla");
            return;
        }

        $this->_token =  self::getElementByTag($response, 'TOKEN');

        return $this->_token;
    }

    public function enviarBoleta($usuario, $empresa, $dte, $retry = null)
    {
        $token = $this->getToken();
        // definir datos que se usarán en el envío
        list($rutSender, $dvSender) = explode('-', str_replace('.', '', $usuario));
        list($rutCompany, $dvCompany) = explode('-', str_replace('.', '', $empresa));
        if (strpos($dte, '<?xml') === false) {
            $dte = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n" . $dte;
        }
        do {
            $file = sys_get_temp_dir() . '\dte_' . md5(microtime() . $token . $dte) . '.' . ('xml');
        } while (file_exists($file));

        file_put_contents($file, $dte);
        $data = [
            'rutSender' => $rutSender,
            'dvSender' => $dvSender,
            'rutCompany' => $rutCompany,
            'dvCompany' => $dvCompany,
            'archivo' => curl_file_create(
                $file,
                'application/xml',
                basename($file)
            ),
        ];

        // definir reintentos si no se pasaron
        if (!$retry) {
            $retry = 10;
        }

        // crear sesión curl con sus opciones
        $curl = curl_init();
        $header = [
            'User-Agent: Mozilla/4.0 (compatible; PROG 1.0; LibreDTE)',
            'Referer: https://libredte.cl',
            'Cookie: TOKEN=' . $token,
        ];
        $url = $this->urlBase . '/boleta.electronica.envio';
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // enviar XML al SII
        for ($i = 0; $i < $retry; $i++) {
            $response = curl_exec($curl);
            if ($response and $response != 'Error 500') {
                break;
            }
        }

        unlink($file);

        // verificar respuesta del envío y entregar error en caso que haya uno
        if (!$response or $response == 'Error 500') {
            if (!$response) {
                \sasco\LibreDTE\Log::write(\sasco\LibreDTE\Estado::ENVIO_ERROR_CURL,  \sasco\LibreDTE\Estado::get(\sasco\LibreDTE\Estado::ENVIO_ERROR_CURL, curl_error($curl)));
            }
            if ($response == 'Error 500') {
                \sasco\LibreDTE\Log::write(\sasco\LibreDTE\Estado::ENVIO_ERROR_500,  \sasco\LibreDTE\Estado::get(\sasco\LibreDTE\Estado::ENVIO_ERROR_500));
            }
            return false;
        }

        // cerrar sesión curl
        curl_close($curl);

        $r = json_decode($response, true);

        return $r;
    }
    public function estadoEnvioBoleta($empresa, $trackId)
    {
        $token = $this->getToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.sii.cl/recursos/v1/boleta.electronica.envio/$empresa-$trackId");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "accept: application/json",
            'Cookie: TOKEN=' . $token,
        ));

        $response = curl_exec($ch);

        $err = curl_error($ch);
        if (!empty($err)) {
            return [
                'success' => false,
                'data' => $err
            ];
        }

        curl_close($ch);

        $r = json_decode($response, true);

        return $r;
    }

    public static function getTokenRequest($seed, $Firma = [])
    {
        if (is_array($Firma))
            $Firma = new \sasco\LibreDTE\FirmaElectronica($Firma);

        $seedSigned = $Firma->signXML(
            (new \sasco\LibreDTE\XML())->generate([
                'getToken' => [
                    'item' => [
                        'Semilla' => $seed
                    ]
                ]
            ])->saveXML()
        );
        if (!$seedSigned) {
            \sasco\LibreDTE\Log::write(
                \sasco\LibreDTE\Estado::AUTH_ERROR_FIRMA_SOLICITUD_TOKEN,
                \sasco\LibreDTE\Estado::get(\sasco\LibreDTE\Estado::AUTH_ERROR_FIRMA_SOLICITUD_TOKEN)
            );
            return false;
        }
        return $seedSigned;
    }

    public static function getElementByTag($xml, $tag)
    {
        $dom = new \DOMDocument;
        $dom->loadXML($xml);

        $elementos = $dom->getElementsByTagName($tag);
        if ($elementos->count() == 0)   return;

        return $elementos->item(0)->nodeValue;
    }

    protected function connect($url, $params = [])
    {
        $curl = curl_init();
        $configCurl = [
            CURLOPT_URL => $this->urlBase . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_CONNECTTIMEOUT => 300,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "accept: application/json",
                "content-type: application/json"
            )
        ];
        if ($params == [] || !is_array($params)) {
            $configCurl[CURLOPT_CUSTOMREQUEST] = "GET";
        } else {
            $configCurl[CURLOPT_CUSTOMREQUEST] = "POST";
            $configCurl[CURLOPT_POSTFIELDS] = json_encode($params);
        }
        curl_setopt_array($curl, $configCurl);

        $response = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        if (!empty($err)) {
            return [
                'success' => false,
                'data' => $err
            ];
        }
        if ($response == null || is_string($response)) {
            return [
                'success' => false,
                'data' => 'Respuesta de Sii' . $response
            ];
        }
        Yii::error($response);
        return [
            'success' => true,
            'data' => $response
        ];
    }

    public function estadoEnvio()
    {
        $r = $this->connect("/globales/boleta.electronica.envio.estado");
        if ($r['success']) {
            return $r['data'];
        }
        return false;
    }
    public function estadoBoletas()
    {
        $r = $this->connect("/globales/boleta.electronica.estado");
        if ($r['success']) {
            return $r['data'];
        }
        return false;
    }
    public function tipoBoletas()
    {
        $r = $this->connect("/globales/boleta.electronica.tipo");
        if ($r['success']) {
            return $r['data'];
        }
        return false;
    }
    public function nivelBoletas()
    {
        $r = $this->connect("/globales/boleta.electronica.nivel");
        if ($r['success']) {
            return $r['data'];
        }
        return false;
    }
    public function seccionBoletas()
    {
        $r = $this->connect("/globales/boleta.electronica.seccion");
        if ($r['success']) {
            return $r['data'];
        }
        return false;
    }
}
