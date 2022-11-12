<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%mantenedor}}`.
 */
class m221112_213030_add_sec_column_to_mantenedor_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%mantenedor_folio}}', 'sec_envio', $this->integer()->null());
        $this->addColumn('{{%mantenedor_folio}}', 'sec_envio_fecha', $this->date()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%mantenedor_folio}}', 'sec_envio');
        //$this->dropColumn('{{%mantenedor_folio}}', 'sec_envio_fecha');
    }
}
