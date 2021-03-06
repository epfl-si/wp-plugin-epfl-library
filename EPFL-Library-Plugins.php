<?php

/*
Plugin Name: EPFL Library Plugins
Plugin URI:
Description:
    1: Provides a shortcode to transmit parameters to specific Library APP
    and get external content from this external source according to the
    transmitted parameters.
    2: Automatically inserts the Beast box in the Library pages
Version: 1.5
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
        $response = wp_remote_get($url . '?' . $_SERVER['QUERY_STRING']);
        $page = $response['body'];
        return $page;
    } else {
        $error = new WP_Error('not found', 'The page cannot be displayed', $url);
        return 'ERROR: page not found.';
        // epfl_library_external_content_log($error);
    }
}

add_shortcode('epfl_library_external_content', 'epfl_library_external_content_process_shortcode');


/*

EPFL Library BEAST redirection
Description: Automatically redirect to the search tool BEAST depending of the url arguments

FONCTIONNEMENT:
    Les url http://library.epfl.ch/beast/ ou http://library.epfl.ch/en/beast/ renvoient
    à BEAST. La variante en majuscules pour BEAST http://library.epfl.ch/BEAST/ fonctionne
    également. Des paramètres peuvent être ajouté pour effectuer directement une recherche:
        - renvoi par défaut vers: http://beast-epfl.hosted.exlibrisgroup.com/primo_library/libweb/action/search.do?vid=EPFL
        - l'option "?isbn=9781849965378,1849965374" effectue une recherche avancée sur
        les isbn dans l'onglet par défaut (livres + périodiques)
        - L'option "?query=" effectue une recherche simple dans l'onglet "tout"
        - L'option et "?record=" effectue une recherche simple dans l'onglet "tout" en ajoutant au préalable
        ebi01_prod devant et en vérifiant que les numéros contiennent 9 caractères. Si
        des caractères manquent, des 0 sont ajoutés.

    En séparant les isbn ou identifiants par des virgules, des requetes sur plusieurs
    isbns sont possibles.

    Les langues sont prises en compte. Dans Primo, les langues sont gérées dans les préférences.
    Pour obtenir la langue de la page d'origine, il faut:
        1. Détecter la langue de la page d'origine (présence de "/en/" ou non dans l'url)
        2. Ajouter "&prefLang=en_US" ou rien à l'url ("&prefLang=fr_FR" provoque des problèmess)

    La langue test sert à vérifier si le script doit être exécuté. Il ne l'est pas si
    "/edit/" se trouve dans le pathname.

UTILISATION:
    1. Compléter/adapter les patterns
    2. Donner une url et les patterns en paramètre au constructeur
    3. Récupérer l'url de redirection via obj.getDestUrl()
        Exemple: window.location.href = new Url_redirect(window.location.href, PATTERNS).getDestUrl();
*/


