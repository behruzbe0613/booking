<?php

namespace frontend\modules\api\controllers;

use common\models\Category;
use common\models\Hotels;
use common\models\User;
use yii\swiftmailer\Mailer;
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
        'verify'
      ],
    ];

    return $behaviors;
  }



  public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }


    public function actionRegister()
    {

      $data = json_decode(\Yii::$app->request->getRawBody(), true);
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
        $user->verification_token = \Yii::$app->security->generateRandomString(32);
        $user->password_reset_token = \Yii::$app->security->generateRandomString();
        $user->created_at = time();
        $user->updated_at = time();
        $user->status = 9;

        if ($user->save(false)) {
          // Email yuborish
          \Yii::$app->mailer->compose('verify', ['username' => $user->username,'key'=>$user->verification_token]) // shablon bilan
//          \Yii::$app->mailer->compose()
          ->setFrom(['nurmuhammad.dev13@gmail.com' => 'Admin'])
            ->setTo($user->email)
            ->setSubject('Ro‘yxatdan o‘tganingiz uchun tashakkur')
//            ->setTextBody("Salom {$user->username}, tizimga ro‘yxatdan o‘tdingiz!")
            ->send();

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
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Method not allowed'
            ];
        }
      $data = json_decode(\Yii::$app->request->getRawBody(), true);

        if(empty($data['username']) || empty($data['password'])){
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'empty',
                'error' => 'Login yoki parol kiritilmagan'
            ];
        }

        $user = User::findOne(['username' => $data['username']]);
        if(!$user || !$user->validatePassword($data['password'])) {
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'notfound',
                'error' => 'Login yoki parol xato'
            ];
        }

        if($user->status != 10){
          \Yii::$app->response->statusCode = 400;
          return [
            'status' => 'unverifyed',
            'error' => 'Email tasdiqlanmagan'
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
          \Yii::$app->response->statusCode = 400;
            return [
                'status' => 'error',
                'error' => 'Login failed',
                'user_profile' => $user->errors,
            ];
        }
    }

  public function actionVerify()
  {
    date_default_timezone_set('Asia/Tashkent');

    if(!\Yii::$app->request->isPost){
      \Yii::$app->response->statusCode = 400;
      return [
        'status' => 'error',
        'error' => 'Method not allowed'
      ];
    }

    $verification_token = json_decode(\Yii::$app->request->getRawBody(), true);

    if(!$verification_token){
      return [
        'status' => 'error',
        'message' => 'Auth key kiritilmagan'
      ];
    }
    \Yii::error('Token: ' . $verification_token['verification_token'], 'verify');
    $user = User::findOne(['verification_token' => $verification_token['verification_token']]);
    if(!$user) {
      \Yii::$app->response->statusCode = 400;
      return [
        'status' => 'error',
        'error' => 'User topilmadi.'
      ];
    }

    $user->status = 10;

    if($user->save(false)) {
      return [
        'status' => 'success',
        'message' => 'Email tastiqlandi'
      ];
    } else {
      \Yii::$app->response->statusCode = 400;
      return [
        'status' => 'error',
        'error' => 'Email tastiqlanmadi',
        'user_profile' => $user->errors
      ];
    }
  }

}

