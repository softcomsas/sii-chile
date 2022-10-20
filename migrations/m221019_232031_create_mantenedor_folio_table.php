<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%mantenedor_folio}}`.
 */
class m221019_232031_create_mantenedor_folio_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%mantenedor_folio}}', [
            'id' => $this->primaryKey(),
            'codigo_documento' => $this->tinyInteger(2)->notNull(),
            'tipo_documento' => $this->string(45)->notNull(),
            'siguiente_folio' => $this->integer(),
            'total_disponible' => $this->integer(),
            'alerta' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%mantenedor_folio}}');
    }
}
