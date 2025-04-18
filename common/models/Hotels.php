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
 * @property int $bathrooms
 * @property int $bedrooms
 * @property int $beds
 * @property string $city
 * @property int $persons
 * @property int $rating
 * @property string $address
 * @property int $owner_id
 * @property int $category_id
 * @property int $status
 * @property int|null $created_ta
 * @property int|null $updated_at
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
            [['name', 'price', 'description', 'bathrooms', 'bedrooms', 'beds', 'city', 'persons', 'rating', 'address', 'owner_id', 'category_id', 'status'], 'required'],
            [['price', 'bathrooms', 'bedrooms', 'beds', 'persons', 'rating', 'owner_id', 'category_id', 'status', 'created_ta', 'updated_at'], 'integer'],
            [['name', 'description', 'city', 'address'], 'string', 'max' => 255],
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
            'bathrooms' => 'Bathrooms',
            'bedrooms' => 'Bedrooms',
            'beds' => 'Beds',
            'city' => 'City',
            'persons' => 'Persons',
            'rating' => 'Rating',
            'address' => 'Address',
            'owner_id' => 'Owner ID',
            'category_id' => 'Category ID',
            'status' => 'Status',
            'created_ta' => 'Created Ta',
            'updated_at' => 'Updated At',
        ];
    }

}
