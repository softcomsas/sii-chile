<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%folios_utilizados_mes}}`.
 */
class m221020_010426_create_folios_utilizados_mes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%folios_utilizados_mes}}', [
            'anno' => $this->smallInteger(4)->notNull(),
            'mes' => $this->tinyInteger(2)->notNull(),
            'cantidad' => $this->integer()->notNull()->defaultValue(0),
            'PRIMARY KEY (anno, mes)'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%folios_utilizados_mes}}');
    }
}
