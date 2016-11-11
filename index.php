<?php
/**
 * Created by PhpStorm.
 * User: Kyle
 * Date: 10/9/2016
 * Time: 7:17 PM
 * Purpose: This is a single page guestbook which uses a database for users and comments.
 */





?>

<!-- We begin the page and add the ckeditor script, and site banner.-->
<!DOCTYPE html>
<html>
<head>
    <title>Guest Book Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="ckeditor/ckeditor.js"></script>
</head>
<body>
<div class="container">
    <img src="banner.jpg" id="siteBanner">

    <div class="content">

<?php
session_start();

//This function will display a form that when submitted will add a comment - it is called when a user is successfully logged in.
function displayAddComment(){
?>


                    <div class="loginForm">
                    <h2>Guest Book Add Comment</h2>

                    <?php if (isset($_SESSION['error'])){
                        echo $_SESSION['error'];
                    }

                    ?>
                <form action="index.php" method="post">

                    <label for="title">Title</label>
                    <br>
                    <input type="text" id="title" name="title">
                    <br>
                    <label for="message">Message</label>
                    <br>
                    <textarea name="message" id="message" rows="10" cols="80">
                    </textarea>
                    <script>
                        CKEDITOR.replace( 'message' );
                        </script>
                    <br>
                    <input type="submit" name="comment" value="Submit Comment">
                    <input type="submit" name="logout" value="Logout">

                </form>

                </div>

            </div>

        </div>

<?php
}
// This function will read all comments in the database and display them to their own div - if a user is logged in,
// it will create buttons that allow a user to edit or delete a comment.
function displayComments(){

    $mysqli = new mysqli("localhost", "forum_admin", "supersecretpancakebatter", "forum");

    if ($mysqli->connect_error) {
        die('Connect error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
    }

    $query = "select comments.id, comments.date, users.username, comments.title, comments.message from comments inner join users ON comments.USER_ID = users.USERID ORDER BY comments.date";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {

    ?>
    <br>
    <div class="comment">
        <h3>Date: <?php echo $row['date']; ?></h3>
        <h3>Username: <?php echo $row['username']; ?></h3>
        <h4>Title: <?php echo $row['title']; ?></h4>
        <h4>Message</h4>
        <p><?php echo $row['message']; ?></p>

        </div>
        <?php

        //A user is set only whena  successful login occurs, and is removed on logout - this will only display buttons
        //for comments created by the user.
        if (isset($_SESSION['user'])) {
            if ($row['username'] == $_SESSION['user']) {
                ?><form action="index.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <div class='comment'><input type='submit' name='delete' value='Delete'> <input type='submit' name='edit' value='Edit'></div>
                </form>
                <?php
            }
        }
        ?>

    <?php
}

    } else {
        $_SESSION['error'] = "No comments available";
    }
    $mysqli->close();

}

