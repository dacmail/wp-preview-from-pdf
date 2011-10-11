<?
/*
Plugin Name: Create Preview from PDF
Plugin URI: http://e-dac.es/
Description: Creates a preview image from an uploaded PDF file, and assign it as post thumbnail.
Author: Daniel Aguilar
Version: 1.0
Author URI: http://e-dac.es/
*/
	add_action('add_attachment', 'create_preview_from_pdf');
	function create_preview_from_pdf($attachment_id) {

		$attach = get_post($attachment_id);
		if ($attach->post_mime_type == 'application/pdf') :
			$upload_dir = wp_upload_dir();
			$file = $upload_dir['path'] . '/' . basename(get_attached_file($attach->ID));
			$jpg = $file . '.jpeg';
			system("convert " . $file . "[0] " . $jpg);
			$wp_filetype = wp_check_filetype(basename($jpg), null );
		  	$attachment = array(
			     'post_mime_type' => $wp_filetype['type'],
			     'post_title' => get_the_title($attach->post_parent),
			     'post_content' => '',
			     'post_status' => 'inherit'
			  );
			$attach_id = wp_insert_attachment( $attachment, $jpg, $attach->post_parent );
		  	$attach_data = wp_generate_attachment_metadata( $attach_id, $jpg );
		  	wp_update_attachment_metadata( $attach_id, $attach_data );
			add_post_meta($attach->post_parent, '_thumbnail_id', $attach_id, true) or update_post_meta($attach->post_parent, '_thumbnail_id', $attach_id) ;
		endif;
	}
?>