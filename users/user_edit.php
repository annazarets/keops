<?php
/**
 * Updates the user information, and redirects to Users tab.
 */
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/resources/config.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/dao/user_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/dao/user_langs_dao.php");
require_once(filter_input(INPUT_SERVER, 'DOCUMENT_ROOT') . "/utils/utils.php");

$PAGETYPE = "admin";
require_once(RESOURCES_PATH . "/session.php");

$failedparams = checkPostParameters(["id", "name", "email", "role", "langs"]);

if (isRoot() && isset($_POST["new_password"]) && isset($_POST["id"])) {
  $user_dao = new user_dao();
  $user_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);
  if ($user_dao->updateUserPassword($_POST["id"], $user_password)) {
    $_SESSION["error"] = "none";
    header("Location: /admin/user_edit.php?id=" . $_POST["id"]);
  } else {
    $_SESSION["error"] = "yes";
    header("Location: /admin/user_edit.php?id=" . $_POST["id"]);
  }
} else {
  if (count($failedparams) == 0) {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $langs = array();
    foreach ($_POST['langs'] as $lang ){
      array_push($langs, $lang);
    } 
    $active = "false";
    
    if (isset($_POST["active"]) && $_POST["active"]=="on") {
      $active = "true";
    }
    $user_dao = new user_dao();
    $user_langs_dao = new user_langs_dao();
    
    $user_dto = user_dto::newUser($id, $name, $email, $role, $active);

    $user_langs = array();
    foreach($langs as $lang){
    $user_lang = user_langs_dto::newUserLang($user_dto->id, $lang);
    array_push($user_langs, $user_lang);
    }
    
  if ($user_dao->updateUser($user_dto) && $user_langs_dao->updateUserLangs($user_langs)) {
        $user_dto->langs = $user_langs;
        $_SESSION["error"] = null;
        //$_SESSION["userinfo"] = $user_dto;
        if ($_SESSION["userinfo"]->id == $user_dto->id) {
          $_SESSION["userinfo"] = $user_dto;
        }
        header("Location: /admin/index.php#users");
        die();      
  }
  else {
        $_SESSION["error"] = "userediterror";
        header("Location: /admin/index.php#users");
        die();
    }
  }

  else {
    $_SESSION["error"] = "missingparams";
        header("Location: /admin/index.php#users");
        die();
  }   
}