<div class="row">
     
                          <?php
                           if(!empty($mostComments)){ 
                            ?>  <div class="col-md-12">
                      <h3>Most Comment</h3>
                        <br>
                        <div class="row">
                          <?php
                     foreach($mostComments as $row){
                        
                       $url = Helpers::PICTURE_URL.$row['owner_id'];
                      $arrayUrl = @get_headers($url); 
                      if(strpos($arrayUrl[0], "200")) { 
                          $ava = $url;
                      }  
                      else {
                          $ava = Url::base()."/ism/assets/img/default-avatar.png";
                      }

                     ?>
                   
                           <div class="col-md-4">
                            <div class="card card-product">
                              <div class="card-header card-header-image" data-header-animation="true">
                                <a href="">
                          <?php
                            $src = 'data:'.$row->postingDetail['file_type'].';base64,'.$row->postingDetail['file_content'].'';
                            echo '<img src="'.$src.'" alt="" width=200 height=175/>';?>
                                </a>
                              </div>
                              <div class="card-body">
                                <div class="card-actions text-center">
                                  <button type="button" class="btn btn-danger btn-link fix-broken-card">
                                    <i class="material-icons">build</i> Fix Header!
                                  </button>
                                  
                                </div>
                            
                                <h4 class="card-title">
                                    <div class="photo">
                                  <img src="<?= $ava ?>" style="width: 40px;border-radius: 100%;float: left;border:2px solid #999;margin-left: 10px;margin-top:5px;">
                                </div>
                                  <a href="#pablo">   <?php echo $row->owner_name;?></a>
                                </h4>
                                <div class="card-description">
                                 <?php echo $row->caption;?>
                                </div>
                              </div>
                              <div class="card-footer">
                                 <div class="stats">
                                  
                                  <p class="card-category"><i class="material-icons">
                                   <?= Html::a('favorite', ['posting/listlike', 'id' => $row->id_posting]) ?></i> <?php echo $row->like_count;?></p>
                                </div>
                                <div class="stats">
                                  <p class="card-category"><i class="material-icons"><?= Html::a('comment', ['posting/listcomment', 'id' => $row->id_posting]) ?></i> <?php echo $row->comment_count;?></p>
                                </div>
                              </div>
                            </div>
                          </div>

                           <?php
                         }}else{

                         }
                          ?>
                           </div>
                    </div>
                       <?php
                       if(!empty($mostLikes)){
                        ?>
                          <div class="col-md-12">
                     <h3>Most Likes</h3>
                        <br>
                        <div class="row">
                    <?php
                     foreach($mostLikes as $row){
                     $url = Helpers::PICTURE_URL.$row['owner_id'];
                      $arrayUrl = @get_headers($url); 
                      if(strpos($arrayUrl[0], "200")) { 
                          $ava = $url;
                      }  
                      else {
                          $ava = Url::base()."/ism/assets/img/default-avatar.png";
                      }
                     ?>

                  
                          <div class="col-md-4">
                            <div class="card card-product">
                              <div class="card-header card-header-image" data-header-animation="true">
                                <a href="">
                          <?php
                            $src = 'data:'.$row->postingDetail['file_type'].';base64,'.$row->postingDetail['file_content'].'';
                            echo '<img src="'.$src.'" alt="" width=200 height=175/>';?>
                                </a>
                              </div>
                              <div class="card-body">
                                <div class="card-actions text-center">
                                  <button type="button" class="btn btn-danger btn-link fix-broken-card">
                                    <i class="material-icons">build</i> Fix Header!
                                  </button>
                                  
                                </div>
                            
                                <h4 class="card-title">
                                  <a href="#pablo">   <?php echo $row->owner_name;?></a>
                                </h4>
                                <div class="card-description">
                                 <?php echo $row->caption;?>
                                </div>
                              </div>
                              <div class="card-footer">
                                 <div class="stats">
                                  
                                  <p class="card-category"><i class="material-icons">
                                   <?= Html::a('favorite', ['posting/listlike', 'id' => $row->id_posting]) ?></i> <?php echo $row->like_count;?></p>
                                </div>
                                <div class="stats">
                                  <p class="card-category"><i class="material-icons"><?= Html::a('comment', ['posting/listcomment', 'id' => $row->id_posting]) ?></i> <?php echo $row->comment_count;?></p>
                                </div>
                              </div>
                            </div>
                          </div>

                           <?php
                         }} else
                         {

                         }
                          ?>
                           </div>
                    </div>
            

                 
                          <?php
                      if(!empty($mostViewers)){
                        ?> 
                          <div class="col-md-12">
                     <h3>Most Viewers</h3>
                        <br>
                        <div class="row">
                          <?php
                     foreach($mostViewers as $row){

                     ?>

                         
                          <div class="col-md-4">
                            <div class="card card-product">
                              <div class="card-header card-header-image" data-header-animation="true">
                                <a href="">
                          <?php
                            $src = 'data:'.$row->postingDetail['file_type'].';base64,'.$row->postingDetail['file_content'].'';
                            echo '<img src="'.$src.'" alt="" width=200 height=175/>';?>
                                </a>
                              </div>
                              <div class="card-body">
                                <div class="card-actions text-center">
                                  <button type="button" class="btn btn-danger btn-link fix-broken-card">
                                    <i class="material-icons">build</i> Fix Header!
                                  </button>
                                  
                                </div>
                            
                                <h4 class="card-title">
                                  <a href="#pablo">   <?php echo $row->owner_name;?></a>
                                </h4>
                                <div class="card-description">
                                 <?php echo $row->caption;?>
                                </div>
                              </div>
                              <div class="card-footer">
                                 <div class="stats">
                                  
                                  <p class="card-category"><i class="material-icons">
                                   <?= Html::a('favorite', ['posting/listlike', 'id' => $row->id_posting]) ?></i> <?php echo $row->like_count;?></p>
                                </div>
                                <div class="stats">
                                  <p class="card-category"><i class="material-icons"><?= Html::a('comment', ['posting/listcomment', 'id' => $row->id_posting]) ?></i> <?php echo $row->comment_count;?></p>
                                </div>
                              </div>
                            </div>
                          </div>

                           <?php
                         } }else
                         {

                         }
                          ?>
                           </div>
                    </div>
                </div>