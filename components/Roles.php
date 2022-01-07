<?php
namespace app\components;

use Yii;

class Roles
{
    /**
     * Asignar roles a un usuario.
     *
     * @param $idUser
     * @param array $roles
     *
     * @return bool
     */
    public static function assign($idUser, array $roles): bool
    {
        $manager = Yii::$app->authManager;
        foreach ($roles as $name) {
            $rol = $manager->getRole($name);
			if (empty($rol)) {
				continue;
			}
            $manager->assign($rol, $idUser);
        }

        return true;
    }
    /**
     * Remover roles a un usuario.
     *
     * @param $idUser
     * @param array $roles
     *
     * @return bool
     */
    public static function revoke($idUser, array $roles): bool
    {
        $manager = Yii::$app->authManager;
        foreach ($roles as $name) {
            $rol = $manager->getRole($name);
			if (empty($rol)) {
				continue;
			}
            $manager->revoke($rol, $idUser);
        }

        return true;
    }
}
