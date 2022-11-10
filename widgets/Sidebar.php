<?php

namespace humhub\modules\social_invite\widgets;

//use humhub\modules\social_invite\models\InviteForm;

use humhub\models\Setting;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Widget;

class Sidebar extends Widget
{

	public function run()
	{
		$theAuthorizedGroup = Setting::Get('theGroup', 'social_invite');
		$userID = Yii::$app->user->id;
		
		$userGroupArr=[]; 
		$eachUserGroups_cmd=Yii::$app->db->createCommand("SELECT group_id FROM group_user WHERE (group_user.user_id=$userID);")->queryAll(); 
		foreach($eachUserGroups_cmd as $eachUserGroups_row){
			array_push($userGroupArr,$eachUserGroups_row['group_id']); 
			}
		
		if(in_array($theAuthorizedGroup,$userGroupArr)){
		/* if($theAuthorizedGroup==3){ */
			return $this->render('sidebar');
			}
		else{
			return; 
			}
		/* return;  */
	}

}

?>
