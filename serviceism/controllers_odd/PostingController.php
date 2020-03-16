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

use yii\web\UploadedFile;

use FFMpeg\FFMpeg;

/**
 * Posting controller
 */
class PostingController extends Controller
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

    public function actionContentPhoto()
    {
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

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 1;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->caption = $raw->caption;
        $model->url_content = "";
        $model->thumnail_content = "";
        $model->text = "";
        try {

            if($model->save()){

                $primaryKey = $model->getPrimaryKey() ?? null;
                $arrImages = $raw->images ?? [];

                if($arrImages){
                    foreach ($arrImages as $key => $value) {
                        $models = new PostingDetail;
                        $info_file = $parse->getFileInfo($value->file);

                        $file_name = $info_file['file_name'] ?? "";
                        $file_type = $info_file['file_type'] ?? "";

                        $models->id_posting =  $primaryKey;
                        $models->file_name =  $key."_".$file_name;
                        $models->file_type =  $file_type;
                        $models->file_size =  $value->size;
                        $models->file_content =  $value->file;
                        $models->save();
                    }
                }

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success.";
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

    public function actionContentVideo()
    {
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
       $request = Yii::$app->request;
       $getID3 = new \getID3;
       //$file = $getID3->analyze($tmp_file);

       if(empty($_FILES['video']['size'])) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }

        $tmp_file = $_FILES['video']['tmp_name'];

        $file = $getID3->analyze($tmp_file);
        $extention = $file['fileformat'];

        $allowed_extensions = ["webm","mp4","ogv"];

        $checkExtention = array_search($extention,$allowed_extensions);

        if(empty($checkExtention)){
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Your file is not video.";
            $response['data']=[];
            return $response;
        }

        if ($_FILES['video']['error'] > 0){
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }else{

            $filesize = $file['filesize'];
            $file_type = $file['mime_type'];
            $duration = round($file['playtime_seconds']);

            //upload file
            $file_name = "video_hcm_ism_".date("sih_dmy").".".$extention;
            $thumbnail = "thumbnail_video_hcm_ism_".date("sih_dmy").".jpg";

            

             $FFMpeg = FFMpeg::create([
                'ffmpeg.binaries'  => 'C:/FFmpeg/bin/ffmpeg.exe', // the path to the FFMpeg binary
                'ffprobe.binaries' => 'C:/FFmpeg/bin/ffprobe.exe', // the path to the FFProbe binary
                'timeout'          => 3600, // the timeout for the underlying process
                'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
            ]);
            $video = $FFMpeg->open($_FILES["video"]["tmp_name"]);

            $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(3))
                ->save('thumbnail/'.$thumbnail);
            move_uploaded_file($_FILES["video"]["tmp_name"], "video/".$file_name);

            //save posting to db

            $model = new Posting;
            $model->id_group = $request->post('group');
            $model->owner_id = $person_id;
            $model->active = 1;
            $model->like_count = 0;
            $model->views_count = 0;
            $model->comment_count = 0;
            $model->type_posting = 3;
            $model->group_name = $request->post('group_name') ?? "";
            $model->owner_name = $owner_nama;
            $model->caption = $request->post('caption');
            $model->url_content =  $file_name;
            $model->thumnail_content = $thumbnail;
            $model->text = "";

            try {

                $model->save();

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="success.";
                $response['data']=[];
                return $response;
                
            } catch (Exception $e) {
                Yii::$app->response->statusCode = 400;
                $response['code']=400;
                $response['message']="Bad Request.";
                $response['data']=[];
                return $response;
            }

            //return $video;

        }

    }

    public function actionContentLink()
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

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 5;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->caption = $raw->caption;
        $model->url_content = $raw->additionalData->url ?? "";
        $model->thumnail_content = "";
        $model->text = "";

        try {

            if($model->save()){

                $primaryKey = $model->getPrimaryKey() ?? null;
                $additionalData = $raw->additionalData;

                $modelLink = new PostingLink;

                $modelLink->id_posting = $primaryKey;
                $modelLink->url = $additionalData->url;
                $modelLink->title = $additionalData->title;
                $modelLink->sitename = $additionalData->siteName;
                $modelLink->media_type = $additionalData->mediaType;
                $modelLink->images = $additionalData->images[0];
                $modelLink->favicons = $additionalData->favicons[0];
                $modelLink->description = $additionalData->description;
                $modelLink->content_type = $additionalData->contentType;

                if($modelLink->save()){
                    Yii::$app->response->statusCode = 200;
                    $response['code']=200;
                    $response['message']="Success.";
                    $response['data']=[];
                    return $response;
                }else{
                    Yii::$app->response->statusCode = 400;
                    $response['code']=400;
                    $response['message']="Bad Request.";
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

    public function actionContentFile()
    {
        $helper = new Helper;
        $getID3 = new \getID3;
        $parse = new AttachmentFile;

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
       //$file = $getID3->analyze($tmp_file);

       if(empty($_FILES['file']['size'])) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="File is requeired.";
            $response['data']=[];
            return $response;
        }

        $name_file = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        

        $info = pathinfo($name_file,PATHINFO_EXTENSION);

        // print_r($_FILES['file']);die();

        //echo $info;die();

        $checkTypeFile = $this->checkTypeFile($info);

        if($checkTypeFile =='image'){

             Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Please use api post photo.";
            $response['data']=[];
            return $response;

        }elseif($checkTypeFile =='video'){

           Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Please use api post video.";
            $response['data']=[];
            return $response;

        }else{

            $model = new Posting;
            $model->id_group = $request->post('group');
            $model->owner_id = $person_id;
            $model->active = 1;
            $model->like_count = 0;
            $model->views_count = 0;
            $model->comment_count = 0;
            $model->type_posting = 4;
            $model->group_name = $request->post('group_name') ?? "";
            $model->owner_name = $owner_nama;
            $model->caption = $request->post('caption');
            $model->url_content =  $name_file;
            $model->thumnail_content = "";
            $model->text = "";

            try {
                $file_name = "file_hcm_ism_".date("sih_dmy").".".$info;

                $model->save();
                move_uploaded_file($_FILES["file"]["tmp_name"], "file/".$file_name);

                $primaryKey = $model->getPrimaryKey() ?? null;

                $models = new PostingDetail;
                //$info_file = $parse->getFileInfo($_FILES["file"]["tmp_name"]);

                
                $file_type = $_FILES['file']['type'] ?? "";

                $models->id_posting =  $primaryKey;
                $models->file_name =  $file_name;
                $models->file_type =  $file_type;
                $models->file_size =  $_FILES['file']['size'];
                $models->file_content =  "";
                $models->save();

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success.";
                $response['data']=[];
                return $response;
                
            } catch (Exception $e) {
                Yii::$app->response->statusCode = 400;
                $response['code']=400;
                $response['message']="Bad Request.";
                $response['data']=[];
                return $response;
            }

        }
    }

    public static function checkTypeFile($file)
    {
        $check = new AttachmentFile;

        $fileres = "other_file";

        if($check->checkImageFile($file)){

            $fileres = "image";

        }else{

            if($check->checkVideoFile($file)){
                $fileres = "video";
            }

        }

        return $fileres;
    }


    public function actionDetail()
    {
         $request = Yii::$app->request;
         $id = $request->getQueryParam('id');

        $headers = Yii::$app->request->headers;
         $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

         $person_id = $employee['person_id'] ?? null;

         $posting = Posting::find()->where(['=','id_posting',$id])->andWhere(["=","active",1])->one();

         if(empty($posting)){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
         }

         $viewsRes = intval($posting->views_count + 1);

        try {

            $save =  \Yii::$app->db->createCommand()
            ->update('posting', ['views_count' => $viewsRes], 'id_posting = '.$id.'')
            ->execute();

            if($save){

                $posting = [];
                $posting = Posting::findOne($id);
                $posting->comments = Comment::find()->where(['=','id_posting',$id])->limit(20)->all();

                //likes
                $likes = Like::find()->where(['=','id_posting',$id])
                ->andWhere(['=','owner_id',$person_id])
                ->one();

                if($likes){
                    $posting->likes = 1;
                }else{
                    $posting->likes = 0;
                }
                //endlikes

                if($posting->type_posting==1){    
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$id])->all();
                 
                    $posting->additionalData = $images;
                                    
                }elseif($posting->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
                }elseif($posting->type_posting==4){
                    $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$id])->one();
                   $posting->additionalData = $file;
                }elseif($posting->type_posting==5){
                    $file = PostingLink::find()->where(['=','id_posting',$id])->one();
                   $posting->additionalData = $file;
                }


                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success.";
                $response['data']=$posting;
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
   

   public function actionListAll()
   {
        $offset = Yii::$app->request->getQueryParam('offset');
        $group_id = Yii::$app->request->getQueryParam('group_id') ?? 0;
        $headers = Yii::$app->request->headers;

        if($group_id==''){
            $group_id = 0;
        }else{
            $group_id = $group_id;
        }

        $token = $headers['token'];
        $limit = 1;

        $employee = Employee::find()->select(['person_id'])->where(['=','person_id',$headers['token']])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        $posting = Posting::find()->where(['=','id_group',$group_id])
                    ->andWhere(["=","active",1])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();

        if($posting){

            foreach ($posting as $key => $value) {
                 $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();

                //likes
                $likes = Like::find()->where(['=','id_posting',$value->id_posting])
                ->andWhere(['=','owner_id',$person_id])
                ->one();

                if($likes){
                    $value->likes = 1;
                }else{
                    $value->likes = 0;
                }
                //endlikes

                if($value->type_posting==1){    
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                 
                    $value->additionalData = $images;
                                    
                }elseif($value->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
                }elseif($value->type_posting==4){
                    $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }elseif($value->type_posting==5){
                    $file = PostingLink::find()->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }
            }

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$posting;
            return $response;

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

       
   }

   public function actionListHashtag()
   {
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;

        $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

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

        //print_r($idGroup);die();

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

        $posting = Posting::find()->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere(["like","caption","".$raw->keyword.""])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();

        if($posting){

            foreach ($posting as $key => $value) {
                 $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();

                //likes
                $likes = Like::find()->where(['=','id_posting',$value->id_posting])
                ->andWhere(['=','owner_id',$person_id])
                ->one();

                if($likes){
                    $value->likes = 1;
                }else{
                    $value->likes = 0;
                }
                //endlikes

                if($value->type_posting==1){    
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                 
                    $value->additionalData = $images;
                                    
                }elseif($value->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
                }elseif($value->type_posting==4){
                    $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }elseif($value->type_posting==5){
                    $file = PostingLink::find()->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }
            }

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$posting;
            return $response;

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }
   }

   public function actionSearch()
   {
       $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;

        $employee = Employee::find()->where(['=','person_id',$headers['token']])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

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

        //print_r($idGroup);die();

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

        $posting = Posting::find()->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere(["like","caption","".$raw->keyword.""])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();

        $hastagValues = [];
        $hastagValuesNew = [];

        if($posting){
            $captionArray = array();
            foreach($posting as $value){
                if(strpos($value->caption, '#') !== false){
                    array_push($captionArray,$value->caption);
                }
            }

            $captionData = implode(",",$captionArray);    
            preg_match_all("/(#\w+)/", $captionData, $matched);
            $hastagArray = $matched[0];
            $hastagArray = array_map('strtolower', $hastagArray);
            $hastagValues = array_unique($hastagArray);

            $count_hasta = array_count_values($hastagArray);
            //arsort($hastagValues);
            //
        }

        foreach ($count_hasta as $key => $value_new) {
            $j['tag'] = $key;
            $j['count'] = $value_new;
            $hastagValuesNew[]=$j;
        }

        //print_r(json_encode($hastagValuesNew));die();

        $resTa = [];
        $resTa = $hastagValues;

        //print_r($hastagValues);die();

        // if($posting){

        //     foreach ($posting as $key => $value) {
        //          $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();

        //         //likes
        //         $likes = Like::find()->where(['=','id_posting',$value->id_posting])
        //         ->andWhere(['=','owner_id',$person_id])
        //         ->one();

        //         if($likes){
        //             $value->likes = 1;
        //         }else{
        //             $value->likes = 0;
        //         }
        //         //endlikes

        //         if($value->type_posting==1){    
        //            $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                 
        //             $value->additionalData = $images;
                                    
        //         }elseif($value->type_posting==3){
        //            //setting ketika naik server , kalau video gak muncul
        //         }elseif($value->type_posting==4){
        //             $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->one();
        //            $value->additionalData = $file;
        //         }elseif($value->type_posting==5){
        //             $file = PostingLink::find()->where(['=','id_posting',$value->id_posting])->one();
        //            $value->additionalData = $file;
        //         }
        //     }

        // }

        $employeeSearch = Employee::find()->select(['person_id','nama','title'])->where(['like','nama',"".$raw->keyword.""])
               ->limit($limit)
               ->offset($offset)
               ->all();

        if(!empty($posting) || !empty($employeeSearch)){
             Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']['hashtag']=$hastagValuesNew;
            $response['data']['employe']=$employeeSearch;
            return $response;
        }else{
             Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']['posting']=[];
            $response['data']['employe']=[];
            return $response;
        }

       
   }

   public function actionListAllUser()
   {
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;
        $user_id = Yii::$app->request->getQueryParam('user_id') ?? 0;

        $employee = Employee::find()->where(['=','person_id',$headers['token']])
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

        //print_r($idGroup);die();

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

        // print_r($groudetail);die();

        $posting = Posting::find()->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere(['=','owner_id',$user_id])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();
        // echo count($posting);die();
        if($posting){

            foreach ($posting as $key => $value) {
                 $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();

                //likes
                $likes = Like::find()->where(['=','id_posting',$value->id_posting])
                ->andWhere(['=','owner_id',$person_id])
                ->one();

                if($likes){
                    $value->likes = 1;
                }else{
                    $value->likes = 0;
                }
                //endlikes

                if($value->type_posting==1){    
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                 
                    $value->additionalData = $images;
                                    
                }elseif($value->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
                }elseif($value->type_posting==4){
                    $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }elseif($value->type_posting==5){
                    $file = PostingLink::find()->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }
            }

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$posting;
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
