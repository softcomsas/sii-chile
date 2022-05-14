<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%facturas}}`.
 */
class m220402_165006_create_facturas_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%facturas}}', [
            'id' => $this->primaryKey(),
            'id_doc' => $this->string(50),
            'TipoDTE' => $this->string(10),
            'Folio' => $this->string(10),
            'FchEmis' => $this->string(10),
            'TpoTranCompra' => $this->char(1),
            'TpoTranVenta' => $this->char(1),
            'FmaPago' => $this->char(1),
            'RUTEmisor' => $this->string(10),
            'RznSocEmisor' => $this->string(255),
            'GiroEmis' => $this->string(255),
            'TelefonoEmisor' => $this->string(20),
            'CorreoEmisor' => $this->string(100),
            'Acteco' => $this->string(20),
            'CdgSIISucur' => $this->string(20),
            'DirOrigen' => $this->string(255),
            'CmnaOrigen' => $this->string(255),
            'CiudadOrigen' => $this->string(255),
            'RUTRecep' => $this->string(10),
            'RznSocRecep' => $this->string(255),
            'GiroRecep' => $this->string(255),
            'ContactoRecep' => $this->string(255),
            'DirRecep' => $this->string(255),
            'CmnaRecep' => $this->string(255),
            'CiudadRecep' => $this->string(255),
            'RUTSolicita' => $this->string(10),
            'MntNeto' => $this->double(),
            'TasaIVA' => $this->double(),
            'IVA' => $this->double(),
            'MntTotal' => $this->double(),
            'estado' => $this->tinyInteger()->notNull()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%facturas}}');
    }
}
