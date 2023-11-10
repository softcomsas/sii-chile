<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%factura_dsc_rcg}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%factura}}`
 */
class m231110_125048_create_factura_dsc_rcg_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%factura_dsc_rcg}}', [
            'id' => $this->primaryKey(),
            'id_factura' => $this->integer()->notNull(),
            'TpoMov' => $this->char(1)->notNull(),
            'TpoValor' => $this->char(1)->notNull(),
            'ValorDR' => $this->double()->notNull(),
        ]);

        // creates index for column `id_factura`
        $this->createIndex(
            '{{%idx-factura_dsc_rcg-id_factura}}',
            '{{%factura_dsc_rcg}}',
            'id_factura'
        );

        // add foreign key for table `{{%factura}}`
        $this->addForeignKey(
            '{{%fk-factura_dsc_rcg-id_factura}}',
            '{{%factura_dsc_rcg}}',
            'id_factura',
            '{{%facturas}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%factura}}`
        $this->dropForeignKey(
            '{{%fk-factura_dsc_rcg-id_factura}}',
            '{{%factura_dsc_rcg}}'
        );

        // drops index for column `id_factura`
        $this->dropIndex(
            '{{%idx-factura_dsc_rcg-id_factura}}',
            '{{%factura_dsc_rcg}}'
        );

        $this->dropTable('{{%factura_dsc_rcg}}');
    }
}
