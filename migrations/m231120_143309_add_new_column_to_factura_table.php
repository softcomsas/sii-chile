<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%factura}}`.
 */
class m231120_143309_add_new_column_to_factura_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%facturas}}', 'FchVenc', $this->string(10)->after('FchEmis'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%factura}}', 'FchVenc');
    }
}
