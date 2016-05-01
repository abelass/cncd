<?php
/**
 * Fonctions utiles au plugin CNCD
 *
 * @plugin     CNCD
 * @copyright  2016
 * @author     rainer
 * @licence    GNU/GPL
 * @package    SPIP\Cncd\Fonctions
 */

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
* Modifie l'autorisation pour permettre d'encoder publiquement un événement
*
* @param string $faire 
*   une action ('modifier', 'publier'...)
* @param string $type 
*   type d'objet ou nom de table ('article')
* @param int $id
*   id de l'objet sur lequel on veut agir
* @param null|int|array $qui
*   si null on prend alors visiteur_session
*   un id_auteur (on regarde dans la base)
*   un tableau auteur complet, y compris [restreint]
* @param null|array $options
*   options sous forme de tableau associatif
* @return bool
*   true si la personne peut effectuer l'action
*/
function autoriser_article_creerevenementdans($faire,$quoi,$id,$qui,$options){
	if (!$id) return false; // interdit de creer un evenement sur un article vide !
	// si on a le droit de modifier l'article alors on a peut-etre le droit d'y creer un evenement
	$afficher = false;
	if (autoriser('modifier','article',$id,$qui)) {
		$afficher = true;
		// un article avec des evenements a toujours le droit
		if (!sql_countsel('spip_evenements','id_article='.intval($id))){
			// si au moins une rubrique a le flag agenda
			if (sql_countsel('spip_rubriques','agenda=1')){
				// alors il faut le flag agenda dans cette branche !
				$afficher = false;
				include_spip('inc/rubriques');
				$in = calcul_hierarchie_in(sql_getfetsel('id_rubrique','spip_articles','id_article='.intval($id)));
				$afficher = sql_countsel('spip_rubriques',sql_in('id_rubrique',$in)." AND agenda=1");
			}
		}
	}
	//autoriser tout le monde depuis l'espace public
	elseif (!_request('exec')) {
		$afficher = true;
	}
	return $afficher;
}