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
        $user->password_reset_token = \Yii::$app->security->generateRandomString();
        $user->created_at = time();
        $user->updated_at = time();
        $user->email_verification_token = \Yii::$app->security->generateRandomString() . '_' . time();
        $user->is_email_verified = false;

        if ($user->save(false)) {
            $verifyLink = \Yii::$app->urlManager->createAbsoluteUrl([
                'site/verify-email',
                'token' => $user->email_verification_token,
            ]);

            \Yii::$app->mailer->compose()
                ->setTo($user->email)
                ->setFrom([\Yii::$app->params['adminEmail'] => 'Upen Community'])
                ->setSubject('Email Verification')
                ->setTextBody("Please click the link below to verify your email:\n\n$verifyLink")
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

