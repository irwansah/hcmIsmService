<?php

namespace cms\controllers;

use Yii;
use cms\models\Posting;
use cms\models\Group;
use cms\models\PostingComment;
use cms\models\PostingLike;
use cms\models\PostingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\Pagination;
use kartik\mpdf\Pdf;
use yii\db\Query;

/**
 * PostingController implements the CRUD actions for Posting model.
 */
class PostingController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [

            'access' => [
            'class' => AccessControl::className(),
            'only' => ['index','comment','like','create','individu','indexxomment','indexlike','indexindividu','filterdateview','filterdatecomment','filterdatelike','filterdateindividu',
            'update','delete','view','trending','posting','postinglist','filterlist','postinghierarchy','filterhierarchy',
            'postinggroted','filtergroted','filterdate','listcommentfetch','detail','postingdateindividu','postingindividu','listlike','listcomment','employeedepartment'],
            'rules' => [   
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
               
            ],
        ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Posting models.
     * @return mixed
     */
    public function actionIndex()
    {
        
        return $this->render('index');
    }

 public function actionComment()
    {
          return $this->render('comment');
    } 

    public function actionLike()
    {
          return $this->render('like');
    } 

    public function actionIndividu()
    {
        
        return $this->render('individu');
    }
    

// str
    public function actionIndexView()
    {
       $data = Posting::find()->orderBy('created_at desc')->all();
        return $this->renderAjax('_index', ['data' => $data,]);
    } 
    
    public function actionIndexComment()
    {
       $data = Posting::find()->orderBy('created_at desc')->all();
        return $this->renderAjax('_comment', ['data' => $data,]);
    } 

     public function actionIndexLike()
    {
       $data = Posting::find()->orderBy('created_at desc')->all();
        return $this->renderAjax('_like', ['data' => $data,]);
    }

    public function actionIndexIndividu()
    {
        $start_date="";
        $end_date="";
        $new = new Posting;
        $model = $new->filterDateindividu();
        return $this->renderAjax('_individu', ['data' => $model,
        'start_date' => $start_date,
        'end_date' => $end_date]);
    }

// logic

    public function actionFilterdateView()
    {
        $new = new Posting;
        $result = $new->filterDateview();
        return $this->renderAjax('_index', ['data' => $result]);
    }

    public function actionFilterdateComment()
    {
        $new = new Posting;
        $result= $new->filterDatecomment();
         return $this->renderAjax('_comment', ['data' => $result]);
    }

    public function actionFilterdateLike()
    {
        $new = new Posting;
        $result = $new->filterDatelike();
         return $this->renderAjax('_like', ['data' => $result]);
    }
    
    public function actionFilterdateIndividu()
    {   
        $start_date="";
        $end_date="";
        $request = Yii::$app->request;
         $start_date=$request->post('start_date');
         $end_date=$request->post('end_date');

    //create object posting baru
        $posting = new Posting;
        $posting->start_date = $start_date;
        $posting->end_date = $end_date;
        $result = $posting->filterDateindividu();
         return $this->renderAjax('_individu', ['data' => $result,
         'start_date' => $start_date,
         'end_date' => $end_date]);
    }

     public function actionPosting()
    {
        $gets=empty($_GET['flags'])? 'list':$_GET['flags'];
        if($gets=='hierarchy'):
            return $this->render('view_posting_hierarchy');
        elseif($gets=='group'):
            return $this->render('view_posting_groted');
        elseif($gets=='list'):
            return $this->render('view_posting_list');
        endif;
    }


    public function actionPostinglist()
    {
        $new = new Posting;
        $data = $new->getPosting();
        return $this->renderAjax('_listposting', ['data' => $data]);
    }


    public function actionFilterlist()
    {   
        
            $new = new Posting;
            $model = $new->getListposting();
            return $this->renderAjax('_listposting', ['data' => $model]);
    }


    public function actionPostinghierarchy()
    {
        $posting = new Posting;        
        $models_bgroup = $posting->getFromBGroup();
        $models_division = $posting->getFromDivision();
        $models_department = $posting->getFromDepartment();        
        $models_groted = $posting->getFromGroupCreated();
        $models_posting = $posting->getFromPosting();

        return $this->renderAjax('_hierarchyposting', [
            'models_bgroup' => $models_bgroup,
            'models_division' => $models_division,
            'models_department' => $models_department,            
            'models_groted' => $models_groted,   
            'models_posting' => $models_posting
        ]);
    }
    public function actionFilterhierarchy()
    {
        $request = Yii::$app->request;
        
         $tag=$request->post('tag');
         $start_date=$request->post('start_date');
         $end_date=$request->post('end_date');
         $data_tag=explode('#',$tag);
         $where="";
         if(!empty($tag))
         {
         for($i=1;$i<count($data_tag);$i++){
            if($i<count($data_tag)-1){
                $where.=" caption like '%#".$data_tag[$i]."%' or ";
            }else{
                $where.=" caption like '%#".$data_tag[$i]."%'";
            }
         }
         if(empty($start_date) && empty($end_date)){
            $where.="";
         }else if(!empty($start_date) && !empty($end_date)){
            $where.=" and (created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59')";
         }else if(empty($start_date)){
            $where.=" and (created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59')";
         }else if(empty($end_date)){
            $where.=" and (created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59')";
         }
         
            $posting = new Posting;        
            $posting->where_date=$where;
            $models_bgroup = $posting->getFromBGroup();
            $models_division = $posting->getFromDivision();
            $models_department = $posting->getFromDepartment();        
            $models_groted = $posting->getFromGroupCreated();
            $models_posting = $posting->getFromPosting();
            
            return $this->renderAjax('_hierarchyposting', [
                'models_bgroup' => $models_bgroup,
                'models_division' => $models_division,
                'models_department' => $models_department,            
                'models_groted' => $models_groted,   
                'models_posting' => $models_posting
            ]);
        }else{
            if(empty($start_date) && empty($end_date)){
                $where.="";
            }else if(!empty($start_date) && !empty($end_date)){
                $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
            }else if(empty($start_date)){
                $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
            }else if(empty($end_date)){
                $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
            }
            
            $posting = new Posting;        
            $posting->where_date=$where;
            $models_bgroup = $posting->getFromBGroup();
            $models_division = $posting->getFromDivision();
            $models_department = $posting->getFromDepartment();        
            $models_groted = $posting->getFromGroupCreated();
            $models_posting = $posting->getFromPosting();
            return $this->renderAjax('_hierarchyposting', [
                'models_bgroup' => $models_bgroup,
                'models_division' => $models_division,
                'models_department' => $models_department,            
                'models_groted' => $models_groted,   
                'models_posting' => $models_posting
            ]);
        }

    }

    public function actionPostinggroted()
    {
        $posting = new Posting;               
        $models_groted = $posting->getFromGroupCreated();
        $models_posting = $posting->getFromPosting();

        return $this->renderAjax('_grotedposting', [            
            'models_groted' => $models_groted,   
            'models_posting' => $models_posting
        ]);
    }
    public function actionFiltergroted()
    {
         $request = Yii::$app->request;
         $grup=$request->post('grup');
         $tag=$request->post('tag');
         $start_date=$request->post('start_date');
         $end_date=$request->post('end_date');
         $data_tag=explode('#',$tag);
         $where="";
         if(!empty($tag))
         {
                for($i=1;$i<count($data_tag);$i++){
                   if($i<count($data_tag)-1){
                       $where.=" posting.caption like '%".$data_tag[$i]."%' and ";
                   }else{
                       $where.=" posting.caption like '%".$data_tag[$i]."%'";
                   }
       
                }
        if(empty($grup)){
            $where.="";
        }
        elseif(!empty($grup))
        {
            $where.=" and posting.group_name='$grup' ";
        }
         
         if(empty($start_date) && empty($end_date)){
            $where.="";
         }else if(!empty($start_date) && !empty($end_date)){
            $where.=" and (created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59') ";
         }else if(empty($start_date)){
            $where.=" and (created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59') ";
         }else if(empty($end_date)){
            $where.=" and (created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59') ";
         }
            
            $posting = new Posting;        
            $posting->where_date=$where;
            $models_groted = $posting->getFromGroupCreated();
            $models_posting = $posting->getFromPosting();
            
            return $this->renderAjax('_grotedposting', [
                'models_groted' => $models_groted,   
                'models_posting' => $models_posting
            ]);
        }elseif($grup){

            $where.="group_name='$grup' ";
            if(empty($start_date) && empty($end_date)){
                $where.="";
            }else if(!empty($start_date) && !empty($end_date)){
                $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59' ";
            }else if(empty($start_date)){
                $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59' ";
            }else if(empty($end_date)){
                $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59' ";
            }
            
            $posting = new Posting;        
            $posting->where_date=$where;
            $models_groted = $posting->getFromGroupCreated();
            $models_posting = $posting->getFromPosting();
            return $this->renderAjax('_grotedposting', [                
                'models_groted' => $models_groted,   
                'models_posting' => $models_posting
            ]);
            }
        else{
            if(empty($start_date) && empty($end_date)){
                $where.="";
            }else if(!empty($start_date) && !empty($end_date)){
                $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59' ";
            }else if(empty($start_date)){
                $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59' ";
            }else if(empty($end_date)){
                $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59' ";
            }

            $posting = new Posting;        
            $posting->where_date=$where;
            $models_groted = $posting->getFromGroupCreated();
            $models_posting = $posting->getFromPosting();
            return $this->renderAjax('_grotedposting', [                
                'models_groted' => $models_groted,   
                'models_posting' => $models_posting
            ]);
        }

    }
    
     public function actionDetail($id)
    {
        
        $data = Posting::findOne($id);
        return $this->render('detailposting', [
            'data' => $data
        ]);
    }
    public function actionPostingdateIndividu($id,$flag,$start_date,$end_date)
    {
        
        $new = new Posting;
        $data = $new->getPostingdateindividu($id,$flag,$start_date,$end_date);
        return $this->render('postingindividu', [
            'data' => $data
        ]);
    }

    
    public function actionPostingIndividu($id,$flag)
    {
        
        $new = new Posting;
        $data = $new->getPostingindividu($id,$flag);
        return $this->render('postingindividu', [
            'data' => $data
        ]);
    }

    public function actionPostingIndividureport($id,$start_date,$end_date)
    {
        
        $new = new Posting;
        $data = $new->getPostingindividureport($id,$start_date,$end_date);
        return $this->render('postingindividu', [
            'data' => $data
        ]);
    }

     public function actionListlike($id)
    {
        $data=PostingLike::find()->where(['=','id_posting',$id])->andWhere(['=','likes',1])->all();
        return $this->render('listlike', [
            'data' => $data,
        ]);
    }
    
     public function actionListcomment($id)
    {
        return $this->render('listcomment');
    }

    public function actionListcommentfetch()
    {
        $id=$_GET['id'];
        $data=PostingComment::find()->where(['=','id_posting',$id])->andWhere("")->all();
        return $this->renderAjax('fetch_list_comment', [
            'data' => $data,
        ]);
    }


    public function actionCreate()
    {
      
        $model = new Posting();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_posting]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_posting]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        
        if (($model = Posting::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionEmployeedepartment(){
        $search=$_GET['search'];
        $query = new Query;
        //    $query->distinct(true)
        $query
           ->select(['department'])
           ->from('employee')
           ->where(['like','department',$search])
           ->groupby('department');
           $command = $query->createCommand();
            $model = $command->queryAll();
            $rest= [];
    
            foreach ($model as $key => $value) {
                $h1['id'] = $value['department'];   
                $h1['text'] =$value['department'];
                $rest[]=$h1;
    
            }
            return json_encode($rest);
    }

    public function actionNamegroup(){
        $search=$_GET['search'];
        $query = new Query;
        //    $query->distinct(true)
        $query
           ->select(['group_name'])
           ->from('group')
           ->where(['like','group_name',$search])
           ->groupby('group_name');
           $command = $query->createCommand();
            $model = $command->queryAll();
            $rest= [];
    
            foreach ($model as $key => $value) {
                $h1['id'] = $value['group_name'];   
                $h1['text'] =$value['group_name'];
                $rest[]=$h1;
    
            }
            return json_encode($rest);
    }
}
