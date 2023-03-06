<?php

namespace humhub\modules\social_invite\widgets;

//use humhub\modules\social_invite\models\InviteForm;

use humhub\models\Setting;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;

class Sidebar extends Widget
{

	public function run()
	{
		$AuthorizedGroupArr = Json::decode(Setting::Get('theGroup', 'social_invite'));
		if(is_int($AuthorizedGroupArr)){$AuthorizedGroupArr=[$AuthorizedGroupArr];}
		$userID = Yii::$app->user->id;
		
		$userGroupArr=[]; 
		$eachUserGroups_cmd=Yii::$app->db->createCommand("SELECT group_id FROM group_user WHERE (group_user.user_id=$userID);")->queryAll(); 
		foreach($eachUserGroups_cmd as $eachUserGroups_row){
			array_push($userGroupArr,$eachUserGroups_row['group_id']); 
			}
		
		if(count(array_intersect($AuthorizedGroupArr,$userGroupArr))>0){
			return $this->render('sidebar');
			}
		else{
			return; 
			}
		/* return;  */
	}

}

?>
