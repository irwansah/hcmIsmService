<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "posting skill".
 *
 * @property int $id_posting_skill
 * @property int $id_posting
 * @property int $id_skill
 * @property string $skill_name
 * @property string $created_at
 * @property string $updated_at
 */

class PostingSkill extends \yii\db\ActiveRecord
{
	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting_skill"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting_skill';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['skill_name'],'string'],
   			[['id_posting','id_skill'], 'integer'],
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
    	
        return [
            'id_posting_skill'=>"ID Posting Skill",
            'id_posting'=>"Id Posting",
            'id_skill'=>"Id Skill",
            'skill_name'=>"Skill Name",
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