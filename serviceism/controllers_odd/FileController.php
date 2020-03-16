<?php
namespace serviceism\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use serviceism\models\ApiUser;
use serviceism\models\Employee;
use serviceism\models\Group;
use serviceism\models\GroupDetail;
use serviceism\models\PostingDetail;
use serviceism\helpers\Helper;
use serviceism\helpers\AttachmentFile;

/**
 * File controller
 */
class FileController extends Controller
{

	public function actionGet()
	{
		$request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());

        $group = Group::find()->where(['=','id_group',$raw->group_id])
                ->one();

        if($group){

        	 header('Content-Type:'.$group['file_type']); 
        	 echo base64_decode($group['file_content']);
        	 exit();

        }else{
        	throw new \yii\web\NotFoundHttpException;
        }

       
	}

    public function actionGetPostingFile()
    {
        $id = $_GET['id'] ?? "";

        $posting = PostingDetail::find()->where(['=','id_posting_detail',$id])
                ->one();

        if($posting){

             header('Content-Type:'.$posting['file_type']); 
             echo base64_decode($posting['file_content']);
             exit();

        }else{
            throw new \yii\web\NotFoundHttpException;
        }


    } 

    public function actionGroup()
    {
        $id = $_GET['id'] ?? "";

        $posting = Group::find()->where(['=','id_group',$id])
                ->one();

        if($posting){

             header('Content-Type:'.$posting['file_type']); 
             echo base64_decode($posting['file_content']);
             exit();

        }else{
            throw new \yii\web\NotFoundHttpException;
        }


    }
}