<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "recognation_skill".
 *
 * @property int $value_id
 * @property int $active
 * @property string $value_name 
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */

class ValueRec extends \yii\db\ActiveRecord
{
  	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["value_id"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recognation_value';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['value_name','description'],'string'],
   			[['active'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'value_id'=>"ID Skill",
            'value_name'=>"Skill Name",
            'description'=>"Description",
            'active'=>"Active",
            'created_at'=>"Created At",
            'updated_at'=>"UPdated At",
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

