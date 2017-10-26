<!DOCTYPE html>
<html lang="en">
<head> <!--Заголовок страницы-->
    <meta charset="utf-8">
    <title>Вход в систему</title>
    <link href="FormStyle.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<?php
define("MYSQL_SERVER", "localhost"); //Подключение БД
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "");
define("MYSQL_DB", "CompDB");
$db = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
mysqli_set_charset($db, "utf8");
if (isset($_POST['tk-number'])) { //проверка сущесвтования запроса
    $tknumber = $_POST['tk-number'];
    $query = "SELECT * FROM Сотрудники WHERE `Номер трудового договора` = '" . $tknumber . "'";
    $resquery = mysqli_query($db, $query);
    if (mysqli_num_rows($resquery) === 0) { //проверка существования пользователя
        echo "<p class = 'errormsg'>Пользователя с таким номером трудового договора не существует</p>";
    } else {
        $password = $_POST['password']; //проверка правильности пароля
        $query = "SELECT * FROM Сотрудники WHERE `Пароль` = '" . $password . "' and `Номер трудового договора` = '" . $tknumber . "'";
        $resquery = mysqli_query($db, $query);
        if (mysqli_num_rows($resquery) === 0) { 
            echo "<p class = 'errormsg'>Неверный пароль</p>";
        } else {
            session_start(); //начало сессии, сбор информации и переход на главную страницу
            $userdata = mysqli_fetch_row($resquery);
            mysqli_free_result($resquery);
            $_SESSION['userdata'] = $userdata;
            header("Location: mainpage.php");
        }
    }
}

?>
    <div class="block1"> <!--разметка страницы авторизации-->
        <form action="LoginForm.php" method="POST"> <!--форма авторищации-->
        <h1>Авторизируйтесь в системе</h1>
        <label for="tk-number-field"> <!--поле для договора-->
            <p class = "headtext">Номер трудового договора:</p>
            <input class="textfield" type="text"
                   name="tk-number" id = "tk-number-field"
                   placeholder="1234567890" value = "<?=@$tknumber;?>"><br>
        </label>    
            <label for="password-field"><!--поле для пароля-->
                <p class = "headtext">Пароль:</p>
                <input class="textfield" type="password"
                       name="password" id = "password-field"
                       placeholder="*******"><br>
            </label>
            <input class = "buttn" type="submit" value="Принять"> <!--кнопка входа-->
        </form>
    </div>
</body>