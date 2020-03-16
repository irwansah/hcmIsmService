<?php
namespace serviceism\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use serviceism\models\ApiUser;
use serviceism\models\Comment;
use serviceism\models\Employee;
use serviceism\models\Posting;
use serviceism\helpers\Helper;

/**
 * Comment controller
 */
class CommentController extends Controller
{

	public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return [
            'basicAuth' => [
                'class' => \yii\filters\auth\HttpBasicAuth::className(),
                'auth' => function ($username, $password) {
                    return ApiUser::authenticateLogin($username, $password);
                },
            ],
        ];
    }

    public function actionAdd()
    {
    	$helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

        $person_id = $employee['person_id'] ?? null;
        $owner_nama = $employee['nama'] ?? "";
        $owner_email = $employee['email'] ?? "";

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $idPosting = $raw->idPosting ?? "";
        $comment = $raw->comment ?? "";

        $models = new Comment;

        $models->id_posting = $idPosting;
        $models->owner_id = $person_id ;
        $models->owner_email = $owner_email;
        $models->owner_name = $owner_nama;
        $models->comment = $comment;

        $Posting = Posting::find()->where(['=','id_posting',$idPosting])->one();

        $countNew = intval($Posting->comment_count + 1);

        \Yii::$app->db->createCommand()
	    ->update('posting', ['comment_count' => $countNew], 'id_posting = '.$idPosting.'')
	    ->execute();


        //print_r($countNew);die();

        try {

        	if($models->save()){
        		$res = [];
        		$primaryKey = $models->getPrimaryKey() ?? null;

        		$res['id_comments'] = $primaryKey;
        		$res['owner_id'] = $person_id;
        		$res['owner_email'] = $owner_email;
        		$res['owner_name'] = $owner_nama;
        		$res['comment'] = $comment;

        		Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$res;
	            return $response;

        	}else{
        		Yii::$app->response->statusCode = 400;
	            $response['code']=400;
	            $response['message']="Bad request, please check your parameters.";
	            $response['data']=[];
	            return $response;
        	}

        	
        } catch (Exception $e) {
        	Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad request, please check your parameters.";
            $response['data']=[];
            return $response;
        }

        print_r($raw);die();
    }

}