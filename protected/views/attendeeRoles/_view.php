<div class="view">

	<?php echo GxHtml::encode($data->getAttributeLabel('id')); ?>:
	<?php echo GxHtml::link(GxHtml::encode($data->id), array('view', 'id' => $data->id)); ?>
	<br />

	<?php echo GxHtml::encode($data->getAttributeLabel('attendee_id')); ?>:
		<?php echo GxHtml::encode(GxHtml::valueEx($data->attendee)); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('role_id')); ?>:
		<?php echo GxHtml::encode(GxHtml::valueEx($data->role)); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('assigned_by')); ?>:
	<?php echo GxHtml::encode($data->assigned_by); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('assigned_at')); ?>:
	<?php echo GxHtml::encode($data->assigned_at); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('created_at')); ?>:
	<?php echo GxHtml::encode($data->created_at); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('updated_at')); ?>:
	<?php echo GxHtml::encode($data->updated_at); ?>
	<br />
	<?php /*
	<?php echo GxHtml::encode($data->getAttributeLabel('deleted_at')); ?>:
	<?php echo GxHtml::encode($data->deleted_at); ?>
	<br />
	*/ ?>

</div>