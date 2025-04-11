<?php

use yii\db\Migration;

class m250411_133856_alter_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'login_time', $this->string());
        $this->addColumn('user', 'session_expired_time', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250411_133856_alter_user_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250411_133856_alter_user_table cannot be reverted.\n";

        return false;
    }
    */
}
