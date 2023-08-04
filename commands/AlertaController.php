<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\MantenedorFolio;
use Yii;
use yii\console\ExitCode;
use yii\console\Controller;


class AlertaController extends Controller
{
    public function actionIndex()
    {
        $mantenedoresConDeficit = MantenedorFolio::find()
            ->where('total_disponible <= alerta')
            ->all();

        return ExitCode::OK;
    }
}
