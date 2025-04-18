<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%images}}`.
 */
class m250414_143259_create_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%images}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string()->notNull(),
            'hotel_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk-images-hotel_id-hotels-id', 'images', 'hotel_id', 'hotels', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%images}}');
    }
}
