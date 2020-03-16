<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Group */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="user-form">
      <div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-primary card-header-icon">
                  <div class="card-icon">
                    <i class="material-icons">assignment</i>
                  </div>
                  <h4 class="card-title">Create Data Group</h4>
                </div>
                  <div class="card-body">
                  <div class="toolbar">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'group_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'active')->textInput() ?>

    <?= $form->field($model, 'type_group')->textInput() ?>

    <?= $form->field($model, 'type_group_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'owner_id')->textInput() ?>

    <?= $form->field($model, 'owner_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'owner_email')->textInput(['maxlength' => true]) ?>

    <br>
      <h4 class="card-title">Data Member</h4>
     <div class="row form-group">
              <table id="dataTable" class='table'>
                <thead>
            <tr>
                 
                <th>Owner ID </th>
                <th>Owner Name </th>
                <th>Aksi </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="row form-group">
                   <select name="owner_id[]"  class="form-control">
                               
                                <option value="">masukan nama </option>
                                  <?php

                   foreach ($pegawai as $off)
                    { 
                      echo "<option value=$off[person_id]> $off[person_id] - $off[nama]</option>";
                    
                    }
                  ?>
                </select>
               </div></td>
                <td>
                  <input name="owner_name[]" type="text" value="" class="form-control"> </td>
              
                  <td><button type="button" value="Remove Item" onClick="deleteRow('dataTable',this)" class="btn btn-danger btn-sm" />hapus</button></td>
            </tr>
          </tbody>
        </table>
        
      <div class="form-group">
      <button type="button" value="Add Item"  onClick="addRow('dataTable',this)" class="btn btn-primary btn-sm">Tambah Data</button>
      </div>

       </div>               
     <div class="form-group">
         <button type="submit" class="btn btn-outline-primary">Submit</button>
                      <button type="reset" class="btn btn-outline-danger">Reset</button>
                      
                      </div>
                   
            
                        </form>

    <?php ActiveForm::end(); ?>

</div>
