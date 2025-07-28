<?php
session_start();

require_once 'database/database.php';

// Verifiez les permissions d'accès a la page
if($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

function clean_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function createSlug($title) {
    $slug =removeAccents($title);
    $slug= strtolower( str_replace(' ', '-',$slug));
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug); // ---Supprimer les caractères non alphanumériques
    $slug = preg_replace('/-+/', '-', $slug); // Remplacer les tirets multiples par un seul

    return trim($slug, '-'); // Supprimer les tirets en début et fin de chaîne
}

function removeAccents($string)
{
  $accents = [
    'à' => 'a',
    'á' => 'a',
    'â' => 'a',
    'ã' => 'a',
    'ä' => 'a',
    'å' => 'a',
    'ç' => 'c',
    'è' => 'e',
    'é' => 'e',
    'ê' => 'e',
    'ë' => 'e',
    'ì' => 'i',
    'í' => 'i',
    'î' => 'i',
    'ï' => 'i',
    'ñ' => 'n',
    'ò' => 'o',
    'ó' => 'o',
    'ô' => 'o',
    'õ' => 'o',
    'ö' => 'o',
    'ø' => 'o',
    'ù' => 'u',
    'ú' => 'u',
    'û' => 'u',
    'ü' => 'u',
    'ý' => 'y',
    'ÿ' => 'y',
    'À' => 'A',
    'Á' => 'A',
    'Â' => 'A',
    'Ã' => 'A',
    'Ä' => 'A',
    'Å' => 'A',
    'Ç' => 'C',
    'È' => 'E',
    'É' => 'E',
    'Ê' => 'E',
    'Ë' => 'E',
    'Ì' => 'I',
    'Í' => 'I',
    'Î' => 'I',
    'Ï' => 'I',
    'Ñ' => 'N',
    'Ò' => 'O',
    'Ó' => 'O',
    'Ô' => 'O',
    'Õ' => 'O',
    'Ö' => 'O',
    'Ø' => 'O',
    'Ù' => 'U',
    'Ú' => 'U',
    'Û' => 'U',
    'Ü' => 'U',
    'Ý' => 'Y'
  ];
  return strtr($string, $accents);
}

if(isset($_POST['add-article'])) {

    $title = clean_input($_POST['title']);
    $slug =createSlug($title);
        // echo $slug;
        // die();

    $introduction = clean_input($_POST['introduction']);
    $content = clean_input($_POST['content']);
    $imagepath = null; // initialiser la varriable pour l,image


    // Gestion de l'image
if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK){
$uploadDir = 'storages/articles/';
if(!is_dir($uploadDir)) {
    mkdir($uploadDir,0777,true); //creer le repertoire s'il n'escite pas 
}
}



// verification si le ficher est une image
$extention = strtolower(pathinfo($_FILES['image']['name'],
PATHINFO_EXTENSION));
$allowedEXtensions = ['jpg','jpeg','png','gif','webp'];

if(in_array($extension, $allowedEXtensions)){
    $imagepath = $uploadDir . uniqid('article_') . '.' . 
    $extension; // generer un nom de fichier unique

    move_uploaded_file($_FILES['image']['tmp_name'],$imagepath);// deplacer le fichier telecharger

}else{
    $error = "le fichier telecharger n'es pas une image valide.";
}


    //validation des champs
    if(empty($title) || empty($introduction) || empty($content)) {
        $error = "Tous les champs sont obligatoires.";
    }else{

        //--Verification de l'unicité du slug
        $query = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE slug = :slug");

        $query->execute(['slug' => $slug]);

        $count = $query->fetchColumn();
        // echo $count;
        //  die();
        if($count > 0) {
            $error = "Un article avec ce slug existe déjà.";
        }else{   
        //--Insertion de l'article dans la base de données
        $query = $pdo->prepare("INSERT INTO articles (title, slug, introduction, content, created_at) VALUES (:title, :slug, :introduction, :content,  NOW())");

        $query->execute([
            'title' => $title,
            'slug' => $slug,
            'introduction' => $introduction,
            'content' => $content
        ]);
        }

    }
}




$pageTitle = 'Page  Add article';

// Début du tampon de la page de sortie
ob_start();

// Inclure le layout de la page d'accueil
require_once 'resources/views/admin/articles/add-article_html.php';

// Récupération du contenu du tampon de la page d'accueil
$pageContent = ob_get_clean();

// Inclure le layout de la page de sortie
require_once 'resources/views/layouts/admin-layout/layout_html.php';

