<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "course".
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $is_public
 * @property int $owner_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $structure
 *
 * @property CourseAccess[] $courseAccesses
 * @property CourseElement[] $courseElements
 * @property ElementConnection[] $elementConnections
 * @property User $owner
 * @property User[] $users
 */
class Course extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'course';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'default', 'value' => null],
            [['is_public'], 'default', 'value' => 1],
            [['title', 'owner_id'], 'required'],
            [['description'], 'string'],
            [[ 'owner_id'], 'integer'],
            ['is_public', 'boolean'],
            [['created_at', 'updated_at', 'structure'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['owner_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'is_public' => 'Is Public',
            'owner_id' => 'Owner ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[CourseAccesses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourseAccesses()
    {
        return $this->hasMany(CourseAccess::class, ['course_id' => 'id']);
    }

    /**
     * Gets query for [[CourseElements]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCourseElements()
    {
        return $this->hasMany(CourseElement::class, ['course_id' => 'id']);
    }

    /**
     * Gets query for [[ElementConnections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElementConnections()
    {
        return $this->hasMany(ElementConnection::class, ['course_id' => 'id']);
    }

    /**
     * Gets query for [[Owner]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('course_access', ['course_id' => 'id']);
    }

}
