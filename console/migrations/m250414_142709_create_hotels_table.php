<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%hotels}}`.
 */
class m250414_142709_create_hotels_table extends Migration
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
            'bathrooms' => $this->integer()->notNull(),
            'bedrooms' => $this->integer()->notNull(),
            'beds' => $this->integer()->notNull(),
            'city' => $this->string()->notNull(),
            'persons' => $this->integer()->notNull(),
            'rating' => $this->integer()->notNull(),
            'address' => $this->string()->notNull(),
            'owner_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'created_ta' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%hotels}}');
    }
}
