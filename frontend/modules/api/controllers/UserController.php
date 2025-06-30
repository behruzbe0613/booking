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
        'verify',
        'get-user',
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
          $sent = \Yii::$app->mailer->compose('verify', ['username' => $user->username,'key'=>$user->verification_token]) // shablon bilan
          ->setFrom(['nurmuhammad.dev13@gmail.com' => 'Admin'])
            ->setTo($user->email)
            ->setSubject('Ro‘yxatdan o‘tganingiz uchun tashakkur')
            ->send();

          if (!$sent) {
            \Yii::error('Email yuborilmadi', 'register');
          } else {
            \Yii::error('Email yuborildi', 'register');
          }

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

    public function actionVerifyEmail($token)
    {
        $user = User::find()->where(['email_verification_token' => $token])->one();

        if (!$user) {
            return ['error' => 'Noto‘g‘ri yoki eskirgan token'];
        }

        $expireTime = 60; // 24 hours
        $tokenTime = explode('_', $user->email_verification_token)[1] ?? 0;

        if (time() - (int)$tokenTime > $expireTime) {
            return ['error' => 'Tasdiqlash linki eskirgan. Qayta yuborishni so‘rang.'];
        }

        $user->is_email_verified = true;
        $user->email_verification_token = null;
        $user->save(false);

        return ['message' => 'Email muvaffaqiyatli tasdiqlandi.'];
    }

    public function actionResendVerification()
    {
        $data = json_decode(\Yii::$app->request->getRawBody(), true);
        $user = User::find()->where(['email' => $data['email']])->one();

        if (!$user) {
            return ['error' => 'Foydalanuvchi topilmadi.'];
        }

        if ($user->is_email_verified) {
            return ['message' => 'Email allaqachon tasdiqlangan.'];
        }

        $user->email_verification_token = \Yii::$app->security->generateRandomString() . '_' . time();
        $user->save(false);

        $verifyLink = \Yii::$app->urlManager->createAbsoluteUrl([
            'site/verify-email',
            'token' => $user->email_verification_token,
        ]);

        \Yii::$app->mailer->compose()
            ->setTo($user->email)
            ->setFrom([\Yii::$app->params['supportEmail'] => 'YourApp Name'])
            ->setSubject('Qayta tasdiqlash')
            ->setTextBody("Yangi tasdiqlash linki:\n\n$verifyLink")
            ->send();

        return ['message' => 'Yangi tasdiqlash linki yuborildi.'];
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

    public function actionGetUser()
    {
        date_default_timezone_set('Asia/Tashkent');

        if (!\Yii::$app->request->isGet) {
            return [
                'status' => 'error',
                'message' => 'Method not allowed',
            ];
        }

        $user_id = \Yii::$app->request->get('user_id');

        if (!$user_id) {
            return [
                'status' => 'error',
                'message' => 'user_id is required',
            ];
        }

        $user = \common\models\User::findOne($user_id);

        if (!$user) {
            return [
                'status' => 'error',
                'message' => 'User not found',
            ];
        }

        return [
            'status' => 'success',
            'profile' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'login_time' => $user->login_time,
                'session_expired_time' => $user->session_expired_time,
                'status' => $user->status,
                'created_at' => date('Y-m-d H:i:s', $user->created_at),
            ],
        ];


    }

}

