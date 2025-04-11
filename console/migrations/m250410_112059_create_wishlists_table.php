<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%wishlists}}`.
 */
class m250410_112059_create_wishlists_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wishlists}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'hotel_id' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk-wishlists-user_id-user-id', 'wishlists', 'user_id', 'user', 'id');
        $this->addForeignKey('fk-wishlists-hotel_id-hotels-id', 'wishlists', 'hotel_id', 'hotels', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wishlists}}');
    }
}
