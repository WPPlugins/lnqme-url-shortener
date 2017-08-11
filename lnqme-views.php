<?php
/*
 * Add settings page for LNQ.ME
 */
function addPages()
{
	$hook = add_options_page("LNQ.ME Options", "LNQ.ME", "edit_posts", "lnqme", "setupForm");
		
	add_action("admin_print_styles-" . $hook, "lnqmeCss"); 
}

/*
 * Get the custom CSS for the settings page
 */
function lnqmeCss()
{
	wp_enqueue_style("dashboard");
	wp_enqueue_style("lnqme", plugins_url("", __FILE__) . "/css/lnqme.css", false, "screen");
}

/*
 * Setup the frame for the settings page
 */
function setupForm()
{
	global $lnqme;
	?>	
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2 style="margin-bottom: 1em;"><?php echo __("LNQ.ME Options", "lnqme") ?></h2>
		<div class="postbox-container" style="width: 75%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
					<form action="options.php" id="lnqme" method="post">
					<?php
			        	settings_fields("lnqme_admin_options");
						setupOptions();
					?>
					</form>
				</div>
			</div>
		</div>
		<div class="postbox-container" style="width: 24%;">
			<div class="metabox-holder">	
				<div class="meta-box-sortables">
				<?php
					setupSupportBox();
				?>
				</div>
			</div>
		</div>
	</div>
<?php
}

/*
 * Create the content for the settings page
 */
function setupOptions()
{
	global $lnqme;

	$options 	= array();
	$options[] 	= array	(
							"id"    => "lnqme_username",
							"name"  => __("LNQ.ME username:", "lnqme"),
							"desc"  => __("Your LNQ.ME username", "lnqme" ),
							"input" => "<input name=\"lnqme_options[lnqme_username]\" type=\"text\" value=\"".$lnqme->options["lnqme_username"]."\" />"
						);
	$options[] = array	(
							"id"    => "lnqme_api_key",
							"name"  => __("LNQ.ME API key:", "lnqme" ),
							"desc"  => __("Your API key can be found on http://www.lnq.me/", "lnqme"),
							"input" => "<input name=\"lnqme_options[lnqme_api_key]\" type=\"text\" value=\"". $lnqme->options["lnqme_api_key"] . "\" />"
						);

	$output  = "<div class=\"intro\">";
	$output .= "<p>" . __("Configure your LNQ.ME plugin here.", "lnqme" ) . "</p>";
	$output .= "</div>";
	$output .= buildSetupForm($options);

	buildSetupBox("lnqme_options", __("General Settings", "lnqme"), $output);
}

/*
 * Create the content for the side box on the settings page
 */
function setupSupportBox()
{
	$output  = "<p>" . __("If you require support or do you want to support us? Please choose one of the links below", "lnqme") . "</p>";
	$output .= "<ul>";
	$output	.= __("<li><a href=\"http://www.lnq.me\" title=\"LNQ.ME\" target=\"_blank\">Visit the plugin homepage</a></li>", "lnqme");
	$output .= "</ul>";

	buildSetupBox("sidebox", "LNQ.ME", $output );
}

/*
 * Create the setup box
 */
function buildSetupBox($id, $title, $content)
{
	$output  = "<div id=\"lnqme_" . $id . "\" class=\"postbox\">";
	$output .= "<h3 class=\"hndle\"><span>" . $title . "</span></h3>";
	$output .= "<div class=\"inside\">";
	$output .= $content;
	$output .= "</div></div>";

	echo $output;

	return $output;
}

/*
 * Create the settings form
 */
function buildSetupForm($options, $button = "secondary")
{

	$output 	= "<fieldset>";

	foreach ($options as $option)
	{
		$output .= "<dl" . (isset($option["class"]) ? " class=\"" . $option["class"] . "\"" : "") . ">";
		$output .= "<dt><label for=\"lnqme_options[" . $option["id"] . "\">" . $option["name"] . "</label>";

		if(isset( $option["desc"]))
		{
			$output .= "<p>" . $option["desc"] . "</p>";
		}

		$output .= "</dt>";
		$output .= "<dd>" . $option["input"] . "</dd>";
		$output .= "</dl>";

	}

	$output 	.= "<div style=\"clear: both;\"></div>";
	$output 	.= "<p class=\"lnqme_submit\"><input type=\"submit\" class=\"button-" . $button . "\" value=\"" . __("Save", "lnqme") . "\" /></p>";
	$output 	.= "</fieldset>";

	return $output;
}

/*
 * If the user is a valid API user, create the LNQ.ME statistics box
 */
function addLNQMEStatisticsBox()
{
	if(isValidAPIUser() && isset($_GET["post"]))
	{
    	add_meta_box("lnqme_statistics", __("LNQ.ME link statistics", "lnqme"), "getLNQMEStatistics", "post", "side", "high");
    	add_meta_box("lnqme_statistics", __("LNQ.ME link statistics", "lnqme"), "getLNQMEStatistics", "page", "side", "high");
	}
	else
	{
		return false;
	}
}

/*
 * Add the settings link for LNQ.ME in the plugin overview
 */
function addSettingsLink($links, $file)
{
	static $plugin;
	
	if(!$plugin)
	{
		$plugin = plugin_basename(__FILE__);
	}
	
	if($file == $plugin)
	{
		$settings_link = "<a href=\"options-general.php?page=lnqme\" title=\"Settings\">" . __("Settings", "lnqme") . "</a>";
		
		array_unshift($links, $settings_link);
	}
	
	return $links;
}

/*
 * Create the "Twitter this" link
 */
function createTwitterButton()
{
	if(isset($_GET["post"]))
	{
		$shortlink		= get_post_meta($_GET["post"], "lnqme_link");
		$title			= get_the_title($_GET["post"]);
		
		if(!empty($shortlink) && isset($shortlink[0]))
		{
			add_screen_meta_link(	"twitter-link", "Tweet this link!", "http://www.twitter.com/?status=" . $shortlink[0], 
									array("post", "page"), array("target" => "_blank", "title" => "Tweet this link!"));
		}
	}
	else
	{
		return false;
	}
}

?>