<?php

namespace cms\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use cms\models\User;
use cms\models\UserSearch;
use cms\models\Employee;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use cms\models\SignupForm;
use yii\db\Query;


/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
            'class' => AccessControl::className(),
            'only' => ['index', 'create', 'update','delete','view'],
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $data = User::find()->all();

        return $this->render('index', [
            'data' => $data,
          
        ]);
    }

    public function actionEmployeenik(){
        $search=$_GET['search'];
        $query = new Query;
        //    $query->distinct(true)
        $query
           ->select(['nik','nama','email'])
           ->from('employee')
           ->where(['like','nik',$search])
           ->orWhere(['like','nama',$search])
           ->groupby('nik');
           $command = $query->createCommand();
            $model = $command->queryAll();
            $rest= [];
    
            foreach ($model as $key => $value) {
                $h1['id'] = $value['nik'];   
                $h1['text'] =$value['nik'].'-'.$value['nama'].'-'.$value['email'];
                $h1['nama']=$value['nama'];
                $h1['email']=$value['email'];
                $rest[]=$h1;
    
            }
            return json_encode($rest);
    }
    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if($model->signup()){
                Yii::$app->session->setFlash('success-notif', "User data successfully created."); 
            }else{
                $error = implode(",",$model->getErrorSummary(true));
                Yii::$app->session->setFlash('notif-notif', "User data failed created because ".$error."."); 
            }
            
            return $this->redirect(['user/index']);
        }

        return $this->render('create', [
            'model' => $model,
           
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    
    public function actionUpdate($id)
    {
        
        $model = $this->findModel($id);

        if (Yii::$app->request->post())  {
            $data = Yii::$app->request->post();
            $model->load(Yii::$app->request->post());
            $password = $data['User']['password'];

            if(!empty($password)){
                $model->password_hash = Yii::$app->security->generatePasswordHash($password);
            }

            if($model->save(false)){
                Yii::$app->session->setFlash('success-notif', "User data successfully updated."); 
            }else{
                $error = implode(",",$model->getErrorSummary(true));
                Yii::$app->session->setFlash('notif-notif', "User data failed updated ".$error."."); 
            }

            return $this->redirect(['user/index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);
        if ($model->delete()) {
            Yii::$app->session->setFlash('success-notif', "Your data successfully deleted."); 
        } else {
            $error = implode(",",$model->getErrorSummary(true));
            Yii::$app->session->setFlash('error-notif', "Your data was not deleted ".$error.".");
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
