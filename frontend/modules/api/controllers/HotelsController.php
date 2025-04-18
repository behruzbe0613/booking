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
        return parent::behaviors() + [
                [
                    'class' => \yii\filters\ContentNegotiator::className(),
                    'formats' => [
                        'application/json' => \yii\web\Response::FORMAT_JSON,
                    ],
                ],
                'bearerAuth' => [
                    'class' => \yii\filters\auth\HttpBearerAuth::className(),
                    'optional' => [
                        'login',
                        'index',
                        'create',
                        'register',
                        'get-version-apk',
                        'get-hotels',
                        'get-categories',
                    ],
                ],
            ];
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
        $images_sql  = Images::find()->all();
        return $images_sql;
        return [
            'status' => 'success',
            'message' => 'Data fetched successfully',
            'hotels' => $hotels,
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

}

