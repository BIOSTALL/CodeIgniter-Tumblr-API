<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tumblr API integration
 *
 * @package		Tumblr
 * @subpackage	Libraries
 * @category	Libraries
 * @author		BIOSTALL (Steve Marks)
 * @link		http://biostall.com
 */

class Tumblr {	
	
	// general
	var $tumblr_email 		= '';
	var $tumblr_password 	= '';
	var $tumblr_blog_url 	= '';
	
	// reading normal, dashboard and liked posts
	var $tumblr_read_start 	= 0; // The post offset to start from. The default is 0. If reading Dasboard posts the maximum is 250. If reading liked posts the maximum is 1,000
	var $tumblr_read_num 	= 20; // The number of posts to return. The default is 20, and the maximum is 50.
	var $tumblr_read_type 	= ''; // The type of posts to return. If unspecified or empty, all types of posts are returned. Must be one of text, quote, photo, link, chat, video, or audio.
	var $tumblr_read_id 	= ''; // A specific post ID to return. Use instead of start, num, or type.
	var $tumblr_read_filter = ''; // Alternate filter to run on the text content. Allowed values: 'text' (Plain text only. No HTML.) or 'none' (No post-processing. Output exactly what the author entered.)
	var $tumblr_read_tagged = ''; // Return posts with this tag in reverse-chronological order (newest first). 
	var $tumblr_read_chrono = 0; // Order of posts returned. The default is 0 (newest first). Optionally specify 1 to sort by oldest first.
	var $tumblr_read_search = ''; // Search for posts with this query.
	var $tumblr_read_state 	= ''; // (Authenticated read required) - Specify one of the values 'draft', 'queue', or 'submission' to list posts in the respective state.
	var $tumblr_read_likes	= 0; // Used when reading Dashboard posts. 1 or 0, default 0. If 1, liked posts will have the liked="true" attribute.
	
	// reading pages
	var $tumblr_read_show_link_enabled = true; // If true will only include pages where the "Show a link to this page" option in the Customize screen is enabled. Set to false to include ALL pages
	
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct($params = array())
	{
		if (count($params) > 0)
		{
			$this->initialize($params);
		}

		log_message('debug', "Tumblr Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize Preferences
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 * @return	void
	 */
	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
		
	}
	
    function read_posts() 
    {
    	
    	$posts = array();
    	
    	$params = array();
    	$method = "GET";
    	if ($this->tumblr_read_start!=0 && is_numeric($this->tumblr_read_start)) { array_push($params, "start=".$this->tumblr_read_start); }
    	if ($this->tumblr_read_num!=20 && is_numeric($this->tumblr_read_num)) { if ($this->tumblr_read_num>50) { $this->tumblr_read_num = 50; } array_push($params, "num=".$this->tumblr_read_num); }
    	if ($this->tumblr_read_type!="" && in_array($this->tumblr_read_type, array("text", "quote", "photo", "link", "chat", "video", "audio"))) { array_push($params, "type=".$this->tumblr_read_type); }
    	if ($this->tumblr_read_id!="" && is_numeric($this->tumblr_read_id)) { array_push($params, "id=".$this->tumblr_read_id); }
    	if ($this->tumblr_read_filter!="" && in_array($this->tumblr_read_filter, array("text", "none"))) { array_push($params, "filter=".$this->tumblr_read_filter); }
    	if ($this->tumblr_read_tagged!="") { array_push($params, "tagged=".$this->tumblr_read_tagged); }
    	if ($this->tumblr_read_chrono!=0 && is_numeric($this->tumblr_read_chrono)) { array_push($params, "chrono=".$this->tumblr_read_chrono); }
   		if ($this->tumblr_read_search!="") { array_push($params, "search=".$this->tumblr_read_search); }
    	if ($this->tumblr_read_state!="" && in_array($this->tumblr_read_type, array("draft", "queue", "submission"))) { $method = "POST"; array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password, "state=".$this->tumblr_read_state); }
    	
    	$posts = $this->parse_posts($this->tumblr_blog_url.'/api/read', implode("&", $params), $method);

    	return $posts;
    	
    }
    
