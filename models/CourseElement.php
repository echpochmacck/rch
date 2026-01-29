<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course_element".
 *
 * @property int $id
 * @property int $course_id
 * @property string|null $content_url
 * @property string|null $file_url
 * @property string $structure
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Course $course
 * @property ElementConnection[] $elementConnections
 * @property ElementConnection[] $elementConnections0
 * @property UserElementProgress[] $userElementProgresses
 * @property User[] $users
 */
class CourseElement extends \yii\db\ActiveRecord
{


    public $file;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course_element';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [  
            [['content_url', 'file_url'], 'default', 'value' => null],
            [['course_id',  'structure'], 'required'],
            [['course_id'], 'integer'],
            [['content_url', 'file_url'], 'string'],
            ['file', 'file', 'skipOnEmpty' => true ],
            [['structure', 'created_at', 'updated_at'], 'safe'],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Course::class, 'targetAttribute' => ['course_id' => 'id']],
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
            'title' => 'Title',
            'content_url' => 'Content Url',
            'file_url' => 'File Url',
            'comment' => 'Comment',
            'structure' => 'Structure',
            'is_hidden' => 'Is Hidden',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
     * Gets query for [[ElementConnections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementConnections()
    {
        return $this->hasMany(ElementConnection::class, ['from_element_id' => 'id']);
    }

    /**
     * Gets query for [[ElementConnections0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementConnections0()
    {
        return $this->hasMany(ElementConnection::class, ['to_element_id' => 'id']);
    }

    /**
     * Gets query for [[UserElementProgresses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserElementProgresses()
    {
        return $this->hasMany(UserElementProgress::class, ['element_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('user_element_progress', ['element_id' => 'id']);
    }
}
