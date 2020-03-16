<?php

namespace cms\models;

use Yii;
use yii\db\Query;

class Posting extends \yii\db\ActiveRecord {

    public $start_date, $end_date, $count_posting, $where_date, $title, $person_id, $nik, $nama,$department,$division;
    public static function tableName() {
        return 'posting';
    }

    public function rules() {
        return array(
            array('id_posting, id_group, group_name, owner_id, owner_name, caption,url_content, thumnail_content', 'required', 'message' => '{attribute} tidak boleh kosong.'),
        );
    }

    public function attributeLabels() {
        return array(
            'nik' => 'NIK',
            'nama' => 'Nama',
            'no_telp' => 'No Telp',
            'status' => 'Status',
            'alamat' => 'Alamat'
        );
    }

    public function getEmployee()
	{
		return $this->hasOne(Employee::className(), ['person_id' => 'owner_id']);
    }
    
    public function getPostingDetail()
	{
		return $this->hasOne(PostingDetail::className(), ['id_posting' => 'id_posting']);
    }

    //count dashboard posting
    public function getCountDashboard(){
        //count posting, sum likes and comments
        $count_posting_all = Posting::find()->where($this->where_date)->count();
        $count_posting = Posting::find()->where($this->where_date)->andWhere("id_posting NOT IN (SELECT id_posting FROM posting_link)")->count();
        $count_link = PostingLink::find()->select(['id_posting'])->where($this->where_date)->distinct()->count();
        $sum_likes = PostingLike::find()->where($this->where_date)->sum('likes');
        $count_comments = PostingComment::find()->where($this->where_date)->count();

        $data = [
            'likes' => empty($sum_likes) ? 0 : $sum_likes,
            'comments' => $count_comments,
            'posting' => $count_posting,
            'link' => $count_link
        ];

        return $data;
    }

    public function getCountHastag(){
        $akhir=array();
       $sql = Posting::find()->where($this->where_date)->all();
            // $sql= $db->createCommand('SELECT caption from Posting')->queryAll();
            $i=0;
            foreach ($sql as $row)
            {
                $hasil[$i]= $row['caption'];
                $i++;
            }
            $count =Posting::find()->where($this->where_date)->count(); 
            // $db->createCommand('SELECT caption from Posting')->execute();
            $bil= 0;
            for($j =0 ; $j<=$count-1; $j++ )
             {
              
                preg_match_all("/(#\w+)/u", $hasil[$j], $matches);
                $output[$j] = $matches[0];
                $output[$j] = array_map('strtolower', $output[$j]);
                $output2[$j]=array_unique($output[$j]);
                sort($output2[$j]);
                $jml=count($output2[$j]);
                for($m = 0 ; $m < $jml ; $m++ )
                {
                
                $akhir[$bil] = $output2[$j][$m];
                $bil++;

                }       
             }
             $count_values = array_count_values($akhir);
                arsort($count_values);
                 $count_values = array_slice($count_values, 0, 5);    
                return $count_values;
    }

    public function getTopActivity(){
        $query = new Query;
		$query
			->select('posting.owner_id, posting.owner_name, COUNT(owner_name) AS count_posting, employee.person_id, employee.nik, employee.nama, employee.title,employee.department')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->groupBy('owner_name')
            ->orderBy('count_posting desc')
            ->limit(5);
		$command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;
    }

      public function getTopDepartment(){
        $query = new Query;
        $query
            ->select('posting.owner_id, posting.owner_name, COUNT(department) AS count_department, employee.person_id, employee.nik, employee.nama, employee.title,employee.department')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->groupBy('department')
            ->orderBy('count_department desc')
            ->limit(5);
        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;
    }


     public function getTopGroup(){
        $query = new Query;
        $query
            ->select('posting.owner_id, posting.owner_name, COUNT(bgroup) AS count_posting, employee.bgroup,employee.person_id, employee.nik, employee.nama, employee.title,employee.department')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->groupBy('employee.bgroup')
            ->orderBy('count_posting desc')
            ->limit(5);
        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;

    }

     public function getTopDivision(){
        $query = new Query;
        $query
            ->select('posting.owner_id, posting.owner_name, COUNT(division) AS count_division, employee.person_id, employee.nik, employee.nama, employee.title,employee.department,division')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->groupBy('division')
            ->orderBy('count_division desc')
            ->limit(5);
        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;
    }

    public function getFromBGroup(){
        
        $query = new Query;
        $query
            ->select('posting.owner_id, posting.owner_name, COUNT(bgroup) AS count_bgroup,employee.person_id, employee.nik, employee.nama, employee.title,employee.department,employee.division,employee.bgroup')
            ->from('posting')
            ->innerJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->groupBy('bgroup')
            ->orderBy('count_bgroup desc');

        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;

    }
    public function getFromDivision(){
        $query = new Query;
        $query
            ->select('posting.owner_id, posting.owner_name, COUNT(owner_id) AS count_division, employee.person_id, employee.nik, employee.nama, employee.title,employee.department,employee.division,employee.bgroup')
            ->from('posting')
            ->innerJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->groupBy('division')
            ->orderBy('count_division desc');

            
        $command = $query->createCommand();
        $model = $command->queryAll();
        return $model;
    }


