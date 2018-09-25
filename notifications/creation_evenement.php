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
	$to = $options['to'];
	$options= array(
		'id_evenement' => $id_evenement,
		'qui' => 'visiteur',
	);

	$message = recuperer_fond('notifications/contenu_creation_evenement_mail', $options);

	// Envoyer les emails
	$o = array (
			'html' => $message,
			'from' => $options['from'],
			'destinataire' => $options['from'],
	);
	$envoyer_mail($to, $subject, $o);
}
