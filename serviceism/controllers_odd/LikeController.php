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
use serviceism\helpers\Helper;

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

    public function actionPost()
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

        $idPosting = $raw->idPosting ?? "";

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $Posting = Posting::find()->where(['=','id_posting',$idPosting])->one();
        $like = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();

        if($like){

        	if($like->like==1){

        		$countLike = intval($Posting->like_count - 1);
	        	 \Yii::$app->db->createCommand()
			    ->update('posting', ['like_count' => $countLike], 'id_posting = '.$idPosting.'')
			    ->execute();

			    \Yii::$app->db->createCommand()
			    ->update('posting_like', ['like' => 0], ['id_posting' => $idPosting,'owner_id'=>$person_id ])
			    ->execute();

			    $likeNew = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();

			    Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$likeNew->like;
	            return $response;

        	}else{

        		$countLike = intval($Posting->like_count + 1);
	        	 \Yii::$app->db->createCommand()
			    ->update('posting', ['like_count' => $countLike], 'id_posting = '.$idPosting.'')
			    ->execute();

			    \Yii::$app->db->createCommand()
			    ->update('posting_like', ['like' => 1], ['id_posting' => $idPosting,'owner_id'=>$person_id ])
			    ->execute();

			    $likeNew = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();

			    Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$likeNew->like;
	            return $response;

        	}

        	 

        }else{
        	$countLike = intval($Posting->like_count + 1);
        	 \Yii::$app->db->createCommand()
		    ->update('posting', ['like_count' => $countLike], 'id_posting = '.$idPosting.'')
		    ->execute();

		    $models = new Like;

	        $models->id_posting = $idPosting;
	        $models->owner_id = $person_id ;
	        $models->owner_email = $owner_email;
	        $models->owner_name = $owner_nama;
	        $models->like = 1;

	        if($models->save()){
	        	$likeNew = Like::find()->where(['=','id_posting',$idPosting])->andWhere(['=','owner_id',$person_id])->one();
	        	Yii::$app->response->statusCode = 200;
	            $response['code']=200;
	            $response['message']="Success.";
	            $response['data']=$likeNew->like;
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