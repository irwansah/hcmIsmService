 $('#select2').select2({
                       minimumInputLength: 3,
                       allowClear: true,
                       placeholder: 'Input Department Name',
                       ajax: {
                          dataType: 'json',
                          url: 'index.php?r=site%2Fdaftardepartment',
                          delay: 800,
                          data: function(params) {
                            return {
                              search: params.term
                            }
                          },
                          processResults: function (data, page) {
                          return {
                            results: data
                          };
                        },
                      }
                  })

                  <div class="row" style="margin-top:5px;">
        <div class="col-md-6 mb-3">
          <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1"><i class="fa fa-institution"></i></span>
              </div>
              <select name="department" id="select2" class="form-control">
                    <option value="">SELECT DEPARTMENT</option>
              </select>
            </div>
        </div>
  </div>
  </div>
</div>