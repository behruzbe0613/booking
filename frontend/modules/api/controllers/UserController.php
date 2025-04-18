<?php

namespace frontend\modules\api\controllers;

use common\models\Category;
use common\models\Hotels;
use common\models\User;
use yii\rest\Controller;

/**
 * Default controller for the `api` module
 */
class UserController extends Controller
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


    public function actionRegister()
    {

        $data = \Yii::$app->request->post();
        if(!\Yii::$app->request->isPost){
            return [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }

        if (empty($data['username']) || empty($data['password']) || empty($data['email']) || empty($data['phone_number'])) {
            \Yii::$app->response->statusCode = 400;
            return ['error' => 'Tepadagi barcha formalar to‘ldirilishi kerak.'];
        }

        // Agar bunday username allaqachon mavjud bo‘lsa
        if (User::find()->where(['username' => $data['username']])->exists()) {
            \Yii::$app->response->statusCode = 409;
            return ['error' => 'Bu username allaqachon mavjud.'];
        }

        $user = new User();
        $user->username = $data['username'];
        $user->password = $data['password'];
        $user->password_hash = \Yii::$app->security->generatePasswordHash($data['password']);
        $user->email = $data['email'];
        $user->phone_number = $data['phone_number'];
//        $user->auth_key = \Yii::$app->security->generateRandomString(32);
        $user->password_reset_token = \Yii::$app->security->generateRandomString();
        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save(false)) {
            return [
                'status' => 'success',
                'message' => 'Register successful',
                'user_profile' => $user,
            ];
        } else {
            \Yii::$app->response->statusCode = 500;
            return ['error' => 'Server xatosi: foydalanuvchi saqlanmadi'];
        }


    }


    public function actionLogin()
    {
        date_default_timezone_set('Asia/Tashkent');

        if(!\Yii::$app->request->isPost){
            return [
                'status' => 'error',
                'message' => 'Method not allowed'
            ];
        }

        $username = \Yii::$app->request->post('username');
        $password = \Yii::$app->request->post('password');

        if(!$username || !$password){
            return [
                'status' => 'error',
                'message' => 'Login yoki parol kiritilmagan'
            ];
        }

        $user = User::findOne(['username' => $username]);
        if(!$user || !$user->validatePassword($password)) {
            return [
                'status' => 'error',
                'message' => 'Login yoki parol xato'
            ];
        }

        $user->login_time = date("Y-m-d H:i:s");
        $user->auth_key = \Yii::$app->security->generateRandomString(32);
        $user->session_expired_time = date("Y-m-d H:i:s", strtotime('+1 hour'));

        if($user->save()) {
            return [
                'status' => 'success',
                'message' => 'Login successful',
                'user_profile' => $user,
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Login failed',
                'user_profile' => $user->errors,
            ];
        }
    }


}

