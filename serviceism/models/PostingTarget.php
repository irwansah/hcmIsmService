<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "posting target".
 *
 * @property int $id_posting_target
 * @property int $id_posting
 * @property int $id_target
 * @property string $nik
 * @property string $created_at
 * @property string $updated_at
 */

class PostingTarget extends \yii\db\ActiveRecord
{
	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting_target"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_target';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['nik'],'string'],
   			[['id_posting','id_target'], 'integer'],
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
    	
        return [
            'id_posting_target'=>"ID Posting Target",
            'id_posting'=>"Id Posting",
            'id_target'=>"Id Target",
            'nik'=>"Nik",
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