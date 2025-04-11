<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "hotels".
 *
 * @property int $id
 * @property string $name
 * @property int $price
 * @property string $description
 * @property string $country
 * @property int $owner_id
 * @property int $category_id
 * @property int $status
 * @property int|null $created_ta
 * @property int|null $updated_at
 *
 * @property Category $category
 * @property Images[] $images
 * @property User $owner
 * @property Trips[] $trips
 * @property Wishlists[] $wishlists
 */
class Hotels extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hotels';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_ta', 'updated_at'], 'default', 'value' => null],
            [['name', 'price', 'description', 'country', 'owner_id', 'category_id', 'status'], 'required'],
            [['price', 'owner_id', 'category_id', 'status', 'created_ta', 'updated_at'], 'integer'],
            [['name', 'description', 'country'], 'string', 'max' => 255],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['owner_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'price' => 'Price',
            'description' => 'Description',
            'country' => 'Country',
            'owner_id' => 'Owner ID',
            'category_id' => 'Category ID',
            'status' => 'Status',
            'created_ta' => 'Created Ta',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Images]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(Images::class, ['hotel_id' => 'id']);
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[Trips]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTrips()
    {
        return $this->hasMany(Trips::class, ['hotel_id' => 'id']);
    }

    /**
     * Gets query for [[Wishlists]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWishlists()
    {
        return $this->hasMany(Wishlists::class, ['hotel_id' => 'id']);
    }

}
