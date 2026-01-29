<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course_access".
 *
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property string $access_type
 *
 * @property Course $course
 * @property User $user
 */
class CourseAccess extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ACCESS_TYPE_VIEWER = 'viewer';
    const ACCESS_TYPE_EDITOR = 'editor';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['access_type'], 'default', 'value' => 'viewer'],
            [['course_id', 'user_id'], 'required'],
            [['course_id', 'user_id'], 'integer'],
            [['access_type'], 'string'],
            ['access_type', 'in', 'range' => array_keys(self::optsAccessType())],
            [['course_id', 'user_id'], 'unique', 'targetAttribute' => ['course_id', 'user_id']],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Course::class, 'targetAttribute' => ['course_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'course_id' => 'Course ID',
            'user_id' => 'User ID',
            'access_type' => 'Access Type',
        ];
    }

    /**
     * Gets query for [[Course]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    /**
     * column access_type ENUM value labels
     * @return string[]
     */
    public static function optsAccessType()
    {
        return [
            self::ACCESS_TYPE_VIEWER => 'viewer',
            self::ACCESS_TYPE_EDITOR => 'editor',
        ];
    }

    /**
     * @return string
     */
    public function displayAccessType()
    {
        return self::optsAccessType()[$this->access_type];
    }

    /**
     * @return bool
     */
    public function isAccessTypeViewer()
    {
        return $this->access_type === self::ACCESS_TYPE_VIEWER;
    }

    public function setAccessTypeToViewer()
    {
        $this->access_type = self::ACCESS_TYPE_VIEWER;
    }

    /**
     * @return bool
     */
    public function isAccessTypeEditor()
    {
        return $this->access_type === self::ACCESS_TYPE_EDITOR;
    }

    public function setAccessTypeToEditor()
    {
        $this->access_type = self::ACCESS_TYPE_EDITOR;
    }
}
