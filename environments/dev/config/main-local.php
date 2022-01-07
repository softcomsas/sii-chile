<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=yii2basic',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@app/mail',
			'transport' => [
				 'class' => 'Swift_SmtpTransport',
				 'host' => 'smtp.nauta.cu',  //smtp.gmail.com
				 'username' => 'rguerral@nauta.cu',
                       'password' => 'MyPassword',                
				 'port' => '25', // Asi deberia funcionar, 587 el fichero traia el 465
				 //'encryption' => 'tls', // tls Si no funciona dejalo en blanco 'ssl'
			 ],
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
        ],
    ],
];
