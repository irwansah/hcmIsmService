<?php 
use yii\helpers\Html; 
use yii\helpers\Url;
use yii\widgets\Menu;
 $srcImg = Url::base()."/themes/atlantis/assets/img/tlt.jpg";
?>

<!-- Sidebar start -->
<div class="sidebar sidebar-style-2">
	<div class="sidebar-wrapper scrollbar scrollbar-inner">
		<div class="sidebar-content" data-image="<?= $srcImg?>">  											
			<!-- Navigation / Side Menu Bar -->
			<?php
			echo Menu::widget([
				'options' => [
					"class" => 'nav nav-danger'
				],
				'items' => [
					[
						'label' => 'Navigation', 
						'template'=>'
								<span class="sidebar-mini-icon">
									<i class="fa fa-ellipsis-h"></i>
								</span>
								<h4 class="text-section">{label}</h4>',
					],
					[
						'label' => 'Dashboard', 
						'url' => ['/site/index'], 
						'template'=>'<a href="{url}"><i class="fas fa-home"></i> <p>{label}</p></a>',
					],
					[
						'label' => 'Users Management', 
						'url' => ['/user/index'], 
						'template'=>'<a href="{url}"><i class="fas fa-user-friends"></i> <p>{label}</p></a>',
						'active' => $this->context->route == 'user/index' || $this->context->route == 'user/create' || $this->context->route == 'user/update'
					],
					[
						'label' => 'Posts Management', 
						'url' => ['/posting/posting'], 
						'template'=>'<a href="{url}"><i class="fas fa-pencil-ruler"></i> <p>{label}</p></a>',
						'active' => $this->context->route == 'posting/posting' || $this->context->route == 'posting/detail'					
					],
					[
						'label' => 'Groups Management', 
						'url' => ['/group/index'], 
						'template'=>'<a href="{url}"><i class="fas fa-object-ungroup"></i> <p>{label}</p></a>',
						'active' => $this->context->route == 'group/index' || $this->context->route == 'group/detail'					
					],
					[
						'label' => 'Reports', 
						'template'=>'<a data-toggle="collapse" href="#submenu-reports"><i class="fas fa-file-alt"></i> <p>{label}</p><span class="caret"></span></a>',
						'items' => [
							[
								'label' => 'Views', 
								'url' => ['/posting/index'],
								'template'=>'<a href="{url}"><span class="sub-item">{label}</span></a>',
							],
							[
								'label' => 'Likes', 
								'url' => ['/posting/like'],
								'template'=>'<a href="{url}"><span class="sub-item">{label}</span></a>',
							],
							[
								'label' => 'Comments', 
								'url' => ['/posting/comment'],
								'template'=>'<a href="{url}"><span class="sub-item">{label}</span></a>',
							],
						],
						'submenuTemplate' => '<div class="collapse" id="submenu-reports" style="margin-top: -1.2em;"><ul class="nav nav-collapse">{items}</ul></div>',
						'active' => $this->context->route == 'posting/index' || $this->context->route == 'posting/like' || $this->context->route == 'posting/comment'						
					],
				],
				'firstItemCssClass' => 'nav-section',
				'itemOptions' => ['class' =>'nav-item'],
			]);
			?>
		</div>
	</div>
</div>
<!-- Sidebar end -->	 