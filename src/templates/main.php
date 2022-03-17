<div class="Content">
	<h1 class="Title">Docx Batch Import</h1>
	<div class="Desc">
		Please select a zip folder containing the niche folders with articles properly formatted inside.
		<br>
		If you need a template of the zip format, please 
		<a href="<?php echo plugins_url( 'templates/Example.zip', __FILE__ ); ?>">click here.</a>
	</div>
	<br><br>
	<form class="Form" method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" enctype="multipart/form-data" >
		<input type="file"   name="zip" value="" />
		<input type="hidden" name="action" value="batchImport" />
		<input type="hidden" name="BatchImport" value="0" />
		<?php wp_nonce_field('batchImportNonce', 'batchImportNonce'); ?>
		<input type="submit" value="Submit" />
	</form>
</div>

<?php

$post = get_post(1680);
var_dump($post->post_title);


?>