<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%mantenedor_folio}}`.
 */
class m221022_184903_add_new_column_to_mantenedor_folio_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%configuracion_folio}}');
        $this->addColumn('{{%mantenedor_folio}}', 'rut_empresa', $this->string(10)->notNull()->after('id'));
        $this->addColumn('{{%mantenedor_folio}}', 'multiplicador', $this->integer()->notNull()->defaultValue(5)->after('alerta'));
        $this->addColumn('{{%mantenedor_folio}}', 'rango_maximo', $this->integer()->after('multiplicador'));
        $this->addColumn('{{%mantenedor_folio}}', 'total_utilizado', $this->integer()->after('total_disponible'));
        $this->addColumn('{{%mantenedor_folio}}', 'ambiente', $this->string('4')->notNull('DEV'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%mantenedor_folio}}', 'rut_empresa');
        $this->dropColumn('{{%mantenedor_folio}}', 'multiplicador');
        $this->dropColumn('{{%mantenedor_folio}}', 'rango_maximo');
        $this->dropColumn('{{%mantenedor_folio}}', 'total_utilizado');
        $this->dropColumn('{{%mantenedor_folio}}', 'ambiente');
    }
}
