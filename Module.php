<?php

namespace humhub\modules\social_invite;

use Yii;
use yii\helpers\Url;
use humhub\models\Setting;

use humhub\modules\ui\menu\MenuLink;
use humhub\modules\admin\widgets\AdminMenu;
use humhub\modules\admin\permissions\ManageModules;

class Module extends \humhub\components\Module
{

	/**
	 * On build of the dashboard sidebar widget, add the social_invite widget if module is enabled.
	 *
	 * @param type $event			
	 */
	
	public static function onSidebarInit($event) {
		if (Yii::$app->hasModule('social_invite')) {
			
			$SISortOrderSet=800; 
			/**/
			if (Setting::Get('SISortOrder', 'social_invite') >= 0) {
				$SISortOrderSet = Setting::Get('SISortOrder', 'social_invite'); 
				}
			
			$event->sender->addWidget(widgets\Sidebar::className(), array(), array('sortOrder' => intval($SISortOrderSet)));
			}
		}

	public function getConfigUrl() {
		return Url::to(['/social_invite/config/config']);
		}

	/**
	 * Enables this module
	 */
	public function enable()
	{
		parent::enable();

		if (Setting::Get('theGroup', 'social_invite') == '') {
			Setting::Set('theGroup', 1, 'social_invite'); 
			}
		if (Setting::Get('theSpace', 'social_invite') == '') {
			Setting::Set('theSpace', 1, 'social_invite'); 
			}
		if (Setting::Get('ResponsiveTop', 'social_invite') == '') {
			Setting::Set('ResponsiveTop', 0, 'social_invite'); 
			}
		if (Setting::Get('SISortOrder', 'social_invite') == '') {
			Setting::Set('SISortOrder', 20, 'social_invite'); 
			}
		}
	
	public static function onAdminMenuInit($event){
		
		if (!Yii::$app->user->can(ManageModules::class)) {
			return;
			}
		
		/** @var AdminMenu $menu */
		$menu = $event->sender;
		$menu->addEntry(new MenuLink([
			'label' => 'Social Invite',
			'url' => Url::to(['/social_invite/config/config']),
			//'group' => 'manage',
			'icon' => 'share-square-o',
			'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'social_invite' && Yii::$app->controller->id == 'admin'),
			'sortOrder' => 700,
			]));
		
		}

}

?>
