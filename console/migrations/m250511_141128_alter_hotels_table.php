<?php

use yii\db\Migration;

class m250511_141128_alter_hotels_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('hotels', 'country', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250511_141128_alter_hotels_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250511_141128_alter_hotels_table cannot be reverted.\n";

        return false;
    }
    */
}
