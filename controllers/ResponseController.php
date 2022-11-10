<?php

namespace humhub\modules\social_invite\controllers;

use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\Invite;
use humhub\modules\user\models\User;

use Yii;

use yii\base\Behavior;
use yii\base\Exception;
use yii\validators\EmailValidator;

use yii\db; 
use yii\db\Query; 
use yii\db\Command; 

class ResponseController extends \humhub\components\Controller
{

	/**
	 * @inheritdoc
	 */
	public function behaviors(){
		return [
			'acl' => [
				'class' => \humhub\components\behaviors\AccessControl::className(),
				'guestAllowedActions' => ['response']
				]
			];
		}
	
	
	
	public function actionResponse(){
		$userID=Yii::$app->user->id;
		$MyBR='<br>'; 
		
		$ResponseMessage=''; 
		
		$TheGuestEmail=''; 
		$TheGuestSpaceID=''; 
		$userEmail=''; 
		$ActivityLog='';
		$ResponseTitle=Yii::t('SocialInviteModule.base','Error'); 
		/*
		if (Yii::$app->request->get('ThisIsAtest')=='Yes'){
			$TheGuestEmail='The GET Request worked'; 
			$ResponseMessage='The test worked'; 
			return $this->renderAjax('response', ['TheGuestEmail' => $TheGuestEmail,'TheOriginatorID' => $userID,'ResponseMessage' => $ResponseMessage]);
			return; 
			}
		*/
		
		if (Yii::$app->request->get('TheGuestEmail')!=''){
			$TheGuestEmail=trim(urldecode(Yii::$app->request->get('TheGuestEmail'))); 
			$ActivityLog='Received the Guest email: '.$TheGuestEmail.$MyBR; 
			}else{return $this->renderAjax('response', ['TheGuestEmail' => '','TheOriginatorID' => '','ResponseMessage' => 'fatal error; There Was no email']);}
		
		if (Yii::$app->request->get('TheGuestSpaceID')!=''){
			$TheGuestSpaceID=urldecode(Yii::$app->request->get('TheGuestSpaceID')); 
			$ActivityLog='Received the Space ID: '.$TheGuestSpaceID.$MyBR; 
			}else{return $this->renderAjax('response', ['TheGuestEmail' => '','TheOriginatorID' => '','ResponseMessage' => 'fatal error; There Was no Space ID']);}
		
		if ($userID==''){
			return $this->renderAjax('response', ['TheGuestEmail' => '','TheOriginatorID' => '','ResponseMessage' => 'fatal error; Problem getting your ID']);
			}
		
		
		//ResponseController::inviteMemberByEMail($TheGuestEmail,$userID); 
		/**/
		// Invalid E-Mail
		$validator = new EmailValidator;
		if (!$validator->validate($TheGuestEmail)) {
			//$ResponseMessage.='The Guest email you provided could not be validated'.$MyBR; 
			$ResponseMessage.=Yii::t('SocialInviteModule.base','The Guest email you provided could not be validated').$MyBR; 
			//return false;
			}

		// User already registered
		$user = User::findOne(['email' => $TheGuestEmail]);
		if ($user != null) {
			//$ResponseMessage.='The Guest email you provided belongs to a member already registered, <b><u>no email was sent</u></b>'.$MyBR; 
			$ResponseMessage.=Yii::t('SocialInviteModule.base','The Guest email you provided belongs to a member already registered, <b><u>no email was sent</u></b>').$MyBR; 
			$ResponseTitle=Yii::t('SocialInviteModule.base','No Invitation Sent'); 
			//return false; 
			}

		$NativeInviteCount=0; 
		$userInvite = Invite::findOne(['email' => $TheGuestEmail]);
		// No invite yet
		if ($userInvite == null) {
			// Invite EXTERNAL user
			$userInvite = new Invite();
			$userInvite->email = $TheGuestEmail;
			$userInvite->source = 'invite';
			$userInvite->user_originator_id = $userID;
			$userInvite->space_invite_id = $TheGuestSpaceID; // the space ID
			// There is a pending registration
			// Steal it and send mail again
			// Unfortunately there are no multiple workspace invites supported
			// So we take the last one
			$ActivityLog.='No prior Inivtes'.$MyBR; 
		} else {
			$userInvite->user_originator_id = $userID;
			$userInvite->space_invite_id = 32; 
			$ActivityLog.='A prior Inivte exists'.$MyBR; 
			$NativeInviteCount=1; 
			}

		if ($userInvite->validate() && $userInvite->save()) {
			
			$userEmail=Yii::$app->db->createCommand("SELECT email FROM user WHERE id=$userID;")->queryScalar(); 
			if($NativeInviteCount){}; 
			//social_invite
			$CheckSocInvitedb_cmd=Yii::$app->db->createCommand("SELECT id as SocInviteID,guest_email,date_updated,originator_ID,times_sent FROM social_invite WHERE guest_email='$TheGuestEmail';")->queryAll(); 
			$MyRecordsCount=count($CheckSocInvitedb_cmd); 
			
			if($NativeInviteCount==0){
				if($MyRecordsCount==0){
					$ActivityLog.='No records of this yet in social_invite'.$MyBR; 
					Yii::$app->db->createCommand("INSERT INTO social_invite (guest_email,originator_ID,originator_email,times_sent) VALUES ('$TheGuestEmail','$userID','$userEmail',1);")->query(); 
					}
				elseif($MyRecordsCount==1){
					$ActivityLog.='Nothing in social_invite, but there was a native Invite'.$MyBR; // incomplete!
					Yii::$app->db->createCommand("UPDATE social_invite SET times_sent=$NewTimesSent,date_updated=NOW() WHERE social_invite.id = $SocInviteID;")->query(); 
					}
				$userInvite->sendInviteMail(); 
				//$ResponseMessage.='An email has just been sent to invite your Guest'.$MyBR; 
				$ResponseMessage.=Yii::t('SocialInviteModule.base','An email has just been sent to invite your Guest').$MyBR; 
				$ResponseTitle=Yii::t('SocialInviteModule.base','Invitation Sent'); 
				}
			elseif($NativeInviteCount>0){
				$date_updated=$CheckSocInvitedb_cmd[0]['date_updated']; 
				$timeSinceDateUpdated=round((time()-strtotime($date_updated))/3600,1); 
				//$ResponseMessage.='This email address has already received an invitation on: '.$date_updated.'; which is '.$timeSinceDateUpdated.' hours ago'.$MyBR;
				$ResponseMessage.=Yii::t('SocialInviteModule.base','This email address <u>has already received an invitation</u> on: ').$date_updated.Yii::t('SocialInviteModule.base','; which is ').$timeSinceDateUpdated.Yii::t('SocialInviteModule.base',' hours ago').$MyBR; 
				$ActivityLog.='1 record in social_invite'.$MyBR;
				//86400=1day; 172800=2days; 
				if($timeSinceDateUpdated<48){
					//$ResponseMessage.='<br><b><u>no email was sent</u></b><br>Please wait at least 48 hours before you send another invitation to this email addtress..'.$MyBR;
					$ResponseMessage.=Yii::t('SocialInviteModule.base','<br><b><u>no email was sent</u></b><br>Please wait at least 48 hours before you send another invitation to this email addtress..').$MyBR;
					$ResponseTitle=Yii::t('SocialInviteModule.base','No Invitation Sent'); 
					}
				else{
					$SocInviteID=$CheckSocInvitedb_cmd[0]['SocInviteID']; 
					$NumOfTimesSent=$CheckSocInvitedb_cmd[0]['times_sent']; 
					if($NumOfTimesSent==''){$NumOfTimesSent=1; }
					if($NumOfTimesSent<10){
						$NewTimesSent=$NumOfTimesSent+1; 
						//$ResponseMessage.='An email has just been sent to invite your Guest again'.' (attempt #'.$NewTimesSent.')'.$MyBR; 
						$ResponseMessage.=Yii::t('SocialInviteModule.base','An email has just been sent to invite your Guest again').Yii::t('SocialInviteModule.base',' (attempt #').$NewTimesSent.')'.$MyBR; 
						$ResponseTitle=Yii::t('SocialInviteModule.base','Invitation Sent'); 
						
						Yii::$app->db->createCommand("UPDATE social_invite SET times_sent=$NewTimesSent,date_updated=NOW() WHERE social_invite.id = $SocInviteID;")->query(); 
						
						$userInvite->sendInviteMail(); 
						}
					else{
						//$ResponseMessage.='Too many invitations have been sent to this email, please contact an administrator'.' ('.$NumOfTimesSent.' attempts)'.$MyBR; 
						$ResponseMessage.=Yii::t('SocialInviteModule.base','Too many invitations have been sent to this email, please contact an administrator').' ('.$NumOfTimesSent.Yii::t('SocialInviteModule.base',' attempts)').$MyBR; 
						}
					}
				if($MyRecordsCount>1){$ActivityLog.='There are several records in social_invite; that\'s not supposed to happen!'.$MyBR;}
				}
			
			/* $userInvite->sendInviteMail(); */
			/* $ResponseMessage.='An email has just been sent to invite your Guest'.$MyBR;  */
			}
		// end
		
		
		return $this->renderAjax('response', [
					'TheGuestEmail' => $TheGuestEmail,
					'TheOriginatorID' => $userID.'; '.$userEmail,
					'ResponseMessage' => $ResponseMessage,
					'ResponseTitle' => $ResponseTitle,
					'ActivityLog' => $ActivityLog
					]);
		}

	}

?>
