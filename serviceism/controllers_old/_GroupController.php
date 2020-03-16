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
            $models->member_except = $raw->member_except;
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
        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
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

        $dataGroupRes= array_merge($idGroup,$dataGroup);

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
            'file_type'=>"image/jpeg"
        ];
    


        if($dataGroupRes){

            $group = Group::find()->select(['id_group', 'group_name','description','active','created_at','owner_id','file_name','file_content','file_type'])
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

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
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

        $dataGroupRes= array_merge($idGroup,$dataGroup);

        if($dataGroupRes){

            $group = Group::find()->select(['id_group', 'group_name','type_group_name','description','active','created_at','owner_id','file_name','file_content','file_type'])
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

             if($raw->member_scope==0){
                $delete =  \Yii::$app->db->createCommand()
                ->delete('group_detail', 'id_group = '.$id_group.'')
                ->execute();
    
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

        $update = [
            "group_name"=> $raw->name,
            "description"=> $raw->description,
            "active"=> $raw->active,
            "type_group"=> $raw->group_type,
            "type_group_name"=> $helper->GroupName($raw->group_type) ?? "Undefined",
            "updated_at"=> date('Y-m-d H:i:s'),
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

        $findroup = Group::find()->select(['id_group','owner_id','group_name','description','file_content','file_type','active','owner_name','type_group'])
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
        $resGroup['file_content'] = $findroup->file_content;
        $resGroup['file_type'] = $findroup->file_type;
        $resGroup['active'] = $findroup->active;
        $resGroup['imageGroup'] = $helps->serverName()."/index.php?r=file%2Fgroup&id=".$findroup->id_group;

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

        if($raw->member_scope==0){
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

            if($group){
            
                $arr=json_decode($group->member_except);
                if(empty($group->member_except)){
                    array_pop($arr);
                    array_push($arr,$personid);                
                }else{
                    array_push($arr,$personid);
                }

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

    

}
