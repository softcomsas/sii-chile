<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%factura_detalle}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%factura}}`
 */
class m220513_215005_create_factura_detalle_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%factura_detalle}}', [
            'id' => $this->primaryKey(),
            'id_factura' => $this->integer()->notNull(),
            'TpoCodigo' => $this->integer(),
            'VlrCodigo' => $this->integer(),
            'NmbItem' => $this->string(255),
            'QtyItem' => $this->double(),
            'UnmdItem' => $this->string(10),
            'PrcItem' => $this->double(),
            'DescuentoPct' => $this->double(),
            'DescuentoMonto' => $this->double(),
            'MontoItem' => $this->double(),
        ]);

        // creates index for column `id_factura`
        $this->createIndex(
            '{{%idx-factura_detalle-id_factura}}',
            '{{%factura_detalle}}',
            'id_factura'
        );

        // add foreign key for table `{{%facturas}}`
        $this->addForeignKey(
            '{{%fk-factura_detalle-id_factura}}',
            '{{%factura_detalle}}',
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
            '{{%fk-factura_detalle-id_factura}}',
            '{{%factura_detalle}}'
        );

        // drops index for column `id_factura`
        $this->dropIndex(
            '{{%idx-factura_detalle-id_factura}}',
            '{{%factura_detalle}}'
        );

        $this->dropTable('{{%factura_detalle}}');
    }
}
