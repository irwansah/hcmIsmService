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
use serviceism\helpers\Helper;
use serviceism\helpers\PushNotif;

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
        $push_notif = new PushNotif;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $token=$_GET['token'];
        $employee = Employee::find()->where(['=','person_id',$token])
                ->one();

        $from_user=$employee->person_id;
        $from_nik=$employee->nik;
        $from_email=$employee->email;
        $from_nama=$employee->nama;

        

        $person_id = $employee['person_id'] ?? null;
        $owner_nama = $employee['nama'] ?? "";
        $owner_email = $employee['email'] ?? "";
        $nik = $employee['nik'] ?? "";

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
        $models->nik = $nik;
        $models->comment = $comment;

        $Posting = Posting::find()->where(['=','id_posting',$idPosting])->one();
        $posting_owner=$Posting->owner_name;
        
        $to_employee = Employee::find()->where(['=','person_id',$Posting->owner_id])
                ->one();
        $to_user=$Posting->owner_id;
        $to_nik=$Posting->nik;
        $to_email=$to_employee->email;
        $to_nama=$to_employee->nama;
        
        
        
        $countNew = intval($Posting->comment_count+1);
        $countView = intval($Posting->views_count+1);

        \Yii::$app->db->createCommand()
        ->update('posting', ['comment_count' => $countNew,'views_count'=>$countView], 'id_posting = '.$idPosting.'')
        ->execute();


        
                
            

        try {

            if($models->save()){


                

                $res = [];
                $primaryKey = $models->getPrimaryKey() ?? null;

                $res['id_comments'] = $primaryKey;
                $res['owner_id'] = $person_id;
                $res['owner_email'] = $owner_email;
                $res['owner_name'] = $owner_nama;
                $res['comment'] = $comment;
              
                $data = Comment::find()->where(['=','id_posting_comment',$primaryKey])->one();

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
                $notif->text = $comment;
                $notif->type = 1;

                    if($notif->save(false)){
                    

                        // push notif untuk mention
                        $mention_member=$raw->mention_member ?? '';
                        if($mention_member){
                            $push_notif->sendNotification("Comment",$from_nama." commented your post ",$to_email);
                            foreach($mention_member as $res){
                                $push_notif->sendNotification("Mention",$from_nama." mentioned you in ".$posting_owner." post",$res['to_email']);
                            }

                        }else{
                            $push_notif->sendNotification("Comment",$from_nama." commented on your post ",$to_email);
                        }
                        
                    }
                    
                }
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success.";
                $response['data']=$data;
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

    }

    


    
  

}