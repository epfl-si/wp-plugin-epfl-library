<?php

/*
Plugin Name: EPFL Library Plugins
Plugin URI:
Description:
    1: Automatically inserts the Beast box in the Library pages
Version: 2.0
Author: Sylvain VUILLEUMIER
Author URI: https://people.epfl.ch/sylvain.vuilleumier
License: Copyright (c) 2020 Ecole Polytechnique Federale de Lausanne, Switzerland
*/

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
      "bibepfl" => "Bibliothèque de l\"EPFL",
      "lang" => "fr",
      "inscrire_message" => "Inscription",
      "inscrire_url" => "https://go.epfl.ch/inscrire",
      "compte_lecteur_message" => "Mon compte",
      "compte_lecteur_url" => "https://go.epfl.ch/beast-compte",
      "nouveautes_message" => "Nouveautés",
      "nouveautes_url" => "https://go.epfl.ch/acquisitions"
    );
	if ($lang == 'en'){
		$trad = array(
      "search_barcontent" => "Search in the BEAST catalog",
      "bibepfl" => "EPFL Library",
      "lang" => "en",
      "inscrire_message" => "Registration",
      "inscrire_url" => "https://go.epfl.ch/registration",
      "compte_lecteur_message" => "My acccount",
      "compte_lecteur_url" => "https://go.epfl.ch/beast-account",
      "nouveautes_message" => "New acquisitions",
      "nouveautes_url" => "https://go.epfl.ch/new-acquisitions"
		);
	}

    $beastbox_content = <<<HTML


    <style>

    /*==================== CSS ====================*/

    #searchbar {
      background-color: #FF0000;
      padding: 15px 20px 15px 20px;
      border-radius: 5px!important;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-around;
      align-items: center;
    }
    #searchbar label {
      position: absolute;
      top: 15px;
      width: 30px;
      height: 30px;
      color: white;
      margin: 0 auto;
    }
    /* Ajouter la loupe dans la barre de recherche */
    #searchbar label:before {
      content: "";
      position: absolute;
      left: 10px;
      top: 0;
      bottom: 0;
      width: 20px;
      /*background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='25' height='25' viewBox='0 0 25 25' fill-rule='evenodd'%3E%3Cpath d='M16.036 18.455l2.404-2.405 5.586 5.587-2.404 2.404zM8.5 2C12.1 2 15 4.9 15 8.5S12.1 15 8.5 15 2 12.1 2 8.5 4.9 2 8.5 2zm0-2C3.8 0 0 3.8 0 8.5S3.8 17 8.5 17 17 13.2 17 8.5 13.2 0 8.5 0zM15 16a1 1 0 1 1 2 0 1 1 0 1 1-2 0'%3E%3C/path%3E%3C/svg%3E") center / contain no-repeat;*/
      background-image: url("data:image/svg+xml,%3Csvg id='searchbutton' width='100%25' height='100%25' viewbox='0 0 24 24' y='264' style='transform:scale(-1,1)' xmlns='http://www.w3.org/2000/svg' fit='' preserveaspectratio='xMidYMid meet' focusable='false'%3E%3Cpath d='M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z'%3E%3C/path%3E%3C/svg%3E");
      opacity: .7;
    }

    /* Contenu de la barre de recherche */
    #searchbar input {
        padding-left: 3rem;
        height: 50px;
    }

    /*Supprime la marge du bas de la barre de recherche */
    #searchbar form,
    .form-group{
        margin-bottom: 0;
        width: 100%;
    }

    #searchbar select:hover {
        color: red;
        cursor: pointer;
    }
    #searchbar select option {
        color: black;
    }
    #searchbar select:option {
        transition: .2s;
    }

    /* Défini la largeur de la barre de recherche */
    .searchbar_div {
        width: 100%;
    }

    select#tab {
      height: 50px!important;
    }

    .ico {
        top : 5px;
        width: 30px;
        height: 30px;
        fill: white;
    }

    .menuBEAST {
      padding-left: 0;
      padding-right: 1%;
      display: flex;
      align-items: center;
      justify-content: space-around;
    }

    .menuBEAST a {
      color: white;
      font-weight: 500;
      padding: 10px 15px;
    }
    .menuBEAST a:active {
      text-decoration-color: white;
    }

    .menuBEAST a:hover {
      text-decoration-color: white;
    }

    form,
    .form-group {
      margin-bottom: 0;
    }

    /* ===== DIFFERENTS MEDIA QUERIES ===== */
    @media (max-width: 991px){
        #searchbar {
          padding-bottom: 0;
        }
        #searchbar a {
            text-align: center;
            padding: 15px 5px;
        }
    }
    </style>

    <script>
    function redirect_to_Beast(){

      var query = $("#querytext").val();
      var tab = "epfl";
      //var tab = $("#tab").val(); // On ne tient plus compte du choix du profil de recherche
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
          case "swisscovery plus":
            tab = "DN_and_CI_unfiltered";
            search_scope = "DN_and_CI_unfiltered";
            break;
      }

      var result = "https://epfl.swisscovery.slsp.ch/discovery/search?"+ "tab=" + tab + "&search_scope=" + search_scope + "&vid=41SLSP_EPF:prod&lang=" + lang + "&facet=rtype,exclude,reviews,lk";
      if (query.length > 0){
          result += "&query=any,contains," +  encodeURIComponent(query);
      }
      window.open(result);
  };
  </script>

<div class="container rounded-sm mb-5" id="searchbar">
    <form name="beast" method="get" action="javascript:redirect_to_Beast();">
      <div class="form-group">
        <div class="form-row">
          <div class="col-12 col-lg-6">
            <label></label>
            <input type="text" class="form-control mr-2" id="querytext" name="querytext" placeholder="$trad[search_barcontent]" />
          </div>
          <div class="col-12 col-lg-6 menuBEAST">
              <a href="$trad[inscrire_url]">$trad[inscrire_message]</a>
              <a href="$trad[compte_lecteur_url]">$trad[compte_lecteur_message]</a>
              <a href="$trad[nouveautes_url]">$trad[nouveautes_message]</a>
          </div>
          <!--
          <select class="custom-select col-md-3 d-none d-md-block" id="tab" name="tab">
            <option selected value="epfl">EPFL</option>
            <option value="swisscovery">swisscovery</option>
            <option value="swisscovery plus">swisscovery plus</option>
          </select>
          <div class="ico col-md-1 col-2 text-left">
            <a onclick="this.closest('form').submit();return false;" style="cursor:pointer">
              <svg id="searchbutton" width="100%" height="100%" viewbox="0 0 24 24" y="264" style="transform:scale(-1,1)" xmlns="http://www.w3.org/2000/svg" fit="" preserveaspectratio="xMidYMid meet" focusable="false">
                <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z"></path>
              </svg>
            </a>
          </div>
          -->
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