<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if (!isset($user->id)) {
    http_response_code(401);
    echo '{"message": "Utilisateur non authentifié"}';
    exit();
}

$idUser = $user->id;

$requete = $connexion->prepare("SELECT r.date_debut, r.date_fin, r.accepte, m.nom, m.numero_de_serie, r.date_demande
                                FROM reservation AS r
                                LEFT JOIN materiel AS m ON r.id_materiel = m.id
                                WHERE r.id_loueur = ?");
$requete->execute([$idUser]);

$reservations = $requete->fetchAll(PDO::FETCH_ASSOC);

foreach ($reservations as &$reservation) {
    // Formater date_debut
    $date_debut = new DateTime($reservation['date_debut']);
    $reservation['date_debut'] = $date_debut->format('d-m-Y');

    // Formater date_fin
    $date_fin = new DateTime($reservation['date_fin']);
    $reservation['date_fin'] = $date_fin->format('d-m-Y');

    // Formater date_demande
    $date_demande = new DateTime($reservation['date_demande']);
    $reservation['date_demande'] = $date_demande->format('d-m-Y');

    // Convertir accepte en booléen
    $reservation['accepte'] = $reservation['accepte'] > 0 ? 'Oui' : 'Non';
}

echo json_encode($reservations);
