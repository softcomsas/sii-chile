<?php
/**
 * Configuración Local de Producción
 * 
 * Este archivo lee la configuración desde variables de entorno.
 * Ideal para despliegues en Docker, Kubernetes, o cualquier entorno cloud.
 * 
 * Variables de entorno requeridas:
 * - DB_DSN: DSN de conexión a la base de datos
 * - DB_USER: Usuario de la base de datos
 * - DB_PASS: Contraseña de la base de datos
 * - SMTP_HOST: Servidor SMTP para envío de correos
 * - SMTP_USER: Usuario SMTP
 * - SMTP_PASS: Contraseña SMTP
 * - SMTP_PORT: Puerto SMTP (opcional, default: 587)
 * - SMTP_ENCRYPTION: Tipo de encriptación (opcional: tls, ssl)
 * - MAIL_USE_FILE: true para enviar emails a archivos (desarrollo)
 */

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=yii2basic',
            'username' => getenv('DB_USER') ?: 'root',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
                'username' => getenv('SMTP_USER') ?: '',
                'password' => getenv('SMTP_PASS') ?: '',
                'port' => getenv('SMTP_PORT') ?: '587',
                'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
            ],
            'useFileTransport' => getenv('MAIL_USE_FILE') === 'true',
        ],
    ],
];
