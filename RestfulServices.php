<?php
require_once "Management/UserAccountManager.php";
$userAccountManager = UserAccountManager::getInstance();

if (isset($_POST["resetPasswordButton"])) {
    $email = $_POST["emailResetPassword"];
    $userAccountManager->sendResetPasswordEmail($email);
}

//process the email link received by the user
if (isset($_GET["verifyEmail"])) {
    $email = $_GET["verifyEmail"];
     $userAccountManager->markEmailVerified($email);
    echo '<div class="alert alert-primary" role="alert">You have 
successfully confirmed your email</div>';
}
if (isset($_GET["resetPasswordURL"])) {
    $view = new stdClass();
    $view->encryptedEmail = $_GET["resetPasswordURL"];
    include "Views/pageElements/EnterNewPasswordForm.phtml";
}
if (isset($_POST["submitChangedPassword"])) {
    $newPassword = $_POST["newPasswordInput"];
    $userEmailEncrypted = $_POST["userEmailEncrypted"];
    echo $userEmailEncrypted;
    $userAccountManager->changeUserPassword($userEmailEncrypted, $newPassword);
    echo '<meta http-equiv="refresh"
   content="0; url=index.php">';
}
?>