<?php

namespace cms\models;

use Yii;

/**
 * This is the model class for table "posting_link".
 *
 * @property int $id_posting_link
 * @property int|null $id_posting
 * @property string|null $url
 * @property string|null $title
 * @property string|null $sitename
 * @property string|null $media_type
 * @property string|null $images
 * @property string|null $favicons
 * @property string|null $description
 * @property string|null $content_type
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class PostingLink extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_link';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_posting'], 'integer'],
            [['images', 'description'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['url', 'title', 'sitename', 'media_type', 'favicons', 'content_type'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_posting_link' => 'Id Posting Link',
            'id_posting' => 'Id Posting',
            'url' => 'Url',
            'title' => 'Title',
            'sitename' => 'Sitename',
            'media_type' => 'Media Type',
            'images' => 'Images',
            'favicons' => 'Favicons',
            'description' => 'Description',
            'content_type' => 'Content Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }
}
