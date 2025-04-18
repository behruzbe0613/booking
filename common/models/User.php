<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property string $phone_number
 * @property string $login_time
 * @property string $session_expired_time
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int|null $wishlists_id
 * @property string|null $verification_token
 */
class User extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password_reset_token', 'wishlists_id', 'verification_token'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 10],
            [['username', 'auth_key', 'password', 'password_hash', 'email', 'phone_number', 'login_time', 'session_expired_time', 'created_at', 'updated_at'], 'required'],
            [['status', 'created_at', 'updated_at', 'wishlists_id'], 'integer'],
            [['username', 'password', 'password_hash', 'password_reset_token', 'email', 'phone_number', 'login_time', 'session_expired_time', 'verification_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password' => 'Password',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'phone_number' => 'Phone Number',
            'login_time' => 'Login Time',
            'session_expired_time' => 'Session Expired Time',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'wishlists_id' => 'Wishlists ID',
            'verification_token' => 'Verification Token',
        ];
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


}
