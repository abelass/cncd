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

	$flux .= '<link rel="stylesheet" type="text/css" href="' . find_in_path("styles/plugin_cncd.css") . '" />';

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

		$flux['data']['prive'] = $espace_prive;

		if (!$espace_prive) {
			$flux['data']['id_gis'] = _request('id_gis');
			$flux['data']['titre_gis'] = _request('titre_gis');
			$flux['data']['adresse_gis'] = _request('adresse_gis');
			$flux['data']['code_postale'] = _request('code_postale');
			$flux['data']['ville'] = _request('ville');
			$flux['data']['region'] = _request('region');
			$flux['data']['pays'] = _request('pays');
			$flux['data']['gis_url'] = _request('gis_url');
			$flux['data']['enregistrer_adresse'] = _request('enregistrer_adresse');
			$flux['data']['types_evenements'] = _request('types_evenements');
			$flux['data']['regions'] = _request('regions');
			$flux['data']['log_on'] = _request('log_on');
			
			$flux['data']['_hidden'] .= '<input type="hidden" name="id_parent" value="' . $flux['data']['id_parent'] . '" />';
		}
			$flux['data']['_hidden'] .= '<input type="hidden" name="statut" value="prop" />';
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
	if ($form == 'editer_evenement' AND !_request('exec')){

		// Vérifie la limite des charactères
		$limit_descriptif = 400;
		if($descriptif =_request('descriptif') AND strlen(textebrut($descriptif)) > $limit_descriptif) {
			$flux['data']['descriptif'] = _T('cncd:erreur_chacracteres', array(
				'limite' => $limit_descriptif
			));
		}

		// Champs obligatoires en cas d'enregistrement d'adresse
		if (_request('enregistrer_adresse')) {
			$obligatoires = array('titre_gis', 'adresse_gis', 'code_postal', 'ville', 'pays');

			foreach($obligatoires as $champ) {
				if (!_request($champ)) {
					$flux['data'][$champ] = _T("info_obligatoire");
				}
			}
		}
		
		// Le logo.
		$verifier_logo = charger_fonction('verifier','formulaires/editer_logo');
		if($erreurs = $verifier_logo('evenement', '')) {
			$flux = array_merge($flux['data'], $erreurs);
		}
		
	}
	return $flux;
}

/**
 * Permet de compléter le tableau de réponse ou d’effectuer des traitements supplémentaires.
 *
 * @pipeline formulaire_traiter
 * @param  array $flux Dornnées du pipeline
 * @return array       Données du pipeline
 */
function cncd_formulaire_traiter($flux){
	$form = $flux['args']['form'];

	if ($form == 'editer_evenement' AND !_request('exec')){
		include_spip('action/editer_gis');
		$id_evenement = $flux['data']['id_evenement'];

		//Traitement des point gis.
		if (_request('enregistrer_adresse')) {
			$editer_objet = charger_fonction('editer_objet','action');

			$set = array (
				'titre' => _request('titre_gis'),
				'adresse' => _request('adresse_gis'),
				'code_postal' => _request('code_postal'),
				'ville' => _request('ville'),
				'region' => _request('region'),
				'gis_url' => _request('gis_url')
			);

			$pays = sql_fetsel('code,nom', 'spip_pays', 'id_pays=' ._request('pays'));

			$set['pays'] = extraire_multi($pays['nom']);
			$set['code_pays'] = $pays['code'];

			$gis = $editer_objet('new', 'gis', $set);

			lier_gis($gis[0], 'evenement', $id_evenement);
		}
		elseif ($id_gis = _request('id_gis')) {
			lier_gis($id_gis, 'evenement',$id_evenement);
		}
	// Mettre l'événement en prop.
		sql_updateq('spip_evenements', array('statut' => 'prop'),'id_evenement=' .$id_evenement);

		// modifier l'auteur attaché
		sql_updateq("spip_auteurs_liens",
			array('id_auteur' => _request('id_auteur')),
			'objet =' .sql_quote('evenement') . ' AND id_objet=' . $id_evenement);

		// Lier les mots clés
		$types_evenements = _request('types_evenements') ? _request('types_evenements') : array();
		$regions = _request('regions') ? _request('regions') : array();

		$mots = array_merge($types_evenements, $regions);

		$set =array();

		foreach ($mots AS $id_mot) {
			$set[] = array(
				'id_mot' => $id_mot,
				'objet' => 'evenement',
				'id_objet' => $id_evenement,
				);
		}
		if (count($set) > 0) {
			sql_insertq_multi('spip_mots_liens', $set);
		}
		
		//Insérer le logo
		$uploader_logo = charger_fonction('traiter','formulaires/editer_logo');
		$logo = $uploader_logo('evenement', $id_evenement);

	}
	return $flux;
}

