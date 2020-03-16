<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "group".
 *
 * @property int $id_group
 * @property string $group_name
 * @property string $description
 * @property string $file_name
 * @property string $file_type
 * @property int $file_size
 * @property string $file_content
 * @property int $active
 * @property int $type_group
 * @property string $type_group_name
 * @property int $owner_id
 * @property string $owner_name
 * @property string $owner_email
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class Group extends \yii\db\ActiveRecord
{  
	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_group"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'group';
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['group_name','description','file_name','file_type','type_group_name','owner_name','owner_email'],'string'],
   			[['file_size', 'active', 'type_group', 'owner_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        return [
            'id_group'=>"ID Group",
            'group_name'=>"Group Name",
            'description'=>"Description",
            'file_name'=>"File Name",
            'file_type'=>"File Type",
            'file_size'=>"File Size",
            'file_content'=>"File Content",
            'active'=>"Active",
            'type_group'=>"Group Type",
            'type_group_name'=>"Group Type Name",
            'owner_id'=>"Owner Id",
            'owner_name'=>"Owner Name",
            'owner_email'=>"Owner Email",
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