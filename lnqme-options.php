<?php
/*
 * Register the lnqme_admin_options
 */
function lnqmeUserDataInit()
{
	register_setting("lnqme_admin_options", "lnqme_options", "validateLNQMEUserData");
}

/*
 * Is the user a valid API user
 */
function isValidAPIUser()
{
	if(get_option("lnqme_invalid") !== false)
	{
		return false;
	}
	else
	{
		return true;
	}
}

/*
 * Clean the user input
 */
function clean($options)
{
	foreach ($options as $key => $value )
	{
		if(is_array($value))
		{
			$options[$key] = clean($value);
		}
		else
		{
			$options[$key] = trim(esc_attr(urlencode($value)));
		}
	}

	return $options;
}

/*
 * Validate the user input
 */
function validateLNQMEUserData($options)
{
	global $lnqme;

	$valid		 		= false;
	$options			= clean($options);

	if(!empty($options["lnqme_username"]) && !empty($options["lnqme_api_key"])) 
	{
		$validateUrl 	= "http://lnq.me/api/v2/validate?apiUser=" . $options["lnqme_username"] . "&apiKey=" . $options["lnqme_api_key"] . "&format=json";
		$response 		= @file_get_contents($validateUrl);
		
		if($response != false)
		{	
			$responseArray	= json_decode($response);
	
			if($responseArray->data == true)
			{
				$valid 		= true;
			}
		}
	}
	
	if($valid === true)
	{
		delete_option("lnqme_invalid");
	}
	else
	{
		update_option("lnqme_invalid", 1);
	}

	return $options;
}

class lnqmeOptions
{
	public $options;

	public function __construct(array $defaults)
	{
		$this->_refreshUserData($defaults);

		add_action("init", array($this, "checkUserData"));
	}
	
	/*
	 * Refresh the user data. 
	 */
	private function _refreshUserData($defaults)
	{
		$options 			= get_option("lnqme_options", false);

		if($options === false)
		{
			update_option("lnqme_options", $defaults);
		}
		elseif(is_array($options))
		{
			$diff 			= array_diff_key($defaults, $options);

			if(!empty($diff))
			{
				$options 	= array_merge($options, $diff);
				
				update_option("lnqme_options", $options);
			}
		}

		$this->options 		= $options;
	}
	
	/*
	 * Check the user data. If there is an user error, create a notice.
	 */
	public function checkUserData()
	{
		if(current_user_can("edit_posts"))
		{
			if(empty($this->options["lnqme_username"]) || empty($this->options["lnqme_api_key"]))
			{
				if(!isset($_GET["page"]) || $_GET["page"] != "lnqme")
				{
					add_action("admin_notices", array($this, "noticeSetup"));
				}
			}

			if(get_option("lnqme_invalid") !== false && isset($_GET["page"]))
			{
				add_action("admin_notices", array($this, "noticeInvalidUserData"));
			}
		}
	}
	
	/*
	 * Notice for the setup. If the plugin is installed create a notice for the LNQ.ME settings page.
	 */
	public function noticeSetup()
	{
		$title 			= __("LNQ.ME is almost ready!", "lnqme");
		$message 		= __("Please visit the <a href=\"options-general.php?page=lnqme\">" . __("settings page", "lnqme") . "</a> to configure LNQ.ME", "lnqme");

		return $this->displayNotice("<strong>{$title}</strong> {$message}", "error");
	}
	
	/*
	 * If the API user is invalid create a notice
	 */
	public function noticeInvalidUserData()
	{

		$title 			= __("LNQ.ME: Invalid username or API key!", "lnqme");
		$message 		= __("Your username or API key for LNQ.ME are not valid! You can't use the link shortener till the data is valid.", "lnqme");

		return $this->displayNotice("<strong>{$title}</strong> {$message}", "error");
	}

	/*
	 * Show all notices
	 */
	public function displayNotice($string, $type = "updated")
	{
		if($type != "updated")
		{
			$type 		= "error";
		}
		
		$string 		= "<div id=\"message\" class=\"" . $type . " fade\"><p>" . $string . "</p></div>";

		echo $string;
	}
}

/*
 * function to uninstall the plugin
 */
function uninstallLNQME()
{
	delete_option("lnqme_options");
	delete_option("lnqme_invalid");
	
	$posts = get_posts("numberposts=-1&post_type=any");

	foreach($posts as $post)
	{
		delete_post_meta($post->id, "lnqme_link");
	}
}

/*
 * Get the shortlink from an item
 */
function getShortlink($url, $id, $context)
{
	global $lnqme;
	
	if((is_singular() && !is_preview()) || $context == "post")
	{
		if(isset($_GET["post"]))
		{
			$short_link = get_post_meta($id, "lnqme_link", true);
			
			if (!$short_link || $short_link == "") 
			{
				$url 			= get_permalink($id);				
				$shortenUrl		= "http://lnq.me/api/v2/shorten?longUrl=" . urlencode($url) . "&apiUser=" . $lnqme->options["lnqme_username"] . "&apiKey=" . $lnqme->options["lnqme_api_key"] . "&format=json";
				$response		= @file_get_contents($shortenUrl);				
				$jsonObject		= json_decode($response);
				$short_link 	= $jsonObject->data;
				
				update_post_meta($id, "lnqme_link", $short_link);
			}
			
			return $short_link;
		}
	}
	
	return false;
}

/*
 * Get the click statistics of a item from the LNQ.ME API
 */
function getLNQMEStatistics()
{
	global $lnqme;
	
	$link			= get_post_meta($_GET["post"], "lnqme_link");
	
	if(isset($link[0]))
	{
		$response 		= json_decode(file_get_contents("http://lnq.me/api/v2/clicks?shortUrl=" . substr($link[0], -6) . "&format=json"));
		$data			= $response->data;
	}
	else
	{
		$data			= "No stats available";
	}	
	
	echo __("Number of hits: " . $data, "lnqme");
}

?>