<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;
/**
 * This is the model class for table "posting".
 *
 * @property int $id_posting
 * @property int $id_group
 * @property string $group_name
 * @property int $owner_id
 * @property int $active
 * @property int $like_count
 * @property int $views_count
 * @property int $comment_count
 * @property int $mute_comment
 * @property int $type_posting
 * @property string $owner_name
 * @property string $nik
 * @property string $caption
 * @property string $url_content
 * @property string $thumnail_content
 * @property string $text
 * @property string $type_target
 * @property string $privacy
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class Posting extends \yii\db\ActiveRecord
{  
    public $additionalData;
    public $target;
    public $skill;
    public $value;
	public $comments;
	public $likes;
    public $heightDynamic;

	/**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_posting"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posting';
    }

     /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id_posting' => 'id_posting',
            'id_group' => 'id_group',
            'group_name' => 'group_name',
            'owner_id' => 'owner_id',
            'active' => 'active',
            'like_count' => 'like_count',
            'views_count' => 'views_count',
            'comment_count' => 'comment_count',
            'type_posting' => 'type_posting',
            'owner_name' => 'owner_name',
            'nik' => 'nik',
            'caption' => 'caption',
            'url_content' => 'url_content',
            'thumnail_content' => 'thumnail_content',
            'text' => 'text',
            'type_target' => 'type_target',
            'privacy' => 'privacy',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
            'additionalData' => 'additionalData',
            'target' => 'target',
            'value' => 'value',
            'skill' => 'skill',
            'comments' => 'comments',
            'likes' => 'likes',
            'nik' => 'nik',
            'heightDynamic' => 'heightDynamic',
            'mute_comment' => 'mute_comment',
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
   			[['group_name','owner_name','caption','url_content','thumnail_content','text','nik'],'string'],
   			[['id_group','owner_id','active', 'like_count','views_count','comment_count','type_posting','type_target','privacy','mute_comment'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        return [
            'id_posting'=>"ID Posting",
            'id_group'=>"ID Group",
            'active'=>"Active",
            'owner_id'=>"Owner Id",
            'like_count'=>"Like Count",
            'views_count'=>"Views Count",
            'comment_count'=>"Comment Count",
            'type_posting'=>"Type Posting",
            'group_name'=>"Group Name",
            'owner_name'=>"Owner Name",
            'nik'=>"Onwer NIK",
            'caption'=>"Caption",
            'url_content'=>"Url Content",
            'thumnail_content'=>"Thumnail Content",
            'text'=>"Text",
            'type_target'=>"type Target",
            'privacy'=>"Privacy",
            'mute_comment'=>"mute_comment",
            'created_at'=>"Created At",
            'updated_at'=>"UPdated At",
            'deleted_at'=>"Deleted At"
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