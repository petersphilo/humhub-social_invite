<?php

namespace humhub\modules\social_invite\controllers;

use Yii;
use yii\console\Controller;
use yii\web\Request;
use humhub\modules\social_invite\models\ConfigureForm;
use humhub\models\Setting;
use yii\helpers\Json;

/**
 * Defines the configure actions.
 *
 * @package humhub.modules.social_invite.controllers
 * @author Marjana Pesic
 */
class ConfigController extends \humhub\modules\admin\components\Controller {
	
	public function behaviors(){
		return [
			'acl' => [
				'class' => \humhub\components\behaviors\AccessControl::className(),
				'adminOnly' => true
				]
			];
		}
	
	/**
	 * Configuration Action for Super Admins
	 */
	public function actionConfig(){
		if(Yii::$app->request->get('SocInviteDL')){$this->MyDataRequest(); }
		else{
			$form = new ConfigureForm();
			$form->theGroup = Json::decode(Setting::Get('theGroup', 'social_invite'));
			$form->theSpace = Setting::Get('theSpace', 'social_invite');
			$form->ResponsiveTop = Setting::Get('ResponsiveTop', 'social_invite');
			$form->SISortOrder = Setting::Get('SISortOrder', 'social_invite');
			if ($form->load(Yii::$app->request->post()) && $form->validate()) {
				$form->theGroup = Setting::Set('theGroup', Json::encode($form->theGroup), 'social_invite');
				$form->theSpace = Setting::Set('theSpace', $form->theSpace, 'social_invite');
				$form->ResponsiveTop = Setting::Set('ResponsiveTop', $form->ResponsiveTop, 'social_invite');
				$form->SISortOrder = Setting::Set('SISortOrder', $form->SISortOrder, 'social_invite');
				return $this->redirect(['/social_invite/config/config']);
				}

			return $this->render('config', array('model' => $form));
			}
		}
	
	public function MyDataRequest(){
		if(Yii::$app->request->get('SocInviteDL')=='Yes'){
			$MyTabChar="\t"; 
			$dlSocInviteFile='guest_email'.$MyTabChar.'originator_ID'.$MyTabChar.'originator_email'.$MyTabChar.'date_created'.$MyTabChar.'date_updated'.$MyTabChar.'times_sent'.$MyTabChar.'GuestBecameUser'."\n"; 
			$dlSocInvite_cmd=Yii::$app->db->createCommand("SELECT guest_email,originator_ID,originator_email,date_created,date_updated,times_sent 
				FROM social_invite ORDER BY id ASC;")->queryAll(); 
			foreach($dlSocInvite_cmd as $dlSocInvite_row){
				$EachGuestEmail=$dlSocInvite_row['guest_email']; 
				$EachUserMemberYN=''; 
				$EachUserTurnedMember=Yii::$app->db->createCommand("SELECT id as MemberID FROM user WHERE (email='$EachGuestEmail');")->queryScalar(); 
				if($EachUserTurnedMember !=''){$EachUserMemberYN='Yes'; }
				$dlSocInviteFile.=$dlSocInvite_row['guest_email'].$MyTabChar.$dlSocInvite_row['originator_ID'].$MyTabChar.$dlSocInvite_row['originator_email'].$MyTabChar.$dlSocInvite_row['date_created'].$MyTabChar.$dlSocInvite_row['date_updated'].$MyTabChar.$dlSocInvite_row['times_sent'].$MyTabChar.$EachUserMemberYN."\n";
				}
			echo $dlSocInviteFile; 
			exit;
			}
		}
	
	}

?>
