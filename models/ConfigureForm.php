<?php

namespace humhub\modules\social_invite\models;

use Yii;

class ConfigureForm extends \yii\base\Model
{
	
	public $theGroup;
	public $theSpace;
	public $ResponsiveTop;
	public $SISortOrder;
	
	public function rules()
	{
		return array(
			array('theGroup', 'required'),
			array('theGroup', 'safe'),
			array('theSpace', 'required'),
			array('theSpace', 'integer', 'min' => 0, 'max' => 5000),
			array('ResponsiveTop', 'required'),
			array('ResponsiveTop', 'integer', 'min' => 0, 'max' => 1),
			array('SISortOrder', 'required'),
			array('SISortOrder', 'integer', 'min' => 0, 'max' => 1000),
		);
	}
	
	
	public function attributeLabels()
	{
		
		$theGroup_title=Yii::t('SocialInviteModule.base','The group ID Allowed to Invite'); 
		$theSpace_title=Yii::t('SocialInviteModule.base','The Space ID Where Guests Will Land'); 
		$ResponsiveTop_title=Yii::t('SocialInviteModule.base','Put the Widget on Top when Responsive'); 
		$SISortOrder_title=Yii::t('SocialInviteModule.base','Set the sortOrder - Between 0 and 1000'); 
		
		return array(
			'theGroup' => $theGroup_title,
			'theSpace' => $theSpace_title,
			'ResponsiveTop' => $ResponsiveTop_title,
			'SISortOrder' => $SISortOrder_title,
		);
	}

}
