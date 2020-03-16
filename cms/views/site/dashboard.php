<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\components\Helpers;
$this->title = 'Dashboard';	
$this->params['breadcrumbs'][] = $this->title;
?>

<!-- field hidden -->
<input type="hidden" id="url-dashboard-data" value="<?= Url::to(['site/dashboard']) ?>"/>
<input type="hidden" id="url-filter-data" value="<?= Url::to(['site/filter']) ?>"/>


<?php $param= Yii::$app->request->csrfParam; ?>
<?php $token=Yii::$app->request->csrfToken; ?>
<div id="loader" style="display:block;"></div>
<div id="dashboard-data"></div>

<!-- JS SCRIPT -->
<?php
$script = <<< JS
    $(document).ready(function(){
        
      
        $(document).on('click', '#btn-datetime', function (e) {
            let ds=$('#date_start').val();
            let de=$('#date_end').val();
            $.ajax({
                url:$("#url-filter-data").val(),
                type: "POST",
                data:{"date_start":ds,"date_end":de,"$param":"$token"},
                success:function(data){
                console.log(data);  
                    $("#loader").fadeOut('fast');
                    $("#dashboard-data").html(data);
                    // $("#select2").select2();
                    $('#date_start').val(ds);
                    $('#date_end').val(de);
                }
            });
        });


        //inisiasi filter dashboard today
        function defaultFilter(){
            $("#dashboard-data").html("");
            $.ajax({
                url:$("#url-dashboard-data").val(),
                type: "GET",
                data:"flag=today",
                success:function(data){
                    $("#loader").fadeOut('fast');
                    $("#dashboard-data").html(data);
                   
                }
            });
        }

        defaultFilter();
        
    });
JS;

$this->registerJs($script);
?>    