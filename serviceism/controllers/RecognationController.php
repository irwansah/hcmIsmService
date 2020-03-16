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
use serviceism\models\Notif;
use serviceism\models\Skill;
use serviceism\models\ValueRec;
use serviceism\models\Group;
use serviceism\models\GroupDetail;
use serviceism\models\PostingSkill;
use serviceism\models\PostingTarget;
use serviceism\models\PostingValue;
use serviceism\helpers\Helper;
use serviceism\helpers\PushNotif;
use serviceism\helpers\AttachmentFile; 

use yii\helpers\ArrayHelper;

/**
 * Recognation controller
 */
class RecognationController extends Controller
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

	public function actionPost()
    {
        $parse = new AttachmentFile;
        $helper = new Helper;
        $push_notif = new PushNotif;

        $request = Yii::$app->request;
        
        //  echo json_encode($request->getRawBody());exit;      
        $raw = json_decode($request->getRawBody());


        
        
         //echo json_encode($raw);exit;
		$headers = Yii::$app->request->headers;
		
		
        

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
		}
		
        $token=$_GET['token'];
        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
				->one();
				
		if(!$employee){
					Yii::$app->response->statusCode = 404;
					$response['code']=404;
					$response['message']="Employee Not Found.";
					$response['data']=[];
					return $response;
		}

		$person_id = $employee['person_id'] ?? null;
        $from_nama = $employee['nama'] ?? "";
        $from_email = $employee['email'] ?? "";
        $from_nik = $employee['nik'] ?? "";

		try{

		$model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
		$model->type_posting = 6;
		$model->type_target = 0;
		$model->privacy = $raw->privacy ?? 0;
        $model->mute_comment = $raw->mute_comment ?? 0 ;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $from_nama;
        $model->nik = $from_nik;
        $model->caption = $raw->caption;
        $model->url_content = "";
        $model->thumnail_content = "";
		$model->text = "";
		
		if($model->save()){
			$primaryKey = $model->getPrimaryKey() ?? null;
			
			

				foreach($raw->skill as $key=>$value){
						$posting_skill=new PostingSkill;
						$posting_skill->id_posting=$primaryKey;
						$posting_skill->id_skill=$value->skill_id;
						$posting_skill->skill_name=$value->skill_name;
						$posting_skill->save();
				}
			
		

			foreach($raw->value as $key=>$value){
						$posting_value=new PostingValue;
						$posting_value->id_posting=$primaryKey;
						$posting_value->id_value=$value->value_id;
						$posting_value->value_name=$value->value_name;
						$posting_value->save();
			}
			

			foreach($raw->target as $key=>$value){
					    $posting_target=new PostingTarget;
						$posting_target->id_posting=$primaryKey;
						$posting_target->id_target=$value->person_id;
						$posting_target->nik=$value->nik;

						if($posting_target->save(false)){
							$notif = new Notif;
							$notif->id_posting = $primaryKey;
							$notif->to_nik = $value->nik;
							$notif->from_nik = $from_nik;
							$notif->to_user = $value->person_id;
							$notif->from_user = $person_id;
							$notif->to_email = $value->email;
							$notif->from_email = $from_email;
							$notif->to_nama = $value->nama;
							$notif->from_nama = $from_nama;
							$notif->text = $raw->caption;
							$notif->type = 1;	
							if($notif->save(false)){
								$push_notif->sendNotification("Recognition ",$from_nama." give you recognition : ".$raw->caption,$value->email);
							}else{
								Yii::$app->response->statusCode = 400;
								$response['code']=400;
								$response['message']="Notif Not Continue.";
								$response['data']=[];
								return $response;
							}

							
						}
						
				}
			}

			 
			Yii::$app->response->statusCode = 200;
			$response['code']=200;
			$response['message']="Success.";
			$response['data']=[];
			return $response;
        
        }catch(Exception $e){
			Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request. ".$e;
            $response['data']=[];
            return $response;		
        }



	}
	
    public function actionListSkill()
    {
    	$data = Skill::find()->select(['skill_id','skill_name','description'])->all();
    	
    	if(count($data) > 0){
    		$res = [];
    		foreach ($data as $key => $value) {
    			$res3['id'] = $value->skill_id;
    			$res3['name'] = $value->skill_name;
    			$res3['description'] = $value->description;
    			$res[] = $res3;
    		}
    		Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="success.";
            $response['data']=$res;
            return $response; 
    	}else{
    		Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="data not found.";
            $response['data']=[];
            return $response; 
    	}
    }

    public function actionListValue()
    {
    	$data = ValueRec::find()->select(['value_id','value_name','desctiption','file_content','file_type'])->all();
    	
    	if(count($data) > 0){
    		$res = [];
    		foreach ($data as $key => $value) {
    			$res3['id'] = $value->value_id;
    			$res3['name'] = $value->value_name;
    			$res3['description'] = $value->desctiption;
    			$res3['content'] = $value->file_content;
    			$res3['type'] = $value->file_type;
    			$res[] = $res3;
    		}
    		Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="success.";
            $response['data']=$res;
            return $response; 
    	}else{
    		Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="data not found.";
            $response['data']=[];
            return $response; 
    	}
    }

    public function actionListMember()
    {
    	$offset = Yii::$app->request->getQueryParam('offset');
        $search = Yii::$app->request->getQueryParam('search');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;
        $id_group = Yii::$app->request->getQueryParam('id_group') ?? 0;
        $token = Yii::$app->request->getQueryParam('token') ?? 0;

         $employee = Employee::find()->select(['person_id','nama','email','nik'])->where(['=','person_id',$token ?? ''])
                ->one();

        if($id_group==0){
        	$Allemployee = Employee::find()->select(['person_id','nama','email','nik'])
        	    ->where(['!=','person_id',$token ?? '']);
	        	if($search){
	               $Allemployee = $Allemployee->andWhere(['LIKE','nama',$search]);  
	        	}

                $Allemployee = $Allemployee
                ->orderBy(['nama'=>SORT_ASC])
                ->limit($limit)
                ->offset($offset)
                ->all();
            if(count($Allemployee)> 0){
            	foreach ($Allemployee as $key => $value) {
            		$value->person_id = (int)$value->person_id;
            		$value->nik = (int)$value->nik;
            	}
            	Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="success.";
	            $response['data']=$Allemployee;
	            return $response; 
            }else{
            	Yii::$app->response->statusCode = 404;
	            $response['code']=404;
	            $response['message']="data not found.";
	            $response['data']=[];
	            return $response; 
            }

        }else{
        	$Group = Group::find()->select(['id_group','group_name','member_scope','member_except','owner_id'])->where(['=','id_group',$id_group])->one();

        	if($Group){
        		if($Group->member_scope==0){
        			$owner = $Group->owner_id;
        			$member = GroupDetail::find()->select(['id_member'])->where(['=','id_group',$id_group])->all();

        			$members =[];

        			foreach ($member as $key => $value) {
        				$members[]=$value->id_member;
        			}

        			 if(!in_array($owner, $members, true)){
					        array_push($members, $owner);
					 }

					 $new_member = array_unique($members);

					 $deleted_index = array_search($token, $new_member);

					 unset($new_member[$deleted_index]);

        			 $Allemployee = Employee::find()->select(['person_id','nama','email','nik'])
		        	    ->where(['IN','person_id',$new_member]);
			        	if($search){
			               $Allemployee = $Allemployee->andWhere(['LIKE','nama',$search]);  
			        	}

		                $Allemployee = $Allemployee
		                ->orderBy(['nama'=>SORT_ASC])
		                ->limit($limit)
		                ->offset($offset)
		                ->all();
		            if(count($Allemployee)> 0){
		            	foreach ($Allemployee as $key => $value) {
		            		$value->person_id = (int)$value->person_id;
		            		$value->nik = (int)$value->nik;
		            	}
		            	Yii::$app->response->statusCode = 200;
			            $response['code']=200;
			            $response['message']="success.";
			            $response['data']=$Allemployee;
			            return $response; 
		            }else{
		            	Yii::$app->response->statusCode = 404;
			            $response['code']=404;
			            $response['message']="data not found.";
			            $response['data']=[];
			            return $response; 
		            }
        		}else{
        			$memberExcept = json_decode($Group->member_except);
        			
        			$Allemployee = Employee::find()->select(['person_id','nama','email','nik'])
		        	    ->where(['!=','person_id',$token])
		        	    ->andWhere(['NOT IN','person_id',$memberExcept]);
			        	if($search){
			               $Allemployee = $Allemployee->andWhere(['LIKE','nama',$search]);  
			        	}

		                $Allemployee = $Allemployee
		                ->orderBy(['nama'=>SORT_ASC])
		                ->limit($limit)
		                ->offset($offset)
		                ->all();

		            if(count($Allemployee)> 0){
		            	foreach ($Allemployee as $key => $value) {
		            		$value->person_id = (int)$value->person_id;
		            		$value->nik = (int)$value->nik;
		            	}
		            	Yii::$app->response->statusCode = 200;
			            $response['code']=200;
			            $response['message']="success.";
			            $response['data']=$Allemployee;
			            return $response; 
		            }else{
		            	Yii::$app->response->statusCode = 404;
			            $response['code']=404;
			            $response['message']="data not found.";
			            $response['data']=[];
			            return $response; 
		            }
        		}
        	}else{
        		Yii::$app->response->statusCode = 404;
	            $response['code']=404;
	            $response['message']="group found.";
	            $response['data']=[];
	            return $response;	
        	}

        }
    }
}


