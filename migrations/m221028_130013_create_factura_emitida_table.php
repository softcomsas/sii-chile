<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%factura_emitida}}`.
 */
class m221028_130013_create_factura_emitida_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%factura_emitida}}', [
            'id' => $this->primaryKey(),
            'rut_empresa' => $this->string(10)->notNull(),
            'rut_receptor' => $this->string(10)->notNull(),
            'fecha' => $this->date()->notNull(),
            'url_xml' => $this->string(45),
            'track_id' => $this->integer(),
        ]);
        $this->createIndex('idx-factura_emitida-rut_empresa', '{{%factura_emitida}}', 'rut_empresa');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-factura_emitida-rut_empresa', '{{%factura_emitida}}');
        $this->dropTable('{{%factura_emitida}}');
    }
}
