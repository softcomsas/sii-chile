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
        <getToken><item><Semilla>' . $semilla . '</Semilla></item>' . $signature_docto . '</getToken>';

        # guardar el elemento set para calcular su digesta
        $archivo = fopen(Yii::getAlias('@runtime') . DIRECTORY_SEPARATOR . 'semilla_firmada.xml', "w") or die("ERROR");
        fputs($archivo, $semilla_firmada);
        fclose($archivo);

        # NOMBRE DEL SCRIPT
        $serverScript = "GetTokenFromSeed.jws"; // sii.cl 	 https://maullin.sii.cl/DTEWS/GetTokenFromSeed.jws

        # METODO A LLAMAR
        $metodoALlamar = 'getToken'; // sii.cl   ESTE METODO DEVUELVE 12+2 caracteres   001412726972 00  

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

        #  EJECUTAR LA LLAMADA A LA URL DEL SII ENVIANDO EL XML CON LA SEMILLA FIRMADA
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
        //  Embeber
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
                'data'    => $err
            ];
        }
        if ($response == null || is_string($response)) {
            return [
                'success' => false,
                'data'    => 'Respuesta de Sii' . $response
            ];
        }
        Yii::error($response);
        return [
            'success' => true,
            'data'    => $response
        ];
    }
}
