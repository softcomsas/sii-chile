<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%factura_emitida}}`.
 */
class m221107_231055_add_new_column_to_factura_emitida_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%factura_emitida}}', 'folio', $this->integer());
        $this->addColumn('{{%factura_emitida}}', 'tipo', $this->integer());

        $this->createIndex('idx-factura_emitida-folio-tipo', '{{%factura_emitida}}', ['folio', 'tipo']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-factura_emitida-folio-tipo', '{{%factura_emitida}}');

        $this->dropColumn('{{%factura_emitida}}', 'tipo');
        $this->dropColumn('{{%factura_emitida}}', 'folio');
    }
}
