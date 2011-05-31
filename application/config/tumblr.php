<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Email address and Password used to log in to Tumblr.
// Required if doing anything other than reading posts.
$config['tumblr_email'] 	= '';
$config['tumblr_password']	= '';

// URL of tumblr blog
$config['tumblr_blog_url']	= 'http://[YOU].tumblr.com';

// Default read config settings. Used if reading posts
$config['tumblr_read_start'] 	= 0; // The post offset to start from. The default is 0. If reading Dasboard posts the maximum is 250. If reading liked posts the maximum is 1,000
$config['tumblr_read_num'] 		= 20; // The number of posts to return. The default is 20, and the maximum is 50.
$config['tumblr_read_type'] 	= ''; // The type of posts to return. If unspecified or empty, all types of posts are returned. Must be one of text, quote, photo, link, chat, video, or audio.
$config['tumblr_read_id'] 		= ''; // A specific post ID to return. Use instead of start, num, or type.
$config['tumblr_read_filter'] 	= ''; // Alternate filter to run on the text content. Allowed values: 'text' (Plain text only. No HTML.) or 'none' (No post-processing. Output exactly what the author entered.)
$config['tumblr_read_tagged'] 	= ''; // Return posts with this tag in reverse-chronological order (newest first).
$config['tumblr_read_chrono']	= 0; // Order of posts returned. The default is 0 (newest first). Optionally specify 1 to sort by oldest first.
$config['tumblr_read_search'] 	= ''; // Search for posts with this query.
$config['tumblr_read_state'] 	= ''; // (Authenticated read required) - Specify one of the values 'draft', 'queue', or 'submission' to list posts in the respective state.
$config['tumblr_read_likes']	= 1; // Used when reading Dashboard posts. 1 or 0, default 0. If 1, liked posts will have the liked="true" attribute.

// Default read config settings. Used if reading pages
$config['tumblr_read_show_link_enabled'] = true; // If true will only include pages where the "Show a link to this page" option in the Customize screen is enabled. Set to false to include ALL pages