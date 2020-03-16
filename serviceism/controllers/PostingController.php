<?php
namespace serviceism\controllers;
date_default_timezone_set("Asia/Jakarta");

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
use serviceism\models\PostingSkill;
use serviceism\models\PostingValue;
use serviceism\models\PostingTarget;
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

      function compressImage($source, $destination, $quality) {

        $info = getimagesize($source);
      
        if ($info['mime'] == 'image/jpeg') 
          $image = imagecreatefromjpeg($source);
      
        elseif ($info['mime'] == 'image/gif') 
          $image = imagecreatefromgif($source);
      
        elseif ($info['mime'] == 'image/png') 
          $image = imagecreatefrompng($source);
      
        imagejpeg($image, $destination, $quality);
      }

    public function actionContentPhoto()
    {
        $parse = new AttachmentFile;
        $helper = new Helper;

        $request = Yii::$app->request;
        $post = Yii::$app->request->post();
        //  echo json_encode($request->getRawBody());exit;      
        $raw = json_decode($request->getRawBody());
         //echo json_encode($raw);exit;
        $headers = Yii::$app->request->headers;
        $token=$_GET['token'];
        $employee = Employee::find()->where(['=','person_id',$token ?? ""])
                ->one();

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

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 1;
        $model->mute_comment = $raw->mute_comment ?? 0 ;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->nik = $nik;
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

                        $mt=explode("/",$value->type);
                        $mime_type=end($mt);

                        $var=base64_decode($value->file);
                        $fp = fopen('temp/origin_'.$token.$key.'.'.$mime_type, 'w');
                        fwrite($fp,$var);
                        fclose($fp);
                        
                        if($value->size > 700000){
                            $this->compressImage("temp/origin_".$token.$key.".".$mime_type,"temp/compress_".$token.$key.".".$mime_type,40);
                        }else{
                            $this->compressImage("temp/origin_".$token.$key.".".$mime_type,"temp/compress_".$token.$key.".".$mime_type,70);
                        }

                        $path = "temp/compress_".$token.$key.".".$mime_type;
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $base64img = base64_encode($data);

                        
                            $models = new PostingDetail;
                            $info_file = $parse->getFileInfo($value->file);

                            $file_name = $info_file['file_name'] ?? "";
                            $file_type = $info_file['file_type'] ?? "";

                            $models->id_posting =  $primaryKey;
                            $models->file_name =  $key."_".$file_name;
                            $models->file_type =  $file_type;
                            $models->file_size =  $value->size;
                            $models->file_content =  $base64img;
                            $models->height = $value->height ?? "";
                            $models->width = $value->width ?? "";
                            $models->save();

                            unlink("temp/compress_".$token.$key.".".$mime_type);
                            unlink("temp/origin_".$token.$key.".".$mime_type);

                            
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
        $nik = $employee['nik'] ?? "";

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
    
        
        
        
        $FFMpeg = FFMpeg::create([
            'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg', // the path to the FFMpeg binary
             'ffprobe.binaries' => '/usr/local/bin/ffprobe', // the path to the FFProbe binary
            //'ffmpeg.binaries'  => 'C:\ffmpeg\bin\ffmpeg.exe', // the path to the FFMpeg binary
            //'ffprobe.binaries' => 'C:\ffmpeg\bin\ffprobe.exe', // the path to the FFProbe binary
            'timeout'          => 3600, // the timeout for the underlying process
            'ffmpeg.threads'   => 12,   // the number of threads that FFMpeg should use
        ]);

        $getID3 = new \getID3;
        
        $var=base64_decode($raw->video);
        $fp = fopen('temp/'.$token.'.'.$mime_type, 'w');
        fwrite($fp,$var);
        fclose($fp);
        chmod('temp/'.$token.'.'.$mime_type, 0777);
        
        
        // bitrate list
        $bit150p="150k";
        $bit240p="350k";
        $bit360p="700k";
        $bit420p="1200k";
        $bit720p="2500k";
        $bit1080p="5000k";
        $bitrate="";

        $info_video=$getID3->analyze('temp/'.$token.'.'.$mime_type);
    
        // set bitrate .. this step for use validation
        if($info_video['filesize']>3000000){
            $bitrate=$bit360p;
        }else{
            if($info_video['filesize']>2000000){
                $bitrate=$bit420p;
            }else{
                $bitrate=$bit150p;
            }
            
        }


        $path_video_compress="temp/".$token.".".$mime_type;
         
        
        
        //$command = "ffmpeg -i $path_video_compress -b:v $bitrate -bufsize $bitrate temp/compress_$token.$mime_type";
        $command = "/usr/local/bin/ffmpeg -i ".$path_video_compress." -b ".$bitrate." temp/compress_".$token.".".$mime_type."";
        // $command = "/usr/local/bin/ffmpeg -i $path_video_compress -b:v $bitrate -bufsize $bitrate temp/compress_$token.$mime_type";
        
        // need permission.
        $outpustcompress = "";
        $exed =  exec($command." 2>&1",$outpustcompress);
        
        $path = "temp/compress_".$token.".".$mime_type;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64video = base64_encode($data);
        
       

        $files_url=fopen($path_video.'/'.$video_txt_name,'w') or die("Unable to open file!");
        fwrite($files_url, $base64video);
        fclose($files_url); 
        
        $video_exists = $path_video.'/'.$video_txt_name;
        if (!file_exists($video_exists)) {
            Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad Request.";
            $response['data']=[];
            return $response;
        }

        
        $file=$getID3->analyze('temp/compress_'.$token.'.'.$mime_type);

        $duration=round($file['playtime_seconds']);
        
        $path = 'temp/'.$token.'.jpg';

        $video = $FFMpeg->open('temp/compress_'.$token.'.'.$mime_type);
        $video->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(1))
        ->save($path);
        
        
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
        unlink('temp/compress_'.$token.'.'.$mime_type);
        unlink('temp/'.$token.'.jpg');

        

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 3;
        $model->mute_comment = $raw->mute_comment ?? 0 ;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->nik = $nik;
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


    public function actionContentVideoOLd()
    {
        //echo "ee";die;
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
        $nik = $employee['nik'] ?? "";

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
        $model->nik = $nik;
        $model->caption = $raw->caption;
        $model->url_content = $video_txt_name;
        $model->thumnail_content = $thumbnail_txt_name;
        $model->text = "";  

        try {

            if($model->save()){

                $primaryKey = $model->getPrimaryKey() ?? null;

                $files_url=fopen($path_video.'/'.$video_txt_name,'w') or die("Unable to open file!");
                fwrite($files_url, $raw->video);
                fclose($files_url);      
            
                $FFMpeg = FFMpeg::create([
                    'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg', // the path to the FFMpeg binary
                    'ffprobe.binaries' => '/usr/local/bin/ffprobe', // the path to the FFProbe binary
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

                unlink('temp/'.$token.'.'.$mime_type);
                unlink('temp/'.$token.'.jpg');


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

    public function actionContentVideo2()
    {
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
        $model->mute_comment = $raw->mute_comment ?? 0 ;
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

    public function actionContentLinkOLD()
    {
        //echo "ee";die();
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        //print_r(json_decode($raw));die();
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

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
        $model->nik = $nik;
        $model->caption = $raw->caption;
        $model->url_content = $raw->url ?? "";
        //$model->url_content = $raw->additionalData->url ?? "";
        $model->thumnail_content = "";
        $model->text = "";

        try {

            if($model->save()){

                $primaryKey = $model->getPrimaryKey() ?? null;

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success.";
                $response['data']=[];
                return $response;
                // $additionalData = $raw->additionalData;

                // $modelLink = new PostingLink;

                // $modelLink->id_posting = $primaryKey ?? "" ;
                // $modelLink->url = $additionalData->url ?? "" ;
                // $modelLink->title = $additionalData->title ?? "" ;
                // $modelLink->sitename = $additionalData->siteName ?? "" ;
                // $modelLink->media_type = $additionalData->mediaType ?? "" ;
                // $modelLink->images = $additionalData->images[0] ?? "" ;
                // $modelLink->favicons = $additionalData->favicons[0] ?? "" ;
                // $modelLink->description = $additionalData->description ?? "" ;
                // $modelLink->content_type = $additionalData->contentType ?? "" ;

                // if($modelLink->save()){
                //     Yii::$app->response->statusCode = 200;
                //     $response['code']=200;
                //     $response['message']="Success.";
                //     $response['data']=[];
                //     return $response;
                // }else{
                //     Yii::$app->response->statusCode = 400;
                //     $response['code']=400;
                //     $response['message']="Bad Request.";
                //     $response['data']=[];
                //     return $response;
                // }



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

    public function actionContentLink()
    {
        //echo "ee";die();
        $helper = new Helper;

        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());
        //print_r(json_decode($raw));die();
        $headers = Yii::$app->request->headers;

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

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

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 5;
        $model->mute_comment = $raw->mute_comment ?? 0 ;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->nik = $nik;
        $model->caption = $raw->caption;
        $model->url_content = $raw->url ?? "";
        //$model->url_content = $raw->additionalData->url ?? "";
        $model->thumnail_content = "";
        $model->text = "";

        try {

            if($model->save()){

                $primaryKey = $model->getPrimaryKey() ?? null;

                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="Success.";
                $response['data']=[];
                return $response;
                // $additionalData = $raw->additionalData;

                // $modelLink = new PostingLink;

                // $modelLink->id_posting = $primaryKey ?? "" ;
                // $modelLink->url = $additionalData->url ?? "" ;
                // $modelLink->title = $additionalData->title ?? "" ;
                // $modelLink->sitename = $additionalData->siteName ?? "" ;
                // $modelLink->media_type = $additionalData->mediaType ?? "" ;
                // $modelLink->images = $additionalData->images[0] ?? "" ;
                // $modelLink->favicons = $additionalData->favicons[0] ?? "" ;
                // $modelLink->description = $additionalData->description ?? "" ;
                // $modelLink->content_type = $additionalData->contentType ?? "" ;

                // if($modelLink->save()){
                //     Yii::$app->response->statusCode = 200;
                //     $response['code']=200;
                //     $response['message']="Success.";
                //     $response['data']=[];
                //     return $response;
                // }else{
                //     Yii::$app->response->statusCode = 400;
                //     $response['code']=400;
                //     $response['message']="Bad Request.";
                //     $response['data']=[];
                //     return $response;
                // }



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
        $parse = new AttachmentFile;
        $request = Yii::$app->request;
        $raw = json_decode($request->getRawBody());

        $employee = Employee::find()->where(['=','person_id',$_GET['token'] ?? ""])
                ->one();

        $person_id = $employee['person_id'] ?? null;
        $owner_nama = $employee['nama'] ?? "";
        $owner_email = $employee['email'] ?? "";
        $nik = $employee['nik'] ?? "";

        if(!$employee){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Employee not found.";
            $response['data']=[];
            return $response;
        }


        if(empty($raw)){
             Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad request.";
            $response['data']=[];
            return $response;
        }

        $info_file = $parse->getFileInfo($raw->file);

        $file_name = $info_file['file_name'] ?? "";
        $file_type = $info_file['file_type'] ?? "";

        $model = new Posting;
        $model->id_group = $raw->group;
        $model->owner_id = $person_id;
        $model->active = 1;
        $model->like_count = 0;
        $model->views_count = 0;
        $model->comment_count = 0;
        $model->type_posting = 4;
        $model->mute_comment = $raw->mute_comment ?? 0 ;
        $model->group_name = $raw->group_name ?? "";
        $model->owner_name = $owner_nama;
        $model->nik = $nik;
        $model->caption = $raw->caption;
        $model->url_content = $raw->original_name ?? $file_name;
        $model->thumnail_content = "";
        $model->text = "";

        if($model->save()){

            $primaryKey = $model->getPrimaryKey() ?? null;

            $size = strlen(base64_decode($raw->file)) ?? 0;

            $models = new PostingDetail;
            
            $models->id_posting =  $primaryKey;
            $models->file_name =  $primaryKey."_".$file_name;
            $models->file_type =  $file_type;
            $models->file_size =  $size;
            $models->file_content =  $raw->file;
           
            if($models->save()){
                Yii::$app->response->statusCode = 200;
                $response['code']=200;
                $response['message']="success.";
                $response['data']=[];
                return $response;

            }else{ 
                Yii::$app->response->statusCode = 400;
                $response['code']=400;
                $response['message']="Bad request.";
                $response['data']=[];
                return $response;
            }

                    
        }else{
             Yii::$app->response->statusCode = 400;
            $response['code']=400;
            $response['message']="Bad request.";
            $response['data']=[];
            return $response;
        }


    }

    public function actionContentFile2()
    {

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
         $helper = new Helper();

        //print_r($_SERVER);die();
    
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
                $comments = Comment::find()->where(['=','id_posting',$id])
                ->andWhere(['!=','owner_id',''])
                ->limit(70)
                ->all();

                foreach ($comments as $key => $valueComment) {

                      $nikComment = Employee::find()->where(['=','person_id',$valueComment->owner_id ?? ""])
                    ->one();

                    $valueComment->nik = $nikComment->nik ?? "";
                   
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
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','height','width','created_at'])->where(['=','id_posting',$posting->id_posting])->all();
                   
                   $dataImae = [];
                  
                  foreach ($images as $key => $valueDetail) {
                        $res1['id_posting_detail'] = $valueDetail->id_posting_detail;
                        $res1['id_posting'] = $valueDetail->id_posting;
                        $res1['file_name'] = $valueDetail->file_name;
                        $res1['file_type'] = $valueDetail->file_type;
                        $res1['file_size'] = $valueDetail->file_size;
                        $res1['file_content'] = $valueDetail->file_content;
                        $res1['height'] = $valueDetail->height;
                        $res1['width'] = $valueDetail->width;
                        $res1['created_at'] = $valueDetail->created_at;
                        if( $valueDetail->height >  $valueDetail->width){
                            $tipe = "potrait";
                            $ratio = 650;
                        }else{
                            $tipe = "landscape";
                            $ratio = 350;

                        }
                        $res1['type_pictures'] = $tipe;
                        $res1['rationIma'] = $ratio;
                        $dataImae[]=$res1;
                  }

                  $posting->additionalData =  $dataImae;
                  $posting->heightDynamic =  @$dataImae[0]['rationIma'] ?? 0;
                                    
                }elseif($posting->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
          
            $posting->url_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-video%26id=".$posting->id_posting;

           $file2 = file_get_contents('thumbnail/'.$posting->thumnail_content) ?? "";

           //$value->thumnail_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-thumbnail-video%26id=".$value->id_posting;
           $posting->thumnail_content="data:image/jpeg;base64,".$file2;


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
   
    public function actionListAllB1()
   {
        $offset = Yii::$app->request->getQueryParam('offset');
        $group_id = Yii::$app->request->getQueryParam('group_id') ?? 0;
        $headers = Yii::$app->request->headers;
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;

        $helper = new Helper();

        //print_r($_SERVER);die();
    
        $serverName = $helper->serverName();

        if($group_id==''){
            $group_id = 0;
        }else{
            $group_id = $group_id;
        }

        $token = $_GET['token'] ?? "";
            
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
                if($value->privacy==1){
                    $posting_target = PostingTarget::find()
                    ->where(['=','id_posting',$value->id_posting])
                    ->one();
                    
                    if($posting_target->id_target!=$_GET['token']){
                         $posting[$key]=null;
                        
                    }
                }
                 $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();


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
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','height','width','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                   
                   $dataImae = [];

                   //$ratio=0;
                  
                  foreach ($images as $key => $valueDetail) {
                        $res1['id_posting_detail'] = $valueDetail->id_posting_detail;
                        $res1['id_posting'] = $valueDetail->id_posting;
                        $res1['file_name'] = $valueDetail->file_name;
                        $res1['file_type'] = $valueDetail->file_type;
                        $res1['file_size'] = $valueDetail->file_size;
                        $res1['file_content'] = $valueDetail->file_content;
                        $res1['height'] = $valueDetail->height;
                        $res1['width'] = $valueDetail->width;
                        $res1['created_at'] = $valueDetail->created_at;
                        if( $valueDetail->height >  $valueDetail->width){
                            $tipe = "potrait";
                            $ratio = 650;
                        }else{
                            $tipe = "landscape";
                            $ratio = 350;

                        }
                        $res1['type_pictures'] = $tipe;
                        $res1['rationIma'] = $ratio;
                        $dataImae[]=$res1;
                  }

                  $value->additionalData =  $dataImae;
                  $value->heightDynamic =  @$dataImae[0]['rationIma'] ?? 0;
                                    
                }elseif($value->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
           $value->url_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-video%26id=".$value->id_posting;

           //$file2 = file_get_contents('thumbnail/'.$value->thumnail_content) ?? "";
           $file2 = @file_get_contents('thumbnail/'.$value->thumnail_content,false) ?? "";

                       if($file2 === FALSE){
                          $value->thumnail_content="data:image/jpeg;base64,";
                      
                       }else{
                            $value->thumnail_content="data:image/jpeg;base64,".$file2;
                       }

           //$value->thumnail_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-thumbnail-video%26id=".$value->id_posting;
           $value->thumnail_content="data:image/jpeg;base64,".$file2;

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
            $data_rows=[];
            foreach($posting as $posting_row){
                
                if($posting_row!=null){
                    $data_rows[]=$posting_row;
                }
                    

            }
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$data_rows;
            return $response;

        }else{
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;
        }

       
   }

   public function actionListAll()
   {
        $offset = Yii::$app->request->getQueryParam('offset');
        $group_id = Yii::$app->request->getQueryParam('group_id') ?? 0;
        $headers = Yii::$app->request->headers;
        $limit = Yii::$app->request->getQueryParam('limit') ?? 10;

        $helper = new Helper();

        //print_r($_SERVER);die();
    
        $serverName = $helper->serverName();

        if($group_id==''){
            $group_id = 0;
        }else{
            $group_id = $group_id;
        }

        $token = $_GET['token'] ?? "";
            
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
                if($value->privacy==1){
                    $posting_target = PostingTarget::find()
                    ->where(['=','id_posting',$value->id_posting])
                    ->one();
                    
                    if($posting_target->id_target!=$_GET['token']){
                         $posting[$key]=null;
                        
                    }
                }
                 $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();


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
                   $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','height','width','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                   
                   $dataImae = [];

                   //$ratio=0;
                  
                  foreach ($images as $key => $valueDetail) {
                        $res1['id_posting_detail'] = $valueDetail->id_posting_detail;
                        $res1['id_posting'] = $valueDetail->id_posting;
                        $res1['file_name'] = $valueDetail->file_name;
                        $res1['file_type'] = $valueDetail->file_type;
                        $res1['file_size'] = $valueDetail->file_size;
                        $res1['file_content'] = $valueDetail->file_content;
                        $res1['height'] = $valueDetail->height;
                        $res1['width'] = $valueDetail->width;
                        $res1['created_at'] = $valueDetail->created_at;
                        if( $valueDetail->height >  $valueDetail->width){
                            $tipe = "potrait";
                            $ratio = 650;
                        }else{
                            $tipe = "landscape";
                            $ratio = 350;

                        }
                        $res1['type_pictures'] = $tipe;
                        $res1['rationIma'] = $ratio;
                        $dataImae[]=$res1;
                  }

                  $value->additionalData =  $dataImae;
                  $value->heightDynamic =  @$dataImae[0]['rationIma'] ?? 0;
                                    
                }elseif($value->type_posting==3){
                   //setting ketika naik server , kalau video gak muncul
                    $value->url_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-video%26id=".$value->id_posting;

                    //$file2 = file_get_contents('thumbnail/'.$value->thumnail_content) ?? "";
                    $file2 = @file_get_contents('thumbnail/'.$value->thumnail_content,false) ?? "";

                                if($file2 === FALSE){
                                    $value->thumnail_content="data:image/jpeg;base64,";
                                
                                }else{
                                        $value->thumnail_content="data:image/jpeg;base64,".$file2;
                                }

                    //$value->thumnail_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-thumbnail-video%26id=".$value->id_posting;
                    $value->thumnail_content="data:image/jpeg;base64,".$file2;

                        $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at','height','duration','width'])->where(['=','id_posting',$value->id_posting])->one();
                            $value->additionalData = $file;

                }elseif($value->type_posting==4){
                    $file = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }elseif($value->type_posting==5){
                    $file = PostingLink::find()->where(['=','id_posting',$value->id_posting])->one();
                   $value->additionalData = $file;
                }
                elseif($value->type_posting==6){
                    $file_skill = PostingSkill::find()->where(['=','id_posting',$value->id_posting])->all();
                   $value->skill = $file_skill;
                   $file_value = PostingValue::find()->where(['=','id_posting',$value->id_posting])->all();
                   $value->value = $file_value;
                   $file_target = PostingTarget::find()->where(['=','id_posting',$value->id_posting])->all();
                   $value->target = $file_target;
                }
            }
            $data_rows=[];
            foreach($posting as $posting_row){
                
                if($posting_row!=null){
                    $data_rows[]=$posting_row;
                }
                    

            }
            Yii::$app->response->statusCode = 200;
            $response['code']=200;
            $response['message']="Success.";
            $response['data']=$data_rows;
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
         $helper = new Helper();

    //print_r($_SERVER);die();
    
        $serverName = $helper->serverName();



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
                   $value->url_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-video%26id=".$value->id_posting;

                   $file2 = file_get_contents('thumbnail/'.$value->thumnail_content) ?? "";

                   //$value->thumnail_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-thumbnail-video%26id=".$value->id_posting;
                   $value->thumnail_content="data:image/jpeg;base64,".$file2;

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

    public function actionSearch()
    {
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

    public function actionSearchOld()
   {
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

        $employeeSearch = Employee::find()->select(['person_id','nama','title','nik','email'])->where(['like','nama',"".$raw->keyword.""])
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
        $person_id = Yii::$app->request->getQueryParam('token');
        $user_name = Yii::$app->request->getQueryParam('user_name');

        if(empty($user_id)){
            $Employee = Employee::find()->select(['*'])->where(['Like','email',$user_name])->one();

            if(empty($Employee)){
                Yii::$app->response->statusCode = 404;
                $response['code']=404;
                $response['message']="Data not found.";
                $response['data']=[];
                return $response;
            }else{
                $user_id = $Employee->person_id;
            }



        }

        $helper = new Helper();
    
        $serverName = $helper->serverName();

        $posting=[];
        
    
        if($person_id == $user_id){
            
            $posting = Posting::find()
                    ->andWhere(["=","active",1])
                    ->andWhere(['=','owner_id',$user_id])
                    ->orderBy('id_posting DESC')
                    ->offset($offset)
                    ->limit($limit)
                    ->all();

            if($posting){

                foreach ($posting as $key => $value) {
                    $value->comments = Comment::find()->where(['=','id_posting',$value->id_posting])->limit(20)->all();

                    $likes = Like::find()->where(['=','id_posting',$value->id_posting])
                    ->andWhere(['=','owner_id',$person_id])
                    ->one();

                    if($likes){
                        $value->likes = 1;
                    }else{
                        $value->likes = 0;
                    }

                    if($value->type_posting==1){    
                       $images = PostingDetail::find()->select(['id_posting_detail','id_posting','file_name','file_type','file_size','file_content','created_at'])->where(['=','id_posting',$value->id_posting])->all();
                     
                        $value->additionalData = $images;
                                        
                    }elseif($value->type_posting==3){
                        
                       $value->url_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-video%26id=".$value->id_posting;

                       $file2 = @file_get_contents('thumbnail/'.$value->thumnail_content,false) ?? "";

                      if($file2 === FALSE){
                          $value->thumnail_content="data:image/jpeg;base64,";
                      
                       }else{
                            $value->thumnail_content="data:image/jpeg;base64,".$file2;
                       }

                       

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
        }else{
             $idGroup = [0];
             $group = Group::find()->select(['id_group'])
                ->where(['=','owner_id',$user_id])
                ->orderBy('id_group DESC')
                ->andWhere(['=','active',1])
                ->andWhere(['=','type_group',0])
                ->all();

            if($group){
                foreach ($group as $key => $value) {
                    $idGroup[] =$value->id_group;
                }
            }

            $groudetail = GroupDetail::find()->select(['id_member','id_group','created_at','active'])
            ->where(['=','id_member',$user_id])
            ->andWhere(['=','active',1])
            ->all();

             $group_except = Group::find()->select(['id_group','member_except'])
            ->where(['=','member_scope',1])
            ->all();

            $array_id=[];
            foreach($group_except as $ge){
                    if($ge->member_except){  
                    //$data=array_search($token, json_decode($ge->member_except));
                    $data=in_array($person_id, json_decode($ge->member_except));
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
                        ->andWhere(['=','type_group',0])
                        ->andWhere(['=','active',1])
                        ->all();

                        if($groupOfMember){
                            foreach ($groupOfMember as $key => $value) {
                                $dataGroup[] =$value->id_group;
                            }
                        }
                        
                    }
                }

            $dataGroupRes= array_unique(array_merge($idGroup,$dataGroup,$dataGrosNew));

            $posting = Posting::find()->where(['id_group'=>$dataGroupRes])
                    ->andWhere(["=","active",1])
                    ->andWhere(['=','owner_id',$user_id])
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
                        $value->url_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-video%26id=".$value->id_posting;

                       $file2 = @file_get_contents('thumbnail/'.$value->thumnail_content,false) ?? "";

                       if($file2 === FALSE){
                          $value->thumnail_content="data:image/jpeg;base64,";
                      
                       }else{
                            $value->thumnail_content="data:image/jpeg;base64,".$file2;
                       }

                       //$value->thumnail_content=$serverName."/hcm/xcodepar?xcodepar=ism-get-thumbnail-video%26id=".$value->id_posting;
                       $value->thumnail_content="data:image/jpeg;base64,".$file2;

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
   }

    public function actionEditPosting()
    {
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
        $mute_comment = $raw->mute_comment ?? 0 ;


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
            ->update('posting', ['caption'=>$caption,'mute_comment'=>$mute_comment], 'id_posting = '.$id_posting.'')
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

    public function actionDeletePosting() 
    {
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

    public function actionGetThumnailVideo()
    {
        $request = Yii::$app->request; 
        $id = $request->getQueryParam('id');

        $posting = Posting::find()->select(['thumnail_content'])->where(['=','id_posting',$id])->one();


        if(empty($posting)){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[];
            return $response;  
        }

        $thumnail_content = $posting->thumnail_content ?? "";

        

        $file = file_get_contents('thumbnail/'.$thumnail_content);
        
        echo json_encode("data:image/jpeg;base64,".$file);die;
    }

    public function actionGetVideo()
    {
        $request = Yii::$app->request;
        $id = $request->getQueryParam('id');

        $posting = PostingDetail::find()->where(['=','id_posting',$id])->one();

        if(empty($posting)){
            Yii::$app->response->statusCode = 404;
            $response['code']=404;
            $response['message']="Data not found.";
            $response['data']=[]; 
            return $response;  
        }
        $file_content = $posting->file_content ?? "";
        $file_type = $posting->file_type ?? "";

        $file = file_get_contents('video_base64/'.$file_content);
        
        echo json_encode($file);die;
    }

    
}
