<?php
$_ADDONLANG['scheduled_direct_debits'] = 'Prélévements';
$_ADDONLANG['scheduled_on'] = 'est prévu pour prélévement le ';
$_ADDONLANG['total_scheduled'] = 'Le montant total de votre prélévement est ';

// Direct debit authorization form
$_ADDONLANG['directdebit_mandate'] = 'Mandat de prélévement';
$_ADDONLANG['directdebit_no_mandate'] = 'Nous ne pouvons valider cette facture pour l\'instant. Ouvrez le';
$_ADDONLANG['directdebit_authorization'] = 'Recurring collections';
$_ADDONLANG['directdebit_intro'] = 'Vous pouvez régler vos factures par prélévement. Nous n’avons pas encore reçu le mandat rempli pour traiter vos paiements. Avec ce mandat vous nous autorisez à prélever le montant de vos factures.';
$_ADDONLANG['directdebit_name'] = 'Nom';
$_ADDONLANG['directdebit_address'] = 'Adresse';
$_ADDONLANG['directdebit_postcode'] = 'Code Postal';
$_ADDONLANG['directdebit_city'] = 'Ville';
$_ADDONLANG['directdebit_country'] = 'Pays';
$_ADDONLANG['directdebit_creditorid'] = 'Creditor ID';
$_ADDONLANG['directdebit_mandate_reference'] = 'Référence du Mandat';
$_ADDONLANG['directdebit_mandate_clientid'] = 'ID Client';
$_ADDONLANG['directdebit_mandate_reason'] = 'Raison du paiement';
$_ADDONLANG['directdebit_sign_first'] = 'En signant ce formulaire vous autorisez';
$_ADDONLANG['directdebit_sign_second'] = 'a envoyer de maniére récurente à votre banque une demande de prélévement et vous autorisez votre banque à vous prélever le montant du chaque mois en corrélation avec :';
$_ADDONLANG['directdebit_sign_third'] = 'Si vous êtes en déssacord avec la transaction vous avez le droit à un remboursement. Contactez votre banque dans les 8 semaines. Demandez à votre banque les conditions.';
$_ADDONLANG['directdebit_city_date'] = 'Lieu et Date';
$_ADDONLANG['directdebit_signature'] = 'Signature';
$_ADDONLANG['directdebit_close'] = 'Fermer cette fenêtre et voir la facture';
$_ADDONLANG['directdebit_clear'] = 'Effacer la signature';
$_ADDONLANG['directdebit_sign'] = 'Signer le mandat';
$_ADDONLANG['directdebit_sign_failed'] = 'Vous n\'avez pas signé le mandat.';
$_ADDONLANG['directdebit_invoice_process'] = 'Cette facture sera prélévée sur votre compte numéro ';
$_ADDONLANG['directdebit_default_payment_method'] = 'Changer mon moyen de paiement par défaut par le prélévement';


// Admin area
$_ADDONLANG['modulename'] = 'Incasso';

// Mandate index
$_ADDONLANG['general'] = 'General';
$_ADDONLANG['mandates'] = 'Mandat';
$_ADDONLANG['create'] = 'Ajouter une demande ';
$_ADDONLANG['manage'] = 'Liste des demandes';
$_ADDONLANG['settings'] = 'Parametres des demandes';

$_ADDONLANG['uid'] = 'UID';
$_ADDONLANG['client_name'] = 'Nom du Client';
$_ADDONLANG['view_mandate'] = 'Voir le mandat';
$_ADDONLANG['signing_date'] = 'Date de signature';
$_ADDONLANG['introduction'] = 'Voir les mandats signés. Il est impossible de modifier un mandat. Si le mandat est incorrect il faut en émettre un nouveau.';
$_ADDONLANG['records_found'] = 'Enregistrements trouvés';
$_ADDONLANG['search'] = 'Rechercher';
$_ADDONLANG['clear'] = 'Nettoyer Filtres';
$_ADDONLANG['clientsearch_placeholder'] = 'Cliquez pour chercher des clients';

// View mandate
$_ADDONLANG['go_back'] = 'Retour';
$_ADDONLANG['signed_by_ip'] = 'Ce formulaire a été rempli par l\'adresse Ip suivante :';
$_ADDONLANG['signed_at'] = 'à';
$_ADDONLANG['no_mandate_found'] = 'Ce mandat n\'existe pas.';

// Client area
$_ADDONLANG['direct_debit'] = 'prélévement';
$_ADDONLANG['menu_list'] = 'Vue Globale';
$_ADDONLANG['menu_sign'] = 'Mandat en Ligne';
$_ADDONLANG['valid_mandate_exists'] = 'Un mandat de prélévement est déjà actif sur votre compte.Contactez nous si vous souhaitez le révoquer ou le modifier.';
$_ADDONLANG['help_signature'] = 'Utilisez votre souris pour signer le mandat';
$_ADDONLANG['sign_mandate_failed'] = 'Nous ne pouvons valider le formulaire. Veuillez ouvrir un ticket au support.';

// Help
$_ADDONLANG['help_bic'] = 'Le code BIC est l\'identifiant unique de chaque banque.';
$_ADDONLANG['help_default_payment_method'] = 'Cette action va changer votre de mode de paiement par défaut par le prélévement.';

if (file_exists(__DIR__ . '/overrides/french.php')) {
  require __DIR__ . '/overrides/french.php';
}

?>
