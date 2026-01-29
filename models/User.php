<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property int $role_id
 * @property string $created_at
 * @property string $token
 * 
 *
 * @property CourseAccess[] $courseAccesses
 * @property Course[] $courses
 * @property Course[] $courses0
 * @property CourseElement[] $elements
 * @property Role $role
 * @property UserElementProgress[] $userElementProgresses
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const SCENARIO_REGISTER = 'register';

    public $password_repeat;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['password_repeat', 'required', 'on' => self::SCENARIO_REGISTER],
            [['role_id'], 'integer'],
            ['email', 'email'],
            ['email', 'unique', 'on' => self::SCENARIO_REGISTER],
            ['password', 'string', 'min' => 8],
            [
                'password',
                'match',
                'pattern' => '/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                'message' => 'Пароль должен содержать буквы и цифры'
            ],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
            [['created_at', 'token'], 'safe'],
            [['email', 'password'], 'string', 'max' => 255],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'password' => 'Password',
            'role_id' => 'Role ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[CourseAccesses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourseAccesses()
    {
        return $this->hasMany(CourseAccess::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Courses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourses()
    {
        return $this->hasMany(Course::class, ['owner_id' => 'id']);
    }

    /**
     * Gets query for [[Courses0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourses0()
    {
        return $this->hasMany(Course::class, ['id' => 'course_id'])->viaTable('course_access', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Elements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElements()
    {
        return $this->hasMany(CourseElement::class, ['id' => 'element_id'])->viaTable('user_element_progress', ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for [[UserElementProgresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserElementProgresses()
    {
        return $this->hasMany(UserElementProgress::class, ['user_id' => 'id']);
    }
        public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        // return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        // return $this->authKey === $authKey;
    }
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
