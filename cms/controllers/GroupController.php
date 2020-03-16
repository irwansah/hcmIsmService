<?php

namespace cms\controllers;

use Yii;
use cms\models\Employee;
use cms\models\Group;
use cms\models\GroupDetail;
use cms\models\GroupSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class GroupController extends Controller
{

    public function behaviors()
    {
        return [
             'access' => [
            'class' => AccessControl::className(),
            'only' => ['index','create','update','delete','view'],
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
     * Lists all Group models.
     * @return mixed
     */
    public function actionIndex()
    {

        $group= new Group;
        $data = $group->memberGroup;
        return $this->render('index', [
            'data' => $data,
            'count' =>$data
        ]);
    }

 public function actionDetail($id)
    {
        $postDetail= new GroupDetail;
        $data= $postDetail->getdetailPosting($id);
       
        return $this->render('detailgroup', [
            'data' => $data
        ]);
    }
   
    public function actionView($id)
    {
            
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

   
    public function actionUpdate($id)
    {
            
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_group]);
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
            $this->layout = "adminlte";
        if (($model = Group::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
