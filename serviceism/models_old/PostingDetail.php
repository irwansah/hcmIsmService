<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "posting".
 *
 * @property int $id_posting_detail
 * @property int $id_posting
 * @property string $file_name
 * @property string $file_type
 * @property string $file_size
 * @property string $file_content
 * @property string $duration
 * @property string $width
 * @property string $height
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class PostingDetail extends \yii\db\ActiveRecord
{
	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting_detail"];
    }

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
            [['created_at', 'updated_at'], 'safe'],
   			[['file_name','file_type'],'string'],
   			[['id_posting','duration','width','height'], 'integer'],
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
    	
        return [
            'id_posting_detail'=>"ID Posting Detail",
            'id_group'=>"ID Group",
            'file_name'=>"File Name",
            'file_type'=>"File Type",
            'file_size'=>"File Size",
            'file_content'=>"File Content",
            'created_at'=>"Created At",
            'updated_at'=>"UPdated At",
            'deleted_at'=>"Deleted At",
	    'duration'=>"Duration",
	    'width'=>"Witdh",
            'height'=>"height",
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