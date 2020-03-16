<div class="content">
	<div class="container-fluid">				
				<?= $content ?>
	</div>
</div>
<footer class="footer">
	<div class="container-fluid">
			<div class="copyright ml-auto" title="Arif Nur Rahman">
				Copyright &copy; 
				<?php
					date_default_timezone_set('Asia/Jakarta');
					$date = date("Y");
					echo $date . ".";
				?> <a class="font-weight-bold ml-1"  style="color:#3c4858;">Human Capital Management - Telkomsel. All rights reserved.</a>
			</div>				
	</div>
</footer>