<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%trips}}`.
 */
class m250414_143828_create_trips_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%trips}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'hotel_id' => $this->integer()->notNull(),
            'started_at' => $this->dateTime(),
            'ended_at' => $this->dateTime(),
            'active' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk-trips-user_id-user-id', 'trips', 'user_id', 'user', 'id');
        $this->addForeignKey('fk-trips-hotel_id-hotels-id', 'trips', 'hotel_id', 'hotels', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%trips}}');
    }
}
