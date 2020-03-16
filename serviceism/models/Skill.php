<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "recognation_skill".
 *
 * @property int $skill_id
 * @property string $skill_name 
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 */

class Skill extends \yii\db\ActiveRecord
{
  	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["skill_id"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recognation_skill';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['skill_name','description'],'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'skill_id'=>"ID Skill",
            'skill_name'=>"Skill Name",
            'description'=>"Description",
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

