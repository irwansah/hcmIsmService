<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "posting_link".
 *
 * @property int $id_posting_link
 * @property int $id_posting
 * @property string $url
 * @property string $title
 * @property string $sitename
 * @property string $media_type
 * @property string $images
 * @property string $favicons
 * @property string $description
 * @property string $content_type
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class PostingLink extends \yii\db\ActiveRecord
{

	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting_link"];
    }

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
            [['created_at', 'updated_at'], 'safe'],
   			[['url','title','sitename','media_type','images','favicons','description','content_type'],'string'],
   			[['id_posting'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_posting_link'=>"ID Posting Link",
            'id_posting'=>"ID Posting",
            'url'=>"URL",
            'title'=>"Title",
            'sitename'=>"Sitename",
            'media_type'=>"Media Type",
            'images'=>"Images",
            'favicons'=>"Favicons",
            'description'=>"Description",
            'content_type'=>"Content Type",
            'created_at'=>"Created At",
            'updated_at'=>"UPdated At",
            'deleted_at'=>"Deleted At",
        ];
    }



    public function beforeSave($insert)
	{
		if($this->isNewRecord){
            $this->created_at = date("Y-m-d H:i:s");
            $this->updated_at = date("Y-m-d H:i:s");
		}
        $this->updated_at = date("Y-m-d H:i:s");
		return parent::beforeSave($insert);
	}

}
