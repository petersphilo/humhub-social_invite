<?php

/* use Yii; */

use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;

use humhub\models\Setting;

/**
 * @var $model \humhub\modules\social_invite\models\ConfigureForm
 */

$social_invite=Yii::$app->getModule('social_invite'); 

$ReadTheGroup=Json::decode($social_invite->settings->get('theGroup')); 
if(is_int($ReadTheGroup)){$ReadTheGroup=[$ReadTheGroup];}
$ReadTheSpace=$social_invite->settings->get('theSpace'); 

$BlankGroupName=Yii::t('SocialInviteModule.base','Empty Group (to select no group)'); 

$ConcernedGroups=''; 
foreach($ReadTheGroup as $EachGroup){
	if($EachGroup==0){$ConcernedGroups.=$BlankGroupName."; "; }
	else{$ConcernedGroups.=Yii::$app->db->createCommand("SELECT name FROM `group` WHERE id=$EachGroup;")->queryScalar()." (id: $EachGroup); "; }
	}
$GetTheSpaceName_cmd=Yii::$app->db->createCommand("SELECT name FROM space WHERE id=$ReadTheSpace;")->queryScalar(); 


$MyGroupsFull=[]; 
$MyGroupsFull+=[0=>$BlankGroupName]; 
$ListAllGroups_cmd=Yii::$app->db->createCommand("SELECT id,name FROM `group`;")->queryAll(); 
foreach($ListAllGroups_cmd as $ListAllGroups_row){
	$GroupName=$ListAllGroups_row['id'].' -- '.$ListAllGroups_row['name']; 
	$MyGroupsFull+=[$ListAllGroups_row['id']=>$GroupName]; 
	}

$MySpacesFull=[]; 
$ListAllSpaces_cmd=Yii::$app->db->createCommand("SELECT id,name FROM space;")->queryAll(); 
foreach($ListAllSpaces_cmd as $ListAllSpaces_row){
	$SpaceName=$ListAllSpaces_row['id'].' -- '.$ListAllSpaces_row['name']; 
	$MySpacesFull+=[$ListAllSpaces_row['id']=>$SpaceName]; 
	}

?>

<div class="panel panel-default">
	<div class="panel-heading">
		Social Invite Module Configuration
	</div>
	<div class="panel-body">
		<div style='float: right; '>
			<span class="btn btn-info btn-sm" id='SocInvite-DL'><?php echo Yii::t('SocialInviteModule.base','Download Invite List'); ?></span>
		</div>
		<p>
			<?php 
				echo Yii::t('SocialInviteModule.base','The Current Selected Groups are').": <strong>$ConcernedGroups</strong><br>"; 
				echo Yii::t('SocialInviteModule.base','The Current Selected Space is').": <strong>$GetTheSpaceName_cmd</strong><br>"; 
			?>
		</p>
		<br/>

		<?php $form = ActiveForm::begin(); ?>

		<div class="form-group">
			<?php 
				echo $form->field($model, 'theGroup')->checkboxList($MyGroupsFull);
				echo $form->field($model, 'theSpace')->dropdownList($MySpacesFull); 
				echo $form->field($model, 'ResponsiveTop')->dropdownList([0=>'No',1=>'Yes']);  
				echo $form->field($model, 'SISortOrder')->textInput(); 
			?>
		</div>
		<span id='MyCurrentGetURL'></span>
		<span id='MyNewGetURL'></span>

		<hr>

		<?php echo Html::submitButton('Save', ['class' => 'btn btn-primary']); ?>

		<a class="btn btn-default" href="<?php echo Url::to(['/admin/module']); ?>">
			Back to modules
		</a>
		<?php $form::end(); ?>
	</div>
</div>
<script>
	$(function(){
		var MyNewGetURL='',
			MyCurrentGetURL=window.location.search; 
		$('#SocInvite-DL').on('click',function(){
			if(MyCurrentGetURL.length){MyNewGetURL=MyCurrentGetURL+'&SocInviteDL=Yes'; }
			else{MyNewGetURL='?SocInviteDL=Yes'; }
			/* $('#MyCurrentGetURL').text(MyCurrentGetURL);  */
			/* $('#MyNewGetURL').text(MyNewGetURL);  */
			fetch(MyNewGetURL)
				.then(resp => resp.blob())
				.then(blob => {
					var Myurl = window.URL.createObjectURL(blob);
					const TempdlLink = document.createElement('a');
					TempdlLink.style.display = 'none';
					TempdlLink.href = Myurl;
					TempdlLink.download = 'SocialInviteList.csv';
					document.body.appendChild(TempdlLink);
					TempdlLink.click();
					window.URL.revokeObjectURL(Myurl);
					TempdlLink.remove();
					})
				.catch(() => alert('something went wrong..'));
			}); 
		}); 
</script>

