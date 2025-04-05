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
			$ResponseMessage.=Yii::t('SocialInviteModule.base','The Guest email you provided could not be validated').$MyBR; 
			$ResponseTitle=Yii::t('SocialInviteModule.base','No Invitation Sent'); 
			//return false;
			$ActivityLog.='No valid email'.$MyBR; 
			return $this->renderAjax('response', [
						'TheGuestEmail' => $TheGuestEmail,
						'TheOriginatorID' => $userID.'; '.$userEmail,
						'ResponseMessage' => $ResponseMessage,
						'ResponseTitle' => $ResponseTitle,
						'ActivityLog' => $ActivityLog
						]);
			/* die();  */
			}

		// User already registered
		$user = User::findOne(['email' => $TheGuestEmail]);
		if ($user != null) {
			$ResponseMessage.=Yii::t('SocialInviteModule.base','The Guest email you provided belongs to a member already registered, <b><u>no email was sent</u></b>').$MyBR; 
			$ResponseTitle=Yii::t('SocialInviteModule.base','No Invitation Sent'); 
			//return false; 
			$ActivityLog.='Already member'.$MyBR; 
			return $this->renderAjax('response', [
						'TheGuestEmail' => $TheGuestEmail,
						'TheOriginatorID' => $userID.'; '.$userEmail,
						'ResponseMessage' => $ResponseMessage,
						'ResponseTitle' => $ResponseTitle,
						'ActivityLog' => $ActivityLog
						]);
			/* die();  */
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
			$ActivityLog.='No prior Inivtes'.$MyBR; 
		} else {
			$userInvite->user_originator_id = $userID;
			$userInvite->space_invite_id = $TheGuestSpaceID; 
			// There is a pending registration
			// Steal it and send mail again
			// Unfortunately there are no multiple workspace invites supported
			// So we take the last one
			$ActivityLog.='A prior Inivte exists'.$MyBR; 
			$NativeInviteCount=1; 
			}

		if ($userInvite->validate() && $userInvite->save()) {
			
			$userEmail=Yii::$app->db->createCommand("SELECT email FROM user WHERE id=$userID;")->queryScalar(); 
			if($NativeInviteCount){}; 
			//social_invite
			$NewTimesSent=0; 
			$CheckSocInvitedb_cmd=Yii::$app->db->createCommand("SELECT id as SocInviteID,guest_email,date_updated,originator_ID,times_sent FROM social_invite WHERE guest_email=:Email;"); 
			$CheckSocInvitedb=$CheckSocInvitedb_cmd->bindValue(':Email',$TheGuestEmail)->queryAll(); 
			$MyRecordsCount=count($CheckSocInvitedb); 
			
			if($NativeInviteCount==0){
				if($MyRecordsCount==0){
					$ActivityLog.='No records of this yet in social_invite'.$MyBR; 
					$FreshNewInvite=Yii::$app->db->createCommand("INSERT INTO social_invite (guest_email,originator_ID,originator_email,times_sent) VALUES (:Email,'$userID','$userEmail',1);"); 
					$FreshNewInvite->bindValue(':Email',$TheGuestEmail)->query(); 
					}
				elseif($MyRecordsCount>=1){
					$NewTimesSent=$MyRecordsCount+1; 
					$ActivityLog.='Exists in social_invite, no native Invite'.$MyBR; // incomplete!
					Yii::$app->db->createCommand("UPDATE social_invite SET times_sent=$NewTimesSent,date_updated=NOW() WHERE social_invite.id = $SocInviteID;")->query(); 
					}
				$userInvite->sendInviteMail(); 
				//$ResponseMessage.='An email has just been sent to invite your Guest'.$MyBR; 
				$ResponseMessage.=Yii::t('SocialInviteModule.base','An email has just been sent to invite your Guest').$MyBR; 
				$ResponseTitle=Yii::t('SocialInviteModule.base','Invitation Sent'); 
				}
			elseif($NativeInviteCount>0){
				$SendInviteException=0; 
				if($MyRecordsCount==0){
					$ActivityLog.='No records of this yet in social_invite'.$MyBR; 
					$OtherNewInvite=Yii::$app->db->createCommand("INSERT INTO social_invite (guest_email,originator_ID,originator_email,times_sent) VALUES (:Email,'$userID','$userEmail',1);"); 
					$OtherNewInviteGo=$OtherNewInvite->bindValue(':Email',$TheGuestEmail)->query(); 
					$OtherNewInviteID=Yii::$app->db->getLastInsertID();
					
					$OtherNewInviteSel=Yii::$app->db->createCommand("SELECT id as SocInviteID,guest_email,date_updated,originator_ID,times_sent FROM social_invite WHERE id=$OtherNewInviteID;")->queryAll(); 
					$date_updated=$OtherNewInviteSel[0]['date_updated']; 
					$SocInviteID=$OtherNewInviteSel[0]['SocInviteID']; 
					$NumOfTimesSent=0; 
					$SendInviteException=1; 
					}
				else{
					$date_updated=$CheckSocInvitedb[0]['date_updated']; 
					$SocInviteID=$CheckSocInvitedb[0]['SocInviteID']; 
					$NumOfTimesSent=$CheckSocInvitedb[0]['times_sent']; 
					}
				$timeSinceDateUpdated=round((time()-strtotime($date_updated))/3600,1); 
				//$ResponseMessage.='This email address has already received an invitation on: '.$date_updated.'; which is '.$timeSinceDateUpdated.' hours ago'.$MyBR;
				$ResponseMessage.=Yii::t('SocialInviteModule.base','This email address <u>has already received an invitation</u> on: ').$date_updated.Yii::t('SocialInviteModule.base','; which is ').$timeSinceDateUpdated.Yii::t('SocialInviteModule.base',' hours ago').$MyBR; 
				$ActivityLog.='1 record in social_invite'.$MyBR;
				//86400=1day; 172800=2days; 
				if(($timeSinceDateUpdated<48)&&($SendInviteException==0)){
					//$ResponseMessage.='<br><b><u>no email was sent</u></b><br>Please wait at least 48 hours before you send another invitation to this email addtress..'.$MyBR;
					$ResponseMessage.=Yii::t('SocialInviteModule.base','<br><b><u>no email was sent</u></b><br>Please wait at least 48 hours before you send another invitation to this email addtress..').$MyBR;
					$ResponseTitle=Yii::t('SocialInviteModule.base','No Invitation Sent'); 
					}
				else{
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
