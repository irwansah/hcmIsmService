<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "posting value".
 *
 * @property int $id_posting_value
 * @property int $id_posting
 * @property int $id_value
 * @property string $value_name
 * @property string $created_at
 * @property string $updated_at
 */

class PostingValue extends \yii\db\ActiveRecord
{
	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting_value"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_value';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['value_name'],'string'],
   			[['id_posting','id_value'], 'integer'],
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
    	
        return [
            'id_posting_value'=>"ID Posting Value",
            'id_posting'=>"Id Posting",
            'id_value'=>"Id Value",
            'value_name'=>"Value name",
            'created_at'=>"Created At",
            'updated_at'=>"UPdated At"	
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