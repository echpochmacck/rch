<?php

namespace app\controllers;

use app\models\Course;
use app\models\CourseElement;
use app\models\Role;
use app\models\User;
use PHPUnit\Framework\Constraint\Count;
use yii\filters\auth\HttpBearerAuth;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\UploadedFile;

class CourseController extends \yii\rest\ActiveController
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
            'only' => ['*'],
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

    public function actionGetAllCourses()
    {
        Yii::$app->response->statusCode = 200;
        return $this->asJson([
            'data' => Course::find()
                ->select(['*'])
                ->where(['is_public' => 1])
                ->asArray()
                ->all(),
            'code' => 200
        ]);
    }

    public function actionCreateCourse()
    {
        $model = new Course();
        $model->load(Yii::$app->request->post(), '');
        $model->owner_id = Yii::$app->user->id;
        if ($model->validate()) {

            $model->save(false);
            Yii::$app->response->statusCode = 201;
            return $this->asJson([
                'message' => 'Course created',
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
    public function actionUpdateCourse($id)
    {
        $model = Course::findOne($id);

        if (!$model) {
            Yii::$app->response->statusCode = 404;
           return '';
           
        }

        if ($model->owner_id !== Yii::$app->user->id) {
            Yii::$app->response->statusCode = 403;
           return '';
        }

        $model->load(Yii::$app->request->post(), '');

        if ($model->validate()) {
            $model->save(false);

            Yii::$app->response->statusCode = 200;
            return $this->asJson([
                'message' => 'Course updated',
                'code' => 200
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

    // public function actionGetCourses($page)
    // {
    //     $array = Course::find()
    //             ->select(['*'])
    //             ->asArray()
    //             ->all();
    //     $dataProvider = new ArrayDataProvider([
    //         'all_models' => $array,
    //         'paginaion' => [
    //             'page'=> $page,
    //             'pageSize' => 3
    //         ]
    //     ]);
    //     return $this->asJson([
    //         'data' => [
    //             'courses' => $dataProvider->getModels(),
    //             'page' => $page,
    //             'total_count' => $dataProvider->getTotalCount()
    //         ],
    //         'code' => 200
    //     ]);

    // }

    public function actionGetCourse($id)
    {
        $course = Course::findOne($id);
        if ($course) {
            return $this->asJson([
                'data' => [
                    'course' => [
                        'id' => $course->id,
                        'title' => $course->title,
                        'description' => $course->description,
                        'structure' => $course->structure,
                    ],
                    'coursesElement' => CourseElement::find()
                        ->select(['*'])
                        ->where(['course_id' => $id])
                        ->asArray()
                        ->all()
                ]
            ]);
        } else {
            Yii::$app->response->statusCode = 404;
            return '';
        }
    }

    public function actionDeleteCourse($id)
    {
        $course = Course::findOne($id);
        if ($course) {
            $course->delete();
            Yii::$app->response->statusCode = 204;
            return '';
        } else {
            Yii::$app->response->statusCode = 404;
            return '';
        }
    }
    //при запросе отденльные поля а при ответе// 
    public function actionCreateElement($id)
    {
        $course = Course::findOne($id);
        if ($course) {
            $model = new CourseElement();
            $model->load(Yii::$app->request->post(), '');
            $model->course_id = $course->id;
            $model->file = UploadedFile::getInstanceByName('file');
            if ($model->validate()) {
                $model->file_url = Yii::$app->security->generateRandomString() . "." . $model->file->extension;
                $model->file->saveAs('uploads/' . $model->file_url);
                $model->save(false);
                Yii::$app->response->statusCode = 201;
                return $this->asJson([
                    'data' => [
                        // id
                        'course_element' => [
                            'structure' => $model->structure,
                            'file_url' => Yii::$app->request->hostInfo . "/uploads/" . $model->file_url,
                        ]
                    ]
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
        } else {
            Yii::$app->response->statusCode = 404;
            return '';
        }
    }
}
