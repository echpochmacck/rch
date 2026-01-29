<?php

namespace app\controllers;

use app\models\Role;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use Yii;


class UserController extends \yii\rest\ActiveController
{
    public $modelClass = '';
    public $enableCsrfValidation = '';
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [
                'Origin' => [isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : 'http ://' . $_SERVER['REMOTE_ADDR']],
                // 'Origin' => ["*"], 
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
            'actions' => [
                'logout' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ];

        $auth = [
            'class' => HttpBearerAuth::class,
            'only' => ['logout'],
        ];
        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options'];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create'], $actions['view']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionRegister()
    {
        $model = new User();
        $model->scenario = 'register';
        $model->load(Yii::$app->request->post(), '');
        if ($model->validate()) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            $model->role_id = ROle::getRoleId('user');
            $model->save(false);
            Yii::$app->response->statusCode = 201;
            return $this->asJson([
                'message' => 'user created',
                'code' => 201
            ]);
        } else {
            Yii::$app->response->statusCode = 422;
            return $this->asJson([
                'data' => [
                    'errors' => $model->errors
                ],
                'code' => 422
            ]);
        }
    }

    public function actionLogin()
    {
        $model = new User();
        $model->load(Yii::$app->request->post(), '');
        if ($model->validate()) {
            $user = User::findOne(['email' => $model->email]);
            if ($user && $user->validatePassword($model->password)) {
                Yii::$app->response->statusCode = 200;
                $user->token = Yii::$app->security->generateRandomString();
                $user->save(false);
                return $this->asJson([
                    'data' => [
                        'token' => $user->token
                    ],
                    'code' => 200
                ]);
            } else {
                // 
                Yii::$app->response->statusCode = 401;
                return '';
            }
        } else {
            Yii::$app->response->statusCode = 422;
            return $this->asJson([
                'data' => [
                    'errors' => $model->errors
                ],
                'code' => 422
            ]);
        }
    }
}
