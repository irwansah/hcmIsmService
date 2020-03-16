<?php
namespace serviceism\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use common\models\LoginForm;

use serviceism\models\ApiUser;
use serviceism\models\Employee;
use serviceism\models\Posting;
use serviceism\models\PostingDetail;
use serviceism\models\PostingLink;
use serviceism\models\Comment;
use serviceism\models\Like;
use serviceism\models\Group;
use serviceism\models\GroupDetail;
use serviceism\helpers\Helper;
use serviceism\helpers\AttachmentFile;

/**
 * Story Controller
 */
class StoryController extends Controller
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

	public function actionList()
	{
		$request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $limit = Yii::$app->request->getQueryParam('limit') ?? 100;
        $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

        $date = date('Y-m-d');

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

        $now = new \yii\db\Expression("DATE_FORMAT(`created_at`, '%Y-%m-%d') = '".$date."'");

        $postings = Posting::find()->select(['id_posting','owner_id','owner_name'])
        			->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere($now)
                    ->orderBy('id_posting DESC')
                    ->groupBy('owner_id')
                    ->limit($limit)
                    ->all();

        $users = [];

        if($postings){

        	foreach ($postings as $key => $value) {
        		$r['owner']=$value->owner_id;
        		$r['owner_name']=$value->owner_name;

        		$stories = Posting::find()
        			->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere($now)
                    ->andWhere(['=','owner_id',$value->owner_id])
                    ->orderBy('id_posting DESC')
                    ->limit($limit)
                    ->all();

                foreach ($stories as $key => $value2) {
                	if($value2->type_posting==1){    
	                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value2->id_posting])->all();
	                 
	                    $value2->additionalData = $images;
	                                    
	                }elseif($value2->type_posting==3){
	                   //setting ketika naik server , kalau video gak muncul
	                }elseif($value2->type_posting==4){
	                    $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value2->id_posting])->one();
	                   $value2->additionalData = $file;
	                }elseif($value2->type_posting==5){
	                    $file = PostingLink::find()->where(['=','id_posting',$value2->id_posting])->one();
	                   $value2->additionalData = $file;
	                }
                }

                $r['stories'] = $stories;




        		$users[] = $r;
        	}

        	Yii::$app->response->statusCode = 200;
	        $response['code']=200;
	        $response['message']="Data found.";
	        $response['data']=$users;
	        return $response;

        	//print_r($users);die();
        }else{

        	Yii::$app->response->statusCode = 404;
	        $response['code']=404;
	        $response['message']="Data not found.";
	        $response['data']=[];
	        return $response;

        }
  
	}
}