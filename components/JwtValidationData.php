<?php
namespace app\components;

use Yii;

class JwtValidationData extends \sizeg\jwt\JwtValidationData
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $jwtParams = Yii::$app->params['jwt'];
		$this->validationData->setIssuer($jwtParams['issuer']);
		$this->validationData->setAudience($jwtParams['audience']);
		$this->validationData->setId($jwtParams['id']);

		parent::init();
    }
}   
