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
			array('theGroup', 'integer', 'min' => 0, 'max' => 500),
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
		return array(
			'theGroup' => 'The group ID Allowed to Invite',
			'theSpace' => 'The Space ID Where Guests Will Land',
			'ResponsiveTop' => 'Put the Widget on Top when Responsive',
			'SISortOrder' => 'Set the sortOrder - Between 0 and 1000',
		);
	}

}
