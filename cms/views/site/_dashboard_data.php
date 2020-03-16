<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\components\Helpers;
use dosamigos\highcharts\HighCharts;

?>

<br>
<!-- button in header start -->   
<div class="row">
  <div class="col-md-12">
<div class="card" style="margin-top:-20px;">
  <div class="card-header">
     <h4 class="card-title">Filter Dashboard</h4>
  </div>
  <div class="card-body">
  <div class="row" style="margin-top:-20px;"> 
          <div class="col-lg-7">
                <div class="form-inline">
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                    </div>
                    <input type="date" class="form-control ml-2" name="date_start" id="date_start">
                  </div>
                  <div class="input-group mb-3 ">
                    <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                    </div>
                    <input type="date" class="form-control" name="date_end" id="date_end">
                  </div>  
                  <div class="input-group mb-3">
                    <button class="btn btn-primary btn-border btn-round ml-3" id="btn-datetime" type="button"><i class="fa fa-search"></i></button>
                  </div>  

                </div>
          </div>

          <div class="col-lg-5 float-right">
                  <!-- <a href="#" id="btnToday" class="<?php if($flag == "filter"){ echo "btn btn-round mr-2"; }else{ echo "btn btn-primary btn-border btn-round mr-2";} ?>">Filter</a> -->
                  <a href="#" id="btnToday" class="<?php if($flag == "today"){ echo "btn btn-round mr-2"; }else{ echo "btn btn-primary btn-border btn-round mr-2";} ?>">Today</a>
                  <a href="#" id="btnWeek" class="<?php if($flag == "week"){ echo "btn btn-round mr-2"; }else{ echo "btn btn-primary btn-border btn-round mr-2";} ?>">Week</a>
                  <a href="#" id="btnMonth" class="<?php if($flag == "month"){ echo "btn btn-round mr-2"; }else{ echo "btn btn-primary btn-border btn-round mr-2";} ?>">Month</a>        
          </div>


  </div>
  </div></div></div></div>
<!-- button in header end --> 
    <div class="row" style="margin-top:-22px;">
                          <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                              <div class="card-header card-header-danger card-header-icon">
                                <div class="card-icon">
                                  <i class="material-icons">favorite</i>
                                </div>
                                <p class="card-category">Total of Likes</p>
                                 <h3 class="card-title"><?= $count_likes ?></h3>
                              </div>
                              <div class="card-footer">
                                <div class="stats">
                                 
                                </div>
                              </div>
                            </div>
                          </div>

                           <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                              <div class="card-header card-header-danger card-header-icon">
                                <div class="card-icon">
                                  <i class="material-icons">comment</i>
                                </div>
                             <p class="card-category">Total of Comments</p>
                            <h3 class="card-title"><?= $count_comments ?></h3>
                              </div>
                              <div class="card-footer">
                                <div class="stats">
                                 
                                </div>
                              </div>
                            </div>
                          </div>

                           <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                              <div class="card-header card-header-danger card-header-icon">
                                <div class="card-icon">
                                  <i class="material-icons">create</i>
                                </div>
                               <p class="card-category">Total Post without Link</p>
                            <h3 class="card-title"><?= $count_posting ?></h3>
                              </div>
                              <div class="card-footer">
                                <div class="stats">
                                 
                                </div>
                              </div>
                            </div>
                          </div>

                           <div class="col-lg-3 col-md-6 col-sm-6">
                            <div class="card card-stats">
                              <div class="card-header card-header-danger card-header-icon">
                                <div class="card-icon">
                                  <i class="material-icons">link</i>
                                </div>
                                <p class="card-category">Total Post with Link</p>
                            <h3 class="card-title"><?= $count_link ?></h3>
                              </div>
                              <div class="card-footer">
                                <div class="stats">
                                 
                                </div>
                              </div>
                            </div>
                          </div>
</div>
<!-- row baris 1 start -->


