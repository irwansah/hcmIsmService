<?php 

use yii\helpers\Url;
use yii\helpers\Html;
use common\components\Helpers;

$this->title = 'Posts Management';	
$this->params['breadcrumbs'][] = $this->title;

?>
<input type="hidden" id="url-posting-data" value="<?= Url::to(['posting/postinglist']) ?>"/>
<input type="hidden" id="url-filter-data" value="<?= Url::to(['posting/filterlist']) ?>"/>

<?php $param=Yii::$app->request->csrfParam; ?>
<?php $token=Yii::$app->request->csrfToken; ?>


<div id="posting-data">


</div>
<!-- JS SCRIPT -->
<?php
$script = <<< JS
    $(document).ready(function(){
		// ajax call filter hastag
    $(document).on('click', '#btn-filter-tag', function (e) {
           let department=$('#department-input').val();
           let tag=$('#tags-input').val();
           let start_date=$('#start_date_tag').val();
           let end_date=$('#end_date_tag').val();
           $.ajax({
                url:$("#url-filter-data").val(),
                type: "POST",
                data:{"department":department,"tag":tag,"start_date":start_date,"end_date":end_date,"$param":"$token"},
                success:function(data){
                    $("#posting-data").html(data);
                    $('#department-input').val(department);
                    $('#tags-input').val(tag);
                    $('#start_date_tag').val(start_date);
                    $('#end_date_tag').val(end_date);
                      $("#data-tables").DataTable({
                      "order": [[10, "desc" ]],
                      dom: 'Bfrtip',
                      buttons: [
                          'excel', 'pdf', 'print'
                      ],
                     
                      responsive: true
                    });
                }
            });
          
        });
    function defaultFilter(){
          $.ajax({
                url:$("#url-posting-data").val(),
                success:function(data){                      
                    $("#posting-data").html(data);
                      $("#data-tables").DataTable({
                      "order": [[10, "desc" ]],
                      dom: 'Bfrtip',
                      buttons: [
                          'excel', 'pdf', 'print'
                      ],
                      
                      responsive: true
                    });
                
                }
            });
    }
    defaultFilter();

		});
JS;

$this->registerJs($script);
?>  