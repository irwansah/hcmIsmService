<?php

namespace cms\models;

use Yii;

/**
 * This is the model class for table "posting_detail".
 *
 * @property int $id_posting_detail
 * @property int|null $id_posting
 * @property string|null $file_name
 * @property string|null $file_type
 * @property string|null $file_size
 * @property resource|null $file_content
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class PostingDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_posting'], 'integer'],
            [['file_content'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['file_name', 'file_type', 'file_size'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_posting_detail' => 'Id Posting Detail',
            'id_posting' => 'Id Posting',
            'file_name' => 'File Name',
            'file_type' => 'File Type',
            'file_size' => 'File Size',
            'file_content' => 'File Content',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
