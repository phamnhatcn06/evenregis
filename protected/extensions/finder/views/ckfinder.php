<script type="text/javascript" src="<?php echo $path."/ckfinder.js"; ?>"></script>
<script type="text/javascript">

	function BrowseServer<?php echo ($attribute)  ?>()
	{	   
        var finder = new CKFinder();
		finder.selectActionFunction = SetFileField<?php echo $attribute  ?>;
		finder.popup();                
                        
	}

	// This is a sample function which is called when a file is selected in CKFinder.
	function SetFileField<?php echo $attribute ?>( fileUrl )
	{	   
		document.getElementById( '<?php echo get_class($model).'_'.$attribute ?>' ).value = fileUrl;
	}
    
</script>

<?php echo CHtml::activeTextField($model,$attribute,array('maxlength' => 255, 'size' => 61)) ?>
<input type="button" value="Select" onclick="BrowseServer<?php echo ($attribute)  ?>();" />