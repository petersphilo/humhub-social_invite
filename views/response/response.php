<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="modal-dialog modal-dialog-normal animated fadeIn">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title">
				<?php echo $ResponseTitle; ?>
			</h4>
		</div>
		<br>
		
		<div class="panel-body	">
			
			<!-- Response Message:<br> -->
			
			<?php echo $ResponseMessage; ?>
			
			<br><br>
			
			
			<div style='color: #999; background-color: #eee; display: none; '>
				Activity Log:<br>
			
				<?php echo 'TheGuestEmail: '.$TheGuestEmail; ?>
				<br>
				<?php echo 'TheOriginatorID: '.$TheOriginatorID; ?>
				<br>
				<?php echo $ActivityLog; ?>
			</div>
		</div>


		<div class="modal-footer" style="padding: 5px">
			<hr>
			<?php
				/* echo 'footer here, if needed';  */
			?>
			<button type="button" class="btn btn-info btn-sm btn-close" data-dismiss="modal" aria-hidden="">Close</button>
		</div>

	</div>
</div>
