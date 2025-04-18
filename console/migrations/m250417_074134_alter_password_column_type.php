<?php

use yii\db\Migration;

class m250417_074134_alter_password_column_type extends Migration
{
    /**
     * {@inheritdoc}
     */
  public function safeUp()
  {
    // 'user' jadvalidagi 'password' ustunini VARCHAR(255) ga o‘zgartirish
    $this->alterColumn('user', 'password', $this->string(255));
  }

    /**
     * {@inheritdoc}
     */
  public function safeDown()
  {
    // Agar rollback qilinsa, eski holatga qaytariladi (masalan, bigint bo‘lsa)
    // Sizning holatingizda bu raqam edi, ehtimol $this->bigInteger() bo‘lgan
    $this->alterColumn('user', 'password', $this->bigInteger());
  }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250417_074134_alter_password_column_type cannot be reverted.\n";

        return false;
    }
    */
}
