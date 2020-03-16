<?php 

use yii\helpers\Html;
?>

<div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-danger card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Trending Topic</h4>
                </div>
                <div class="card-body">
                  <div class="toolbar">
                    <!--        Here you can write extra buttons/actions for the toolbar              -->
                  </div>
                  <div class="material-datatables">
                    <table id="data-tables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                         <tr>
                <th>Nama Hashtag</th>
                <th>Jumlah</th>
              
              
                        </tr>
                      </thead>
        <tbody>
            <?php
           foreach ($coba as $row) : ?>
            <tr>
                <td><?php echo $row['nama']; ?></td>
                <td><?php echo $row['jumlah']; ?></td>
                
               
              
            </tr>
             <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- end content-->
              </div>
              <!--  end card  -->
            </div>