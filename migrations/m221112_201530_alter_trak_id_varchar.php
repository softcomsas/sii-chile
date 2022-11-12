<?php

use yii\db\Migration;

/**
 * Class m221112_201530_alter_trak_id_varchar
 */
class m221112_201530_alter_trak_id_varchar extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%factura_emitida}}', 'track_id', $this->string(32));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221112_201530_alter_trak_id_varchar cannot be reverted.\n";

        return false;
    }
    */
}
