<?php

use yii\db\Migration;

/**
 * Class m230224_163844_alter_vlrCodigo
 */
class m230224_163844_alter_vlrCodigo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('factura_detalle', 'VlrCodigo', $this->string(255));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230224_163844_alter_vlrCodigo cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230224_163844_alter_vlrCodigo cannot be reverted.\n";

        return false;
    }
    */
}
