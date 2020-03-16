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
use serviceism\helpers\Helper;

/**
 * Employee controller
 */
class EmployeeController extends Controller
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

   
    public function actionList(){

        $request = Yii::$app->request;
        $offset = Yii::$app->request->getQueryParam('offset');
        $headers = Yii::$app->request->headers;
	    $search = Yii::$app->request->getQueryParam('search');

        $limit = 10;

        if(empty($_GET['token'])){
           Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="bad request.";
            $response['data']=[];
            return $response; 
        }

        

        // $data = Employee::find()->where(['!=','person_id',$_GET['token'] ?? ""]);

        if($search){
            $length=strlen($search);
            $word=str_word_count($search);

            if($length<=3){
                $data = Employee::find()->select(["SUBSTRING_INDEX(`nama`,' ',$word) as nama","nik","person_id","email"])
                ->where(['!=','person_id',$_GET['token'] ?? ""])
                ->andWhere(['LIKE','nama',$search.'%', false]);  
            }else{
                $data = Employee::find()->select(["nama","nik","person_id","email"])
                ->where(['!=','person_id',$_GET['token'] ?? ""])
                ->andWhere(['LIKE','nama',$search.'%', false]); 
            }
            

        }else{
            $data = Employee::find()->select(["nama","nik","person_id","email"])
                ->where(['!=','person_id',$_GET['token'] ?? ""]);
        }

        $data = $data->limit($limit)
                ->offset($offset)
                ->orderBy('nama asc')
                ->all();


        $newData = [];

        foreach ($data as $key => $value) {
            $h1['personid'] = $value->person_id;
            $h1['nik'] = $value->nik;
            $h1['nama'] = $value->nama;
	    $h1['email'] = $value->email;
            $h1['selected'] = 0;
            $newData[] = $h1;
        }

        if($data){
            $response['code']=200;
            $response['message']="data found.";
            $response['data']=$newData;
            return $response;
        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="data found.";
            $response['data']=[];
            return $response;
        }

    }

    public function actionListGroup(){

        $request = Yii::$app->request;
        $offset = Yii::$app->request->getQueryParam('offset');
        $headers = Yii::$app->request->headers;
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;
        $id_group = Yii::$app->request->getQueryParam('id_group');
        $search = Yii::$app->request->getQueryParam('search');

        if(empty($_GET['token'])){
           Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="bad request.";
            $response['data']=[];
            return $response; 
        }

        $members = [];
        $groups = [];

        $group = Group::find()->where(['=','id_group',$id_group ?? ""])->one();
        
        if(empty($group)){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Group not found.";
            $response['data']=[];
            return $response; 
        }

        if($group->member_scope==0){
            $GroupDetail = GroupDetail::find()->select(['id_member'])
                ->where(['=','id_group',$id_group])
                ->all(); 

                foreach ($GroupDetail as $key => $value) {
                    $members[]=$value->id_member;
                }
        }else{
                $members=json_decode($group->member_except);
        }
    
        
        if($search){
                
                
                if($group->member_scope==0){

                    $length=strlen($search);
                    $word=str_word_count($search);
    
                    if($length<=3){
                        $data = Employee::find()->select(["SUBSTRING_INDEX(`nama`,' ',$word) as nama","nik","person_id","email"])
                        ->where(['!=','person_id',$_GET['token'] ?? ""])
                        ->andWhere(['NOT IN','person_id',$members])
                        ->andWhere(['LIKE','nama',$search.'%', false]); 
                        
                    }else{
                        $data = Employee::find()->select(["nama","nik","person_id","email"])
                        ->where(['!=','person_id',$_GET['token'] ?? ""])
                        ->andWhere(['NOT IN','person_id',$members])
                        ->andWhere(['LIKE','nama',$search.'%', false]); 
                    }

                }else{
                    $data = Employee::find()
                    ->where(['!=','person_id',$_GET['token'] ?? ""])
                    ->andWhere(['IN','person_id',$members])
                    ->andWhere(['LIKE','nama',$search.'%', false]); 
                }
        }else{

            if($group->member_scope==0){
                
                $data = Employee::find()->select(["nama","nik","person_id","email"])
                ->where(['!=','person_id',$_GET['token'] ?? ""])
                ->andWhere(['NOT IN','person_id',$members]);

            }else{
                $data = Employee::find()
                ->where(['!=','person_id',$_GET['token'] ?? ""])
                ->andWhere(['IN','person_id',$members]);
            }
        }

        $data =  $data->limit($limit)
                ->offset($offset)
                ->orderBy('nama asc')
                ->all();

        
        $newData = [];

        foreach ($data as $key => $value) {
            $h1['personid'] = $value->person_id;
            $h1['nik'] = $value->nik;
            $h1['nama'] = $value->nama;
	        $h1['email'] = $value->email;
            $h1['isSelected'] = false;
            $newData[] = $h1;
        }

        if($data){
            $response['code']=200;
            $response['message']="data found.";
            $response['data']=$newData;
            return $response;
        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="data found.";
            $response['data']=[];
            return $response;
        }


    }
}
