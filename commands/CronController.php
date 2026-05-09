<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\ExitCode;
use app\models\ProcessDTE;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CronController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'Hola Mundo')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }
    public function actionProcess()
    {
        echo "Comienza la carga de facturas desde el mail.\n";

        ProcessDTE::descargarAdjuntos();
        ProcessDTE::runProcess();

        echo "Termina la carga de facturas desde el mail.\n";

        return ExitCode::OK;
    }

    public function actionPendientes($rut_empresa = '77321084-5')
    {
        echo "Comienza procesamiento de pendientes para empresa: {$rut_empresa}\n";

        Yii::$app->sii->setEmpresa($rut_empresa);
        Yii::$app->sii->setAmbiente(Yii::$app->params['SII.AMBIENTE']);

        $query = \app\models\FacturaEmitida::find()
            ->where([
                'rut_empresa' => $rut_empresa,
                'tipo' => [39, 61],
                'estado' => \app\models\FacturaEmitida::ESTADO_CREADO
            ])
            ->orderBy(['tipo' => SORT_ASC]);

        $totalProcesados = 0;
        $loteNumero = 0;

        // Procesar en lotes de 100 registros
        foreach ($query->batch(100) as $lote) {
            $loteNumero++;
            echo "Procesando lote #{$loteNumero} (" . count($lote) . " registros)...\n";

            Yii::$app->sii->setEmpresa($rut_empresa);
            Yii::$app->sii->setAmbiente(Yii::$app->params['SII.AMBIENTE']);

            foreach ($lote as $row) {
                Yii::$app->sii->agregar($row->getDte());
            }

            $resultado = Yii::$app->sii->send();
            $totalProcesados += count($lote);

            echo "Lote #{$loteNumero} enviado. Total procesados: {$totalProcesados}\n";

            // Esperar 2 segundos entre cada lote para no saturar el servidor
            sleep(2);
        }

        echo "Proceso completado. Total de registros procesados: {$totalProcesados}\n";

        return ExitCode::OK;
    }
}
