<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%factura_emitida}}`.
 */
class m221114_150633_add_estado_column_to_factura_emitida_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%factura_emitida}}', 'estado', $this->tinyInteger(1)->notNull()->defaultValue(1));
        $this->update('{{%factura_emitida}}', ['estado'=>2], 'track_id IS NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%factura_emitida}}', 'estado');
    }
}
