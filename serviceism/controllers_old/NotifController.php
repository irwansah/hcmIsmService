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
use serviceism\models\PostingDetail;
use serviceism\models\Notif;
use serviceism\models\Like;
use serviceism\helpers\Helper;

/**
 * Notif controller
 */
class NotifController extends Controller
{

    public function beforeAction($action){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    public function behaviors(){
        return [
            'basicAuth' => [
                'class' => \yii\filters\auth\HttpBasicAuth::className(),
                'auth' => function ($username, $password) {
                    return ApiUser::authenticateLogin($username, $password);
                },
            ],
        ];
    }
    public function actionRead(){
        $helper = new Helper;
        
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token=$_GET['token'];
        
        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $employee = Employee::find()->where(['=','person_id',$token ?? ''])
				->one();
        if(empty($employee)){
            Yii::$app->response->statusCode = 404;
                $response['code']=404;
                $response['message']="Employee not found.";
                $response['data']=[];
                return $response; 
        }

        $person_id=$employee->person_id;
        $idnotif = $raw->id_notif ?? "";

        

        $connections =  \Yii::$app->db->createCommand()
            ->update('notif', ['is_read' => 1], 'id_notif = '.$idnotif.'')
            ->execute();

        if($connections){
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$idnotif;
            return $response;
        }else{
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="notification already read.";
            $response['data']=$idnotif;
            return $response;
        }
        
    }
    
    public function actionList(){
		$helper = new Helper;
        
        $serverName=$helper->serverName();
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token=$_GET['token'] ?? "";
        $person_id=$_GET['token'] ?? "";
        $limit=$_GET['limit'] ?? "";
        $offset=$_GET['offset'] ?? "";
        if($_SERVER['REQUEST_METHOD'] !='GET'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $employee = Employee::find()->where(['=','person_id',$token ?? ''])
				->one();
        if(empty($employee)){
            Yii::$app->response->statusCode = 404;
                $response['code']=404;
                $response['message']="Employee not found.";
                $response['data']=[];
                return $response; 
        }

        
        
        $notif_list=Notif::find()
            ->where(['=','to_user',$employee->person_id])
            ->limit($limit)
            ->offset($offset)
            ->all();

        
        if($notif_list){
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$notif_list;
            return $response;
        }else{
            Yii::$app->response->statusCode = 404;
	            $response['code']=404;
	            $response['message']="Data not found.";
	            $response['data']=[];
	            return $response;
        }


		
        
    }

}
