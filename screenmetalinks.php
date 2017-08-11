<?php

/**
 * @author Janis Elsts
 * @copyright 2010
 */

class wsScreenMetaLinks10 
{
	public $registered_links;
	
	public function wsScreenMetaLinks10()
	{
		$this->registered_links = array();
		
		add_action("admin_notices", array(&$this, "append_meta_links"));
		add_action("admin_print_styles", array(&$this, "add_link_styles"));
	}
	
	public function add_screen_meta_link($id, $text, $href, $page, $attributes = null)
	{
		if(!is_array($page))
		{
			$page 			= array($page);
		}
		
		if(is_null($attributes))
		{
			$attributes 	= array();
		}
		
		$link = compact("id", "text", "href");
		$link = array_merge($link, $attributes);
		
		if(empty($link["class"]))
		{
			$link["class"] 	= "";
		}
		
		$link["class"] 		= "show-settings custom-screen-meta-link " . $link["class"];
		
		foreach($page as $page_id)
		{
			if(!isset($this->registered_links[$page_id]))
			{
				$this->registered_links[$page_id] 	= array();
			}
			
			$this->registered_links[$page_id][] 	= $link;
		}
	}

	public function append_meta_links()
	{
		global $hook_suffix;
		
		$links = $this->get_links_for_page($hook_suffix);
		
		if(empty($links))
		{
			return;
		}
		
		?>
		<script type="text/javascript">
			(function($, links){
				var container = $("#screen-meta-links");
				for(var i = 0; i < links.length; i++){
					container.append(
						$("<div/>")
							.attr({
								"id" 	: links[i].id + "-wrap",
								"class" : "hide-if-no-js screen-meta-toggle custom-screen-meta-link-wrap"
							})
							.append( $("<a/>", links[i]) )
					);
				}
			})(jQuery, <?php echo $this->json_encode($links); ?>);
		</script>
	<?php
	}
	
	public function get_links_for_page($page)
	{
		$links = array();
		
		if(isset($this->registered_links[$page]))
		{
			$links 			= array_merge($links, $this->registered_links[$page]);
		}
		
		$page_as_screen 	= $this->page_to_screen_id($page);
		
		if(($page_as_screen != $page) && isset($this->registered_links[$page_as_screen]))
		{
			$links 			= array_merge($links, $this->registered_links[$page_as_screen]);
		}
		
		return $links;
	}
	
	public function add_link_styles()
	{
		global $hook_suffix;

		$links = $this->get_links_for_page($hook_suffix);
		
		if(empty($links))
		{
			return;
		}
		?>
		<style type="text/css">
			.custom-screen-meta-link-wrap {
				float: right;
				height: 22px;
				padding: 0;
				margin: 0 6px 0 0;
				font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
				background: #C3DFEB;
				color: #FFFFFF;
				
				border-bottom-left-radius: 3px;
				border-bottom-right-radius: 3px;
				-moz-border-radius-bottomleft: 3px;
				-moz-border-radius-bottomright: 3px;
				-webkit-border-bottom-left-radius: 3px;
				-webkit-border-bottom-right-radius: 3px;
			}
			
			#screen-meta .custom-screen-meta-link-wrap a.custom-screen-meta-link {
				background-image: none;
				padding-right: 6px;
			}
		</style>
	<?php
	}

	public function page_to_screen_id($page)
	{
		if(function_exists("convert_to_screen"))
		{
			$screen = convert_to_screen($page);
			
			if(isset($screen->id))
			{
				return $screen->id;
			} 
			else 
			{
				return "";
			}
		} 
		else 
		{
			return str_replace( array(".php", "-new", "-add" ), "", $page);
		}
	}
	
	public function json_encode($data)
	{
		if(function_exists("json_encode"))
		{
			return json_encode($data);
		} 
		else 
		{
			$json 	= new Services_JSON();
			
        	return $json->encodeUnsafe($data);
		}
	}
}

global $ws_screen_meta_links_versions;

if(!isset($ws_screen_meta_links_versions))
{
	$ws_screen_meta_links_versions		= array();
}

$ws_screen_meta_links_versions["1.0"] 	= "wsScreenMetaLinks10";


function add_screen_meta_link($id, $text, $href, $page, $attributes = null)
{
	global $ws_screen_meta_links_versions;
		
	static $instance 	= null;
	
	if(is_null($instance))
	{
		uksort($ws_screen_meta_links_versions, "version_compare");
		
		$className 		= end($ws_screen_meta_links_versions);
		$instance 		= new $className;
	}
	
	return $instance->add_screen_meta_link($id, $text, $href, $page, $attributes);
}
?>