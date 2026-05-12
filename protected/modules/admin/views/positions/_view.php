<div class="view">

	<?php echo GxHtml::encode($data->getAttributeLabel('id')); ?>:
	<?php echo GxHtml::link(GxHtml::encode($data->id), array('view', 'id' => $data->id)); ?>
	<br />

	<?php echo GxHtml::encode($data->getAttributeLabel('unique_code')); ?>:
	<?php echo GxHtml::encode($data->unique_code); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('property_code')); ?>:
	<?php echo GxHtml::encode($data->property_code); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('division_code')); ?>:
	<?php echo GxHtml::encode($data->division_code); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('department_code')); ?>:
	<?php echo GxHtml::encode($data->department_code); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('level')); ?>:
	<?php echo GxHtml::encode($data->level); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('code')); ?>:
	<?php echo GxHtml::encode($data->code); ?>
	<br />
	<?php /*
	<?php echo GxHtml::encode($data->getAttributeLabel('name')); ?>:
	<?php echo GxHtml::encode($data->name); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('amount')); ?>:
	<?php echo GxHtml::encode($data->amount); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('notes')); ?>:
	<?php echo GxHtml::encode($data->notes); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('status')); ?>:
	<?php echo GxHtml::encode($data->status); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('created_at')); ?>:
	<?php echo GxHtml::encode($data->created_at); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('updated_at')); ?>:
	<?php echo GxHtml::encode($data->updated_at); ?>
	<br />
	<?php echo GxHtml::encode($data->getAttributeLabel('deleted_at')); ?>:
	<?php echo GxHtml::encode($data->deleted_at); ?>
	<br />
	*/ ?>

</div>