<?php require_once "DB.php";
session_start();
global $warningMessage;
$warningMessage = "";

if(isset($_POST['signOutButton'])){
    signUserOut();
}

if(isset($_POST['loginButton'])){
    loginUser();

}

if (isset($_POST['registerUser'])) {
    createUser();
}

include "structure/index.phtml";


//
//function displayWarningModal(){
//    include "structure/WarningModal.phtml";
//    echo '<script>$("#warningModal").modal("show")</script>';
//}

?>


