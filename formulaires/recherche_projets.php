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
function formulaires_recherche_projets_charger_dist($id_rubrique){

	$valeurs = [
		'id_pays' => _request('id_pays'),
		'id_rubrique' => $id_rubrique,
		'themes' => _request('themes'),
		'organisations' => _request('organisations'),
	];

	return $valeurs;
}