//This function determines what buttons have been selected on the page, and triggers associated functionality for each button.
function addCommentLogout(){

    if (isset($_POST['delete']))
    {
        deleteComment();
    }

   else if (isset($_POST['edit']) || isset($_POST['submitEdit']))
    {
        editComment();
    }
    //if the comment button is selected, we begin sanitizing, validating, and adding a comment to the database.
    else if (isset($_POST['comment'])) {

        $mysqli = new mysqli("localhost", "forum_admin", "supersecretpancakebatter", "forum");

        if ($mysqli->connect_error) {
            die('Connect error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
        }

        //sanitize title and comment

        $title = $mysqli->real_escape_string($_POST['title']);
        $message = $mysqli->real_escape_string($_POST['message']);
        $title = trim($title);
        $message = trim($message);

        if (empty($title) || $title == "") {
            $_SESSION['error'] = "<br>Your title cannot be blank<br>";

        }
        else if (empty($message) || $message == "") {
            $_SESSION['error'] = "<br>Your message cannot be blank<br>";

        }

        //POST title and message, date is auto, id is auto, and user_id is in session.
        //This is where we write the comment to the database, and the userID must be included as it is a foreign key associated with
        //the users table.
        else {
            $query = "INSERT INTO comments(title, message, user_id) VALUES ('" . $title . "','" . $message . "','" . $_SESSION['userID'] . "')";


            if ($mysqli->query($query) === TRUE) {
                $_SESSION['error'] = "";
                header("Refresh:0");
            } else {
                var_dump($_SESSION['userID']);
                echo $_SESSION['error'] = "Error: " . $query . "<br>" . $mysqli->error;

            }
        }
        $mysqli->close();
    }
    //If the user wants to logout, we destroy the session and refresh the page.
    if (isset($_POST['logout'])){
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Refresh:0");
    }

    }


    //This function is the login form, which includes a captcha - Functionality will differ whether a user selects register
    //or login.
function loginForm(){

    ?>

                    <div class="loginForm">
                    <h2>Guest Book User Login</h2>

                    <?php if (isset($_SESSION['error'])){
                        echo $_SESSION['error'];
                    }
                    ?>

                    <form action="index.php" method="post">

                        <label for="userName">User Name</label>
                        <br>
                        <input type="text" id="userName" name="userName" required>
                        <br>
                        <label for="password">Password</label>
                        <br>
                        <input type="password" id="password" name="password" required>
                        <br>
                        <h4>Type the text you see in the CAPTCHA image:</h4>
                        <img id="captcha" src="securimage/securimage_show.php" alt="CAPTCHA Image" >
                        <br>
                        <input required type="text" name="captcha_code" size="10" maxlength="6" />
                        <a href="#" onclick="document.getElementById('captcha').src = 'securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>
                        <br>
                        <input type="submit" name="login" value="Login">
                        <input type="submit" name="register" value="Register Account">


                    </form>

                    </div>

                </div>

            </div>


<?php
}
    //This function will always run, and is looking for a username and password from the login form.
function acceptLogin()
{
    //We determine if a username and password has been submitted, and if not we do nothing.
    if (isset($_POST['userName']) && isset($_POST['password'])) {
        include_once 'securimage/securimage.php';

        $securimage = new Securimage();

        if ($securimage->check($_POST['captcha_code']) == false) {
            // the code was incorrect
            // you should handle the error so that the form processor doesn't continue

            // or you can use the following code if there is no validation or you do not know how
            $_SESSION['error'] = "The security code entered was incorrect.<br /><br />";
        } else {
            $mysqli = new mysqli("localhost", "forum_admin", "supersecretpancakebatter", "forum");

            if ($mysqli->connect_error) {
                die('Connect error (' . $mysqli->connect_errno . ') '
                    . $mysqli->connect_error);
            }


            $user = $mysqli->real_escape_string($_POST['userName']);
            $pass = $mysqli->real_escape_string($_POST['password']);
            $user = trim($user);
            $pass = trim($pass);
            //We check if the username or password is blank.
            if (!empty($user) && !empty($pass) && $pass != "" && $user != "") {
                //We check if the user selected login or register.
                //If login we search for the username and password in the database and determine if a user exists.
                if (isset($_POST['login'])) {


                    $query = "select * from users where username='" . $user . "' and password=sha1('" . $pass . "')";
                    $result = $mysqli->query($query);

                    //testing for a result
                    if (!$result) {
                        echo "Could not run query:" . $query . "<br>" . $mysqli->error;
                        $_SESSION['error'] = "Could not run query:" . $query . "<br>" . $mysqli->error;
                        exit;
                    } else {
//                $row = $result->fetch_row();
//                Testing purpose
//                echo "RESULT:";
//                var_dump($result);
//                echo "<br>ROW: ";
//                var_dump($row);
                        //If one result is returned we set the user, userid, login authentication, and reset the errors.
                        if ($result->num_rows == 1) {
                            $row = $result->fetch_assoc();
                            $_SESSION['loginAuth'] = true;
                            $_SESSION['user'] = $row['USERNAME'];
                            $_SESSION['userID'] = $row['USERID'];
                            $_SESSION['error'] = "";
                            //If any other result occurs, we state there is an issue.
                        } else {
                            $_SESSION['error'] = "<br>ERROR <br> There was an error with the username and password you submitted<br><br>";
                        }
                    }
                }
                //If the user selects to register, we determine if a user already exists with that name.
                if (isset($_POST['register'])) {
                    $query = "select count(*) from users where username='" . $user . "'";
                    $result = $mysqli->query($query);

                    //testing for a result
                    if (!$result) {
                        var_dump($result);
                    }

                    $row = $result->fetch_row();
                    //If we do receive a result we state a user already exists.
                    if ($row[0] >= 1) {
                        $_SESSION['error'] = "A user already exists with this username";
                    } else {
                        //IF there is no user with the given username we allow the registration and inser the values, and login the user.
                        $query = "INSERT INTO users(username, password) VALUES ('" . $user . "', sha1('" . $pass . "'))";

                        if ($mysqli->query($query) === TRUE) {
                            $_SESSION['userID'] = $mysqli->insert_id;
                            echo "New user registered successfully";
                            $_SESSION['loginAuth'] = true;
                            $_SESSION['error'] = "";
                        } else {
                            $_SESSION['error'] = "Error: " . $query . "<br>" . $mysqli->error;
                        }
                    }
                }
            }
            //If the values for username or password are blank we return an error.
            else {
                $_SESSION['error'] = "Your username and password cannot be blank.";
                $mysqli->close();
            }
        }
    }
}
//This function allows a user to delete their own comments.  This is only called when a user is logged in.
//The id is associated with the comments.id value from the database which is tied to the form as a hidden variable.
//If successful a small success page is displayed where the user can return to the comments.
function deleteComment(){

        $mysqli = new mysqli("localhost", "forum_admin", "supersecretpancakebatter", "forum");

        if ($mysqli->connect_error) {
            die('Connect error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
        }

        $id = $_POST['id'];
        $query = "DELETE FROM comments WHERE id = '".$id."'";
        $result = $mysqli->query($query);
        if (!$result) {
            var_dump($result);
        }
        else{
            echo "Comment deleted successfully.";
            echo "<br><a href='index.php'>Click here to return to comments</a>";
        }


        $mysqli->close();
    exit;
    }

    //This function will allow a user when logged in to edit a comment.
    //The only editable portion is the actual message, and it will create an entirely new message field to be written
    //to the comment ID.  This form is a two step process - when a user selects the edit button the ID is sent to this
    //function, and when the form within this function is submitted the submitEdit is active and we then update the entry
    //in the database.
function editComment(){

        $mysqli = new mysqli("localhost", "forum_admin", "supersecretpancakebatter", "forum");

        if ($mysqli->connect_error) {
            die('Connect error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
        }

        if (!isset($_POST['submitEdit']))
        {  $_SESSION['commentID'] = $_POST['id'];
        ?>
        <div class="loginForm">
            <h2>Guest Book Edit Comment</h2>

            <?php if (isset($_SESSION['error'])) {
                echo $_SESSION['error'];
            }

            ?>
            <form action="index.php" method="post">

                <label for="message">Message</label>
                <br>
                <textarea name="message" id="message" rows="10" cols="80">
                            </textarea>
                <script>
                    CKEDITOR.replace('message');
                </script>
                <br>
                <input type="submit" name="submitEdit" value="Submit Edit">
            </form>
            <?php
            }

            else if (isset($_POST['submitEdit'])) {
                $message = $mysqli->real_escape_string($_POST['message']);
                $message = trim($message);
                if (empty($message) || $message == "") {
                    $_SESSION['error'] = 'Your message cannot be blank.';
                } else {

                    $id = $_SESSION['commentID'];

                    $query = "UPDATE comments SET message='" . $message . "' WHERE id = '".$id."'";
                    $result = $mysqli->query($query);


                    if (!$result) {
                        var_dump($result);
                    } else {
                        echo "Comment edited successfully.";
                        echo "<br><a href='index.php'>Click here to return to comments</a>";
                    }
                    $_SESSION['commentID'] = "";
                }
            }
            $mysqli->close();
            exit;
            }


//We will only allow for the addition of comments, editing, deleting, and logging out when a user is logged in.
            //The loginAuth existing and being true only occurs on a successful login.
            //addCommentLogout() is called first as we first check if any buttons have been submitted before continuing with
            //displaying comments and the option to add comments.
if (isset($_SESSION['loginAuth']) && ($_SESSION['loginAuth'] == true) ) {

    addCommentLogout();
    displayComments();
    displayAddComment();




}
//If a user is not logged in, we will display comments, allow for a login form, and check if a username or password has been submitted.
else {
    //display form to login
    displayComments();
    loginForm();
    acceptLogin();
}
    ?>

</body>
</html>
