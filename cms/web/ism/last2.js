$(document).ready(function() {
      $('#data-tables').DataTable({
        "pagingType": "full_numbers",
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
         "aaSorting": [],
        "lengthMenu": [
          [10, 25, 50, -1],
          [10, 25, 50, "All"]
        ],
        responsive: true,
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Search records",
        }
      });

      

      var table = $('#data-tables').DataTable();

      // Edit record
      table.on('click', '.edit', function() {
        $tr = $(this).closest('tr');
        var data = table.row($tr).data();
        alert('You press on Row: ' + data[0] + ' ' + data[1] + ' ' + data[2] + '\'s row.');
      });

      // Delete a record
      table.on('click', '.remove', function(e) {
        $tr = $(this).closest('tr');
        table.row($tr).remove().draw();
        e.preventDefault();
      });

      //Like record
      table.on('click', '.like', function() {
        alert('You clicked on Like button');
      });

      $('.dts').DataTable({        
        dom: 'Bfrtip',
        responsive: true,
      });

      $(document).on('click','.btn-collapse0',function(e){
        $(this).removeClass('btn-collapse0');
        $(this).addClass('btn-collapse-exit0');
        $(this).html(`<i class="fa fa-minus-circle text-muted"></i>`);  
      });

      $(document).on('click','.btn-collapse-exit0',function(e){    
        $(this).removeClass('btn-collapse-exit0');
          $(this).addClass('btn-collapse0');
        $(this).html(`<i class="fa fa-plus-circle text-primary"></i>`);
      });

      $(document).on('click','.btn-collapse',function(e){
          $(this).removeClass('btn-collapse');
          $(this).addClass('btn-collapse-exit');
          $(this).html(`<i class="fa fa-minus-circle text-muted"></i>`);  
      });

      $(document).on('click','.btn-collapse-exit',function(e){    
        $(this).removeClass('btn-collapse-exit');
          $(this).addClass('btn-collapse');
        $(this).html(`<i class="fa fa-plus-circle text-primary"></i>`);
      });

      $(document).on('click','.btn-collapse2',function(e){
        $(this).removeClass('btn-collapse2');
        $(this).addClass('btn-collapse-exit2');
        $(this).html(`<i class="fa fa-minus-circle text-muted"></i>`);  
      });

      $(document).on('click','.btn-collapse-exit2',function(e){    
        $(this).removeClass('btn-collapse-exit2');
          $(this).addClass('btn-collapse2');
        $(this).html(`<i class="fa fa-plus-circle text-primary"></i>`);
      });

      

});
    



