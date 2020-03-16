<?php
namespace serviceism\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter; 
use yii\filters\AccessControl;
use common\models\LoginForm;
use serviceism\models\ApiUser;
use serviceism\models\Like;
use serviceism\models\Employee;
use serviceism\models\Posting;
use serviceism\models\Notif;
use serviceism\helpers\Helper;
use serviceism\helpers\PushNotif;

/**
 * Like controller
 */
class LikeController extends Controller
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

    public function actionPost(){
		$helper = new Helper;
		$push_notif = new PushNotif;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
		$token=$_GET['token'];
        $employee = Employee::find()->where(['=','person_id',$token ?? ''])
				->one();

        $from_user=$employee->person_id;
        $from_nik=$employee->nik;
		$from_email=$employee->email;
		$from_nama=$employee->nama;


        if(empty($employee)){
           Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Employee not found.";
            $response['data']=[];
            return $response; 
        }

        $person_id = $employee['person_id'] ?? null;
        $owner_nama = $employee['nama'] ?? "";
        $owner_email = $employee['email'] ?? "";
        $nik = $employee['nik'] ?? "";

        $idPosting = $raw->idPosting ?? "";

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

		$Posting = Posting::find()->where(['=','id_posting',$idPosting])->one();
        $to_employee = Employee::find()->where(['=','person_id',$Posting->owner_id])
                ->one();
        $to_user=$Posting->owner_id;
        $to_nik=$Posting->nik;
		$to_email=$to_employee->email;
		$to_nama=$to_employee->nama;
        
        $like = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();


        if($like){

        	if($like->likes==1){

        		$countLike = intval($Posting->like_count - 1);
	        	 \Yii::$app->db->createCommand()
			    ->update('posting', ['like_count' => $countLike], 'id_posting = '.$idPosting.'')
			    ->execute();

			    \Yii::$app->db->createCommand()
			    ->update('posting_like', ['likes' => 0], ['id_posting' => $idPosting,'owner_id'=>$person_id ])
			    ->execute();

			    $likeNew = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();

			    Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$likeNew->likes;
	            return $response;

        	}else{

        		$countLike = intval($Posting->like_count + 1);

	        	 \Yii::$app->db->createCommand()
			    ->update('posting', ['like_count' => $countLike], 'id_posting = '.$idPosting.'')
			    ->execute();

			    \Yii::$app->db->createCommand()
			    ->update('posting_like', ['likes' => 1], ['id_posting' => $idPosting,'owner_id'=>$person_id ])
			    ->execute();

				$likeNew = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();
				
				if($from_email!=$to_email){
					$notif = new Notif;
					$notif->id_posting = $idPosting;
					$notif->to_nik = $to_nik;
					$notif->from_nik = $from_nik;
					$notif->to_user = $to_user;
					$notif->from_user = $from_user;
					$notif->to_email = $to_email;
					$notif->from_email = $from_email;
					$notif->to_nama = $to_nama;
					$notif->from_nama = $from_nama;
					$notif->text = "";
					$notif->type = 2;
	
					if($notif->save(false)){
							$push_notif->sendNotification("Like",$from_nama." likes your post ",$to_email);
					}
				}

			    Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$likeNew->likes;
	            return $response;

        	}

        	 

        }else{
        	$countLike = intval($Posting->like_count+1);
        	$countView = intval($Posting->views_count+1);
        	 \Yii::$app->db->createCommand()
		    ->update('posting', ['like_count' => $countLike,'views_count'=>$countView], 'id_posting = '.$idPosting.'')
		    ->execute();

		    $models = new Like;

	        $models->id_posting = $idPosting;
	        $models->owner_id = $person_id ;
	        $models->owner_email = $owner_email;
	        $models->owner_name = $owner_nama;
            $models->nik = $nik;
			$models->likes = 1;
			
			if($from_email!=$to_email){
				$notif = new Notif;
				$notif->id_posting = $idPosting;
				$notif->to_nik = $to_nik;
				$notif->from_nik = $from_nik;
				$notif->to_user = $to_user;
				$notif->from_user = $from_user;
				$notif->to_email = $to_email;
				$notif->from_email = $from_email;
				$notif->to_nama = $to_nama;
				$notif->from_nama = $from_nama;
				$notif->text = "";
				$notif->type = 2;

				if($notif->save(false)){
						$push_notif->sendNotification("Like",$from_nama." likes your post ",$to_email);
				}
			}

	        if($models->save()){
	        	$likeNew = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();
	        	Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$likeNew->likes;
	            return $response;
	        }else{
	        	Yii::$app->response->statusCode = 400;
	            $response['code']=400;
	            $response['message']="Bad request, please check your parameters.";
	            $response['data']=[];
	            return $response;
	        }
        }
    }
	
}