<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%configuracion_folio}}`.
 */
class m221019_231416_create_configuracion_folio_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%configuracion_folio}}', [
            'id' => $this->primaryKey(),
            'multiplicador' => $this->tinyInteger(1)->notNull()->defaultValue(5),
            'rut_empresa' => $this->string(10)->notNull(),
            'tipo_documento' => $this->string(45)->notNull(),
            'rango_maximo' => $this->integer()->notNull(),
            'total_utilizado' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%configuracion_folio}}');
    }
}
