<?php
if (! defined ( "_ECRIRE_INC_VERSION" ))
	return;
function notifications_creation_evenement_dist($quoi, $id_evenement, $options) {
	include_spip ( 'inc/config' );
	$nom_site = $GLOBALS ['meta'] ['nom_site'];
	$envoyer_mail = charger_fonction('envoyer_mail', 'inc');
	$subject = _T ('cncd:notification_sujet_creation_evenement', array (
			'nom_site' => $nom_site
		)
	);

	$message = recuperer_fond('notifications/contenu_creation_evenement_mail', array(
			'id_evenement' => $id_evenement,
			'qui' => 'visiteur',
	));

	// Envoyer les emails
	$o = array (
			'html' => $message,
			'from' => $options['from'],
			'repondre_a' => $options['from'],
	);
	$envoyer_mail($options['to'], $subject, $o);
}
