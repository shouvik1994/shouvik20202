<?php
	/**
	 * Handles the saving generated PDF to user-defined output file on server
	 */
	class MyDestinationFile extends Destination {
	  /**
	   * @var String result file name / path
	   * @access private
	   */
	  var $_dest_filename;
	
	  function MyDestinationFile($dest_filename) {
		$this->_dest_filename = $dest_filename;
	  }
	
	  function process($tmp_filename, $content_type) {
		copy($tmp_filename, $this->_dest_filename);
	  }
	}
	
	class MyFetcherMemory extends Fetcher {
	  var $base_path;
	  var $content;
	
	  function MyFetcherMemory($content, $base_path) {
		$this->content   = $content;
		$this->base_path = $base_path;
	  }
	
	  function get_data($url) {
		if (!$url) {
		  return new FetchedDataURL($this->content, array(), "");
		} else {
		  // remove the "file:///" protocol
		  if (substr($url,0,8)=='file:///') {
			$url=substr($url,8);
			// remove the additional '/' that is currently inserted by utils_url.php
			if (PHP_OS == "WINNT") $url=substr($url,1);
		  }
		  return new FetchedDataURL(@file_get_contents($url), array(), "");
		}
	  }
	
	  function get_base_url() {
		return 'file:///'.$this->base_path.'/dummy.html';
	  }
	}
?>