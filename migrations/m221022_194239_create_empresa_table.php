<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%empresa}}`.
 */
class m221022_194239_create_empresa_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%empresa}}', [
            'id' => $this->primaryKey(),
            'rut' => $this->string(10)->notNull(),
            'razon_social' => $this->string(100)->notNull(),
            'giro' => $this->string(100)->notNull(),
            'ateco' => $this->integer(),
            'direccion' => $this->string(150)->notNull(),
            'ciudad' => $this->string(50)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%empresa}}');
    }
}
