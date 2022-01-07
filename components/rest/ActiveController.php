<?php
namespace app\components\rest;

use Yii;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\base\InvalidConfigException;

class ActiveController extends ApiRestController
{
	/**
     * @var string the model class name. This property must be set.
     */
    public $modelClass;
    /**
     * @var string the scenario used for updating a model.
     * @see \yii\base\Model::scenarios()
     */
    public $updateScenario = Model::SCENARIO_DEFAULT;
    /**
     * @var string the scenario used for creating a model.
     * @see \yii\base\Model::scenarios()
     */
    public $createScenario = Model::SCENARIO_DEFAULT;
	/**
     * {@inheritdoc}
     */
	public function init()
    {
        parent::init();
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
    }
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => \app\components\rest\ActiveSearchAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => \yii\rest\ViewAction::class,
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel'],
                'checkAccess' => [$this, 'checkAccess']
            ],
            'create' => [
                'class' => \yii\rest\CreateAction::class,
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => \yii\rest\UpdateAction::class,
                'modelClass' => $this->modelClass,
                'scenario' => $this->updateScenario,
                'findModel' => [$this, 'findModel'],
                'checkAccess' => [$this, 'checkAccess']
            ],
            'delete' => [
                'class' => \yii\rest\DeleteAction::class,
                'modelClass' => $this->modelClass,
                'findModel' => [$this, 'findModel'],
                'checkAccess' => [$this, 'checkAccess']
            ],
            'options' => [
                'class' => \yii\rest\OptionsAction::class,
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
	protected function verbs()
    {
        return [
            'index' => ['GET'],
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['PUT'],
            'delete' => ['DELETE'],
        ];
    }
    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws ForbiddenHttpException if the user does not have access
     */
    public function checkAccess($action, $model = null)
    {
    }
    /**
     * Returns the data model based on the primary key given.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $id the ID of the model to be loaded. If the model has a composite primary key,
     * the ID must be a string of the primary key values separated by commas.
     * The order of the primary key values should follow that returned by the `primaryKey()` method
     * of the model.
     * @return ActiveRecordInterface the model found
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id): Model
    {
        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) 
            $model = $modelClass::findOne($id);        

        if (isset($model)) 
            return $model;
        
        throw new NotFoundHttpException("No existe la fila: $id");
    }
}
