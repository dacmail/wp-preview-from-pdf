<?
/*
Plugin Name: Create Preview From PDF
Plugin URI: http://e-dac.es/
Description: Create a preview image from an uploaded PDF file, and assign it as post thumbnail.
Author: Daniel Aguilar
Version: 1.0
Author URI: http://e-dac.es/
*/
	class Logging{
	    // define default log file		
	    private $log_file = 'logger.log';
	    // define file pointer
	    private $fp = null;
	    // set log file (path and name)
	    public function lfile($path) {
	        $this->log_file = $path;
	    }
	    // write message to the log file
	    public function lwrite($message){
	        // if file pointer doesn't exist, then open log file
	        if (!$this->fp) $this->lopen();
	        // define script name
	        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
	        // define current time
	        $time = date('H:i:s');
	        // write current time, script name and message to the log file
	        fwrite($this->fp, "$time ($script_name) $message\n");
	    }
	    // open log file
	    private function lopen(){
	        // define log file path and name
	        $lfile = $this->log_file;
	        // define the current date (it will be appended to the log file name)
	        $today = date('Y-m-d');
	        // open log file for writing only; place the file pointer at the end of the file
	        // if the file does not exist, attempt to create it
	        $this->fp = fopen($lfile . '_' . $today, 'a') or exit("Can't open $lfile!");
	    }
	}
	
	add_action('add_attachment', 'create_preview_from_pdf');
	function create_preview_from_pdf($attachment_id) {
		//$log = new Logging();
		//$plugin = dirname(__FILE__) . '/create_preview_from_pdf.php';
		//$log->lfile(plugin_dir_path($plugin) . 'logfile.log');
		$attach = get_post($attachment_id);
		$parent = get_post($attach->post_parent);
		if ($attach->post_mime_type == 'application/pdf') :
			$upload_dir = wp_upload_dir($parent->post_date);
			$file = $upload_dir['path'] . '/' . basename(get_attached_file($attach->ID));
			//$log->lwrite('Archivo subido: ' . $file);
			if(!file_exists($file)) $log->lwrite('Archivo no existe: ' . $file);
			$jpg = $file . '.jpeg';
			system("convert " . $file . "[0] " . $jpg);
			//$log->lwrite('Archivo generado: ' . $jpg);
			if(!file_exists($jpg)) $log->lwrite('Archivo no existe: ' . $jpg);
			$wp_filetype = wp_check_filetype(basename($jpg), null );
		  	$attachment = array(
			     'post_mime_type' => $wp_filetype['type'],
			     'post_title' => get_the_title($attach->post_parent),
			     'post_content' => '',
			     'post_status' => 'inherit'
			  );
			//$log->lwrite('Tipo de archivo generado: ' . $wp_filetype['type']);
			$attach_id = wp_insert_attachment( $attachment, $jpg, $attach->post_parent );
		  	$attach_data = wp_generate_attachment_metadata( $attach_id, $jpg );
		  	wp_update_attachment_metadata( $attach_id, $attach_data );
			add_post_meta($attach->post_parent, '_thumbnail_id', $attach_id, true) or update_post_meta($attach->post_parent, '_thumbnail_id', $attach_id) ;
		endif;
	}
?>