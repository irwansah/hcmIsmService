<?php 

use yii\helpers\Url;
use yii\helpers\Html;
use common\components\Helpers;

$this->title = 'Posts Management';  
$this->params['breadcrumbs'][] = $this->title;

?>

<input type="hidden" id="url-posting-data" value="<?= Url::to(['posting/index-individu']) ?>"/>
<input type="hidden" id="url-filter-data" value="<?= Url::to(['posting/filterdate-individu']) ?>"/>

<?php $param=Yii::$app->request->csrfParam; ?>
<?php $token=Yii::$app->request->csrfToken; ?>

<div id="loader" style="display:block;"></div>
<div id="posting-individu">


</div>
<!-- JS SCRIPT -->
<?php
$script = <<< JS
    $(document).ready(function(){
    // ajax call filter hastag
    $(document).on('click', '#btn-filter', function (e) {
           let start_date=$('#start_date').val();
           let end_date=$('#end_date').val();
           $.ajax({
                url:$("#url-filter-data").val(),
                type: "POST",
                data:{"start_date":start_date,"end_date":end_date,"$param":"$token"},
                success:function(data){
                 
                   $("#loader").fadeOut(200);
                    $("#posting-individu").html(data);
                    $('#start_date').val(start_date);
                    $('#end_date').val(end_date);
                    $("#data-tables").DataTable({
                      "order": [[3, "desc" ]],
                      dom: 'Bfrtip',
                      buttons: [
                          'excel', 'pdf', 'print'
                      ],responsive: true
                    });
                }
            });
          
        });
    function defaultFilter(){
          $.ajax({
                url:$("#url-posting-data").val(),
                success:function(data){
                 $("#loader").fadeOut(200);                      
                    $("#posting-individu").html(data);
                    $("#data-tables").DataTable({
                      "order": [[3, "desc" ]],
                      dom: 'Bfrtip',
                      buttons: [
                          'excel', 'pdf', 'print'
                      ],responsive: true

                    });
                }
            });
    }
    defaultFilter();

    });
JS;

$this->registerJs($script);
?>  