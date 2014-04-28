<?php
/**
 * Object used to manipulate image uploads
 */

require_once 'Frogg/img/class.upload.php';

class Frogg_Image{
	
	private $img;
	private $handle;
	
	/**
	 * @param mixed  $name File name for the image
	 * @param string $path Path where the file will be saved
	 */
	public function __construct($name,$path=false){
		if($path && $name){
			$this->img = $path.'/'.$name;
		} else {
			$this->img = $_FILES[$name];
		}
		$this->handle = new Upload($this->img);
	}
	
	/**
	 * Sets the file name for the new image
	 * @param string $path File system path to save the image to
	 */
	public function setName($name){
		$this->handle->file_new_name_body = $name;
		$this->handle->file_src_name_body = $name;
		$this->handle->file_dst_name_body = $name;
	}
	
	/**
	 * Save the current image on the desired path
	 * @param string $path File system path to save the image to
	 */
	public function save($path='i'){
		$this->handle->process($path);
		return $this->handle->file_dst_name;
	}
	
	/**
	 * Save the current image with fixed width
	 * @param string $width the width of the new image
	 * @param string $path File system path to save the image to
	 */
	public function saveFixedWidth($width, $path='i'){
		$this->handle->image_resize	 = true;
		$this->handle->image_ratio_y = true;
		$this->handle->image_x		 = $width;
		$this->handle->process($path);
		return $this->handle->file_dst_name;
	}
	
	/**
	 * Save the current image with fixed height
	 * @param string $height the height of the new image
	 * @param string $path File system path to save the image to
	 */
	public function saveFixedHeight($height, $path='i'){
		$this->handle->image_resize	 = true;
		$this->handle->image_ratio_x = true;
		$this->handle->image_y		 = $height;
		$this->handle->process($path);
		return $this->handle->file_dst_name;
	}
	
	/**
	 * Save the current image with max width and height keeping ratio
	 * @param int $width max width of the image
	 * @param int $height max height of the image
	 * @param string $path File system path to save the image to
	 */
	public function saveMaxWidthHeight($width, $height, $path='i'){
		$this->handle->image_resize	= true;
		$this->handle->image_ratio	= true;
		$this->handle->image_x		= $width;
		$this->handle->image_y		= $height;
		$this->handle->process($path);
		return $this->handle->file_dst_name;
	}
	
	/**
	 * Save the current image with fixed width and height cropping the excedent.
	 * @param int $width width of the thumbnail
	 * @param int $height height of the thumbnail
	 * @param string $path File system path to save the image to
	 */
	public function saveThumb($width, $height, $path='i'){
		$this->handle->image_resize		= true;
		$this->handle->image_ratio_crop	= true;
		$this->handle->image_x			= $width;
		$this->handle->image_y			= $height;
		$this->handle->process($path);
		return $this->handle->file_dst_name;
	}
	
	/**
	 * Open the image on the browser as a download
	 */
	public function download(){
		header('Content-type: ' . $this->handle->file_src_mime);
		header("Content-Disposition: attachment; filename=".rawurlencode($this->handle->file_src_name).";");
		echo $this->handle->process();
		die;
	}
	
	/**
	 * Checkes whether a file is an image
	 * @param string $name Name of the $_FILES[] field to be checked
	 */
	public static function isImage($name){
		if(isset($_FILES[$name])){
			$tempFile = $_FILES[$name]['tmp_name']; 
			$image = getimagesize($tempFile); 
			switch($image['mime']){
				case 'image/jpeg':
				case 'image/gif':
				case 'image/png':
				case 'image/bmp':
				case 'image/tiff':
				case 'image/jpeg':
					return true;
			}
		}
		return false;
	}
}