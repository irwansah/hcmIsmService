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

    public function actionContentPhoto(){
        $parse = new AttachmentFile;
        $helper = new Helper;
        

        $request = Yii::$app->request;
        $post = Yii::$app->request->post();
        //  echo json_encode($request->getRawBody());exit;      
        $raw = json_decode($request->getRawBody());
        //echo json_encode($raw);exit;
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
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

    public function actionContentVideoOld(){
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
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
            //$file_cek = $_FILES;
            //var_dump($file_cek);exit;
       if(empty($_FILES['video']['size'])) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }

        $tmp_file = $_FILES['video']['tmp_name'];

        $file = $getID3->analyze($tmp_file);

            //print_r($file);die();
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
                'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg', // the path to the FFMpeg binary
                'ffprobe.binaries' => '/usr/local/bin/ffprobe', // the path to the FFProbe binary
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

                //$model->save();
        
        if($model->save()){
        
         $primaryKey = $model->getPrimaryKey() ?? null;
                 
         $models= new PostingDetail;

                 $models->id_posting =  $primaryKey;
                 $models->file_name =  $file_name;
                 $models->file_type =  $request->post('mime') ?? "";
                 $models->file_size =  $file['filesize'] ?? "";
                 $models->file_content =  "";
                $models->height = $request->post('height') ?? "";
                $models->width = $request->post('width') ?? "";
                $models->duration = $duration;
                 $models->save();


        }else{

        }

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

    public function actionContentVideo(){
        $parse = new AttachmentFile;
        $helper = new Helper;

        $request = Yii::$app->request;
        $post = Yii::$app->request->post();
        //  echo json_encode($request->getRawBody());exit;      
        $raw = json_decode($request->getRawBody());
        //echo json_encode($raw);exit;
        $headers = Yii::$app->request->headers;

        $token=$_GET['token'];

        $employee = Employee::find()->where(['=','person_id', $token ?? ""])
                ->one();

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

        if($_SERVER['REQUEST_METHOD'] !='POST'){
            Yii::$app->response->statusCode = 405;
            $response['code']=405;
            $response['message']="Method not allowed.";
            $response['data']=[];
            return $response;
        }

        
        $path_video="video_base64";
        $path_thumbnail="thumbnail";
        $video_txt_name="video_".date("sih_dmy_").$token.".txt";
        $thumbnail_txt_name="thumbnail_".date("sih_dmy_").$token.".txt";
        $mt=explode("/",$raw->mime);
        $mime_type=end($mt);
        

        $files_url=fopen($path_video.'/'.$video_txt_name,'w') or die("Unable to open file!");
        fwrite($files_url, $raw->video);
        fclose($files_url); 
        
        $video_exists = $path_video.'/'.$video_txt_name;
        if (!file_exists($video_exists)) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }
    
        $FFMpeg = FFMpeg::create([
            // 'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg', // the path to the FFMpeg binary
            // 'ffprobe.binaries' => '/usr/local/bin/ffprobe', // the path to the FFProbe binary
            'ffmpeg.binaries'  => 'C:\ffmpeg\bin\ffmpeg.exe', // the path to the FFMpeg binary
            'ffprobe.binaries' => 'C:\ffmpeg\bin\ffprobe.exe', // the path to the FFProbe binary
            'timeout'          => 3600, // the timeout for the underlying process
            'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
        ]);

        $getID3 = new \getID3;

        $var=base64_decode($raw->video);
        $fp = fopen('temp/'.$token.'.'.$mime_type, 'w');
        fwrite($fp,$var);
        fclose($fp);

        $file=$getID3->analyze('temp/'.$token.'.'.$mime_type);

        $duration=round($file['playtime_seconds']);
        

        $video = $FFMpeg->open('temp/'.$token.'.'.$mime_type);
        $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(3))
        ->save('temp/'.$token.'.jpg');
        
        $path = 'temp/'.$token.'.jpg';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $thumb_base64 = base64_encode($data);
        
        $files_url=fopen($path_thumbnail.'/'.$thumbnail_txt_name,'w') or die("Unable to open file!");
        fwrite($files_url, $thumb_base64);
        fclose($files_url);      

        $thumbnail_exists = $path_thumbnail.'/'.$thumbnail_txt_name;
        if (!file_exists($thumbnail_exists)) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }

        unlink('temp/'.$token.'.'.$mime_type);
        unlink('temp/'.$token.'.jpg');

        

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 3;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->caption = $raw->caption;
        $model->url_content = $video_txt_name;
        $model->thumnail_content = $thumbnail_txt_name;
        $model->text = "";
            

        try {

            if($model->save()){

                $primaryKey = $model->getPrimaryKey() ?? null;

               

                $models= new PostingDetail;

                $models->id_posting =  $primaryKey;
                $models->file_name =  $video_txt_name;
                $models->file_type =  $raw->mime ?? "";
                $models->file_size =  "0" ?? "";
                $models->file_content =  $video_txt_name;
                $models->height = $raw->height ?? "";
                $models->width = $raw->width ?? "";
                $models->duration = $duration;
                $models->save();

                
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

    public function actionContentLink(){
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
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

    public function actionContentStatus(){
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;
        $owner_nama = $employee['nama'] ?? "";
        $owner_email = $employee['email'] ?? "";
        $nik = $employee['nik'] ?? "";

        // print_r($nik);die;

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
        $model->type_posting = 2;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->caption = $raw->caption;
        $model->url_content = "" ;
        $model->nik = $nik ;
        $model->thumnail_content = "";
        $model->text = "";

        // print_r($model);die;

        if($model->save()){
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

       


    }

    public function actionContentFile(){

        $helper = new Helper;
        $getID3 = new \getID3;
        $parse = new AttachmentFile;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        //print_r($_FILES['size']);die(); 

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
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

        $file = $getID3->analyze($tmp_name);
            

            $info = pathinfo($name_file,PATHINFO_EXTENSION) ?? $file['fileformat'];

        if($info==''||$info==null){
        if($_FILES['file']['type']=='aplication/pdf'){
            $info = "pdf";
        }else{
            $info = $info;
        }
        }

        //print_r($file['fileformat']);die();

        //print_r($_FILES['file']);die();

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

    public static function checkTypeFile($file){
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

    public function actionDetail(){
         $request = Yii::$app->request;
         $id = $request->getQueryParam('id');
         $helper = new Helper;
         $serverName = $helper->serverName();


        $headers = Yii::$app->request->headers;
         $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

         $person_id = $employee['person_id'] ?? null;

         // $posting = Posting::find()->where(['=','id_posting',$id])->andWhere(["=","owner_id",$person_id])->andWhere(["=","active",1])->one();
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
                $comments = Comment::find()->where(['=','id_posting',$id])->limit(70)->all();

                foreach ($comments as $key => $valueComment) {

                      $nikComment = Employee::find()->where(['=','person_id',$valueComment->owner_id ?? ""])
                    ->one();

                    $valueComment->nik = $nikComment->nik;
                   
                }

                $posting->comments = $comments;

                $nik = Employee::find()->where(['=','person_id',$posting->owner_id ?? ""])
                ->one();

                $posting->nik = $nik->nik;

                //likes
                $likes = Like::find()->where(['=','id_posting',$id])
                ->andWhere(['=','likes',1])
                ->andWhere(['=','owner_id',$person_id])
                ->one();

                if($likes){
                    $posting->likes = true;
                }else{
                    $posting->likes = false;
                }
                //endlikes

                if($posting->type_posting==1){    
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$id])->all();
                 
                    $posting->additionalData = $images;
                                    
                }elseif($posting->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
            $posting->url_content=$serverName."/video/".$posting->url_content;
           $posting->thumnail_content=$serverName."/thumbnail/".$posting->thumnail_content;
            $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at','height','duration','width'])->where(['=','id_posting',$id])->one();
                   $posting->additionalData = $file;

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

    public function actionListAll(){
        $offset = Yii::$app->request->getQueryParam('offset');
        $group_id = Yii::$app->request->getQueryParam('group_id') ?? 0;
        $headers = Yii::$app->request->headers;

        //print_r($_SERVER);die();
    
        $serverName = 'http://10.250.200.119/hcmIsmService/serviceism/web';

        if($group_id==''){
            $group_id = 0;
        }else{
            $group_id = $group_id;
        }

        $token = $_GET['token'] ?? "";
        $limit = 10;

        $employee = Employee::find()->select(['person_id'])->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        $posting = Posting::find()->where(['=','id_group',$group_id])
                    ->andWhere(["=","active",1])
                    ->andWhere(["!=","owner_id",''])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();

        if($posting){

            foreach ($posting as $key => $value) {
                 $comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();

                 foreach ($comments as $key => $value_comment) {
                     $nikCOmment = Employee::find()->select(['nik','person_id'])->where(['=','person_id',$value_comment->owner_id ?? ""])->one();

                     $value_comment->nik = $nikCOmment->nik;
                 }

                 $value->comments = $comments;

                 $nik = Employee::find()->select(['nik','person_id'])->where(['=','person_id',$value->owner_id ?? ""])
                ->one();

                $value->nik = $nik->nik ?? null; 


                //likes
                $likes = Like::find()->where(['=','id_posting',$value->id_posting])
                ->andWhere(['=','owner_id',$person_id])
                ->andWhere(["=","likes",1])
                ->one();

                if($likes){
                    $value->likes = true;
                }else{
                    $value->likes = false;
                }
                //endlikes

                if($value->type_posting==1){    
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                 
                    $value->additionalData = $images;
                                    
                }elseif($value->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
            $value->url_content=$serverName."/video/".$value->url_content;
            $value->thumnail_content=$serverName."/thumbnail/".$value->thumnail_content;
            $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at','height','duration','width'])->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;

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

    public function actionListHashtag(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;
        $serverName = 'http://10.250.200.119/hcmIsmService/serviceism/web';


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
           $value->url_content=$serverName."/video/".$value->url_content;
           $value->thumnail_content=$serverName."/thumbnail/".$value->thumnail_content;
            $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at','height','duration','width'])->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;

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
        $response['count_data']=count($posting) ?? 0;
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

    public function actionSearch(){
       $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 100;

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

        $posting = Posting::find()->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere(["like","caption","".$raw->keyword.""])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();

        $hastagValues = [];
        $hastagValuesNew = [];
        $count_hasta  = [];

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

        $employeeSearch = Employee::find()->select(['person_id','nama','title','nik','email'])
                ->where(['like','nama',"".$raw->keyword."%",false])
               ->limit($limit)
               ->offset($offset)
               ->orderBy('nama asc')
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

    public function actionListAllUser(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;
        $offset = Yii::$app->request->getQueryParam('offset');
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;
        $user_id = Yii::$app->request->getQueryParam('user_id') ?? 0;

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

    public function actionEditPosting(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        if(!$raw){
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request..";
            $response['data']=[];
            return $response;
        }   

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        $id_posting = $raw->id_posting ?? 0;
        $caption = $raw->caption ?? 0;


        $posting = Posting::find()
                ->where(['=','owner_id',$person_id])
                ->andWhere(['=','id_posting',$id_posting])
                ->one();

        if(!$posting){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }


        $save =  \Yii::$app->db->createCommand()
            ->update('posting', ['caption' => $caption], 'id_posting = '.$id_posting.'')
            ->execute();

        if($save){
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=[];
            return $response;
        }else{
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad request.";
            $response['data']=[];
            return $response;
        }


    }

    public function actionDeletePosting(){
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        $headers = Yii::$app->request->headers;

        if(!$raw){
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request..";
            $response['data']=[];
            return $response;
        }   

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;

        $id_posting = $raw->id_posting ?? 0;
        
        $posting = Posting::find()
                ->where(['=','owner_id',$person_id])
                ->andWhere(['=','id_posting',$id_posting])
                ->one();

        if(!$posting){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

        $delete =  \Yii::$app->db->createCommand()
            ->delete('posting', 'id_posting = '.$id_posting.'')
            ->execute();

        if($delete){

             $deletes =  \Yii::$app->db->createCommand()
            ->delete('posting_detail', 'id_posting = '.$id_posting.'')
            ->execute();

            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=[];
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
