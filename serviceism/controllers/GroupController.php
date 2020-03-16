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
use serviceism\models\Favorite;
use serviceism\models\GroupDetail;
use serviceism\helpers\Helper;
use serviceism\helpers\AttachmentFile;

/**
 * Employee controller
 */
class GroupController extends Controller
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

    public function actionCreate(){
    
        $parse = new AttachmentFile;
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token = Yii::$app->request->getQueryParam('token');
    
        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();
        
        if(empty($employee)){
            Yii::$app->response->statusCode = 404;
                    $response['code']=404;
                    $response['message']="Employe not found.";
                    $response['data']=[];
                    return $response;
        }

        $person_id = $employee['person_id'] ?? null;
        $check_gorup=Group::find()
            ->where(['=','owner_id',$person_id ?? ""])
            ->andWhere(['LIKE','group_name','%'.$raw->name.'', false])
            ->count();
            
        if($check_gorup>0){
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Group already exist. try different group name";
            $response['data']=$check_gorup;
            return $response;
            
        }
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
            $models->member_scope = $raw->member_scope;
            $models->member_except = null;
            $models->mute_posting=$raw->mute_posting ?? 0;
            $models->owner_email = $owner_email;
            $models->file_size = $raw->size;
            $models->active = 1;
            $models->type_group = $raw->group_type;
            $models->owner_id = $person_id;


            if($models->save()){
                if($raw->member_scope==0){    
                    $primaryKey = $models->getPrimaryKey() ?? null;
                    $arrMember = $raw->member ?? [];
                    

                    foreach ($arrMember as $key => $value) {
                        if($value->isSelect==true){
                        $modelsDetail = new GroupDetail;
                        // insert Member 
                        $employeeDetail = Employee::find()->where(['=','person_id',$value->personid])
                        ->one();
                        $modelsDetail->id_group = $primaryKey;
                        $modelsDetail->group_name = $raw->name;
                        $modelsDetail->id_member = $value->personid;
                        $modelsDetail->member_name = $employeeDetail['nama'];
                        $modelsDetail->email_member = $employeeDetail['email'];
                        $modelsDetail->active = 1;

                        $modelsDetail->save();
                        }
                        
                    }
                
                    Yii::$app->response->statusCode = 200;
                    $response['code']=200;
                    $response['message']="success.";
                    $response['data']=[];
                    return $response;
                }else{
                    Yii::$app->response->statusCode = 200;
                    $response['code']=200;
                    $response['message']="success.";
                    $response['data']=[];
                    return $response;
                }


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

    public function actionMutePosting(){
    
        $parse = new AttachmentFile;
        $helper = new Helper;

        
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token = Yii::$app->request->getQueryParam('token');
        
        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();
        
        if(empty($employee)){
            Yii::$app->response->statusCode = 404;
                    $response['code']=404;
                    $response['message']="Employe not found.";
                    $response['data']=[];
                    return $response;
        }

        $person_id = $employee['person_id'] ?? null;
        $check=Group::find()
            ->where(['=','owner_id',$person_id ?? ""])
            ->andWhere(['=','id_group',$raw->id_group])
            ->one();

        if($raw->mute_posting==1){
            if($check['mute_posting']!=$raw->mute_posting){
                \Yii::$app->db->createCommand()
                ->update('group', ['mute_posting' => 1], 'id_group = '.$raw->id_group.'')
                ->execute();
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Post muted";
                $response['data']=[];
                return $response;    
            }else{
                Yii::$app->response->statusCode = 400;
                $response['code']=400;
                $response['message']="Post has been muted";
                $response['data']=[];
                return $response;    
            }
            
        }else{
            if($check['mute_posting']!=$raw->mute_posting){
                \Yii::$app->db->createCommand()
                ->update('group', ['mute_posting' => 0], 'id_group = '.$raw->id_group.'')
                ->execute();
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Post opened";
                $response['data']=[];
                return $response; 
            }else{
                Yii::$app->response->statusCode = 400;
                $response['code']=400;
                $response['message']="Post already opened";
                $response['data']=[];
                return $response;    
            }
        }


    }

    public function actionChangeOwner(){        
        $parse = new AttachmentFile;
        $helper = new Helper;

        
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token = Yii::$app->request->getQueryParam('token');

        
                
        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $employee_old = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();
                
        if(empty($employee_old)){
            Yii::$app->response->statusCode = 404;
                    $response['code']=404;
                    $response['message']="Employe not found.";
                    $response['data']=[];
                    return $response;
        }

        $owner_id_old = $employee_old['person_id'] ?? null;
        $owner_name_old = $employee_old['nama'] ?? null;
        $owner_email_old = $employee_old['email'] ?? null;

        $in_detail=GroupDetail::find()
                ->where(['=','id_group',$raw->id_group ?? ""])
                ->andWhere(['=','id_member',$raw->person_id ?? ""])
                ->one();

        $owner_id_new=$in_detail['id_member'];
        $owner_name_new=$in_detail['member_name'];
        $owner_email_new=$in_detail['email_member'];

        $column_group=[
            "owner_id"=>$owner_id_new,
            "owner_name"=>$owner_name_new,
            "owner_email"=>$owner_email_new,

        ];
        $column_group_detail=[
            "id_member"=>$owner_id_old,
            "member_name"=>$owner_name_old,
            "email_member"=>$owner_email_old,
        ];

        $update_group =  \Yii::$app->db->createCommand()
            ->update("group", $column_group, "id_group = '".$raw->id_group."' and owner_id='".$owner_id_old."'")
            ->execute();

        if($update_group){
            $update_detail =  \Yii::$app->db->createCommand()
            ->update("group_detail", $column_group_detail, "id_group = '".$raw->id_group."' and id_member ='".$raw->person_id."'")
            ->execute();
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=[];
            return $response;
        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Failed Changes.";
            $response['data']=[];
            return $response;
        }
      

    }

    public function actionAddFavorite(){
    }

    public function actionListFavorite(){
    }
   
    public function actionCreateOdd(){
    
        $parse = new AttachmentFile;
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token = Yii::$app->request->getQueryParam('token');
    
        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();
        
        if(empty($employee)){
            Yii::$app->response->statusCode = 404;
                    $response['code']=404;
                    $response['message']="Employe not found.";
                    $response['data']=[];
                    return $response;
        }

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
            $models->member_scope = $raw->member_scope;
            $models->member_except = null;
            $models->owner_email = $owner_email;
            $models->file_size = $raw->size;
            $models->active = 1;
            $models->type_group = $raw->group_type;
            $models->owner_id = $person_id;


            if($models->save()){
                if($raw->member_scope==0){    
                    $primaryKey = $models->getPrimaryKey() ?? null;
                    $arrMember = $raw->member ?? [];
                    

                    foreach ($arrMember as $key => $value) {
                        if($value->isSelect==true){
                        $modelsDetail = new GroupDetail;
                        // insert Member 
                        $employeeDetail = Employee::find()->where(['=','person_id',$value->personid])
                        ->one();
                        $modelsDetail->id_group = $primaryKey;
                        $modelsDetail->group_name = $raw->name;
                        $modelsDetail->id_member = $value->personid;
                        $modelsDetail->member_name = $employeeDetail['nama'];
                        $modelsDetail->email_member = $employeeDetail['email'];
                        $modelsDetail->active = 1;

                        $modelsDetail->save();
                        }
                        
                    }
                
                    Yii::$app->response->statusCode = 200;
                    $response['code']=200;
                    $response['message']="success.";
                    $response['data']=[];
                    return $response;
                }else{
                    Yii::$app->response->statusCode = 200;
                    $response['code']=200;
                    $response['message']="success.";
                    $response['data']=[];
                    return $response;
                }


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
        $token = $_GET['token'] ?? "";
        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        $idGroup = [0];

        $group = Group::find()->select(['id_group'])
                ->where(['=','owner_id',$person_id])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->all();
        if($group){
            foreach ($group as $key => $value) {
                $idGroup[] =$value->id_group;
            }
        }

         $groudetail = GroupDetail::find()->select(['id_member','id_group','created_at','active'])
            ->where(['=','id_member',$person_id])
            ->andWhere(['=','active',1])
            ->all();

        // scope 1
        $group_except = Group::find()->select(['id_group','member_except'])
            ->where(['=','member_scope',1])
            ->all();

            $array_id=[];
            foreach($group_except as $ge){
                    if($ge->member_except){  
                    //$data=array_search($token, json_decode($ge->member_except));
                    $data=in_array($token, json_decode($ge->member_except));
                        if($data){
                            $array_id[]=$ge->id_group;
                        } 
                    }

            }


        


        $data_group = Group::find()->select(['id_group'])
        ->orderBy('id_group DESC')
        ->andWhere(['NOT IN','id_group',$array_id])
        ->andWhere(['=','member_scope',1])
        ->andWhere(['=','active',1])
        ->all();

        $dataGrosNew = [];

        foreach ($data_group as $key => $value) {
            $dataGrosNew[]=$value->id_group;
        }

        

        
        $dataGroupRes = [];
        $dataGroup = [];

            if($groudetail){
                $joinGroup = [];
                foreach ($groudetail as $key2 => $value2) {

                    $groupOfMember = Group::find()->select(['id_group'])
                    ->orderBy('id_group DESC')
                    ->andWhere(['=','id_group',$value2->id_group])
                    ->andWhere(['=','active',1])
                    ->all();

                    if($groupOfMember){
                        foreach ($groupOfMember as $key => $value) {
                            $dataGroup[] =$value->id_group;
                        }
                    }
                    
                }
            }


        $dataGroupRes= array_merge($idGroup,$dataGroup,$dataGrosNew);
        //print_r($dataGroupRes);die();

        $id_group_0 = json_decode(file_get_contents('general.json'));

        $groupNew_0 = [
            'id_group'=>$id_group_0->id_group,
            'group_name'=>$id_group_0->group_name,
            'description'=>$id_group_0->description,
            'active'=>$id_group_0->active,
            'created_at'=>$id_group_0->created_at,
            'owner_id'=>$id_group_0->owner_id,
            'file_name'=>$id_group_0->file_name,
            'file_content'=>$id_group_0->file_content,
            'file_type'=>"image/jpeg",
            'member_scope'=>0,

        ];
    


        if($dataGroupRes){

            $group = Group::find()->select(['id_group', 'group_name','description','active','created_at','owner_id','file_name','file_content','file_type','member_scope'])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->andWhere(['id_group' => $dataGroupRes])
                ->limit($limit)
                ->all();

            $databaru = [];

            foreach ($group as $key => $value) {
                $h2['id_group'] = $value->id_group;
                $h2['file_type'] = $value->file_type;
                $h2['group_name'] = $value->group_name;
                $h2['description'] = $value->description;
                $h2['owner_id'] = $value->owner_id;
                $h2['file_content']=$value->file_content; 
                $h2['member_scope']=$value->member_scope; 
                $databaru[]=$h2;
            }

            array_push($group,$groupNew_0);

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Data found.";
            $response['data']=$group;
            return $response;


            // if($databaru){
            //     Yii::$app->response->statusCode = 200;
            //     $response['code']=200;
            //     $response['message']="Data found.";
            //     $response['data']=$group;
            //     return $response;
 
            // }else{
            //     Yii::$app->response->statusCode = 404;
            //     $response['code']=404;
            //     $response['message']="Data not found.";
            //     $response['data']=[];
            //     return $response;
            // }

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }


    }

    public function actionAllListGroup(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 200;
        $token=$_GET['token'];

        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();
        
        $person_id = $employee['person_id'] ?? null;

        $idGroup = [0];

        $group = Group::find()->select(['id_group'])
                ->where(['=','owner_id',$person_id])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->all();
        if($group){
            foreach ($group as $key => $value) {
                $idGroup[] =$value->id_group;
            }
        }

     
        $groudetail = GroupDetail::find()->select(['id_member','id_group','created_at','active'])
            ->where(['=','id_member',$person_id])
            ->andWhere(['=','active',1])
            ->all();


        // scope 1
        $group_except = Group::find()->select(['id_group','member_except'])
            ->where(['=','member_scope',1])
            ->all();

            $array_id=[];
            foreach($group_except as $ge){
                    if($ge->member_except){  
                    //$data=array_search($token, json_decode($ge->member_except));
                    $data=in_array($token, json_decode($ge->member_except));
                        if($data){
                            $array_id[]=$ge->id_group;
                        } 
                    }

            }


        


        $data_group = Group::find()->select(['id_group'])
        ->orderBy('id_group DESC')
        ->andWhere(['NOT IN','id_group',$array_id])
        ->andWhere(['=','member_scope',1])
        ->andWhere(['=','active',1])
        ->all();

        $dataGrosNew = [];

        foreach ($data_group as $key => $value) {
            $dataGrosNew[]=$value->id_group;
        }
           
        $dataGroupRes = [];
        $dataGroup = [];
        //$dataGros=[];

        if($groudetail){
                $joinGroup = [];
                foreach ($groudetail as $key2 => $value2) {

                    $groupOfMember = Group::find()->select(['id_group'])
                    ->orderBy('id_group DESC')
                    ->andWhere(['=','id_group',$value2->id_group])
                    ->andWhere(['=','active',1])
                    ->all();

                    // foreach($data_group as $dg){
                    //     if($dg->id_group!=$value2->id_group){
                    //         $dataGros[]=$dg->id_group;
                    //     }
                    // }
                    if($groupOfMember){
                        foreach ($groupOfMember as $key => $value) {
                            $dataGroup[] =$value->id_group;
                        }
                    }
                    
                }
            }
        $dataGroupRes= array_merge($idGroup,$dataGroup,$dataGrosNew);

        //print_r($idGroup);die();
        
        if($dataGroupRes){

            $group = Group::find()->select(['id_group', 'group_name','type_group_name','description','active','created_at','owner_id','file_name','file_content','file_type','member_scope'])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->andWhere(['id_group' => $dataGroupRes])
                ->limit($limit)
                ->offset($offset)
                ->all();

            $databaru = [];

            foreach ($group as $key => $value) {
                $h2['id_group'] = $value->id_group;
                $h2['file_name'] = $value->file_name;
                $h2['type_group_name'] = $value->type_group_name;
                $h2['file_type'] = $value->file_type;
                $h2['group_name'] = $value->group_name;
                $h2['description'] = $value->description;
                $h2['owner_id'] = $value->owner_id;
                $h2['file_content']=$value->file_content; 
                $h2['member_scope']=$value->member_scope; 
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

    public function actionDelete(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;
       
        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        if(!$raw){
           Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response; 
        }

        $id_group = $raw->id_group ?? 0;

        $group = Group::find()
                ->where(['=','owner_id',$person_id])
                ->andWhere(['=','id_group',$id_group])
                ->one();

        if(!$group){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response; 
        }


        $delete =  \Yii::$app->db->createCommand()
            ->delete('group', 'id_group = '.$id_group.'')
            ->execute();

        if($delete){

            //  if($raw->member_scope==0){
                $delete =  \Yii::$app->db->createCommand()
                ->delete('group_detail', 'id_group = '.$id_group.'')
                ->execute();
    
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="success.";
                $response['data']=[];
                return $response; 
            //  }else{
            //     Yii::$app->response->statusCode = 200;
            //     $response['code']=200;
            //     $response['message']="success.";
            //     $response['data']=[];
            //     return $response; 
            //  }

        }else{
             Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response; 
        }

    }

    public function actionUpdate(){
        $parse = new AttachmentFile;
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $id_group = $raw->id_group ?? 0;
        //echo $id_group;die();


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
        
        $findroup = Group::find()->select('id_group','owner_id')
                    ->where(["=","id_group",$id_group])
                    ->andWhere(['=','owner_id',$person_id])
                    ->one();

        if(!$findroup){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

        $check_gorup=Group::find()
            ->where(['!=','id_group',$id_group])
            ->andWhere(['LIKE','group_name','%'.$raw->name.'', false])
            ->count();

        if($check_gorup>0){
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Group already exist. try different group name";
            $response['data']=$check_gorup;
            return $response;
            
        }
        
        $update = [
            "group_name"=> $raw->name,
            "description"=> $raw->description,
            "active"=> $raw->active,
            "type_group"=> $raw->group_type,
            "type_group_name"=> $helper->GroupName($raw->group_type) ?? "Undefined",
            "updated_at"=> date('Y-m-d H:i:s'),
            "mute_posting"=>$raw->mute_posting ?? 0
        ];

        if($raw->file!=""){

            $info_file = $parse->getFileInfo($raw->file);

            $file_name = $info_file['file_name'] ?? "";
            $file_type = $info_file['file_type'] ?? "";

            $r['file_name'] = $file_name;
            $r['file_type'] = $file_type;
            $r['file_content'] = $raw->file;
            $r['file_size'] = $raw->size;

            $update = array_merge($update, $r);

        }


        try {
            $save =  \Yii::$app->db->createCommand()
            ->update('group', $update, 'id_group = '.$id_group.'')
            ->execute();

            if($save){

                // if($raw->member){
                //     $members = $raw->member;

                //     $id_members = [];

                //     foreach ($members as $key => $value) {

                //         $id_members[]=$value->personid;

                //         $findeMember = GroupDetail::find()
                //         ->where(['=','id_group',$id_group])
                //         ->andWhere(['=','id_member',$value->personid])
                //         ->one();

                //         if(!$findeMember){

                //             $modelsDetail = new GroupDetail;
                //             // insert Member
                //             $employeeDetail = Employee::find()->where(['=','person_id',$value->personid])
                //             ->one();

                //             $modelsDetail->id_group = $id_group;
                //             $modelsDetail->group_name = $raw->name;
                //             $modelsDetail->id_member = $value->personid;
                //             $modelsDetail->member_name = $employeeDetail['nama'];
                //             $modelsDetail->email_member = $employeeDetail['email'];
                //             $modelsDetail->active = 1;

                //             $modelsDetail->save();

                //         }
                        
                //     }

                //     $deleteMembers = GroupDetail::deleteAll(['and','id_group = '.$id_group.'',
                //                     ['not in', 'id_member', $id_members]
                //                 ]);

                       
                // }

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success update.";
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

    public function actionGetDetail(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $helps = new Helper();

        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 200;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $id_group = $request->get('id') ?? 0;
    
        $person_id = $employee['person_id'] ?? null;
        $owner_nama = $employee['nama'] ?? "";
        $owner_email = $employee['email'] ?? "";

        $resGroup = [];

        $findroup = Group::find()->select(['id_group','owner_id','group_name','description','file_content','file_type','active','owner_name','type_group','member_except','member_scope','mute_posting'])
                    ->where(["=","id_group",$id_group])
                    ->andWhere(['=','owner_id',$person_id])
                    ->one();

        // print_r($_GET['token']);die();
        if(!$findroup){
             Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="data not found.";
            $response['data']=$resGroup;
            return $response;
        }

        $employeeOwner = Employee::find()->select(['nik'])->where(['=','person_id',$findroup->owner_id])->one();

        $resGroup['id'] = $findroup->id_group;
        $resGroup['ownerId'] = $findroup->owner_id;
        $resGroup['ownerName'] = $findroup->owner_name;
        $resGroup['groupName'] = $findroup->group_name;
        $resGroup['groupType'] = $findroup->type_group;
        $resGroup['nik'] = $employeeOwner->nik;
        $resGroup['description'] = $findroup->description;
        $resGroup['mute_posting'] = $findroup->mute_posting;
        $resGroup['file_content'] = $findroup->file_content;
        $resGroup['file_type'] = $findroup->file_type;
        $resGroup['active'] = $findroup->active;
        $resGroup['imageGroup'] = $helps->serverName()."/index.php?r=file%2Fgroup&id=".$findroup->id_group;

        if($findroup->member_scope==0){
        $groudetail = GroupDetail::find()->select(['id_group','id_member','member_name','email_member','active'])
            ->where(['=','id_group',$findroup->id_group])
            ->andWhere(['=','active',1])
            ->limit($limit)
            ->offset($offset)
            ->all();

            
        $resGroupDetail = [];
        foreach ($groudetail as $key => $value) {

            $employee = Employee::find()->select(['nik'])->where(['=','person_id',$value->id_member])->one();
            
            $resp['idGroup'] = $value->id_group;
            $resp['idMember'] = $value->id_member;
            $resp['memberName'] = $value->member_name;
            $resp['emailMember'] = $value->email_member;
            $resp['nikMember'] = $employee->nik;
            $resp['active'] = $value->active;
            $resp['pictures'] = $helps->getPicturesProfile().$value->id_member;

            $resGroupDetail[] = $resp; 

        }

        $resGroup['members'] = $resGroupDetail;
        }else{
            
            $employee = Employee::find()->where(['!=','person_id',$findroup->owner_id ?? ""]);
            if(!empty($findroup->member_except)){
            $employee = $employee->andWhere(['NOT IN','person_id',json_decode($findroup->member_except)]);
            }
            $employee = $employee->limit($limit)
                ->offset($offset)
                ->all();
                $resEmployee = [];
                foreach ($employee as $emp) {
        
                    
                    
                    $resp['idGroup'] = $findroup->id_group;
                    $resp['idMember'] = $emp->person_id;
                    $resp['memberName'] = $emp->nama;
                    $resp['emailMember'] = $emp->email;
                    $resp['nikMember'] = $emp->nik;
                    $resp['active'] = 1;
                    $resp['pictures'] = $helps->getPicturesProfile().$emp->person_id;
        
                    $resEmployee[] = $resp; 
        
                }
        
                $resGroup['members'] = $resEmployee;
        }
        if($findroup){

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="success.";
            $response['data']=$resGroup;
            return $response;


        }else{
             Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }
                    
    }

    public function actionDeleteMember(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        //print_r($raw->id_group);die();
        $token=$_GET['token'];
        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;
       
        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        if(!$raw){
           Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response; 
        }

        $id_group = $raw->id_group ?? 0;
        $personid = $raw->personid ?? 0;
        $group = Group::find()->where(['=','id_group',$id_group ?? ""])->one();
        if($group->member_scope==0){
            $find = GroupDetail::find()->where(['=','id_group',$id_group])
            ->andWhere(['=','id_member',$personid])
            ->one();

            if($find){

                $deleteMembers = GroupDetail::deleteAll(['and','id_group = '.$id_group.'',
                                        ['and', 'id_member = '.$personid.'']
                                    ]);

                if($deleteMembers){
                    Yii::$app->response->statusCode = 200;
                    $response['code']=200;
                    $response['message']="success.";
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
        }else{

            $group = Group::find()
                ->where(['=','owner_id',$token])
                ->andWhere(['=','id_group',$id_group])
                ->one();

                //print_r($group);die();

            if($group){
            
                $arr=json_decode($group->member_except);
                $dataTampun = [];
                
                if(empty($group->member_except)){
                    //array_pop($arr);
                    //array_push($arr,$personid);                
                    $dataTampun[]=$personid;
                    $arr = $dataTampun;
                }else{
                    array_push($arr,$personid);
                }

                //print_r(json_encode($arr));die();

                try {
                    $update =  \Yii::$app->db->createCommand()
                    ->update('group', ["member_except"=>json_encode($arr)], 'id_group = '.$group->id_group.'')
                    ->execute();
        
                    if($update){
                        Yii::$app->response->statusCode = 200;
                        $response['code']=200;
                        $response['message']="success.";
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

            
            
        }

    }

    public function actionAddMember(){
         $parse = new AttachmentFile;
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        if(!$_GET['token']){
             Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $id_group = $raw->id_group ?? 0;
        //echo $id_group;die();


        $group = Group::find()->where(['=','id_group',$id_group ?? ""])->one();
        $except=json_decode($group->member_except);  


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

        try {

           if($raw->member){
                $members = $raw->member;

                $id_members = [];

                foreach ($members as $key => $value) {

                    $id_members[]=$value->personid;

                    if($group->member_scope==0){
                        $findeMember = GroupDetail::find()
                        ->where(['=','id_group',$id_group])
                        ->andWhere(['=','id_member',$value->personid])
                        ->one();

                        if(!$findeMember){

                            $modelsDetail = new GroupDetail;
                            // insert Member
                            $employeeDetail = Employee::find()->where(['=','person_id',$value->personid])
                            ->one();

                            $modelsDetail->id_group = $id_group;
                            $modelsDetail->group_name = $raw->name;
                            $modelsDetail->id_member = $value->personid;
                            $modelsDetail->member_name = $employeeDetail['nama'];
                            $modelsDetail->email_member = $employeeDetail['email'];
                            $modelsDetail->active = 1;

                            $modelsDetail->save();

                        }
                    }else{
                        $arr=[];
                        foreach($except as $out_key=>$out_value){       
                            foreach($id_members as $in_key=>$in_value){
                                if($out_value==$in_value){
                                    unset($except[$out_key]);
                                }
                            }
                            $arr=$except;
                        }
                        $update =  \Yii::$app->db->createCommand()
                        ->update('group', ["member_except"=>json_encode($arr)], 'id_group = '.$id_group.'')
                        ->execute();
                    }
                    
                    
                }

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success update.";
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

    public function actionListMember(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $id = Yii::$app->request->getQueryParam('id');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 200;


        $find = Group::find()->where(['=','id_group',$id])
        ->one();


        if($find){


            $resp=[];
            $resGroupDetail=[];

           

            if($find->member_scope=='0'){

                $findMember = GroupDetail::find()->where(['=','id_group',$id]);
                $findMember = $findMember->all();

                foreach ($findMember as $key => $value) {

                    $employee = Employee::find()->select(['nik'])->where(['=','person_id',$value->id_member])->one();
                    
                    $resp['idGroup'] = $value->id_group;
                    $resp['idMember'] = $value->id_member;
                    $resp['memberName'] = $value->member_name;
                    $resp['emailMember'] = $value->email_member;
                    $resp['nikMember'] = $employee->nik;
                    $resp['active'] = $value->active;

                    $resGroupDetail[] = $resp; 

                }

            }else{
                $findMember = Employee::find()
                ->where(['!=','person_id',$find->owner_id])
                ->where(['not in','person_id',json_decode($find->member_except)])
                ->limit($limit)
                ->all();

                foreach ($findMember as $key => $value) {

                    $resp['idGroup'] = $find->id_group;
                    $resp['idMember'] = $value->person_id;
                    $resp['memberName'] = $value->nama;
                    $resp['emailMember'] = $value->email; 
                    $resp['nikMember'] = $value->nik;
                    $resp['active'] = 1;

                    $resGroupDetail[] = $resp; 

                }

            }


            if(empty($resGroupDetail)){
               Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response; 
            }

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="success.";
            $response['data']=$resGroupDetail;
            return $response;

            

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

    }

    public function actionListHome()
    {
        $request = Yii::$app->request;
        $token = Yii::$app->request->getQueryParam('token');

        // priority searc
        $priority = Group::find()->select(['id_group'])
                ->orderBy('id_group DESC')
                ->where(['=','priority',1])
                ->andWhere(['=','active',1])
                ->all();

        // priority serac

        // all employe
        $group_except = Group::find()->select(['id_group','member_except'])
                ->orderBy('id_group DESC')
                ->where(['=','member_scope',1])
                ->all();

        $array_id=[];
        foreach($group_except as $ge){
                if($ge->member_except){  
                //$data=array_search($token, json_decode($ge->member_except));
                $data=in_array($token, json_decode($ge->member_except));
                    if($data){
                        $array_id[]=$ge->id_group;
                    } 
                }

        }

        $group_except_self = Group::find()->select(['id_group'])
                ->orderBy('id_group DESC')
                ->where(['=','member_scope',1])
                ->andWhere(['NOT IN','id_group',$array_id])
                ->limit(3)
                ->all();

        $owner_group = Group::find()->select(['id_group'])
                ->orderBy('id_group DESC')
                ->where(['=','owner_id',$token])
                ->limit(3)
                ->all();

        $arr_of_member = [];
        $groudetail = (new \yii\db\Query())
                ->select(['group_detail.id_member','group_detail.id_group','group_detail.created_at','group_detail.active'])
                ->join('INNER JOIN', 'group', 'group.id_group = group_detail.id_group')
                ->where(['=','group_detail.id_member',$token])
                ->andWhere(['=','group_detail.active',1])
                ->andWhere(['=','group.active',1])
                ->from('group_detail')
                ->limit(3)
                ->all();

        foreach ($groudetail as $key => $value_groudetail) {
            $arr_of_member[]= $value_groudetail['id_group'];
        }



        // favorite
        $favorite_id=[];
        $favorite = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->limit(5)->all();

        foreach ($favorite as $key => $value_favorite) {
            $favorite_id[] =  $value_favorite->id_group;
        }

        $dataGroupRes= array_merge($priority,$group_except_self,$favorite,$owner_group);

        $dataGroupRes_id = [];
        foreach ($dataGroupRes as $key => $value_dataGroupRes) {
            $dataGroupRes_id[]=$value_dataGroupRes->id_group;
        }

        $res_id = array_unique(array_merge($dataGroupRes_id,$arr_of_member));

        $id_group_0 = json_decode(file_get_contents('general.json'));

        $groupNew_0 = [
            'id_group'=>$id_group_0->id_group,
            'group_name'=>$id_group_0->group_name,
            'description'=>$id_group_0->description,
            'active'=>$id_group_0->active,
            'created_at'=>$id_group_0->created_at,
            'owner_id'=>$id_group_0->owner_id,
            'file_name'=>$id_group_0->file_name,
            'file_content'=>$id_group_0->file_content,
            'file_type'=>"image/jpeg",
            'member_scope'=>0,
            'priority'=>0,
            'favorite'=>'no',

        ];

        if($res_id){

            $params = implode(",",$res_id);
            $operator = 'FIELD(id_group,'.$params.')';

            //echo $operator;die();

            $connection = Yii::$app->getDb();
            $command = $connection->createCommand("SELECT `id_group`,`group_name`,`description`,`active`,`created_at`,`owner_id`,`file_name`,`file_content`,`file_type`,`member_scope`,`priority` FROM `group` WHERE `active`='1' AND `id_group` IN (".$params.") ORDER BY FIELD(id_group,".$params.")");


            $group = $command->queryAll();
            $balikan = [];
            foreach ($group as $key => $value_group) {
                $res1['id_group'] = $value_group['id_group'];
                $res1['group_name'] = $value_group['group_name'];
                $res1['description'] = $value_group['description'];
                $res1['active'] = $value_group['active'];
                $res1['created_at'] = $value_group['created_at'];
                $res1['owner_id'] = $value_group['owner_id'];
                $res1['file_name'] = $value_group['file_name'];
                $res1['file_content'] = $value_group['file_content'];
                $res1['file_type'] = $value_group['file_type'];
                $res1['member_scope'] = $value_group['member_scope'];
                $res1['priority'] = $value_group['priority'];
                $fav_searc = in_array($value_group['id_group'],$favorite_id);

                if($fav_searc){
                    $res1['favorite'] = 'yes'; 
                }else{
                    $res1['favorite'] = 'no'; 

                }
                
                $balikan[] = $res1;
            }

            //print_r($balikan);die();
            array_push($balikan,$groupNew_0);

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Data found.";
            $response['data']=$balikan;
            return $response;

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }
    }
    
    public function actionListGroupHomeAll()
    {
        $request = Yii::$app->request;
        $token = Yii::$app->request->getQueryParam('token');
        $limit =  50;
        // priority searc
        $priority2 = [];
        $priority = Group::find()->select(['*'])
                ->orderBy('id_group DESC')
                ->where(['=','priority',1])
                ->andWhere(['=','active',1])
                ->all();

        foreach ($priority as $key => $value ) {
            $res2['id_group'] = $value['id_group'];
            $res2['group_name'] = $value['group_name'];
            $res2['description'] = $value['description'];
            $res2['active'] = $value['active'];
            $res2['created_at'] = $value['created_at'];
            $res2['owner_id'] = $value['owner_id'];
            $res2['file_name'] = $value['file_name'];
            $res2['file_content'] = $value['file_content'];
            $res2['file_type'] = $value['file_type'];
            $res2['member_scope'] = $value['member_scope'];
            $res2['priority'] = $value['priority'];

            $fav_group = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->andWhere(['=','id_group',$value->id_group])->all();
            
            if($fav_group){
                $res2['favorite'] = "yes";
            }else{
                $res2['favorite'] = "no";

            }
            $priority2[]=$res2;
        }

        //priority

            //mygroup
            $mygroup = [];
            $mygroups = Group::find()->select(['*'])
                    ->orderBy('id_group DESC')
                    ->where(['=','owner_id',$token])
                    ->limit($limit)
                    ->all();

            foreach ($mygroups as $key => $value_mygroups ) {
                $res2_mygroups['id_group'] = $value_mygroups['id_group'];
                $res2_mygroups['group_name'] = $value_mygroups['group_name'];
                $res2_mygroups['description'] = $value_mygroups['description'];
                $res2_mygroups['active'] = $value_mygroups['active'];
                $res2_mygroups['created_at'] = $value_mygroups['created_at'];
                $res2_mygroups['owner_id'] = $value_mygroups['owner_id'];
                $res2_mygroups['file_name'] = $value_mygroups['file_name'];
                $res2_mygroups['file_content'] = $value_mygroups['file_content'];
                $res2_mygroups['file_type'] = $value_mygroups['file_type'];
                $res2_mygroups['member_scope'] = $value_mygroups['member_scope'];
                $res2_mygroups['priority'] = $value_mygroups['priority'];

                $fav_group = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->andWhere(['=','id_group',$value_mygroups->id_group])->all();
                
                if($fav_group){
                    $res2_mygroups['favorite'] = "yes";
                }else{
                    $res2_mygroups['favorite'] = "no";

                }
                $mygroup[]=$res2_mygroups;
            }
            //mygroup

            //joinedgroup

            $group_except = Group::find()->select(['id_group','member_except'])
                    ->orderBy('id_group DESC')
                    ->where(['=','member_scope',1])
                    ->andWhere(['!=','owner_id',$token])
                    ->all();

            $array_id=[];
            foreach($group_except as $ge){
                    if($ge->member_except){  
                    //$data=array_search($token, json_decode($ge->member_except));
                    $data=in_array($token, json_decode($ge->member_except));
                        if($data){
                            $array_id[]=$ge->id_group;
                        } 
                    }

            }

            $group_except_self_id = [];
            $group_except_self = Group::find()->select(['id_group'])
                        ->orderBy('id_group DESC')
                        ->where(['=','member_scope',1])
                        ->andWhere(['NOT IN','id_group',$array_id])
                        ->andWhere(['!=','owner_id',$token])
                        ->limit($limit)
                        ->all();

            foreach ($group_except_self as $key => $value_group_except_self) {
                $group_except_self_id[] = $value_group_except_self->id_group;
            }

            $groudetail = GroupDetail::find()->select(['id_group'])->andWhere(['=','id_member',$token ?? ""])->limit($limit)->all();


            $dataGroupRes = [];
                $dataGroup = [];

                    if($groudetail){
                        $joinGroup = [];
                        foreach ($groudetail as $key2 => $value2) {

                            $groupOfMember = Group::find()->select(['id_group'])
                            ->orderBy('id_group DESC')
                            ->andWhere(['=','id_group',$value2->id_group])
                            ->andWhere(['=','active',1])
                            ->all();

                            if($groupOfMember){
                                foreach ($groupOfMember as $key => $value) {
                                    $dataGroup[] =$value->id_group;
                                }
                            }
                            
                        }
                    }


                $dataGroupRes = array_unique(array_merge($dataGroup,$group_except_self_id));

                $group_joined = Group::find()->select(['*'])
                        ->orderBy('id_group DESC')
                        ->andWhere(['IN','id_group',$dataGroupRes])
                        ->limit($limit)
                        ->all();

                $group_joined2=[];

            foreach ($group_joined as $key => $value_group_joined ) {
                $res2_group_joined['id_group'] = $value_group_joined['id_group'];
                $res2_group_joined['group_name'] = $value_group_joined['group_name'];
                $res2_group_joined['description'] = $value_group_joined['description'];
                $res2_group_joined['active'] = $value_group_joined['active'];
                $res2_group_joined['created_at'] = $value_group_joined['created_at'];
                $res2_group_joined['owner_id'] = $value_group_joined['owner_id'];
                $res2_group_joined['file_name'] = $value_group_joined['file_name'];
                $res2_group_joined['file_content'] = $value_group_joined['file_content'];
                $res2_group_joined['file_type'] = $value_group_joined['file_type'];
                $res2_group_joined['member_scope'] = $value_group_joined['member_scope'];
                $res2_group_joined['priority'] = $value_group_joined['priority'];

                $fav_group = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->andWhere(['=','id_group',$value_group_joined->id_group])->all();
                
                if($fav_group){
                    $res2_group_joined['favorite'] = "yes";
                }else{
                    $res2_group_joined['favorite'] = "no";

                }
                $group_joined2[]=$res2_group_joined;
            }
        
            //joinedgroup

            //favorite
            $favorite_id = [];
            $favorite = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->limit($limit)->all();

            foreach ($favorite as $key => $value_favorite) {
                $favorite_id[] = $value_favorite->id_group;
            }



            $group_favorite2 = [];

            $group_favorite = Group::find()->select(['*'])
                        ->orderBy('id_group DESC')
                        ->andWhere(['IN','id_group',$favorite_id])
                        ->limit($limit)
                        ->all();

            foreach ($group_favorite as $key => $value_group_favorite ) {
                $res2_group_favorite['id_group'] = $value_group_favorite['id_group'];
                $res2_group_favorite['group_name'] = $value_group_favorite['group_name'];
                $res2_group_favorite['description'] = $value_group_favorite['description'];
                $res2_group_favorite['active'] = $value_group_favorite['active'];
                $res2_group_favorite['created_at'] = $value_group_favorite['created_at'];
                $res2_group_favorite['owner_id'] = $value_group_favorite['owner_id'];
                $res2_group_favorite['file_name'] = $value_group_favorite['file_name'];
                $res2_group_favorite['file_content'] = $value_group_favorite['file_content'];
                $res2_group_favorite['file_type'] = $value_group_favorite['file_type'];
                $res2_group_favorite['member_scope'] = $value_group_favorite['member_scope'];
                $res2_group_favorite['priority'] = $value_group_favorite['priority'];
                $res2_group_favorite['favorite'] = "yes";

                $group_favorite2[]=$res2_group_favorite;
            }

            //print_r($group_favorite2);die();
        
            //favorit

        $balikan = [
            "priority"=>$priority2,
            "mygroup"=>$mygroup,
            "joinedgroup"=>$group_joined2,
            "favoritegroup"=>$group_favorite2
        ];

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Data found.";
            $response['data']=$balikan;
            return $response;
    }

    public function actionListGroupHomeAllSearch()
    {
        $request = Yii::$app->request;
        $token = Yii::$app->request->getQueryParam('token');
        $search = Yii::$app->request->getQueryParam('search') ?? "";
        $limit =  50;

        // priority searc
        $priority2 = [];
        $priority="";
        if($search){
            $priority = Group::find()->select(['*'])
                ->orderBy('id_group DESC')
                ->where(['=','priority',1])
                ->andWhere(['=','active',1])
                ->andWhere(['LIKE','group_name','%'.$search.'', false])
                ->all();
        }else{
            $priority = Group::find()->select(['*'])
                ->orderBy('id_group DESC')
                ->where(['=','priority',1])
                ->andWhere(['=','active',1])
                ->all();
        }

        foreach ($priority as $key => $value ) {
            $res2['id_group'] = $value['id_group'];
            $res2['group_name'] = $value['group_name'];
            $res2['description'] = $value['description'];
            $res2['active'] = $value['active'];
            $res2['created_at'] = $value['created_at'];
            $res2['owner_id'] = $value['owner_id'];
            $res2['file_name'] = $value['file_name'];
            $res2['file_content'] = $value['file_content'];
            $res2['file_type'] = $value['file_type'];
            $res2['member_scope'] = $value['member_scope'];
            $res2['priority'] = $value['priority'];

            $fav_group = Favorite::find()->select(['id_group'])
            ->where(['=','person_id',$token])
            ->andWhere(['=','id_group',$value->id_group])->all();
            
            if($fav_group){
                $res2['favorite'] = "yes";
            }else{
                $res2['favorite'] = "no";

            }
                $priority2[]=$res2;
            }

            //priority

            //mygroup
            $mygroup = [];
            $mygroups="";
            if($search){
                $mygroups = Group::find()->select(['*'])
                    ->orderBy('id_group DESC')
                    ->where(['=','owner_id',$token])
                    ->andWhere(['LIKE','group_name','%'.$search.'', false])
                    ->limit($limit)
                    ->all();
            }else{
                $mygroups = Group::find()->select(['*'])
                    ->orderBy('id_group DESC')
                    ->where(['=','owner_id',$token])
                    ->limit($limit)
                    ->all();
            }

            foreach ($mygroups as $key => $value_mygroups ) {
                $res2_mygroups['id_group'] = $value_mygroups['id_group'];
                $res2_mygroups['group_name'] = $value_mygroups['group_name'];
                $res2_mygroups['description'] = $value_mygroups['description'];
                $res2_mygroups['active'] = $value_mygroups['active'];
                $res2_mygroups['created_at'] = $value_mygroups['created_at'];
                $res2_mygroups['owner_id'] = $value_mygroups['owner_id'];
                $res2_mygroups['file_name'] = $value_mygroups['file_name'];
                $res2_mygroups['file_content'] = $value_mygroups['file_content'];
                $res2_mygroups['file_type'] = $value_mygroups['file_type'];
                $res2_mygroups['member_scope'] = $value_mygroups['member_scope'];
                $res2_mygroups['priority'] = $value_mygroups['priority'];

                $fav_group = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->andWhere(['=','id_group',$value_mygroups->id_group])->all();
                
                if($fav_group){
                    $res2_mygroups['favorite'] = "yes";
                }else{
                    $res2_mygroups['favorite'] = "no";

                }
                $mygroup[]=$res2_mygroups;
            }
            //mygroup

            //joinedgroup
            $group_except="";
            if($search){
                $group_except = Group::find()->select(['id_group','member_except'])
                    ->orderBy('id_group DESC')
                    ->where(['=','member_scope',1])
                    ->andWhere(['!=','owner_id',$token])
                    ->andWhere(['LIKE','group_name','%'.$search.'', false])
                    ->all();
            }else{
                $group_except = Group::find()->select(['id_group','member_except'])
                    ->orderBy('id_group DESC')
                    ->where(['=','member_scope',1])
                    ->andWhere(['!=','owner_id',$token])
                    ->all();
            }

            $array_id=[];
            foreach($group_except as $ge){
                    if($ge->member_except){  
                    //$data=array_search($token, json_decode($ge->member_except));
                    $data=in_array($token, json_decode($ge->member_except));
                        if($data){
                            $array_id[]=$ge->id_group;
                        } 
                    }

            }

            $group_except_self_id = [];
            $group_except_self="";
            if($search){
                $group_except_self = Group::find()->select(['id_group'])
                        ->orderBy('id_group DESC')
                        ->where(['=','member_scope',1])
                        ->andWhere(['NOT IN','id_group',$array_id])
                        ->andWhere(['!=','owner_id',$token])
                        ->andWhere(['LIKE','group_name','%'.$search.'', false])
                        ->limit($limit)
                        ->all();
            }else{
                $group_except_self = Group::find()->select(['id_group'])
                        ->orderBy('id_group DESC')
                        ->where(['=','member_scope',1])
                        ->andWhere(['NOT IN','id_group',$array_id])
                        ->andWhere(['!=','owner_id',$token])                        
                        ->limit($limit)
                        ->all();
            }

            foreach ($group_except_self as $key => $value_group_except_self) {
                $group_except_self_id[] = $value_group_except_self->id_group;
            }

            $groudetail = GroupDetail::find()->select(['id_group'])->andWhere(['=','id_member',$token ?? ""])->limit($limit)->all();


            $dataGroupRes = [];
                $dataGroup = [];

                    if($groudetail){
                        $joinGroup = [];
                        foreach ($groudetail as $key2 => $value2) {

                            $groupOfMember="";

                            if($search){
                                    $groupOfMember = Group::find()->select(['id_group'])
                                ->orderBy('id_group DESC')
                                ->andWhere(['=','id_group',$value2->id_group])
                                ->andWhere(['=','active',1])
                                ->andWhere(['LIKE','group_name','%'.$search.'', false])
                                ->all();
                            }else{
                                    $groupOfMember = Group::find()->select(['id_group'])
                                ->orderBy('id_group DESC')
                                ->andWhere(['=','id_group',$value2->id_group])
                                ->andWhere(['=','active',1])
                                ->all();
                            }

                            if($groupOfMember){
                                foreach ($groupOfMember as $key => $value) {
                                    $dataGroup[] =$value->id_group;
                                }
                            }
                            
                        }
                    }


                $dataGroupRes = array_unique(array_merge($dataGroup,$group_except_self_id));

                $group_joined="";
                if($search){
                    $group_joined = Group::find()->select(['*'])
                        ->orderBy('id_group DESC')
                        ->andWhere(['IN','id_group',$dataGroupRes])
                        ->andWhere(['LIKE','group_name','%'.$search.'', false])
                        ->limit($limit)
                        ->all();
                }else{
                    $group_joined = Group::find()->select(['*'])
                        ->orderBy('id_group DESC')
                        ->andWhere(['IN','id_group',$dataGroupRes])
                        ->limit($limit)
                        ->all();
                }

                $group_joined2=[];

                foreach ($group_joined as $key => $value_group_joined ) {
                    $res2_group_joined['id_group'] = $value_group_joined['id_group'];
                    $res2_group_joined['group_name'] = $value_group_joined['group_name'];
                    $res2_group_joined['description'] = $value_group_joined['description'];
                    $res2_group_joined['active'] = $value_group_joined['active'];
                    $res2_group_joined['created_at'] = $value_group_joined['created_at'];
                    $res2_group_joined['owner_id'] = $value_group_joined['owner_id'];
                    $res2_group_joined['file_name'] = $value_group_joined['file_name'];
                    $res2_group_joined['file_content'] = $value_group_joined['file_content'];
                    $res2_group_joined['file_type'] = $value_group_joined['file_type'];
                    $res2_group_joined['member_scope'] = $value_group_joined['member_scope'];
                    $res2_group_joined['priority'] = $value_group_joined['priority'];

                $fav_group = Favorite::find()->select(['id_group'])
                    ->where(['=','person_id',$token])
                    ->andWhere(['=','id_group',$value_group_joined->id_group])->all();
                
                if($fav_group){
                    $res2_group_joined['favorite'] = "yes";
                }else{
                    $res2_group_joined['favorite'] = "no";

                }
                $group_joined2[]=$res2_group_joined;
            }
        
            //joinedgroup

            //favorite
            $favorite_id = [];
            $favorite = Favorite::find()->select(['id_group'])->where(['=','person_id',$token])->limit($limit)->all();

            foreach ($favorite as $key => $value_favorite) {
                $favorite_id[] = $value_favorite->id_group;
            }



            $group_favorite2 = [];

            $group_favorite="";
            if($search){
                $group_favorite = Group::find()->select(['*'])
                        ->orderBy('id_group DESC')
                        ->andWhere(['IN','id_group',$favorite_id])
                        ->andWhere(['LIKE','group_name','%'.$search.'', false])
                        ->limit($limit)
                        ->all();
            }else{
                $group_favorite = Group::find()->select(['*'])
                        ->orderBy('id_group DESC')
                        ->andWhere(['IN','id_group',$favorite_id])
                        ->limit($limit)
                        ->all();
            }
            

            foreach ($group_favorite as $key => $value_group_favorite ) {
                $res2_group_favorite['id_group'] = $value_group_favorite['id_group'];
                $res2_group_favorite['group_name'] = $value_group_favorite['group_name'];
                $res2_group_favorite['description'] = $value_group_favorite['description'];
                $res2_group_favorite['active'] = $value_group_favorite['active'];
                $res2_group_favorite['created_at'] = $value_group_favorite['created_at'];
                $res2_group_favorite['owner_id'] = $value_group_favorite['owner_id'];
                $res2_group_favorite['file_name'] = $value_group_favorite['file_name'];
                $res2_group_favorite['file_content'] = $value_group_favorite['file_content'];
                $res2_group_favorite['file_type'] = $value_group_favorite['file_type'];
                $res2_group_favorite['member_scope'] = $value_group_favorite['member_scope'];
                $res2_group_favorite['priority'] = $value_group_favorite['priority'];
                $res2_group_favorite['favorite'] = "yes";

                $group_favorite2[]=$res2_group_favorite;
            }

            //print_r($group_favorite2);die();
        
            //favorit

        $balikan = [
            "priority"=>$priority2,
            "mygroup"=>$mygroup,
            "joinedgroup"=>$group_joined2,
            "favoritegroup"=>$group_favorite2
        ];

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Data found.";
            $response['data']=$balikan;
            return $response;
    }

    public function actionAllListGroupSearch(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 200;
        $search = Yii::$app->request->getQueryParam('search') ?? "";
        $token=$_GET['token'];

        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();
        
        $person_id = $employee['person_id'] ?? null;

        $idGroup = [0];

        $group = Group::find()->select(['id_group'])
                ->where(['=','owner_id',$person_id])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->andWhere(['!=','type_group',2])
                ->all();
        if($group){
            foreach ($group as $key => $value) {
                $idGroup[] =$value->id_group;
            }
        }

     
        $groudetail = GroupDetail::find()->select(['id_member','id_group','created_at','active'])
            ->where(['=','id_member',$person_id])
            ->andWhere(['=','active',1])
            ->all();


        // scope 1
        $group_except = Group::find()->select(['id_group','member_except'])
            ->where(['=','member_scope',1])
            ->all();

            $array_id=[];
            foreach($group_except as $ge){
                    if($ge->member_except){  
                    //$data=array_search($token, json_decode($ge->member_except));
                    $data=in_array($token, json_decode($ge->member_except));
                        if($data){
                            $array_id[]=$ge->id_group;
                        } 
                    }

            }


        


        $data_group = Group::find()->select(['id_group'])
        ->orderBy('id_group DESC')
        ->andWhere(['NOT IN','id_group',$array_id])
        ->andWhere(['=','member_scope',1])
        ->andWhere(['=','active',1])
        ->andWhere(['!=','type_group',2])
        ->all();

        $dataGrosNew = [];

        foreach ($data_group as $key => $value) {
            $dataGrosNew[]=$value->id_group;
        }
           
        $dataGroupRes = [];
        $dataGroup = [];
        //$dataGros=[];

        if($groudetail){
                $joinGroup = [];
                foreach ($groudetail as $key2 => $value2) {

                    $groupOfMember = Group::find()->select(['id_group'])
                    ->orderBy('id_group DESC')
                    ->andWhere(['=','id_group',$value2->id_group])
                    ->andWhere(['=','active',1])
                    ->andWhere(['=','type_group',1])
                    ->all();

                    // foreach($data_group as $dg){
                    //     if($dg->id_group!=$value2->id_group){
                    //         $dataGros[]=$dg->id_group;
                    //     }
                    // }
                    if($groupOfMember){
                        foreach ($groupOfMember as $key => $value) {
                            $dataGroup[] =$value->id_group;
                        }
                    }
                    
                }
            }
        $dataGroupRes= array_merge($idGroup,$dataGroup,$dataGrosNew);

        //print_r($idGroup);die();
        
        if($dataGroupRes){

            $group = Group::find()->select(['id_group', 'group_name','type_group','type_group_name','description','active','created_at','owner_id','file_name','file_content','file_type','member_scope'])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->andWhere(['id_group' => $dataGroupRes])
                ->andWhere(['LIKE','group_name',$search])
                ->limit($limit)
                ->offset($offset)
                ->all();

            $databaru = [];

            foreach ($group as $key => $value) {
                $h2['id_group'] = $value->id_group;
                $h2['file_name'] = $value->file_name;
                $h2['type_group'] = $value->type_group;
                $h2['type_group_name'] = $value->type_group_name;
                $h2['file_type'] = $value->file_type;
                $h2['group_name'] = $value->group_name;
                $h2['description'] = $value->description;
                $h2['owner_id'] = $value->owner_id;
                $h2['file_content']=$value->file_content; 
                $h2['member_scope']=$value->member_scope; 
                $databaru[]=$h2;
            }

        
                
            if($databaru){
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Data found ";
                $response['total']=count($group);
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
