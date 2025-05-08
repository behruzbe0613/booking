<?php

namespace frontend\modules\api\controllers;

use common\models\Category;
use common\models\Hotels;
use common\models\Images;
use common\models\User;
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
           'create-hotels',
           'delete-hotels',
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
            return [
                'status' => 'error',
                'message' => 'Method not allowed'
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
            return [
                'status' => 'error',
                'message' => 'Method not allowed'
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
            return [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }

//        if (empty($data['username']) || empty($data['password']) || empty($data['email']) || empty($data['phone_number'])) {
//            \Yii::$app->response->statusCode = 400;
//            return ['error' => 'Tepadagi barcha formalar to‘ldirilishi kerak.'];
//        }


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
            return [
                'status' => 'success',
                'message' => 'Add new thing successful',
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
            return [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }

        if (empty($data['hotel_id'])) {
            return [
                'status' => 'error',
                'message' => 'hotel_id is required'
            ];
        }

        $hotel = Hotels::findOne($data['hotel_id']);
        if (!$hotel) {
            return [
                'status' => 'error',
                'message' => 'Hotel not found'
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
                'message' => 'Could not delete hotel'
            ];
        }
    }
}

