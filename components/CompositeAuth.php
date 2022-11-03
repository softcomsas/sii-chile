<?php

namespace app\components;

use Yii;
use yii\filters\auth\AuthInterface;
use yii\base\InvalidConfigException;
use yii\filters\auth\CompositeAuth as YiiCompositeAuth;

class CompositeAuth extends  YiiCompositeAuth
{
    public function authenticate($user, $request, $response)
    {
        foreach ($this->authMethods as $i => $auth) {
            if (!$auth instanceof AuthInterface) {
                $this->authMethods[$i] = $auth = Yii::createObject($auth);
                if (!$auth instanceof AuthInterface) {
                    throw new InvalidConfigException(get_class($auth) . ' must implement yii\filters\auth\AuthInterface');
                }
            }

            $identity = $auth->authenticate($user, $request, $response);
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }
}
