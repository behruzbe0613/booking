<?php

use yii\db\Migration;

class m250411_104559_alter_hotels_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('hotels', 'image_id', $this->integer());
        $this->addForeignKey('fk-hotels-image_id-images-id', 'hotels', 'image_id', 'images', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250411_104559_alter_hotels_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250411_104559_alter_hotels_table cannot be reverted.\n";

        return false;
    }
    */
}
