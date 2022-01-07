<?php

namespace app\components\rest;

use Yii;
use yii\rest\Action;
use yii\data\ActiveDataProvider;
use yii\db\Schema;

/**
 * SearchAction implementa un método api que devuelve una lista de modelos paginados.
 *
 * Intenta deducir los filtros que se van a usar teniendo en cuenta los tipos de datos de los campos de la base de datos
 * 
 * @author Raubel <raubel1993@gmail.com>
 */
class ActiveSearchAction extends Action
{
    /**
     * @var array|callable
     * En caso de ser una función debe retornar un array
     * Ejemplo:
     *
     * ```php
     * function () {
     *     return ['id_usuario' => Yii::$app->user->id];
     * }
     * ```
     */
    public $staticCondition;

    /**
     * @return ActiveDataProvider
     */
    public function run()
    {
        if ($this->checkAccess)
            call_user_func($this->checkAccess, $this->id);

        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams))
            $requestParams = Yii::$app->getRequest()->getQueryParams();

        if (is_callable($this->staticCondition)) {
            $requestParams = array_merge($requestParams, call_user_func($this->staticCondition));
        } elseif (is_array($this->staticCondition)) {
            $requestParams = array_merge($requestParams, $this->staticCondition);
        }
        Yii::error($requestParams);
        
        $modelClass = $this->modelClass;
        $query = $modelClass::find();

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [1, 50000],
                'defaultPageSize' => 10,
                //'pageSize' => 25,
            ],
        ]);

        if (empty($requestParams))
            return $dataProvider;

        $table = $modelClass::getTableSchema();
        foreach ($table->columns as $column) {
            $columnName = $column->name;
            if (!isset($requestParams[$columnName]))
                continue;
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $query->andFilterWhere([$columnName => $requestParams[$columnName]]);
                    break;
                default:
                    $query->andFilterWhere(['like', $columnName, $requestParams[$columnName]]);
                    break;
            }
        }

        return $dataProvider;
    }
}
