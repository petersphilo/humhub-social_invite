<?php

use yii\helpers\Html;

// humhub\modules\social_invite\Assets::register($this);
use humhub\models\Setting;
use humhub\modules\user\models\User;
/* use Yii; */
//use yii\helpers\Json;

$social_invite=Yii::$app->getModule('social_invite'); 

$theSpaceEcho = $social_invite->settings->get('theSpace');
$ResponsiveTopEcho = $social_invite->settings->get('ResponsiveTop');

?>
<div class="panel panel-default" id="social_invite-panel">

	<!-- Display panel menu widget -->
	<?php humhub\widgets\PanelMenu::widget(array('id' => 'social_invite-panel')); ?>
	<ul data-ui-widget="ui.panel.PanelMenu" data-ui-init="" class="nav nav-pills preferences">
		<li class="dropdown">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-label="Toggle" aria-haspopup="true"><i class="fa fa-angle-down" aria-hidden="true"></i></a>
			<ul class="dropdown-menu pull-right">
				<li>
					<a class="panel-collapse panel-collapsed" data-action-click="toggle" data-ui-loader=""><i class="fa fa-minus-square" aria-hidden="true"></i>Collapse</a>
				</li>
			</ul>
		</li>
	</ul>
	<div class="panel-heading">
		<strong><?php echo Yii::t('SocialInviteModule.base','Invite!'); ?></strong>
	</div>
	
	<div class="panel-body">
		<?php
			echo Yii::t('SocialInviteModule.base','Invite members of your community to join this Network!'); 
			echo '<br>';
			echo Yii::t('SocialInviteModule.base','...'); 
		
			echo '<br><br>'; 
		?>
		<form id='social_invite-inviteForm' action='/social_invite/response/response' method='get' data-target="#globalModal">
			<div class='form-group required'>
				<label class="control-label" for="inviteForm-theEmail"><?php echo Yii::t('SocialInviteModule.base','Enter the email of your guest'); ?>:</label>
				<input type='email' id='inviteForm-theEmail' class='form-control required' >
				<p class="help-block help-block-error"></p>
			</div>
			<button type="submit" class="btn btn-info btn-sm" id='inviteForm-Submit'><?php echo Yii::t('SocialInviteModule.base','Invite'); ?></button>
			<a href='' style='display:none; ' id='inviteForm-SubmitLink' data-target='#globalModal'></a>
			<script>
				$(function(){
					/**/
					$('#social_invite-inviteForm').on('submit',function(e){
						e.preventDefault(); 
						
						var guestEmailEL=$('#inviteForm-theEmail'), 
							MyFromGroup=$('#social_invite-inviteForm .form-group'), 
							guestEmail=guestEmailEL.val(), 
							theSpaceEcho=<?php echo $theSpaceEcho; ?>, 
							theErrorText="<?php echo Yii::t('SocialInviteModule.base','Please provide a valid email address'); ?>", 
							TheNewhref='/social_invite/response/response?TheGuestSpaceID=<?php echo $theSpaceEcho; ?>&TheGuestEmail='+encodeURIComponent(guestEmail);
						
						MyFromGroup.removeClass('has-error'); 
						
						if(guestEmail=='' || !(/^([^@\s]+@[^@\s]+\.[^@\s]+)$/.test(guestEmail))){
							MyFromGroup.addClass('has-error'); 
							$('.help-block-error').text(theErrorText); 
							}
						else{
							//console.log(/^([^@\s]+@[^@\s]+\.[^@\s]+)$/.test(guestEmail)); 
							MyFromGroup.removeClass('has-error'); 
							$('#inviteForm-SubmitLink').attr({href:TheNewhref}).click(); 
							$('#inviteForm-SubmitLink').attr({href:''}); 
							setTimeout(function(){guestEmailEL.val('');}, 1000); 
							}
						})
					
					}); 
			</script>
		</form>
		<?php
			if($ResponsiveTopEcho==1){
		?>
		<script>
			$(function(){
	
				var myVar=new Object(); 
					myVar.DataStream=$('.data-stream-content'); 
					myVar.WallEntry=$('.wall-entry'); 
					myVar.DashBtn=$('[data-menu-id="dashboard"]'); 
	
				function DumpScrollOther(){
					myVar.WinWidth=$(window).width(); 
					myVar.zero=0; 
					if($('#social_invite-panel').length && $('.layout-sidebar-container > #social_invite-panel').length && myVar.WinWidth < 992 && $('#layout-content > .container > .row > .layout-content-container').length && !$('#layout-content > .container > .row > .layout-content-container > #social_invite-panel:first-child').length){
						$('#social_invite-panel').detach().prependTo('.layout-content-container');
						console.log('timing 1 New fired'); 
						}
					if($('#social_invite-panel').length && $('.layout-content-container > #social_invite-panel').length && myVar.WinWidth > 991 && $('#layout-content > .container > .row > .layout-content-container').length){
						$('#social_invite-panel').detach().prependTo('.layout-sidebar-container');
						console.log('timing 2 New fired'); 
						}
					}
	
				function DumpScrollOtherOnATimer(){
					if($('html').hasClass('nprogress-busy')){setTimeout(function(){DumpScrollOtherOnATimer(); },10); return; }
					else{
						DumpScrollOther(); 
						setTimeout(function(){DumpScrollOther(); },600);
						}
					}
	
				DumpScrollOther();
				DumpScrollOtherOnATimer(); 
	
				$(window).on('scroll resize load', function(){DumpScrollOther(); }); 
	
				}); 
		</script>
		<?php
			}
		?>
	</div>
</div>

