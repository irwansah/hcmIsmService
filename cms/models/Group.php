<?php

namespace cms\models;

use Yii;
use yii\db\Query;



/**
 * This is the model class for table "group".
 *
 * @property int $id_group
 * @property string|null $group_name
 * @property string|null $description
 * @property string|null $file_name
 * @property string|null $file_type
 * @property string|null $file_size
 * @property resource|null $file_content
 * @property int|null $active 0:not active, 1: Active
 * @property int|null $type_group 0:general, 1:Close, 2:Private
 * @property string|null $type_group_name
 * @property int|null $owner_id
 * @property string|null $owner_name
 * @property string|null $owner_email
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $deleted_at
 */
class Group extends \yii\db\ActiveRecord
{
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
            [['description', 'file_content'], 'string'],
            [['active', 'type_group', 'owner_id'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['group_name', 'file_name', 'file_type', 'file_size', 'type_group_name', 'owner_name', 'owner_email'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id_group' => 'Id Group',
            'group_name' => 'Group Name',
            'description' => 'Description',
            'file_name' => 'File Name',
            'file_type' => 'File Type',
            'file_size' => 'File Size',
            'file_content' => 'File Content',
            'active' => 'Active',
            'type_group' => 'Type Group',
            'type_group_name' => 'Type Group Name',
            'owner_id' => 'Owner ID',
            'owner_name' => 'Owner Name',
            'owner_email' => 'Owner Email',
            'member_scope' => 'Member Scope',
            'member_except' => 'Member Except',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    public function getmemberGroup(){
        $query = new Query;
        $query
            ->select('group.id_group,owner_id, owner_name, group.group_name,COUNT(group_detail.id_group) AS count_member,member_scope,group.created_at')
            ->from('group')
            ->leftjoin('group_detail','group.id_group = group_detail.id_group')
            ->groupBy('group.id_group');
        $command = $query->createCommand();
        $model = $command->queryAll();
        
        
        return $model;
                
    }
    

    public function getEmployee()
	{
		return $this->hasOne(Employee::className(), ['person_id' => 'owner_id']);
    }
    
}
