<?php

use yii\db\Migration;

class m250420_131930_alter_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'email_verification_token', $this->string());
        $this->addColumn('user', 'is_email_verified', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250420_131930_alter_user_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250420_131930_alter_user_table cannot be reverted.\n";

        return false;
    }
    */
}