/**
 *  Permet de compléter ou modifier le résultat de la compilation d’un squelette donné.
 *
 * @pipeline recuperer_fond
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function cncd_recuperer_fond($flux){

	if ($flux['args']['fond'] == 'formulaires/editer_evenement' AND !_request('exec') ){
		$contexte = $flux['args']['contexte'];
		$contexte['saisies'] = array(
				array(
					'saisie' => 'points_gis',
					'options' => array(
						'nom' => 'id_gis',
						'label' => _T('cncd:label_point_gis'),
						'class' => 'chosen',
					),
				),
				array(
						'saisie' => 'textarea',
						'options' => array(
								'nom' => 'adresse',
								'label' => _T('agenda:evenement_adresse'),
								'explication' => _T('cncd:explication_evenement_adresse'),
								'rows' => 4,
						),
				),
				/*array(
					'saisie' => 'oui_non',
					'options' => array(
						'nom' => 'enregistrer_adresse',
						'label' => _T('cncd:label_enregistrer_adresse'),
					),
				),
				array(
					'saisie' => 'fieldset',
					'options' => array(
						'nom' => 'enregistrer_adresse',
						'label' => _T('cncd:legende_enregistrer_adresse'),
						//'afficher_si' => '@enregistrer_adresse@ == ""',
						),
					'saisies' => array (
						array(
							'saisie' => 'input',
							'options' => array(
								'nom' => 'titre_gis',
								'label' => _T('info_titre'),
							),
						),
						array(
							'saisie' => 'input',
							'options' => array(
								'nom' => 'adresse_gis',
								'label' => _T('gis:label_adress'),
							),
						),
						array(
							'saisie' => 'input',
							'options' => array(
								'nom' => 'code_postal',
								'label' => _T('gis:label_code_postal'),
							),
						),
						array(
							'saisie' => 'input',
							'options' => array(
								'nom' => 'ville',
								'label' => _T('gis:label_ville'),
							),
						),
						array(
							'saisie' => 'input',
							'options' => array(
								'nom' => 'region',
								'label' => _T('gis:label_region'),
							),
						),
						array(
							'saisie' => 'pays',
							'options' => array(
								'nom' => 'pays',
								'label' => _T('gis:label_pays'),
								'class' => 'chosen'
							),
						),
						array(
							'saisie' => 'url',
							'options' => array(
								'nom' => 'gis_url',
								'label' =>'Lien URL',
							),
						),
					),
				),*/
				array(
					'saisie' => 'auteurs',
					'options' => array(
						'nom' => 'id_auteur',
						'label' => _T('auteur'),
						'class' => 'chosen',
						'tri' => array('nom'),
					),
				),
				array(
					'saisie' => 'fieldset',
					'options' => array(
						'nom' => 'mots_cles',
						),
					'saisies' => array(
						array(
							'saisie' => 'mot',
							'options' => array(
								'nom' => 'types_evenements',
								'label' => "Types d’événements",
								'multiple' => 'oui',
								'id_groupe' => 31,
								'forcer_select' => 'oui',
								'class' => 'chosen'
							),
						),
						array(
							'saisie' => 'mot',
							'options' => array(
								'nom' => 'regions',
								'label' => "Régions",
								'multiple' => 'oui',
								'id_groupe' =>26,
								'forcer_select' => 'oui',
								'class' => 'chosen'
							),
						),
					)
				)
			);
		$gis = recuperer_fond('formulaires/evenement_champs_gis',$contexte);
		$flux['data']['texte'] = str_replace('<!--adresse-->', $gis . '<!--adresse-->', $flux['data']['texte']);
	}
	return $flux;
}
