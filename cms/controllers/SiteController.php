<?php
namespace cms\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use cms\models\Posting;
use cms\models\Employee;
use yii\db\Query;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'tables', 'dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['site', 'filter','daftardepartment'],

                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {   
        return $this->render('dashboard');
    }

    public function actionDashboard($flag){
        //validasi start date dan end date
       
      $query = new Query;
       $query->distinct(true)
       ->select('department')
       ->from('employee')
       ->groupby('department');
       $command = $query->createCommand();
        $model = $command->queryAll();

        if($flag == "today"){
            $start_date = date("Y-m-d 00:00:01");
            $end_date = date("Y-m-d 23:59:59" );
        }elseif($flag == "week"){
            $start_date = date('Y-m-d 00:00:01', strtotime("monday this week"));
            $end_date = date('Y-m-d 23:59:59', strtotime("sunday this week"));
        }else{
            $start_date = date("Y-m-01 00:00:01");
            $end_date = date("Y-m-t 23:59:59");
        }

        //create object posting baru
        $posting = new Posting;
        $posting->start_date = $start_date;
        $posting->end_date = $end_date;
        $posting->where_date = "(created_at BETWEEN '".$start_date."' AND '".$end_date."')";
        $countPosting = $posting->countDashboard;
        $hastagData = $posting->countHastag;
        $topActivity = $posting->topActivity;
        $topGroup = $posting->topGroup;
        $topDepartment = $posting->topDepartment;
        $topDivision = $posting->topDivision;
        $mostComments = $posting->getMostData("comment");
        $mostViewers = $posting->getMostData("viewer");
        $mostLikes = $posting->getMostData("like");
        
        
       
        return $this->renderAjax('_dashboard_data', [
            'data' => $model,
            'flag' => $flag,
            'count_likes' => $countPosting['likes'],
            'count_comments' => $countPosting['comments'],
            'count_posting' => $countPosting['posting'],
            'count_link' => $countPosting['link'],
            'hastagData' => $hastagData,
            'topActivity' => $topActivity,
            'topGroup' => $topGroup,
            'topDepartment' => $topDepartment,
            'topDivision' => $topDivision,
            'mostComments' => $mostComments,
            'mostLikes' => $mostLikes,
            'mostViewers' => $mostViewers
        ]);
    }

    public function actionFilter(){
        //validasi start date dan end date
        
        $request = Yii::$app->request;
            // $start_date = date("Y-m-d 00:00:01");
            // $end_date = date("Y-m-d 23:59:59" );
             $start_date=$request->post('date_start');
             $end_date=$request->post('date_end');

        //create object posting baru
        $posting = new Posting;
        $posting->start_date = $start_date;
        $posting->end_date = $end_date;
        // $posting->where_date = "(created_at>='$start_date' AND created_at<='$end_date')";
        $posting->where_date="";
        if(!empty($start_date) && !empty($end_date)){
            $posting->where_date="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
        }elseif(empty($start_date)){
            $posting->where_date="created_at>='$end_date 00:00:01' and created_at<='$end_date 23:59:59'";
        }elseif(empty($end_date)){
            $posting->where_date="created_at>='$start_date 00:00:01' and created_at<='$start_date 23:59:59'";
        }
        
       
        $countPosting = $posting->countDashboard;
        $hastagData = $posting->countHastag;
        $topActivity = $posting->topActivity;
        $topDepartment = $posting->topDepartment;
        $topGroup = $posting->topGroup;
        $topDivision = $posting->topDivision;
        $mostComments = $posting->getMostData("comment");
        $mostViewers = $posting->getMostData("viewer");
        $mostLikes = $posting->getMostData("like");
            
       
        return $this->renderAjax('_dashboard_data', [
            'flag' => "filter",
            'count_likes' => $countPosting['likes'],
            'count_comments' => $countPosting['comments'],
            'count_posting' => $countPosting['posting'],
            'count_link' => $countPosting['link'],
            'hastagData' => $hastagData,
            'topActivity' => $topActivity,
            'topDepartment' => $topDepartment,
            'topGroup' => $topGroup,
            'topDivision' =>$topDivision,
            'mostComments' => $mostComments,
            'mostLikes' => $mostLikes,
            'mostViewers' => $mostViewers,
            'startdate' => $start_date,
            'enddate' => $end_date
        ]);
    }

    public function actionTables()
    {
        return $this->render('tables');

    }

    public function actionDaftardepartment()

    {
        $search=$_GET['search'];
        $query = new Query;
        $query->distinct(true)
        ->select('department')
        ->from('employee')
        ->where(['like','department',$search])
        ->groupby('department');
        $command = $query->createCommand();
        $model = $command->queryAll();
        $rest= [];

        foreach ($model as $key => $value) {
            $h1['id'] = $value['department'];   
            $h1['text'] = $value['department'];
            $rest[]=$h1;

        }
        return json_encode($rest);
    }

   

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