    function read_dashboard() 
    {
    	
    	$posts = array();
    	
    	$params = array();
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password);
    	if ($this->tumblr_read_start!=0 && is_numeric($this->tumblr_read_start)) { if ($this->tumblr_read_num>250) { $this->tumblr_read_num = 250; } array_push($params, "start=".$this->tumblr_read_start); }
    	if ($this->tumblr_read_num!=20 && is_numeric($this->tumblr_read_num)) { if ($this->tumblr_read_num>50) { $this->tumblr_read_num = 50; } array_push($params, "num=".$this->tumblr_read_num); }
    	if ($this->tumblr_read_type!="" && in_array($this->tumblr_read_type, array("text", "quote", "photo", "link", "chat", "video", "audio"))) { array_push($params, "type=".$this->tumblr_read_type); }
    	if ($this->tumblr_read_filter!="" && in_array($this->tumblr_read_filter, array("text", "none"))) { array_push($params, "filter=".$this->tumblr_read_filter); }
    	if ($this->tumblr_read_likes!=0 && is_numeric($this->tumblr_read_likes)) { array_push($params, "likes=".$this->tumblr_read_likes); }
    	
    	$posts = $this->parse_posts('http://www.tumblr.com/api/dashboard', implode("&", $params), "POST");
    	
    	return $posts;
    	
    }
    
	function read_liked_posts() 
    {
    	
    	$posts = array();
    	
    	$params = array();
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password);
    	if ($this->tumblr_read_start!=0 && is_numeric($this->tumblr_read_start)) {  if ($this->tumblr_read_start>1000) { $this->tumblr_read_start = 1000; } array_push($params, "start=".$this->tumblr_read_start); }
    	if ($this->tumblr_read_num!=20 && is_numeric($this->tumblr_read_num)) { if ($this->tumblr_read_num>50) { $this->tumblr_read_num = 50; } array_push($params, "num=".$this->tumblr_read_num); }
    	if ($this->tumblr_read_filter!="" && in_array($this->tumblr_read_filter, array("text", "none"))) { array_push($params, "filter=".$this->tumblr_read_filter); }
    	
    	$posts = $this->parse_posts('http://www.tumblr.com/api/likes', implode("&", $params), "POST");

    	return $posts;
    	
    }
    
	function read_pages($all=false) 
    {
    	
    	$pages = array();
    	
    	$params = array();
    	$method = "GET";
    	if (!$this->tumblr_read_show_link_enabled) {
    		$method = "POST";
    		array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password);
    	}
    	
    	$xml = $this->make_request($this->tumblr_blog_url.'/api/pages', implode("&", $params), $method);
		
    	$xml = simplexml_load_string($xml);
   		
    	if (!$xml) { // error in parsing XML
    		
    		die("Unable to load URL: ".$url."<br /><br />It's possible that an invalid parameter was passed or that if requesting a specific page, this page doesn't exist.");	
    	
    	}else{ // XML parsed ok. Lets get the pages
    		
    		$i = 0;
    		
    		foreach ($xml->pages[0] as $page) { // loop through posts
    			
    			array_push($pages, array(
    				"body" => (string) $page
    			));
    			
    			if (count($page->attributes())) {
		    		foreach($page->attributes() as $a => $b) {
					    $pages[$i][$a] = (string) $b;
					}
    			}
				
				$i++;
    			
    		}
    		
    	}
    	
    	return $pages;
    	
    }
    
    function write_post($post_data=array())
    {
    	
    	$params = array();
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password);
    	if (isset($post_data['generator'])) { array_push($params, "generator=".$post_data['generator']); }
	    if (isset($post_data['date'])) { array_push($params, "date=".$post_data['date']); }
    	if (isset($post_data['tags'])) { array_push($params, "tags=".$post_data['tags']); }
	    if (isset($post_data['group'])) { array_push($params, "group=".$post_data['group']); }
	    if (isset($post_data['slug'])) { array_push($params, "slug=".$post_data['slug']); }
	    if (isset($post_data['state']) && in_array($post_data['state'], array("published", "draft", "queue", "submission"))) {
	    	array_push($params, "state=".$post_data['state']);
	    	if ($post_data['state']=="queue" && isset($post_data['publish-on'])) { array_push($params, "publish-on=".$post_data['publish-on']); }
	    }
	    if (isset($post_data['send-to-twitter'])) { array_push($params, "send-to-twitter=".$post_data['send-to-twitter']); }
    	if (isset($post_data['post-id']) && is_numeric($post_data['post-id'])) { $post_data['post-id'] = (string) $post_data['post-id']; array_push($params, "post-id=".$post_data['post-id']); }
    	if (isset($post_data['private']) && is_numeric($post_data['private'])) { array_push($params, "private=".$post_data['private']); }
		if (isset($post_data['format']) && in_array($post_data['format'], array("html", "markdown"))) { array_push($params, "format=".$post_data['format']); }
	    if (isset($post_data['type'])) { 
    		array_push($params, "type=".$post_data['type']);
    		switch ($post_data['type']) {
    			case "regular": {
    				if (isset($post_data['title'])) { array_push($params, "title=".$post_data['title']); }
    				if (isset($post_data['body'])) { array_push($params, "body=".$post_data['body']); }
    				break;
    			}
    			case "photo": {
    				if (isset($post_data['source'])) { array_push($params, "source=".$post_data['source']); }
    				// if (isset($post_data['data'])) { array_push($params, "data=".$post_data['data']); } // COMING SOON
	    			if (isset($post_data['caption'])) { array_push($params, "caption=".$post_data['caption']); }
	    			if (isset($post_data['click-through-url'])) { array_push($params, "click-through-url=".$post_data['click-through-url']); }
    				break;
    			}
    			case "quote": {
    				if (isset($post_data['quote'])) { array_push($params, "quote=".$post_data['quote']); }
    				if (isset($post_data['source'])) { array_push($params, "source=".$post_data['source']); }
    				break;
    			}
    			case "link": {
    				if (isset($post_data['name'])) { array_push($params, "name=".$post_data['name']); }
    				if (isset($post_data['url'])) { array_push($params, "url=".$post_data['url']); }
    				if (isset($post_data['description'])) { array_push($params, "description=".$post_data['description']); }
    				break;
    			}
    			case "conversation": {
    				if (isset($post_data['title'])) { array_push($params, "title=".$post_data['title']); }
    				if (isset($post_data['conversation'])) { array_push($params, "conversation=".$post_data['conversation']); }
    				break;
    			}
    			case "video": {
    				if (isset($post_data['embed'])) { array_push($params, "embed=".$post_data['embed']); }
    				// if (isset($post_data['data'])) { array_push($params, "data=".$post_data['data']); } // COMING SOON
	    			if (isset($post_data['title'])) { array_push($params, "title=".$post_data['title']); }
	    			if (isset($post_data['caption'])) { array_push($params, "caption=".$post_data['caption']); }
    				break;
    			}
    			case "audio": {
    				// if (isset($post_data['data'])) { array_push($params, "data=".$post_data['data']); } // COMING SOON
	    			if (isset($post_data['externally-hosted-url'])) { array_push($params, "externally-hosted-url=".$post_data['externally-hosted-url']); }
	    			if (isset($post_data['caption'])) { array_push($params, "caption=".$post_data['caption']); }
    				break;
    			}
    			default: { die("Invalid post type: ".$post_data['type']); }
    		} 
    	}
    	print_r($params);
    	$reponse = $this->make_request('http://www.tumblr.com/api/write', implode("&", $params), "POST");
    	
		return $reponse;
    	
    }
    
	function like_post($post_id="", $reblog_key="") 
    {
    	
    	$params = array();
    	
    	$post_id = (string) $post_id;
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password, "post-id=".$post_id, "reblog-key=".$reblog_key);
    	
    	$reponse = $this->make_request('http://www.tumblr.com/api/like', implode("&", $params), "POST");
    	
		return $reponse;
    	
    }
    
	function unlike_post($post_id="", $reblog_key="") 
    {
    	
    	$params = array();
    	
    	$post_id = (string) $post_id;
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password, "post-id=".$post_id, "reblog-key=".$reblog_key);
    	
    	$reponse = $this->make_request('http://www.tumblr.com/api/unlike', implode("&", $params), "POST");
    	
		return $reponse;
    	
    }
    
	function reblog_post($post_data=array())
    {
    
    	$params = array();
    	
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password);
    	if (isset($post_data['post-id']) && is_numeric($post_data['post-id'])) { $post_data['post-id'] = (string) $post_data['post-id']; array_push($params, "post-id=".$post_data['post-id']); }
	    if (isset($post_data['reblog-key'])) { array_push($params, "reblog-key=".$post_data['reblog-key']); }
	    if (isset($post_data['comment'])) { array_push($params, "comment=".$post_data['comment']); }
	    if (isset($post_data['as']) && in_array($post_data['as'], array("text", "link", "quote"))) { array_push($params, "as=".$post_data['as']); }
    	if (isset($post_data['format']) && in_array($post_data['format'], array("html", "markdown"))) { array_push($params, "format=".$post_data['format']); }
    	if (isset($post_data['group'])) { array_push($params, "group=".$post_data['group']); }
    	
    	$reponse = $this->make_request('http://www.tumblr.com/api/reblog', implode("&", $params), "POST");
    	
		return $reponse;
    	
    }
    
    function delete_post($post_id="")
    {
    
    	$params = array();
    	
    	$post_id = (string) $post_id;
    	array_push($params, "email=".$this->tumblr_email, "password=".$this->tumblr_password, "post-id=".$post_id);
    	
    	$reponse = $this->make_request('http://www.tumblr.com/api/delete', implode("&", $params), "POST");
    	
		return $reponse;
    	
    }
    	
    /*
    * Parse returned XML and return an array of posts
    */
    function parse_posts($url, $data="", $method="POST")
    {
    	
    	$posts = array();
    	
   		$xml = $this->make_request($url, $data, $method);
   		
   		$xml = simplexml_load_string($xml);
   		
    	if (!$xml) { // error in parsing XML
    		
    		die("Unable to load URL: ".$url."<br /><br />It's possible that an invalid parameter was passed or that if requesting a specific post, this post doesn't exist.");	
    	
    	}else{ // XML parsed ok. Lets get the posts
    		
    		$i = 0;
    		
    		foreach ($xml->posts[0] as $post) { // loop through posts
    			
    			array_push($posts, array(
    				"title" => (string) $post->{'regular-title'},
    				"body" => (string) $post->{'regular-body'}
    			));
    			
    			if (count($post->attributes())) {
		    		foreach($post->attributes() as $a => $b) {
					    $posts[$i][$a] = (string) $b;
					}
    			}
				
				$i++;
    			
    		}
    		
    	}
    	
    	return $posts;
    	
    }
    
	/*
    * Make request. Credit to Wez Furlong (http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/)
    */
	function make_request($url, $data="", $method="POST") {
        
    	$params = array('http' => array(
        	'method' => 'POST',
            'content' => $data
		));
  		$ctx = stream_context_create($params);
  		$fp = @fopen($url, 'rb', false, $ctx);
  		if (!$fp) {
    		throw new Exception("Problem with $url, $php_errormsg");
  		}
  		$response = @stream_get_contents($fp);
  		if ($response === false) {
    		throw new Exception("Problem reading data from $url, $php_errormsg");
  		}
  		return $response;
  		
	}
    
}