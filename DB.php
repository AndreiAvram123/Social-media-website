<?php require "Post.php";
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cms';
$connection = mysqli_connect($host,$username,$password,$database);


function getAllPosts(){
    global $connection;
    $query = "SELECT * FROM forum_posts";
    $result = mysqli_query($connection,$query);
    $posts = array();
    while($row = mysqli_fetch_assoc($result)){
        $post = new Post($row['post_author'],$row['post_title']
       ,$row['post_date'], $row['post_content']);
        $posts[] = $post;
    }
    return $posts;
}

function getErrorMessagesSignUpForm(){
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $password = $_POST['passwordInput'];
    $confirmedPassword = $_POST['confirmedPasswordInput'];
    echo $username . " " . $email . " " . $password . " ".$confirmedPassword ;
    if(empty($username)){
       return "You have not entered an username";
    }
    if(strlen($username)<6){
       return "Your username is too short";
    }
    if(empty($email)){
        return "You have not entered an email";
    }

     if(empty($password)){
      return "You have not entered a password";
     }
     if(strlen($password) <6){
        return "Your password is too weak";
    }
    if(empty($password)){
        return "You must confirm your password";
    }
    if($password != $confirmedPassword){
        return "Passwords do not match";
    }

    return "";
}
function createUser(){
    $username = $_POST['usernameInput'];
    $email = $_POST['emailInput'];
    $encryptedPassword = md5($_POST['passwordInput']);
    $creationDate =  date('Y/m/d');
    $QUERY = "INSERT INTO USERS VALUES(NULL,'$username','$email','$encryptedPassword','$creationDate') ";
    global $connection;
    $result = mysqli_query($connection,$QUERY);
    echo $result;
}

?>