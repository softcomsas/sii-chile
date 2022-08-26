<?php

use yii\db\Migration;

/**
 * Class m220826_211754_alter_factura_detalle_table
 */
class m220826_211754_alter_factura_detalle_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%factura_detalle}}', 'TpoCodigo', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220826_211754_alter_factura_detalle_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220826_211754_alter_factura_detalle_table cannot be reverted.\n";

        return false;
    }
    */
}