<div class="row" style="margin-top:-22px;">
    <!-- trending topics -->
    <div class="col-md-6">
        <div class="card" style="min-height:370px;">
       <div class="card-header card-header-succes card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">signal_cellular_alt</i>
                    </div>
                    <h4 class="card-title">Trending Topic</h4><button type="button" class="btn btn-primary btn-sm ml-3 float-right" style="margin-top:-30px;" data-toggle="modal" data-target="#ModalTrending">chart</button> 
                  </div>
             <div class="card-body pb-0">
                <?php 
                    if(!empty($hastagData)){ 
                        foreach($hastagData as $caption => $value){
                ?>
                  <div class="row" style="margin-bottom: 1em;">
                        <div class="col-md-9">
                                <h4 class="fw-bold mb-1" > <?=$caption ?></h4>
                            </div>
                            <div class="col-md-3 text-right">
                                <h3 class="text-info fw-bold" style="margin-top: -4px;"><?= $value ?></h3>
                            </div>
                        </div>
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>
    <!-- top activity -->
    <div class="col-md-6">
        <div class="card" style="min-height:370px;">
       <div class="card-header card-header-succes card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">signal_cellular_alt</i>
                    </div>
                    <h4 class="card-title">Top Activity Person</h4>
                    <button type="button" class="btn btn-primary btn-sm ml-3 float-right" style="margin-top:-30px;" data-toggle="modal" data-target="#ModalPerson">chart</button> 
                  </div>
            <div class="card-body pb-0">
                <?php 
                    if(!empty($topActivity)){ 
                        foreach($topActivity as $emp){
                     ?>
                       <div class="row" style="margin-bottom: 1em;">
                     <div class="col-md-9">
                               
                                <?php if($flag == "filter"){?>
                                 <h4 class="fw-bold mb-1">  <?= Html::a($emp['owner_name'] , ['posting/postingdate-individu', 'id' => $emp['owner_id'], 'flag' => $flag ,'start_date' =>$startdate , 'end_date' => $enddate],['style' => ['color' => 'black']]) ?></h4>
                                <?php } else{ ?>
                                <h4 class="fw-bold mb-1"> <?= Html::a($emp['owner_name'] , ['posting/posting-individu', 'id' => $emp['owner_id'], 'flag' => $flag],['style' => ['color' => 'black']]) ?></h4>
                                <?php } ?>
                                
                               
                                  <small class="text-muted" style="font-size:8pt;"><?= $emp['department'] ?></small>
                            </div>
                            <div class="col-md-3 text-right">
                                <h3 class="text-info fw-bold" style="margin-top: -4px;"><?= $emp['count_posting'] ?> <small class="text-muted" style="font-size:8pt;">Posts</small></h3>
                            </div>
                        </div>
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>
  </div>
  <div class="row" style="margin-top:-22px;">
  <div class="col-md-4">
        <div class="card" style="min-height:370px;">
       <div class="card-header card-header-danger card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">assessment</i>
                    </div>
                    <h4 class="card-title">Top Activity Department</h4>
                    <span class="badge badge-primary ml-3 float-right" style="margin-top:-30px;cursor:pointer" data-toggle="modal" data-target="#ModalDepartment">chart</span> 
                    </div>
                    
                    
            <div class="card-body pb-0">
                <?php 
                    if(!empty($topDepartment)){ 
                        foreach($topDepartment as $emp){
                     ?>
                       <div class="row" style="margin-bottom: 1em;">
                       <div class="col-md-9">
                                <h4 class="fw-bold mb-1" ><?= $emp['department'] ?></h4>
                            </div>
                            <div class="col-md-3 text-right">
                                <h3 class="text-info fw-bold" style="margin-top: -6px;"><?= $emp['count_department'] ?> <small class="text-muted" style="font-size:8pt;">Posts</small></h3>
                            </div>
                        </div>
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>

<div class="col-md-4">
        <div class="card" style="min-height:370px;">
       <div class="card-header card-header-danger card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">assessment</i>
                    </div>
                    <h4 class="card-title">Top Activity Division</h4> 
                    <span class="badge badge-primary ml-3 float-right" style="margin-top:-30px;cursor:pointer" data-toggle="modal" data-target="#ModalDivision">chart</span> 
                  </div>
            <div class="card-body pb-0">
                <?php 
                    if(!empty($topDivision)){ 
                        foreach($topDivision as $emp){
                     ?>
                        <div class="row" style="margin-bottom: 1em;">
                            <div class="col-md-9">
                                <h4 class="fw-bold mb-1" ><?= $emp['division'] ?></h4>
                            </div>
                            <div class="col-md-3 text-right">
                                <h3 class="text-info fw-bold ml-30" style="margin-top: -6px;"><?= $emp['count_division'] ?> <small class="text-muted" style="font-size:8pt;">Posts</small></h3>
                            </div>
                        </div>
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>


   <div class="col-md-4">
           <div class="card" style="min-height:370px;">
       <div class="card-header card-header-danger card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">assessment</i>
                    </div>
                    <h4 class="card-title">Top Activity Group</h4>
                    <span class="badge badge-primary ml-3 float-right" style="margin-top:-30px;cursor:pointer" data-toggle="modal" data-target="#ModalGroup">chart</span> 
                  </div>
     
            <div class="card-body pb-0">
                <?php 
                    if(!empty($topGroup)){ 
                        foreach($topGroup as $emp){
                     ?>
                       <div class="row" style="margin-bottom: 1em;">
                     <div class="col-md-9">
                                <h4 class="fw-bold mb-1" ><?= $emp['bgroup'] ?></h4>
                                  
                            </div>
                            <div class="col-md-3 text-right">
                                <h3 class="text-info fw-bold" style="margin-top: -4px;"><?= $emp['count_posting'] ?> <small class="text-muted" style="font-size:8pt;">Posts</small></h3>
                            </div>
                        </div>



                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div></div>
    </div>
<div class="row">
  <div class="col-md-4">
        <div class="card">
       <div class="card-header card-header-succes card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">equalizer</i>
                    </div>
                    <h4 class="card-title">Most Comment</h4>
                  </div>
            <div class="card-body pb-0">
                <?php 
                    if(!empty($mostComments)){ 
                        foreach($mostComments as $row){
                     ?>
                        <div class="d-flex " style="margin-bottom: 1em;">
                            <div class="flex-1 pt-1 ml-2 ">
                           
                                <h4 class="fw-bold mb-1"><?= $row->owner_name ?></h4>
                                <?php
                                $hasil=substr($row->caption, 0, 75);
                                ?>
                                    <small class="text-muted" style="font-size:8pt;"><?= $hasil ?></small>
                                    
                            </div>
                            <div class="d-flex ml-auto align-items-center">
                            <?php 
                            $src="";
                            if($row['type_posting']==1){
                              $src = 'data:'.$row->postingDetail['file_type'].';base64,'.$row->postingDetail['file_content'].'';
                            }elseif($row['type_posting']==2){
                              $src = Url::base()."/ism/assets/img/icons8-text.png";
                            }elseif($row['type_posting']==3){
                              $src = Url::base()."/ism/assets/img/icons8-video.png";
                            }elseif($row['type_posting']==4){

                              $ex= new SplFileInfo($row['url_content']);
                              $file_ex=$ex->getExtension();
                              if($file_ex=='xls' || $file_ex=='xlsx'){
                                $src = Url::base()."/ism/assets/img/icons8-xls.png";
                              }elseif($file_ex=='doc' || $file_ex=='docx'){
                                $src = Url::base()."/ism/assets/img/icons8-doc.png";
                              }elseif($file_ex=='ppt' || $file_ex=='pptx'){
                                $src = Url::base()."/ism/assets/img/icons8-ppt.png";
                              }elseif($file_ex=='pdf' || $file_ex=='xps'){
                                $src = Url::base()."/ism/assets/img/icons8-pdf.png";
                              }
                              
                            }elseif($row['type_posting']==5){
                              $src = Url::base()."/ism/assets/img/icons8-link.png";
                            }
                            ?>
                            <img src="<?php echo $src?>" style="width:120px;min-height:120px;border-radius:100%">
                            </div>
                        </div>
                         
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>

 <div class="col-md-4">
        <div class="card">
       <div class="card-header card-header-succes card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">equalizer</i>
                    </div>
                    <h4 class="card-title">Most Views</h4>
                  </div>
            <div class="card-body pb-0">
                <?php 
                    if(!empty($mostViewers)){ 
                        foreach($mostViewers as $row){
                     ?>
                        <div class="d-flex" style="margin-bottom: 1em;">
                            <div class="flex-1 pt-1 ml-2">
                           
                                <h4 class="fw-bold mb-1"><?= $row->owner_name ?></h4>
                                <?php
                                $hasil=substr($row->caption, 0, 75);
                                ?> 
                                    <small class="text-muted" style="font-size:8pt;"><?= $hasil?></small>
                                     
                            </div>
                            <div class="d-flex ml-auto align-items-center">
                            <?php 
                            $src="";
                            if($row['type_posting']==1){
                              $src = 'data:'.$row->postingDetail['file_type'].';base64,'.$row->postingDetail['file_content'].'';
                            }elseif($row['type_posting']==2){
                              $src = Url::base()."/ism/assets/img/icons8-text.png";
                            }elseif($row['type_posting']==3){
                              $src = Url::base()."/ism/assets/img/icons8-video.png";
                            }elseif($row['type_posting']==4){

                              $ex= new SplFileInfo($row['url_content']);
                              $file_ex=$ex->getExtension();
                              if($file_ex=='xls' || $file_ex=='xlsx'){
                                $src = Url::base()."/ism/assets/img/icons8-xls.png";
                              }elseif($file_ex=='doc' || $file_ex=='docx'){
                                $src = Url::base()."/ism/assets/img/icons8-doc.png";
                              }elseif($file_ex=='ppt' || $file_ex=='pptx'){
                                $src = Url::base()."/ism/assets/img/icons8-ppt.png";
                              }elseif($file_ex=='pdf' || $file_ex=='xps'){
                                $src = Url::base()."/ism/assets/img/icons8-pdf.png";
                              }
                              
                            }elseif($row['type_posting']==5){
                              $src = Url::base()."/ism/assets/img/icons8-link.png";
                            }
                            ?>
                            <img src="<?php echo $src?>" style="width:120px;min-height:120px;border-radius:100%">
                            
                            </div>
                        </div>
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>


 <div class="col-md-4">
        <div class="card">
       <div class="card-header card-header-succes card-header-icon">
                <div class="card-icon">
                      <i class="material-icons">equalizer</i>
                    </div>
                    <h4 class="card-title">Most Likes</h4>
                  </div>
            <div class="card-body pb-0">
                <?php 
                    if(!empty($mostLikes)){ 
                        foreach($mostLikes as $row){
                     ?>
                        <div class="d-flex" style="margin-bottom: 1em;">
                            <div class="flex-1 pt-1 ml-2">
                           
                                <h4 class="fw-bold mb-1"><?= $row->owner_name ?></h4>
                                <?php
                                $hasil=substr($row->caption, 0, 75);
                                ?>
                                    <small class="text-muted" style="font-size:8pt;"><?= $hasil?></small>
            
                            </div>
                            <div class="d-flex ml-auto align-items-center">
                            <?php 
                            $src="";
                            if($row['type_posting']==1){
                              $src = 'data:'.$row->postingDetail['file_type'].';base64,'.$row->postingDetail['file_content'].'';
                            }elseif($row['type_posting']==2){
                              $src = Url::base()."/ism/assets/img/icons8-text.png";
                            }elseif($row['type_posting']==3){
                              $src = Url::base()."/ism/assets/img/icons8-video.png";
                            }elseif($row['type_posting']==4){

                              $ex= new SplFileInfo($row['url_content']);
                              $file_ex=$ex->getExtension();
                              if($file_ex=='xls' || $file_ex=='xlsx'){
                                $src = Url::base()."/ism/assets/img/icons8-xls.png";
                              }elseif($file_ex=='doc' || $file_ex=='docx'){
                                $src = Url::base()."/ism/assets/img/icons8-doc.png";
                              }elseif($file_ex=='ppt' || $file_ex=='pptx'){
                                $src = Url::base()."/ism/assets/img/icons8-ppt.png";
                              }elseif($file_ex=='pdf' || $file_ex=='xps'){
                                $src = Url::base()."/ism/assets/img/icons8-pdf.png";
                              }
                              
                            }elseif($row['type_posting']==5){
                              $src = Url::base()."/ism/assets/img/icons8-link.png";
                            }
                            ?>
                            <img src="<?php echo $src?>" style="width:120px;min-height:120px;border-radius:100%">
                            </div>
                        </div>
                <?php }}else{ ?>
                    <h4 class="text-center">-- No Data Found --</h4>
                <?php } ?>
                <br>
            </div>
        </div>
    </div>
</div>

</div>


 <!-- MODAL CHART TRENDING -->
                     
<!-- The Modal -->
                <div class="modal fade" id="ModalTrending">
                  <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    
                      <!-- Modal Header -->
                      <div class="modal-header">
                        <h4 class="modal-title"> </h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      
                      <!-- Modal body -->
                      <div class="modal-body">
                        <?php
                       $res1=[];

                foreach($hastagData as $caption => $value){
                    $text11[0]= ($caption);
                    $text12[]= ($caption);
                    $res1[]= array('type'=> 'column', 'name' =>$caption, 'data' => array((int)$value));
                 }
                 echo
                 Highcharts::widget([
                    'clientOptions' => [
                       'chart'=>[
                          'type'=>'bar'
                       ],
                       'title' => ['text' => 'Trending Topic'],
                       'xAxis' => [
                          'categories' => ['Hashtag']
                       ],
                       'yAxis' => [
                          'title' => ['text' => 'Count Posting']
                       ],
                       'series' => $res1
                    ]
                 ]);
              ?>
                      </div>
                      
                      <!-- Modal footer -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                      </div>
                      
                    </div>
                  </div>
                </div>


 <!-- MODAL CHART PERSON -->
                     
<!-- The Modal -->
                <div class="modal fade" id="ModalPerson">
                  <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    
                      <!-- Modal Header -->
                      <div class="modal-header">
                        <h4 class="modal-title"> </h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      
                      <!-- Modal body -->
                      <div class="modal-body">
                        <?php

                       $res2=[];

                foreach($topActivity as $values){
                    $text21[0]= ($values['owner_name']);
                    $text22[]= ($values['owner_name']);
                    $res2[]= array('type'=> 'column', 'name' =>$values['owner_name'].' - '.$values['department'] , 'data' => array((int)$values['count_posting']));
                 }
                 echo
                 Highcharts::widget([
                    'clientOptions' => [
                       'chart'=>[
                          'type'=>'bar'
                       ],
                       'title' => ['text' => 'Top Activity Person'],
                       'xAxis' => [
                          'categories' => ['Posting']
                       ],
                       'yAxis' => [
                          'title' => ['text' => 'Count Posting Person']
                       ],
                       'series' => $res2
                    ]
                 ]);
              ?>
                      </div>
                      
                      <!-- Modal footer -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                      </div>
                      
                    </div>
                  </div>
                </div>
<!-- END MODAL CHART Group -->


<!-- MODAL POST PERSON -->
                     
<!-- The Modal -->
<div class="modal fade" id="ModalIndividu">
                  <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    
                      <!-- Modal Header -->
                      <div class="modal-header">
                        <h4 class="modal-title"> </h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      
                      <!-- Modal body -->
                      <div class="modal-body">
                        <?php

                       $res2=[];

                foreach($topActivity as $values){
                    $text21[0]= ($values['owner_name']);
                    $text22[]= ($values['owner_name']);
                    $res2[]= array('type'=> 'column', 'name' =>$values['owner_name'].' - '.$values['department'] , 'data' => array((int)$values['count_posting']));
                 }
                 echo
                 Highcharts::widget([
                    'clientOptions' => [
                       'chart'=>[
                          'type'=>'bar'
                       ],
                       'title' => ['text' => 'Top Activity Person'],
                       'xAxis' => [
                          'categories' => ['Posting']
                       ],
                       'yAxis' => [
                          'title' => ['text' => 'Count Posting Person']
                       ],
                       'series' => $res2
                    ]
                 ]);
              ?>
                      </div>
                      
                      <!-- Modal footer -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                      </div>
                      
                    </div>
                  </div>
                </div>
<!-- END MODAL CHART Group -->


              <!-- MODAL CHART DEPARTMEN -->
              <!-- The Modal -->
                <div class="modal fade" id="ModalDepartment">
                  <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    
                      <!-- Modal Header -->
                      <div class="modal-header">
                        <h4 class="modal-title"></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                      </div>
                      <!-- Modal body -->
                      <div class="modal-body">
                        <?php
                        $res3=[];
                         foreach($topDepartment as $values){
                            $text31[0]= ($values['department']);
                            $text32[]= ($values['department']);
                            $res3[]= array('type'=> 'column', 'name' =>$values['department'], 'data' => array((int)$values['count_department']));
                         }
                         echo
                         Highcharts::widget([
                            'clientOptions' => [
                               'chart'=>[
                                  'type'=>'bar'
                               ],
                               'title' => ['text' => 'Top Department'],
                               'xAxis' => [
                                  'categories' => ['Department Name']
                               ],
                               'yAxis' => [
                                  'title' => ['text' => 'Count Posting Top Department']
                               ],
                               'series' => $res3
                            ]
                         ]);
                        ?>
                       
                    </div>
                    <!-- Modal footer -->
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                      
                    </div>
                  </div>
                </div>
              <!-- END MODAL CHART DEPARTMEN -->


            <!-- MODAL CHART DIVISION -->
            <!-- The Modal -->
              <div class="modal fade" id="ModalDivision">
                <div class="modal-dialog modal-xl">
                  <div class="modal-content">
                  
                    <!-- Modal Header -->
                    <div class="modal-header">
                      <h4 class="modal-title"></h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    
                    <!-- Modal body -->
                    <div class="modal-body">
                      <?php
                      $res4=[];
                   foreach($topDivision as $values){
                      $text41[0]= ($values['division']);
                      $text42[]= ($values['division']);
                      $res4[]= array('type'=> 'column', 'name' =>$values['division'], 'data' => array((int)$values['count_division']));
                   }
                 echo
                 Highcharts::widget([
                    'clientOptions' => [
                       'chart'=>[
                          'type'=>'bar'
                       ],
                       'title' => ['text' => 'Top Division'],
                       'xAxis' => [
                          'categories' => ['Division Name']
                       ],
                       'yAxis' => [
                          'title' => ['text' => 'Count Posting Top Division']
                       ],
                       'series' => $res4
                    ]
                 ]);
                  ?>
                      </div>
                      
                      <!-- Modal footer -->
                      <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                      </div>
                      
                    </div>
                  </div>
                </div>
              <!-- END MODAL CHART DIVISION -->


      <!-- MODAL CHART GROUP -->
                     
<!-- The Modal -->
  <div class="modal fade" id="ModalGroup">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
      
        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title"> 
</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <!-- Modal body -->
        <div class="modal-body">
          <?php
         $res5=[];
   foreach($topGroup as $values){
      $text51[0]= ($values['bgroup']);
      $text52[]= ($values['bgroup']);
      $res5[]= array('type'=> 'column', 'name' =>$values['bgroup'], 'data' => array((int)$values['count_posting']));
   }
   echo
   Highcharts::widget([
      'clientOptions' => [
         'chart'=>[
            'type'=>'bar'
         ],
         'title' => ['text' => 'Top Group'],
         'xAxis' => [
            'categories' => ['Group Name']
         ],
         'yAxis' => [
            'title' => ['text' => 'Count Posting Top Group']
         ],
         'series' => $res5
      ]
   ]);
?>
        </div>
        
        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
        </div>
        
      </div>
    </div>
  </div>
<!-- END MODAL CHART Group -->


<!-- JS SCRIPT -->
<?php

$script = <<< JS
    $(document).ready(function(){
        //inisiasi filter dashboard today
        function changeData(flag){
            $("#loader").fadeIn('fast');
            $("#dashboard-data").html("");

            $.ajax({
                url:$("#url-dashboard-data").val(),
                type: "GET",
                data:"flag="+flag,
                success:function(data){
                    $("#loader").fadeOut('fast');
                    $("#dashboard-data").html(data);

                }
            });
        }

        $("#btnToday").click(function(){
            changeData('today');
        });

        $("#btnWeek").click(function(){
            changeData('week');
        });

        $("#btnMonth").click(function(){
            changeData('month');
        });

        
    });
JS;

$this->registerJs($script);

?> 