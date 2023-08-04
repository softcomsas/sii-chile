<?php

namespace app\components\sii;

use Yii;

/**
 * Clase para el consumo de los Servicios de Impuestos Internos
 * 
 * @author Raubel
 */

class SiiClient
{
    public $urlBase = 'https://rahue.sii.cl/recursos/v1';

    public $semilla;
    public $semillaBoleta;
    public $token;

    public $sw_produccion = 1;

    public function ObtenerSemilla()
    {
        if ($this->semilla) {
            return $this->semilla;
        }
        # URL DEL SERVIDOR
        if ($this->sw_produccion == "1") {
            $serverURL = 'https://palena.sii.cl/DTEWS/'; // sii.cl    maullin=pruebas   palena=real
        } else {
            $serverURL = 'https://maullin.sii.cl/DTEWS/'; // sii.cl    maullin=pruebas   palena=real
        }
        # NOMBRE DEL SCRIPT
        $serverScript = "CrSeed.jws"; // sii.cl
        # METODO A LLAMAR
        $metodoALlamar = 'getSeed'; // sii.cl   ESTE METODO DEVUELVE 12+2 caracteres   001412726972 00  
        // Crear un cliente de NuSOAP para el WebService
        $cliente = new nusoap_client("$serverURL/$serverScript?wsdl", 'wsdl');
        //  
        $error = $cliente->getError();
        if ($error) {
            echo "ERROR";
            return;
        }
        #
        $result = $cliente->call(
            "$metodoALlamar", // Funcion a llamar
            array('parametro' => ''), // Parametros pasados a la funcion
            "uri:$serverURL/$serverScript", // namespace
            "uri:$serverURL/$serverScript/$metodoALlamar" // SOAPAction
        );
        //  
        if ($cliente->fault) {
            //echo "ERROR";
            return;
            # 
        } else {
            $error = $cliente->getError();
            if ($error) {
                //echo "ERROR";
                return;
            } else {
                #
                $semilla_sii = $result;
            }
        }
        #
        Yii::error($semilla_sii, '$semilla_sii');
        #
        $i = 0;
        $dom = new \DOMDocument;
        //$xml = file_get_contents('semilla_sii.xsd');
        $dom->loadXML($semilla_sii);
        $elementos = $dom->getElementsByTagName('SEMILLA');
        foreach ($elementos as $elemento) {
            $x[$i] = $elemento->nodeValue;
            $this->semilla = $x[$i];
            $i++;
        }
        #
        return $this->semilla;
    }

