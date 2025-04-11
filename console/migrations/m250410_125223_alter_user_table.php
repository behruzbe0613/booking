<?php

use yii\db\Migration;

class m250410_125223_alter_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'wishlists_id', $this->integer());
        $this->addForeignKey('fk-user-wishlists_id-wishlists-id', 'user', 'wishlists_id', 'wishlists', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250410_125223_alter_user_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250410_125223_alter_user_table cannot be reverted.\n";

        return false;
    }
    */
}
