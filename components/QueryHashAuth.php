<?php

namespace app\components;

use Yii;
use yii\filters\auth\AuthMethod;

/**
 * QueryHashAuth is an action filter that supports the authentication based on the hash and time passed through a query parameter.
 *
 * @author Raubel
 */
class QueryHashAuth extends AuthMethod
{
    /**
     * @var string the parameter name for passing the hash
     */
    public $hashParam = 'h';

    /**
     * @var string the parameter name for passing the time
     */
    public $timeParam = 't';

    /**
     * @var string
     */
    public $key = 'ABCabc123/*-';

    /**
     * @var string
     */
    public $alg = 'sha256';

    /**
     * @var string duration in segundos
     */
    public $duration = 360;


    /**
     * {@inheritdoc}
     */
    public function authenticate($user, $request, $response)
    {
        $hash = $request->get($this->hashParam);
        $time = $request->get($this->timeParam);
        if (is_string($hash) && is_string($time)) {
            $get = $request->get();
            unset($get[$this->hashParam], $get[$this->timeParam]);
            $post = $request->post();
            if (!$this->isValid($hash, $time, $get, $post)) {
                $this->handleFailure($response);
            }

            $identity = $user->loginByAccessToken($hash, get_class($this));
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }

    public function isValid($oldHash, $time, $get = [], $body = [])
    {
        $timeDiff = time() - (int)$time;
        if ($timeDiff > $this->duration) {
            return false;
        }

        $newHash = $this->getHash($time, $get, $body);
        return $oldHash === $newHash;
    }

    public function getHash($time, $get = [], $body = [])
    {
        $key = $this->key;
        $alg = $this->alg;
        $hash_get = hash_hmac($alg, json_encode($get), $key);
        $hash_body = hash_hmac($alg, json_encode($body), $key);
        return hash_hmac($alg, "$hash_get.$hash_body.$time", $key);
    }
}