    public function ObtenerToken()
    {
        $semilla = $this->semilla;
        $certificado = Yii::$app->params['certificado'];

        if ($this->sw_produccion == "1") {
            $serverURL = 'https://palena.sii.cl/DTEWS/'; // sii.cl    palena=produccion
        } else {
            $serverURL = 'https://maullin.sii.cl/DTEWS/'; // sii.cl   maullin=certificacion  
        }

        $file_out = '<getToken><item><Semilla>' . $semilla . '</Semilla></item></getToken>';

        $ar = fopen(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . "semilla_porfirmar.xml", "w") or die("ERROR");
        fputs($ar, $file_out);
        fclose($ar);

        # Calcular la digestion del archivo
        $digesta = $this->ObtenerDigestion("semilla_porfirmar.xml");

        # plantilla 
        $firmador = '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#"><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"></CanonicalizationMethod><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"></SignatureMethod><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"></Transform></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"></DigestMethod><DigestValue>' . $digesta . '</DigestValue></Reference></SignedInfo>';

        # guardar la plantilla signature con la digesta del documento
        $archivo = fopen(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . "digestador.xml", "w") or die("ERROR");
        fputs($archivo, $firmador);
        fclose($archivo);

        # calcular la $firma binaria
        openssl_sign($firmador, $firma_bin, $certificado["PrivKey"], OPENSSL_ALGO_SHA1);

        # convertir la $firma binaria a base64
        $SignatureValueDocumento = base64_encode($firma_bin);

        # armar el signature con la digesta y con 
        $signature_docto = '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>' . $digesta . '</DigestValue></Reference></SignedInfo><SignatureValue>' . $SignatureValueDocumento . '</SignatureValue><KeyInfo><KeyValue><RSAKeyValue><Modulus>' . $certificado["Modulus"] . '</Modulus><Exponent>' . $certificado["Exponent"] . '</Exponent></RSAKeyValue></KeyValue><X509Data><X509Certificate>' . $certificado["X509Certificate"] . '</X509Certificate></X509Data></KeyInfo></Signature>';

        # DOCUMENTO FIRMADO
        $semilla_firmada = '<?xml version="1.0" encoding="ISO-8859-1"?>
        <getToken>
            <item>
                <Semilla>' . $semilla . '</Semilla>
            </item>' . $signature_docto . '
        </getToken>';

        # guardar el elemento set para calcular su digesta
        $archivo = fopen(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'semilla_firmada.xml', "w") or die("ERROR");
        fputs($archivo, $semilla_firmada);
        fclose($archivo);

        # NOMBRE DEL SCRIPT
        $serverScript = "GetTokenFromSeed.jws"; // sii.cl https://maullin.sii.cl/DTEWS/GetTokenFromSeed.jws

        # METODO A LLAMAR
        $metodoALlamar = 'getToken'; // sii.cl ESTE METODO DEVUELVE 12+2 caracteres 001412726972 00

        # Crear un cliente de NuSOAP para el WebService
        $cliente = new nusoap_client("$serverURL/$serverScript?wsdl", 'wsdl');

        # Se pudo conectar?
        $error = $cliente->getError();
        if ($error) {
            echo "ERRORTOKEN1";
            return;
        }

        # ARCHIVO XML A ENVIAR
        $archivo_xml = file_get_contents(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'semilla_firmada.xml');

        # EJECUTAR LA LLAMADA A LA URL DEL SII ENVIANDO EL XML CON LA SEMILLA FIRMADA
        $result = $cliente->call(
            "$metodoALlamar", // Funcion a llamar
            array($archivo_xml), // Parametros pasados a la funcion
            "uri:$serverURL/$serverScript", // namespace
            "uri:$serverURL/$serverScript/$metodoALlamar" // SOAPAction
        );

        if ($cliente->fault) {
            echo "ERRORTOKEN2";
            return;
        } else {
            $error = $cliente->getError();
            if ($error) {
                echo "ERRORTOKEN3";
                return;
            }
        }

        $ar = fopen(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . "token_sii.xsd", "w") or die("ERRORTOKEN4");
        fputs($ar, $result);
        fclose($ar);


        $i = 0;
        $dom = new \DOMDocument;
        $xml = file_get_contents(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'token_sii.xsd');
        $dom->loadXML($xml);
        $elementos = $dom->getElementsByTagName('TOKEN');
        foreach ($elementos as $elemento) {
            $xtoken[$i] = $elemento->nodeValue;
            $token = $xtoken[$i];
            $i++;
        }
        return $token;
    }

    private function ObtenerDigestion($url_archivo)
    {
        // crear tabulacion automatica
        // $doc->formatOutput = true;
        // Crear Objeto Xml
        $doc = new \DOMDocument();
        // Preservar los espacios tabulaciones, etc...
        $doc->preserveWhiteSpace = true;
        // Carga archivo en el objeto
        $doc->load(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . $url_archivo);
        // Embeber
        $dom = $doc->documentElement;
        // Calcular Digestion
        $digestion = base64_encode(sha1($dom->C14N(), true));
        // Retornar Valor Calculado
        return $digestion;
    }


    public function consulta_dte($rut, $dv, $trackID)
    {
        $estado = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$rut, $dv, $trackID, $this->token]);

        // si el estado se pudo recuperar se muestra estado y glosa
        if ($estado !== false) {
            return [
                'codigo' => (string)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0],
                'glosa' => (string)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0],
            ];
        }
        return false;
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

    public function ObtenerSemillaBoleta()
    {
        if ($this->semillaBoleta) {
            return $this->semillaBoleta;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sii.cl/recursos/v1/boleta.electronica.semilla');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "accept: application/xml",
            "content-type: application/xml"
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

        $dom = new \DOMDocument;
        $dom->loadXML($response);

        $elementos = $dom->getElementsByTagName('SEMILLA');
        $lista = [];

        foreach ($elementos as $element) {
            $lista[] = $element->nodeValue;
        }

        $this->semillaBoleta = $lista[0];

        return $this->semillaBoleta;
    }

    public function ObtenerTokenBoleta()
    {
        $semilla = $this->semillaBoleta;

        return \sasco\LibreDTE\Sii\Autenticacion::getTokenBoleta($semilla, Yii::$app->params['config']);
    }

    public static function enviarBoleta($usuario, $empresa, $dte, $token, $retry = null)
    {
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
        $url = 'https://rahue.sii.cl/recursos/v1/boleta.electronica.envio';
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

    public function EstadoEnvioBoleta($empresa, $trackId, $token)
    {
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
}
