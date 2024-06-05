<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

if (!isset($_GET['id'])) {
    echo '{"message" : "il n\'y a pas d\'identiant dans l\'URL"}';
    http_response_code(400);
    exit;
}

$idUser = $_GET["id"];

// Prend les données brutes de la requête
$json = file_get_contents('php://input');

// Le convertit en objet PHP
$user = json_decode($json);


//On recupère l'utilisateur dans la bdd
$requete = $connexion->prepare("SELECT * FROM user WHERE id = ?");
$requete->execute([$idUser]);
$userDb = $requete->fetch();

//si il n'y a pas d'utilisateur on retourne une erreur 404
if (!$userDb) {
    http_response_code(404);
    echo '{"message" : "Cet utilisateur n\'existe pas"}';
    exit();
}

//si l'utilisateur n'a pas fourni de mot de passe, on lui affecte l'ancien
//sinon on hash le mot de passe à modifier
if ($user->password == '') {
    $user->password =  $userDb['password'];
} else {
    $user->password = password_hash($user->password, PASSWORD_DEFAULT);
}

//on recupere l'id du role à affecter à l'utilisateur
$requete = $connexion->prepare("SELECT id FROM role WHERE name = ?");
$requete->execute([$user->role]);
$role = $requete->fetch();

//si le role n'existe pas
if (!$role) {
    http_response_code(400);
    echo '{"message" : "Ce rôle n\'existe pas"}';
    exit();
}

$requete = $connexion->prepare("UPDATE user SET 
                                    email = :email, 
                                    firstname = :firstname, 
                                    lastname = :lastname, 
                                    password = :password, 
                                    id_role = :id_role
                                WHERE id = :id");

$requete->execute([
    "email" => $user->email,
    "firstname" => $user->firstname,
    "lastname" =>  $user->lastname,
    "password" =>  $user->password,
    "id_role" =>  $role['id'],
    "id" => $idUser
]);

echo '{"message" : "L\'utilisateur a bien été modifié"}';
