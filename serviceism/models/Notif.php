<?php

namespace serviceism\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "models".
 *
 * @property int $id_notif
 * @property int $id_posting
 * @property string $to_nik
 * @property string $from_nik
 * @property string $to_user
 * @property string $from_user
 * @property string $to_email
 * @property string $from_email
 * @property string $to_nama
 * @property string $from_nama
 * @property string $text
 * @property int $type
 * @property int $is_read
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */

class Notif extends \yii\db\ActiveRecord
{  


    
    /**
     * @inheritdoc$primaryKey
     */
    public static function primaryKey()
    {
        return ["id_notif"];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notif';
    }

     /**
     * {@inheritdoc}
     */
    public function fields()
    {
        return [
            'id_notif' => 'id_notif',
            'id_posting' => 'id_posting',
            'to_nik' => 'to_nik',
            'from_nik' => 'from_nik',
            'to_user' => 'to_user',
            'from_user' => 'from_user',
            'to_email' => 'to_email',
            'from_email' => 'from_email',
            'to_nama' => 'to_nama',
            'from_nama' => 'from_nama',
            'text' => 'text',
            'type' => 'type',
            'is_read' => 'is_read',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
            
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_posting', 'to_nik', 'from_nik', 'to_user', 'from_user', 'to_nama', 'from_nama', 'to_email', 'from_email', 'text', 'type'], 'required'],
            [['id_posting', 'type', 'is_read'], 'integer'],
            [['text'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['to_nik', 'from_nik', 'to_user', 'from_user'], 'string', 'max' => 32],
            [['to_nama', 'from_nama', 'to_email', 'from_email'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        return [
            'id_notif'=>"ID Notif",
            'id_posting'=>"ID Posting",
            'to_nik'=>"To NIK",
            'from_nik'=>"From NIK",
            'to_user'=>"To User",
            'from_user'=>"From User",
            'to_email'=>"To Email",
            'from_email'=>"From Email",
            'to_nama'=>"To nama",
            'from_nama'=>"From nama",
            'text'=>"Text",
            'type'=>"Type Notif",
            'is_read'=>"Read Notif",
            'created_at'=>"Created At",
            'updated_at'=>"Updated At",
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