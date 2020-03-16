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
use serviceism\helpers\AttachmentFile;

/**
 * Employee controller
 */
class GroupController extends Controller
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

   
    public function actionCreate(){

        $parse = new AttachmentFile;
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

        $info_file = $parse->getFileInfo($raw->file);

        $file_name = $info_file['file_name'] ?? "";
        $file_type = $info_file['file_type'] ?? "";

        try {

            $models = new Group;
            $models->group_name = $raw->name;
            $models->description = $raw->description;
            $models->file_name = $file_name;
            $models->file_type = $file_type;
            $models->file_content = $raw->file;
            $models->type_group_name = $helper->GroupName($raw->group_type) ?? "Undefined";
            $models->owner_name = $owner_nama;
            $models->owner_email = $owner_email;
            $models->file_size = $raw->size;
            $models->active = 1;
            $models->type_group = $raw->group_type;
            $models->owner_id = $person_id;

            if($models->save()){

                $primaryKey = $models->getPrimaryKey() ?? null;
                $arrMember = $raw->member ?? [];
                

                foreach ($arrMember as $key => $value) {
                    $modelsDetail = new GroupDetail;
                    // insert Member
                    $employeeDetail = Employee::find()->where(['=','person_id',$value])
                    ->one();
                    $modelsDetail->id_group = $primaryKey;
                    $modelsDetail->group_name = $raw->name;
                    $modelsDetail->id_member = $value;
                    $modelsDetail->member_name = $employeeDetail['nama'];
                    $modelsDetail->email_member = $employeeDetail['email'];
                    $modelsDetail->active = 1;

                    $modelsDetail->save();
                }

                //die();

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="success.";
                $response['data']=[];
                return $response;

            }else{
                Yii::$app->response->statusCode = 400;
                $response['code']=400;
                $response['message']="Bad Request.";
                $response['data']=[];
                return $response;
            }

            
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }

    }

    public function actionList(){
       
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $limit = 5;

        $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        //echo $person_id;die();

        //$dataall = [];

        $group = Group::find()->select(['id_group', 'group_name','description','active','created_at','owner_id'])
                ->where(['=','owner_id',$person_id])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->limit($limit)
                ->all();

        $datapush = [];

        //get data member
        $groudetail = GroupDetail::find()->select(['id_member','id_group','created_at','active'])
            ->where(['=','id_member',$person_id])
            ->andWhere(['=','active',1])
            ->all();

        if($groudetail){
            $joinGroup = [];
            foreach ($groudetail as $key2 => $value2) {
                $groupOfMember = Group::find()->select(['id_group', 'group_name','description','active','created_at','owner_id'])
                ->orderBy('id_group DESC')
                ->andWhere(['=','id_group',$value2->id_group])
                ->andWhere(['=','active',1])
                ->all();
                // /$joinGroup[] = $groupOfMember;
                $datapush= array_merge($group,$groupOfMember);
                
            }
        }
        
        if($datapush){

            $databaru = [];
            foreach ($datapush as $key => $value) {
                $h2['id_group'] = $value->id_group;
                $h2['group_name'] = $value->group_name;
                $h2['description'] = $value->description;
                $h2['owner_id'] = $value->owner_id;
                $databaru[]=$h2;
            }
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Data found.";
            $response['data']=$databaru;
            return $response;
        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

    }

    public function actionAllListGroup()
    {
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 200;

        $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        $group = Group::find()->select(['id_group'])
                ->where(['=','owner_id',$person_id])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->all();

         $groudetail = GroupDetail::find()->select(['id_member','id_group','created_at','active'])
            ->where(['=','id_member',$person_id])
            ->andWhere(['=','active',1])
            ->all();

        $datapush = [];

        if($groudetail){
            $joinGroup = [];
            foreach ($groudetail as $key2 => $value2) {

                $groupOfMember = Group::find()->select(['id_group'])
                ->orderBy('id_group DESC')
                ->andWhere(['=','id_group',$value2->id_group])
                ->andWhere(['=','active',1])
                ->all();

                $datapush= array_merge($group,$groupOfMember);
                
            }
        }


        if($datapush){

            $group = Group::find()->select(['id_group', 'group_name','description','active','created_at','owner_id','file_name'])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->andWhere(['id_group' => $datapush])
                ->limit($limit)
                ->offset($offset)
                ->all();

            $databaru = [];
            foreach ($group as $key => $value) {
                $h2['id_group'] = $value->id_group;
                $h2['group_name'] = $value->group_name;
                $h2['description'] = $value->description;
                $h2['owner_id'] = $value->owner_id;
                $databaru[]=$h2;
            }

            if($databaru){
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Data found.";
                $response['data']=$group;
                return $response;
 
            }else{
                Yii::$app->response->statusCode = 404;
                $response['code']=404;
                $response['message']="Data not found.";
                $response['data']=[];
                return $response;
            }

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

    }
}
