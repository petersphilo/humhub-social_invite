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
			$social_invite=Yii::$app->getModule('social_invite'); 
			if ($social_invite->settings->get('SISortOrder') >= 0) {
				$SISortOrderSet = $social_invite->settings->get('SISortOrder'); 
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
		$social_invite=Yii::$app->getModule('social_invite'); 
		
		parent::enable();

		if ($social_invite->settings->get('theGroup') == '') {
			$social_invite->settings->set('theGroup', 0); 
			}
		if ($social_invite->settings->get('theSpace') == '') {
			$social_invite->settings->set('theSpace', 1); 
			}
		if ($social_invite->settings->get('ResponsiveTop') == '') {
			$social_invite->settings->set('ResponsiveTop', 0); 
			}
		if ($social_invite->settings->get('SISortOrder') == '') {
			$social_invite->settings->set('SISortOrder', 160); 
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
