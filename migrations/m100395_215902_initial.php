<?php

//use yii\db\Schema;
//use yii\db\Migration;
use humhub\components\Migration;

class m100395_215902_initial extends Migration{
	
	public function up(){
		$this->createTable('social_invite', [
			'id' => 'pk',
			'guest_email' => 'varchar(255) NULL',
			'originator_ID' => 'int(11) NULL',
			'originator_email' => 'varchar(255) NULL',
			'date_created' => 'datetime NULL DEFAULT CURRENT_TIMESTAMP',
			'date_updated' => 'datetime NULL DEFAULT CURRENT_TIMESTAMP',
			'times_sent' => 'int(11) NULL',
			], '');
		$this->safeCreateIndex('originator_ID','social_invite','originator_ID',false);
		}

	public function down(){
		echo "my_initial_social_invite does not support migration down.\n";
		return false;
		}
}
