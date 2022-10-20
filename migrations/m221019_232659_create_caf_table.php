<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%caf}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%mantenedor_folio}}`
 */
class m221019_232659_create_caf_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%caf}}', [
            'id' => $this->primaryKey(),
            'id_mantenedor' => $this->integer()->notNull(),
            'desde' => $this->integer()->notNull(),
            'hasta' => $this->integer()->notNull(),
            'fecha_autorizacion' => $this->date()->notNull(),
            'meses_autorizados' => $this->TinyInteger(2)->notNull(),
            'estado' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'url_xml' => $this->string(100),
        ]);

        // creates index for column `id_mantenedor`
        $this->createIndex(
            '{{%idx-caf-id_mantenedor}}',
            '{{%caf}}',
            'id_mantenedor'
        );

        // add foreign key for table `{{%mantenedor_folio}}`
        $this->addForeignKey(
            '{{%fk-caf-id_mantenedor}}',
            '{{%caf}}',
            'id_mantenedor',
            '{{%mantenedor_folio}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%mantenedor_folio}}`
        $this->dropForeignKey(
            '{{%fk-caf-id_mantenedor}}',
            '{{%caf}}'
        );

        // drops index for column `id_mantenedor`
        $this->dropIndex(
            '{{%idx-caf-id_mantenedor}}',
            '{{%caf}}'
        );

        $this->dropTable('{{%caf}}');
    }
}
