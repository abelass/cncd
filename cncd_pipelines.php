<?php
/**
 * Utilisations de pipelines par CNCD
 *
 * @plugin     CNCD
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cncd\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Inserer la CSS de l'agenda et jquery-ui.multidatespicker.js
 * 
 *
 * @param $flux
 * @return mixed
 */
function cncd_insert_head_css($flux){

	$flux .= '<link rel="stylesheet" type="text/css" href="' . find_in_path("css/spip.agenda.css") . '" />';

	return $flux;
}


/**
 * Permet de modifier le tableau de valeurs envoyé par la fonction charger d’un formulaire CVT
 *
 * @pipeline formulaire_charger
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cncd_formulaire_charger($flux){
	$form = $flux['args']['form'];
	if ($form == 'editer_evenement'){
	
		if (!$espace_prive = _request('exec')) {
			$espace_prive = FALSE;
			
		}
		
		$flux['data']['espace_prive'] = $espace_prive;
		
		if (!$espace_prive) {
			$flux['data']['_hidden'] .= '<input type="hidden" name="id_parent" value="' . $flux['data']['id_parent'] . '" />';
		}
	}
	return $flux;
}

/**
 *  Permet de compléter le tableau d’erreurs renvoyé par la fonction verifier du formulaire en question.
 *
 * @pipeline formulaire_verifier
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cncd_formulaire_verifier($flux){
	$form = $flux['args']['form'];
	if ($form == 'editer_evenement'){
		$limit_descriptif = 400;
		if($descriptif =_request('descriptif') AND strlen(textebrut($descriptif)) > $limit_descriptif) {
			$flux['data']['descriptif'] = _T('cncd:erreur_chacracteres', array(
				'limite' => $limit_descriptif
			));
		}
	}
	return $flux;
}
