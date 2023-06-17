<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%mantenedor_folio}}`.
 */
class m230617_022133_add_new_column_to_mantenedor_folio_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%mantenedor_folio}}', 'repetir_alerta', $this->integer()->notNull()->defaultValue(60*60*48)->after('alerta'));
        $this->addColumn('{{%mantenedor_folio}}', 'notif_alerta', $this->integer()->after('repetir_alerta'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%mantenedor_folio}}', 'notif_alerta');
        $this->dropColumn('{{%mantenedor_folio}}', 'repetir_alerta');
    }
}
