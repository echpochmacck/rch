<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_element_progress".
 *
 * @property int $id
 * @property int $user_id
 * @property int $element_id
 * @property int $is_viewed
 * @property string|null $viewed_at
 *
 * @property CourseElement $element
 * @property User $user
 */
class UserElementProgress extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_element_progress';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['viewed_at'], 'default', 'value' => null],
            [['is_viewed'], 'default', 'value' => 0],
            [['user_id', 'element_id'], 'required'],
            [['user_id', 'element_id', 'is_viewed'], 'integer'],
            [['viewed_at'], 'safe'],
            [['user_id', 'element_id'], 'unique', 'targetAttribute' => ['user_id', 'element_id']],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CourseElement::class, 'targetAttribute' => ['element_id' => 'id']],
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
            'user_id' => 'User ID',
            'element_id' => 'Element ID',
            'is_viewed' => 'Is Viewed',
            'viewed_at' => 'Viewed At',
        ];
    }

    /**
     * Gets query for [[Element]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getElement()
    {
        return $this->hasOne(CourseElement::class, ['id' => 'element_id']);
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

}
