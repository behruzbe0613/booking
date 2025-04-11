<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "trips".
 *
 * @property int $id
 * @property int $user_id
 * @property int $hotel_id
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property int $active
 *
 * @property Hotels $hotel
 * @property User $user
 */
class Trips extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'trips';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['started_at', 'ended_at'], 'default', 'value' => null],
            [['user_id', 'hotel_id', 'active'], 'required'],
            [['user_id', 'hotel_id', 'active'], 'integer'],
            [['started_at', 'ended_at'], 'safe'],
            [['hotel_id'], 'exist', 'skipOnError' => true, 'targetClass' => Hotels::class, 'targetAttribute' => ['hotel_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'hotel_id' => 'Hotel ID',
            'started_at' => 'Started At',
            'ended_at' => 'Ended At',
            'active' => 'Active',
        ];
    }

    /**
     * Gets query for [[Hotel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHotel()
    {
        return $this->hasOne(Hotels::class, ['id' => 'hotel_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

}
