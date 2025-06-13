<?php

namespace frontend\modules\api\controllers;

use common\models\Category;
use common\models\Hotels;
use common\models\Images;
use common\models\User;
use common\models\Wishlists;
use yii\rest\Controller;

/**
 * Default controller for the `api` module
 */
class HotelsController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */

     public function behaviors()
     {
       $behaviors = parent::behaviors();
   
       // JSON formatlash
       $behaviors['contentNegotiator'] = [
         'class' => \yii\filters\ContentNegotiator::className(),
         'formats' => [
           'application/json' => \yii\web\Response::FORMAT_JSON,
         ],
       ];
   
       // CORS filter
       $behaviors['corsFilter'] = [
         'class' => \yii\filters\Cors::class,
         'cors' => [
           'Origin' => ['http://localhost:3000'], // xavfsizroq variant
           'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
           'Access-Control-Allow-Credentials' => true,
           'Access-Control-Allow-Headers' => [
             'Content-Type',
             'Authorization',
             'X-Requested-With',
             'Accept',
             'Origin'
           ],
           'Access-Control-Expose-Headers' => [
             'Content-Type',
             'Authorization'
           ],
           'Access-Control-Max-Age' => 3600,
         ],
       ];
   
       // Bearer Auth
       $behaviors['authenticator'] = [
         'class' => \yii\filters\auth\HttpBearerAuth::className(),
         'optional' => [
           'login',
           'index',
           'create',
           'register',
           'get-version-apk',
           'get-hotels',
           'get-categories',
           'create-hotels',
           'delete-hotels',
           'add-wishlist',
           'delete-wishlist',
           'get-wishlist',
         ],
       ];
   
       return $behaviors;
     }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionGetHotels()
    {
        date_default_timezone_set('Asia/Tashkent');

        if(!\Yii::$app->request->isGet){
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }

      $hotelId = \Yii::$app->request->get('id'); // GET so‘rovdan id ni olish

      if ($hotelId) {
        $hotel = Hotels::findOne($hotelId);

        if (!$hotel) {
          \Yii::$app->response->statusCode = 404;
          return [
            'status' => 'error',
            'error' => 'Hotel not found'
          ];
        }

        $hotelArray = $hotel->toArray();
        $hotelArray['images'] = Images::find()->where(['hotel_id' => $hotel->id])->all();

        return [
          'status' => 'success',
          'message' => 'Hotel fetched successfully',
          'hotel' => $hotelArray,
        ];
      }

//        $auth_key = \Yii::$app->request->post('auth_key');
//        if(!$auth_key){
//            return [
//                'status' => 'error',
//                'message' => 'Token is required',
//            ];
//        }
//
//        $user = User::findOne(['auth_key' => $auth_key]);
//
//        if (!$user){
//            return [
//                'status' => 'error',
//                'message' => 'You have not ability to get information ',
//            ];
//        }
//
//        $date = date("Y-m-d H:i:s");
//
//        if($user->session_expired_time < $date){
//            return [
//              'status' => 'error',
//              'message' => 'The session period has ended',
//            ];
//        }

        $hotels = Hotels::find()->all();
        $results = [];

        foreach ($hotels as $hotel) {
            $hotelArray = $hotel->toArray();
            $hotelArray['images'] = Images::find()->where(['hotel_id' => $hotel->id])->all();
            $results[] = $hotelArray;
        }

        return [
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'hotels' => $results,
        ];
//        var_dump($hotels);

    }

    public function actionGetCategories()
    {
        date_default_timezone_set('Asia/Tashkent');

        if(!\Yii::$app->request->isGet){
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }


        $categories = Category::find()->all();
        return [
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'hotels' => $categories,
        ];
    }

    public function actionCreateHotels()
    {
        $data = json_decode(\Yii::$app->request->getRawBody(), true);
        if(!\Yii::$app->request->isPost){
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }

        $hotel = new Hotels();
        $hotel->name = $data['name'];
        $hotel->price = $data['price'];
        $hotel->description = $data['description'];
        $hotel->bathrooms = $data['bathrooms'];
        $hotel->bedrooms = $data['bedrooms'];
        $hotel->beds = $data['beds'];
        $hotel->city = $data['city'];
        $hotel->persons = $data['persons'];
        $hotel->rating = $data['rating'];
        $hotel->address = $data['address'];
        $hotel->owner_id = $data['owner_id'];
        $hotel->category_id = $data['category_id'];
        $hotel->status = 1;
        $hotel->created_ta = time();
        $hotel->updated_at = time();

        if ($hotel->save(false)) {

            // 🔽 Rasmlarni yozish
            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $imageUrl) {
                    $image = new \common\models\Images();
                    $image->hotel_id = $hotel->id;
                    $image->url = $imageUrl; // Yoki $image->image_url agar sizda shunday nom bo‘lsa
                    $image->save(false);
                }
            }

            return [
                'status' => 'success',
                'message' => 'Add new hotel successful',
                'hotel' => $hotel,
            ];
        } else {
            \Yii::$app->response->statusCode = 500;
            return ['error' => 'Server xatosi: hotel qoshilmadi'];
        }
    }


    public function actionDeleteHotels()
    {
        $data = json_decode(\Yii::$app->request->getRawBody(), true);

        if (!\Yii::$app->request->isDelete) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }

        if (empty($data['hotel_id'])) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'hotel_id is required'
            ];
        }

        $hotel = Hotels::findOne($data['hotel_id']);
        if (!$hotel) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Hotel not found'
            ];
        }

        // Avval bog'liq rasm yozuvlarini o‘chir
        \common\models\Images::deleteAll(['hotel_id' => $hotel->id]);

        if ($hotel->delete()) {
            return [
                'status' => 'success',
                'message' => 'Hotel deleted successfully'
            ];
        } else {
            \Yii::$app->response->statusCode = 500;
            return [
                'status' => 'error',
                'error' => 'Could not delete hotel'
            ];
        }
    }

    public function actionAddWishlist()
    {
        $data = json_decode(\Yii::$app->request->getRawBody(), true);
        if(!\Yii::$app->request->isPost){
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }

        $wishlist_hotels = new Wishlists();
        $wishlist_hotels->user_id = $data['user_id'];
        $wishlist_hotels->hotel_id = $data['hotel_id'];

        if($wishlist_hotels->save(false)){
            return [
                'status' => 'success',
                'message' => 'Add wishlist successful',
                'hotel' => $wishlist_hotels,
            ];
        }
    }

    public function actionDeleteWishlist()
    {
        $data = json_decode(\Yii::$app->request->getRawBody(), true);

        if (!\Yii::$app->request->isDelete) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }

        if (empty($data['user_id']) || empty($data['hotel_id'])) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'user_id and hotel_id are required'
            ];
        }

        $wishlist = \common\models\Wishlists::find()
            ->where(['user_id' => $data['user_id'], 'hotel_id' => $data['hotel_id']])
            ->one();

        if (!$wishlist) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Wishlist item not found'
            ];
        }

        if ($wishlist->delete()) {
            return [
                'status' => 'success',
                'message' => 'Wishlist item deleted successfully'
            ];
        } else {
            \Yii::$app->response->statusCode = 500;
            return [
                'status' => 'error',
                'error' => 'Could not delete wishlist item'
            ];
        }
    }

    public function actionGetWishlist()
    {
        $data = \Yii::$app->request->get();

        if (empty($data['user_id'])) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'message' => 'user_id is required'
            ];
        }

        $user_id = $data['user_id'];

        $wishlistItems = \common\models\Wishlists::find()
            ->where(['user_id' => $user_id])
            ->all();

        if (empty($wishlistItems)) {
            return [
                'status' => 'success',
                'message' => 'Wishlist is empty',
                'hotels' => []
            ];
        }

        $hotels = [];
        foreach ($wishlistItems as $item) {
            $hotel = \common\models\Hotels::findOne($item->hotel_id);
            if ($hotel) {
                $hotelData = $hotel->toArray();

                // Agar hotelga rasm kerak bo‘lsa, shu yerda olib qo‘shamiz:
                $hotelData['images'] = \common\models\Images::find()
                    ->where(['hotel_id' => $hotel->id])
                    ->all();

                $hotels[] = $hotelData;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Wishlist hotels fetched successfully',
            'hotels' => $hotels,
        ];
    }


}