function epfl_library_beast_redirect_process_shortcode($attributes, $content = null){
    return '<script>
"use strict";function Url_redirect(e,r){var t=this,n=r;t.params_to_analyse=Object.keys(n.params);var s=function(e){var r=e.indexOf("//")+2,t=e.indexOf("/",r),n=e.indexOf("?",t);return-1===n&&(t=e.length),r>=2&&t>=0?e.substring(t,n):""},a=function(e){var r=e.indexOf("?");return-1!==r?e.substring(r):""},i=function(e){for(var r=0;r<e.length;r++)e[r].match(/^\d{3,9}$/)&&(e[r].length<9&&(e[r]="0".repeat(9-e[r].length).concat(e[r])),e[r]="ebi01_prod".concat(e[r]));return e},l=function(e){for(var r="",t=0;t<e.length;t++)0===t?r=e[0]:r+="+OR+"+e[t];return r};t.url_src={url:e},Object.defineProperty(t.url_src,"search",{get:function(){return a(t.url_src.url)}}),Object.defineProperty(t.url_src,"pathname",{get:function(){return s(t.url_src.url)}}),Object.defineProperty(t,"paramsList",{get:function(){var e=[];if(t.url_src.search.length>0)for(var r=t.url_src.search.substring(1),n=r.split("&"),s=0;s<n.length;s++){var a=n[s].split("=");if(a.length>1){var i=a[0],l=a[1].split(",");e.push({key:i,values:l})}}return e}}),Object.defineProperty(t,"lang",{get:function(){for(var e=0;e<n.lang.length;e++)if(t.url_src.pathname.indexOf(n.lang[e].test)>-1&&!1===n.lang[e].default)return e;return n.lang.length-1}}),t.url_dest={},Object.defineProperty(t.url_dest,"key",{get:function(){for(var e=0;e<t.paramsList.length;e++)if(t.params_to_analyse.indexOf(t.paramsList[e].key)>-1)return t.paramsList[e].key;return null}}),Object.defineProperty(t.url_dest,"values",{get:function(){for(var e=0;e<t.paramsList.length;e++)if(t.paramsList[e].key===t.url_dest.key){var r=t.paramsList[e].values;return"record"===t.url_dest.key&&(r=i(r)),r}return[]}}),Object.defineProperty(t.url_dest,"path",{get:function(){return l(t.url_dest.values)}}),t.getDestUrl=function(){var e=n.default_url;return t.url_dest.key&&t.url_dest.path&&(e=n.params[t.url_dest.key]+t.url_dest.path),e+=n.lang[t.lang].path}}const PATTERNS={default_url:"https://slsp-epfl.primo.exlibrisgroup.com/discovery/search?vid=41SLSP_EPF:prod",params:{isbn:"https://slsp-epfl.primo.exlibrisgroup.com/discovery/search?tab=41SLSP_EPF_MyInst_and_CI&search_scope=MyInst_and_CI&vid=41SLSP_EPF:prod&facet=rtype,include,books&query=isbn,contains,",record:"https://slsp-epfl.primo.exlibrisgroup.com/discovery/search?tab=41SLSP_EPF_DN_CI&search_scope=DN_and_CI&vid=41SLSP_EPF:prod&query=any,contains,",query:"https://slsp-epfl.primo.exlibrisgroup.com/discovery/search?tab=41SLSP_EPF_MyInst_and_CI&search_scope=MyInst_and_CI&vid=41SLSP_EPF:prod&query=any,contains,",issn:"https://slsp-epfl.primo.exlibrisgroup.com/discovery/search?tab=41SLSP_EPF_MyInst_and_CI&search_scope=MyInst_and_CI&vid=41SLSP_EPF:prod&query=issn,contains,",fulltext:"https://kissrv117.epfl.ch/beast/redirect?nebis_id="},lang:[{name:"ed",test:"/edit/",path:"",default:!1},{name:"en",test:"/en/",path:"&prefLang=en",default:!1},{name:"default",test:"",path:"",default:!0}]};var link=new Url_redirect(window.location.href,PATTERNS);0!==link.lang&&(window.location.href=link.getDestUrl());
</script>';
}
add_shortcode('epfl_library_beast_redirect', 'epfl_library_beast_redirect_process_shortcode');

function insert_beastbox($content) {

	// Specific Custom Fields
	$hide_beastbox = get_post_meta( get_the_ID(), 'hide_beastbox', true );

	if (( $hide_beastbox == "") && (!(is_admin()))) {

		if (function_exists('pll_current_language')) {
			if ( pll_current_language() == 'fr' ) {
				$content .= do_shortcode( "[remote_content url='https://kissrv117.epfl.ch/beast/searchbox']" ) ;

			}
			else {
				$content .= do_shortcode( "[remote_content url='https://kissrv117.epfl.ch/beast/searchbox?lang=en']" );
			}
		}
		// Default French
		else {
			$content .= do_shortcode( "[remote_content url='https://kissrv117.epfl.ch/beast/searchbox']" );
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