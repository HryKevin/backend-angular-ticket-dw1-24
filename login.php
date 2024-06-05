<?php

include 'header-init.php';
include 'jwt-helper.php';

//tansformer le JSON en objet PHP contenant les informations de l'utilisateur
$json = file_get_contents('php://input');

// Le convertit en objet PHP
$utilisateur = json_decode($json);


// vérifier que l'utilisateur existe dans la base de donnée
$requete = $connexion->prepare("SELECT u.id, u.email, u.firstname, u.lastname, u.password , r.name as role 
                                FROM user as u
                                JOIN role as r ON u.id_role = r.id
                                WHERE email = :email");

$requete->execute([
    "email" => $utilisateur->email
]);

$utilisateurBdd = $requete->fetch();

if (!$utilisateurBdd) {
    http_response_code(403);
    echo '{"message" : "email ou mot de passe incorrect"}';
    exit();
}

//verifier si le mot de passe en clair de l'utilisateur est compatible avec le mot de passe hashé en bdd
if (!password_verify($utilisateur->password, $utilisateurBdd['password'])) {
    http_response_code(403);
    echo '{"message" : "email ou mot de passe incorrect"}';
    exit();
}

$jwt = generateJwt($utilisateurBdd);

echo '{"jwt" : "' . $jwt . '"}';
