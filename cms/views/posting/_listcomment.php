<?php 
use yii\widgets\DetailView;
?>

              <h4 class="card-title">List Comment By Posting</h4>
             
                 
                    <table><thead>
                         <tr>
                         
             
                 <th>Comment</th>
                <th>Comment At</th>
              
                        </tr>
                      </thead>
        <tbody>
            <?php foreach ($data as $row) : ?>
            <tr>
              
                <td><?php echo $row['comment']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
               
               
              
            </tr>
             <?php endforeach; ?>
                      </tbody>
                    </table>
                
