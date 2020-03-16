<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "posting_like".
 *
 * @property int $id_posting_like
 * @property int $id_posting
 * @property int $owner_id
 * @property string $owner_name
 * @property string $owner_email
 * @property string $like
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class Like extends \yii\db\ActiveRecord
{
  	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting_like"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_like';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['owner_name','owner_email'],'string'],
   			[['owner_id', 'id_posting','like'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_posting_comment'=>"ID Posting Comment",
            'id_posting'=>"ID Posting",
            'owner_id'=>"Owner Id",
            'owner_name'=>"Owner Name",
            'owner_email'=>"Owner Email",
            'like'=>"Like",
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
