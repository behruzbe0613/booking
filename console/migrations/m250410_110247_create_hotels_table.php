<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%hotels}}`.
 */
class m250410_110247_create_hotels_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%hotels}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'price' => $this->integer()->notNull(),
            'description' => $this->string()->notNull(),
            'country' => $this->string()->notNull(),
            'owner_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'created_ta' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->addForeignKey('fk-hotels-owner_id-user-id', 'hotels', 'owner_id', 'user', 'id');
        $this->addForeignKey('fk-hotels-category_id-category-id', 'hotels', 'category_id', 'category', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%hotels}}');
    }
}
