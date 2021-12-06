<?php

/*
Plugin Name: EPFL Library Plugins
Plugin URI:
Description:
    1: Provides a shortcode to transmit parameters to specific Library APP
    and get external content from this external source according to the
    transmitted parameters.
    2: Automatically inserts the Beast box in the Library pages
Version: 1.7
Author: Raphaël REY & Sylvain VUILLEUMIER
Author URI: https://people.epfl.ch/raphael.rey
Author URI: https://people.epfl.ch/sylvain.vuilleumier
License: Copyright (c) 2020 Ecole Polytechnique Federale de Lausanne, Switzerland
*/

/*
USAGE: [epfl_library_external_content url="xxx"]
Required parameter:
- url: url source of the external content

Optional parameters :
- script_url: url of an additional js script (required if script_name)
- script_name: name of the script in order to be able to call it (required if script_url)
- css_url: url of an additional css stylesheet (required if css_name)
- css_name: name of the css stylesheet (required if css_url)

The plugin will transmit the arguments of the current url to the external content url.

*/

// function epfl_library_external_content_log($message) {
//
//     if (WP_DEBUG === true) {
//         if (is_array($message) || is_object($message)) {
//             error_log(print_r($message, true));
//         } else {
//             error_log($message);
//         }
//     }
// }

function external_content_urlExists($url)
{
    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

    $response = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

    if ($httpCode >= 200 && $httpCode <= 400) {
        return true;
    } else {
        return false;
    }
    curl_close($handle);
}

function epfl_library_external_content_process_shortcode($attributes, $content = null)
{
    extract(shortcode_atts(array(
                'url' => '',
                'script_name' => '',
                'script_url' => '',
                'css_name' => '',
                'css_url' => ''
    ), $attributes));

    if (url == ''){
      $error = new WP_Error('URL missing', 'The url parameter is missing', $url);
      // epfl_library_external_content_log($error);
      return 'ERROR: url parameter empty.';
    }
    // Add optional css
    if ($css_name != '' and $css_url != ''){
        wp_enqueue_style($css_name, $css_url);
    }

    // Add optional script
    if ($script_name != '' and $script_url != ''){
        wp_enqueue_script($script_name, $script_url);
    }

    // Test the final concatened url
    if (external_content_urlExists($url)) {
        if (strpos($url, '?') !== false) {
			$response = wp_remote_get($url . '&' . $_SERVER['QUERY_STRING']);
		}
		else {
			$response = wp_remote_get($url . '?' . $_SERVER['QUERY_STRING']);
		}

        $page = $response['body'];
        return $page;
    } else {
        $error = new WP_Error('not found', 'The page cannot be displayed', $url);
        return 'ERROR: page not found.';
        // epfl_library_external_content_log($error);
    }
}

add_shortcode('epfl_library_external_content', 'epfl_library_external_content_process_shortcode');


function get_beastbox_content($lang){
/**
* Return the HTML to display the beastbox
* Jquery and Boostrap are required
*
* @param string $lang "en" or "fr". Default value is "fr".
* @return html template
*/
    //<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn" crossorigin="anonymous">
    //<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    $trad = array(
                "search_barcontent" => "Chercher dans le catalogue BEAST",
                "bibepfl" => "Bibliothèque de l'EPFL",
                "lang" => "fr",
            );
	if ($lang == 'en'){
		$trad = array(
			"search_barcontent" => "Search the library catalog",
			"bibepfl" => "EPFL Library",
			"lang" => "en",
		);
	}

    $beastbox_content = <<<HTML


    <style>

    #searchbar{
        /* background-color: #ae0010; */
        background-color: #FF0000;
        height: 70px;
    }
    #searchbar label{
        color:white;
    }
    .ico{
        top : 5px;
        width: 30px;
        height: 30px;
        fill: white;
    }
    </style>

    <script>
    function redirect_to_Beast(){

      var query = $("#querytext").val();
      var tab = $("#tab").val();
      var lang = "$trad[lang]";
      switch(tab) {
          case "epfl":
            tab = "41SLSP_EPF_MyInst_and_CI";
            search_scope = "MyInst_and_CI";
            break;
          case "swisscovery":
            tab = "41SLSP_EPF_DN_CI";
            search_scope = "DN_and_CI";
            break;
      }

      var result = "https://slsp-epfl.primo.exlibrisgroup.com/discovery/search?"+ "tab=" + tab + "&search_scope=" + search_scope + "&vid=41SLSP_EPF:prod&lang=" + lang;
      if (query.length > 0){
          result += "&query=any,contains," +  encodeURIComponent(query);
      }
      window.open(result);
  };
  </script>

  <div class="container rounded-sm mb-5" id="searchbar">
    <form name="beast" method="get" action="javascript:redirect_to_Beast();">
      <div class="form-group pt-3 ">
        <div class="form-row">
          <div class="col-md-8 col-10">
            <input type="text" class="form-control mr-2" id="querytext" name="querytext" placeholder="$trad[search_barcontent]" />
          </div>
          <select class="custom-select col-md-3 d-none d-md-block" id="tab" name="tab">
            <option value="epfl">$trad[bibepfl]</option>
            <option selected value="swisscovery">swisscovery</option>
          </select>
          <div class="ico col-md-1 col-2 text-left">
            <a onclick="this.closest('form').submit();return false;" style="cursor:pointer">
              <svg id="searchbutton" width="100%" height="100%" viewbox="0 0 24 24" y="264" style="transform:scale(-1,1)" xmlns="http://www.w3.org/2000/svg" fit="" preserveaspectratio="xMidYMid meet" focusable="false">
                <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"></path>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </form>
  </div>

HTML;

    return $beastbox_content;
}

function insert_beastbox($content) {

	// Specific Custom Fields
	$hide_beastbox = get_post_meta( get_the_ID(), 'hide_beastbox', true );

	if (( $hide_beastbox == "") && (!(is_admin()))) {

		if (function_exists('pll_current_language')) {
			if ( pll_current_language() == 'fr' ) {
				$content .= get_beastbox_content("fr") ;
			}
			else {
				$content .= get_beastbox_content("en");
			}
		}
		// Default French
		else {
			$content .= get_beastbox_content("fr");
		}
	}
	return $content . "<script>
		if ($('.hero').length) {
			$('#searchbar').insertBefore('.hero');
		}
		else {
			$('#searchbar').insertBefore('.entry-title')
		}
		</script>";
}

add_action( 'the_content', 'insert_beastbox' );


?>