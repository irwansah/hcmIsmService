<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;
use \yii\web\Response;

/**
 * This is the model class for table "group_detail".
 *
 * @property int $id_group_detail
 * @property int $id_group
 * @property string $group_name
 * @property int $id_member
 * @property string $member_name
 * @property string $email_member
 * @property int $active
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class GroupDetail extends \yii\db\ActiveRecord
{  
	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_group_detail"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group_detail';
    }


     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['group_name','member_name','email_member'],'string'],
   			[['id_group', 'active'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_group_detail'=>"ID Group Detail",
            'id_group'=>"Id Group",
            'group_name'=>"Group Name",
            'id_member'=>"ID Member",
            'member_name'=>"Member Name",
            'email_member'=>"Member Email",
            'active'=>"Active",
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