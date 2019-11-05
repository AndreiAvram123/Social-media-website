<?php require_once "DB.php";
  $isUserLoggedIn = false;
?>
<?php include "structure/index.phtml";

if(isset($_POST['registerUser'])) {
    $errorMessage = getErrorMessagesSignUpForm();
    if ($errorMessage == "") {
        createUser();
        $isUserLoggedIn = true;

    } else {
        include "structure/ErrorModal.phtml";
        echo '<script>$("#errorModal").modal("show")</script>';

    }
}
?>