    public function getFromDepartment(){
        $query = new Query;
        $query
            ->select('posting.owner_id, posting.owner_name, COUNT(department) AS count_department, employee.person_id, employee.nik, nama, title,department,division,bgroup')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)            
            ->groupBy('department')
            ->orderBy('count_department desc');
            
        
        
        $countQuery = clone $query;
        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;
                
    }
    
    // group created
    public function getFromGroupCreated(){
        $query = new Query;
        $query
            ->select('owner_id, owner_name, COUNT(group_name) AS count_group, posting.group_name,employee.person_id, employee.nik, employee.nama, employee.title,employee.department')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)
            ->andWhere(['!=','id_group',0])
            ->groupBy('group_name');
        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;

    }

    
    public function getFromPosting(){
       $query = new Query;
		$query
			->select('*')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date);
		$command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;
                
    }

    public function getMostData($flag)
    {
        $orderBy = ($flag == "comment") ? "comment_count desc" : (($flag == "viewer") ? "views_count desc" : "like_count desc");
        $posting_all = Posting::find()
                        ->where($this->where_date)
                        ->orderBy($orderBy)
                        ->limit(3)
                        ->all();
        return $posting_all;  
    }

     public function getPosting(){
        $query = new Query;
        $query
            ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($this->where_date)         
            ->orderBy('posting.created_at DESC');

        $command = $query->createCommand();
        $model = $command->queryAll();
        
        return $model;
                
    }

    public function getListposting()
    {
        $request = Yii::$app->request;
            $depart=$request->post('department');
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
         if(empty($depart)){
            $where.="";
        }
        elseif(!empty($depart))
        {
            $where.=" and employee.department='$depart' ";
        }

         if(empty($start_date) && empty($end_date)){
            $where.="";
         }else if(!empty($start_date) && !empty($end_date)){
            $where.=" and (posting.created_at>='$start_date 00:00:01' and posting.created_at<='$end_date 23:59:59')";
         }else if(empty($start_date)){
            $where.=" and (posting.created_at >='$end_date 00:00:01' and posting.created_at <='$end_date 23:59:59')";
         }else if(empty($end_date)){
            $where.=" and (posting.created_at >='$start_date 00:00:01' and posting.created_at <='$start_date 23:59:59')";
         }
         $query = new Query;
        $query
            ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where($where)         
            ->orderBy('created_at desc');

        $command = $query->createCommand();
        $model = $command->queryAll();
         return $model;
        }elseif($depart){
            $where.=" employee.department ='$depart' ";
            if(empty($start_date) && empty($end_date)){
                $where.="";
            }else if(!empty($start_date) && !empty($end_date)){
                $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59' ";
            }else if(empty($start_date)){
                $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59' ";
            }else if(empty($end_date)){
                $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59' ";
            }
            
            $query = new Query;
            $query
                ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
                ->from('posting')
                ->leftJoin('employee','employee.person_id = posting.owner_id')
                ->where($where)         
                ->orderBy('created_at desc');
    
            $command = $query->createCommand();
            $model = $command->queryAll();
             return $model;

        }
        else{
            if(empty($start_date) && empty($end_date)){
                $where.="";
            }else if(!empty($start_date) && !empty($end_date)){
                $where.="posting.created_at>='$start_date 00:00:01' and posting.created_at<='$end_date 23:59:59'";
            }else if(empty($start_date)){
                $where.="posting.created_at >='$end_date 00:00:01' and posting.created_at <='$end_date 23:59:59'";
            }else if(empty($end_date)){
                $where.="posting.created_at >='$start_date 00:00:01' and posting.created_at <='$start_date 23:59:59'";
            }
            
            $query = new Query;
            $query
                ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
                ->from('posting')
                ->leftJoin('employee','employee.person_id = posting.owner_id')
                ->where($where)         
                ->orderBy('created_at desc');
    
            $command = $query->createCommand();
            $model = $command->queryAll();
             return $model;
  
        }

    }

    public function filterDateview()
    {
        $request = Yii::$app->request;
        $start_date=$request->post('start_date');
        $end_date=$request->post('end_date');
        $where="";
        if(empty($start_date) && empty($end_date)){
           $where.="";
        }else if(!empty($start_date) && !empty($end_date)){
           $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
        }else if(empty($start_date)){
           $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
        }else if(empty($end_date)){
           $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
        }

        $result = Posting::find()->where($where)->orderBy('created_at desc')->all();
        return $result;
    }

    public function filterDatecomment()
    {
        $request = Yii::$app->request;
         $start_date=$request->post('start_date');
         $end_date=$request->post('end_date');
         $where="";
         if(empty($start_date) && empty($end_date)){
            $where.="";
         }else if(!empty($start_date) && !empty($end_date)){
            $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
         }else if(empty($start_date)){
            $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
         }else if(empty($end_date)){
            $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
         }

         $result = Posting::find()->where($where)->orderBy('created_at desc')->all();
         return $result;
    }

    public function filterDatelike()
    {
        $request = Yii::$app->request;
         $start_date=$request->post('start_date');
         $end_date=$request->post('end_date');
         $where="";
         if(empty($start_date) && empty($end_date)){
            $where.="";
         }else if(!empty($start_date) && !empty($end_date)){
            $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
         }else if(empty($start_date)){
            $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
         }else if(empty($end_date)){
            $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
         }

         $result = Posting::find()->where($where)->orderBy('created_at desc')->all();
         return $result;
    }
    public function filterDateindividu()
    {
        $request = Yii::$app->request;
        $start_date=$request->post('start_date');
        $end_date=$request->post('end_date');
        $where="";
        if(empty($start_date) && empty($end_date)){
           $where.="";
        }else if(!empty($start_date) && !empty($end_date)){
           $where.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
        }else if(empty($start_date)){
           $where.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
        }else if(empty($end_date)){
           $where.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
        }
         $query = new Query;
         $query
             ->select('posting.owner_id, posting.owner_name, COUNT(owner_name) AS count_posting, employee.person_id, employee.nik, employee.nama, employee.title,employee.division,employee.department')
             ->from('posting')
             ->leftJoin('employee','employee.person_id = posting.owner_id')
             ->where($where)  
             ->groupBy('owner_name')
             ->orderBy('count_posting desc')
             ->limit(5);
         $command = $query->createCommand();
         $model = $command->queryAll();
        return $model;
    }

    
    public function getPostingdateindividu($id,$flag,$start_date,$end_date)
    {
        $start_date=$start_date;
        $end_date=$end_date;
        $result="";
            if(empty($start_date) && empty($end_date)){
                $result.="";
             }else if(!empty($start_date) && !empty($end_date)){
                $result.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
             }else if(empty($start_date)){
                $result.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
             }else if(empty($end_date)){
                $result.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
             }

      
        $query = new Query;
        $query
            ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where(['=','posting.owner_id',$id])    
            ->andWhere($result)
            ->orderBy('created_at desc');

        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public function getPostingindividu($id,$flag)
    {
       
        if($flag == "today"){
            $start_date = date("Y-m-d 00:00:01");
            $end_date = date("Y-m-d 23:59:59" );
            $result="(created_at BETWEEN '".$start_date."' AND '".$end_date."')";
        }elseif($flag == "week"){
            $start_date = date('Y-m-d 00:00:01', strtotime("monday this week"));
            $end_date = date('Y-m-d 23:59:59', strtotime("sunday this week"));
            $result="(created_at BETWEEN '".$start_date."' AND '".$end_date."')";
        }elseif($flag == "month"){
            $start_date = date("Y-m-01 00:00:01");
            $end_date = date("Y-m-t 23:59:59");
            $result="(created_at BETWEEN '".$start_date."' AND '".$end_date."')";
        }elseif($flag== "filter"){
            $request = Yii::$app->request;
            $start_date=$request->post('date_start');
            $end_date=$request->post('date_end');
        // $posting->where_date = "(created_at>='$start_date' AND created_at<='$end_date')";
          
            $result="";
            if(empty($start_date) && empty($end_date)){
                $result.="";
             }else if(!empty($start_date) && !empty($end_date)){
                $result.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
             }else if(empty($start_date)){
                $result.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
             }else if(empty($end_date)){
                $result.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
             }

        }

        $query = new Query;
        $query
            ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where(['=','posting.owner_id',$id])    
            ->andWhere($result)
            ->orderBy('created_at desc');

        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    public function getPostingindividureport($id,$start_date,$end_date)
    {
            $result="";
            if(empty($start_date) && empty($end_date)){
                $result.="";
             }else if(!empty($start_date) && !empty($end_date)){
                $result.="created_at>='$start_date 00:00:01' and created_at<='$end_date 23:59:59'";
             }else if(empty($start_date)){
                $result.="created_at >='$end_date 00:00:01' and created_at <='$end_date 23:59:59'";
             }else if(empty($end_date)){
                $result.="created_at >='$start_date 00:00:01' and created_at <='$start_date 23:59:59'";
             }


        $query = new Query;
        $query
            ->select('posting.id_posting,posting.owner_id,posting.owner_name,posting.group_name,posting.caption,employee.division,employee.department,posting.views_count,posting.like_count,posting.comment_count,posting.created_at')
            ->from('posting')
            ->leftJoin('employee','employee.person_id = posting.owner_id')
            ->where(['=','posting.owner_id',$id])    
            ->andWhere($result)
            ->orderBy('created_at desc');

        $command = $query->createCommand();
        $data = $command->queryAll();
        return $data;
    }

    



    

}