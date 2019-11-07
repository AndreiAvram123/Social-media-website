<?php require "Post.php";
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cms';
$connection = mysqli_connect($host, $username, $password, $database);

function getAllPosts()
{
    global $connection;
    $query = "SELECT * FROM forum_posts";
    $result = mysqli_query($connection, $query);
    $posts = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $post = new Post($row['post_author'], $row['post_title']
            , $row['post_date'], $row['post_content']);
        $posts[] = $post;
    }
    return $posts;
}
function getErrorMessagesSignUpForm()
{
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $confirmedPassword = $_POST['confirmedPasswordInput'];
    if (empty($username)) {
        return "You have not entered an username";
    }
    if (strlen($username) < 6) {
        return "Your username is too short";
    }
    if (empty($email)) {
        return "You have not entered an email";
    }

    if (empty($password)) {
        return "You have not entered a password";
    }
    if (strlen($password) < 6) {
        return "Your password is too weak";
    }
    if (empty($password)) {
        return "You must confirm your password";
    }
    if ($password != $confirmedPassword) {
        return "Passwords do not match";
    }

    return "";
}
function getErrorMessageFromSignIn()
{
    $username = $_POST['emailSignIn'];
    $password = $_POST['passwordSignIn'];
    if (empty($username)) {
        return "You have not entered an email";
    }
    if (empty($password)) {
        return "You have not entered a password";
    }
    return "";
}
function createUser()
{
    global  $warningMessage;
    $warningMessage = getErrorMessagesSignUpForm();
    if ($warningMessage == "") {
        $username = $_POST['usernameInput'];
        $email = $_POST['emailInput'];
        $encryptedPassword = md5($_POST['passwordInput']);
        $creationDate = date('Y/m/d');
        $result = addUserDataToDatabase($username, $email, $encryptedPassword, $creationDate);
        //insert into session if we were able to create a user
        if ($result) {
            addUserDataToSession($username, $email, $encryptedPassword, $creationDate);
            return true;
        }
    }
    return false;
}
function addUserDataToDatabase($username, $email, $password, $creationDate)
{

    $QUERY = "INSERT INTO USERS VALUES(NULL,'$username','$email','$password','$creationDate') ";
    global $connection;
    return mysqli_query($connection, $QUERY);
}

function addUserDataToSession($username, $email, $password, $creationDate)
{
    $_SESSION['Username'] = $username;
    $_SESSION['Email'] = $email;
    $_SESSION['Password'] = $password;
    $_SESSION['CreationDate'] = $creationDate;

}

function signUserOut()
{
    unset($_SESSION['Username']);
    unset($_SESSION['Email']);
    unset($_SESSION['Password']);
    unset($_SESSION['CreationDate']);


}

function loginUser()
{
    global $warningMessage;
    $warningMessage = getErrorMessageFromSignIn();
    $email = $_POST['emailSignIn'];
    $enteredPassword = $_POST['passwordSignIn'];
    if($warningMessage == "") {
        $user = getUserFromDatabase($email);
        if ($user!=null)
        {
            if(md5($enteredPassword) == $user ->getPassword()) {
                addUserDataToSession($user->getUsername(), $user->getEmail(), $user->getPassword()
                    , $user->getCreationDate());
                return true;
            }else{
                $warningMessage = "Incorrect password";
            }
        }else{
            $warningMessage = "The account does not exist";
        }
    }
    return false;
}


function getUserFromDatabase($email){
   $query = "SELECT * FROM users WHERE email = '$email'";
   global  $connection;
   $result = mysqli_query($connection,$query);
   if($result){
      $data = mysqli_fetch_assoc($result);
      require_once "data/User.php";
      return new User($data['username'],$data['password'],$data['email'],$data['creation_date']);
   }else{
       global $warningMessage;
       $warningMessage = "User does not exist.Either your email or password is wrong";
   }
   return null;
}

?>