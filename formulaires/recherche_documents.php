<?php

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * chargement des valeurs par defaut des champs du #FORMULAIRE_RECHERCHE
 * on peut lui passer l'url de destination en premier argument
 * on peut passer une deuxième chaine qui va différencier le formulaire pour pouvoir en utiliser plusieurs sur une même page
 *
 * @param integer $id id_rubrique
 * @param string $options
 *        pour le moment uniquement parents = true : afficher les cotneus de ls sousribrique
 * @return array
 */
function formulaires_recherche_documents_charger_dist($id, $options=array()){
	$rubriques = _request('rubriques');
	$mots = _request('mots') ? _request('mots') : array();
	$valeurs = array(
		"recherche" => _request('recherche'),
		"par" => _request('par'),
		'mots' => _request('mots'),
		'_mots' => _request('mots'),
		'rubrique' => $id,
		);
	// Si le scope sousrubriques
	if (isset($options['parents'])) {
		// Si recherche sur rubriques déterminés on le retournes
		if (is_array($rubriques) AND array_sum($rubriques)>0 AND !in_array('all',$rubriques)){
			$valeurs['_rubriques'] = $rubriques;
			$valeurs['_rubriques_sel'] = $rubriques;
		}
		// sinon on recherche les sousrubriques
		else {
			$sql = sql_select('id_rubrique,titre','spip_rubriques','id_parent=' . $id);
			$rubriques = array();
			while ($data = sql_fetch($sql)) {
				$rubriques[] = $data['id_rubrique'];
			}
			$valeurs['_rubriques'] = $rubriques;
		}
		$valeurs['parent'] = TRUE;
	}
	else{
		$valeurs['_rubriques'] = $id;
	}

	// Si recherche sur mots déterminés on établis les articles correspondants
	if (is_array($mots) AND array_sum($mots)>0 AND !in_array('all',$mots)){

		$sql = sql_select("id_article","spip_articles AS articles LEFT JOIN  spip_mots_liens AS mots ON articles.id_article=mots.id_objet AND mots.objet='article'",'id_mot IN (' . implode(',',$mots) . ')','id_article');

		$articles = array();

		while ($data = sql_fetch($sql)){
			$articles[] = $data['id_article'];
		}

		if (count($articles) > 0) {
			$valeurs['where'] = 'id_article IN (' . implode(',',$articles) . ')';
		}
		else $valeurs['where'] = 'id_article = 0';
	}
	return $valeurs;
}
?>
