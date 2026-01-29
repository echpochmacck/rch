<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "element_connection".
 *
 * @property int $id
 * @property int $course_id
 * @property int $from_element_id
 * @property int $to_element_id
 * @property string $created_at
 *
 * @property Course $course
 * @property CourseElement $fromElement
 * @property CourseElement $toElement
 */
class ElementConnection extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'element_connection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['course_id', 'from_element_id', 'to_element_id'], 'required'],
            [['course_id', 'from_element_id', 'to_element_id'], 'integer'],
            [['created_at'], 'safe'],
            [['course_id'], 'exist', 'skipOnError' => true, 'targetClass' => Course::class, 'targetAttribute' => ['course_id' => 'id']],
            [['from_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CourseElement::class, 'targetAttribute' => ['from_element_id' => 'id']],
            [['to_element_id'], 'exist', 'skipOnError' => true, 'targetClass' => CourseElement::class, 'targetAttribute' => ['to_element_id' => 'id']],
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
            'from_element_id' => 'From Element ID',
            'to_element_id' => 'To Element ID',
            'created_at' => 'Created At',
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
     * Gets query for [[FromElement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFromElement()
    {
        return $this->hasOne(CourseElement::class, ['id' => 'from_element_id']);
    }

    /**
     * Gets query for [[ToElement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getToElement()
    {
        return $this->hasOne(CourseElement::class, ['id' => 'to_element_id']);
    }

}
