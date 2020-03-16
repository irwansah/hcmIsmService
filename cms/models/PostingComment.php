<?php

namespace cms\models;

use Yii;

/**
 * This is the model class for table "posting_comment".
 *
 * @property int $id_posting_comment
 * @property int|null $id_posting
 * @property int|null $owner_id
 * @property string|null $owner_name
 * @property string|null $owner_email
 * @property string|null $comment
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class PostingComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_posting', 'owner_id'], 'integer'],
            [['comment'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['owner_name', 'owner_email'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_posting_comment' => 'Id Posting Comment',
            'id_posting' => 'Id Posting',
            'owner_id' => 'Owner ID',
            'owner_name' => 'Owner Name',
            'owner_email' => 'Owner Email',
            'comment' => 'Comment',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
