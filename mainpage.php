<!DOCTYPE html>
<html lang="en">
<head> <!--Заголовок главной страницы-->
    <meta charset="utf-8">
    <title>Добро пожаловать</title>
    <link href="FormStyle.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<?php //Подключение БД
define("MYSQL_SERVER", "localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "");
define("MYSQL_DB", "CompDB");
$db = mysqli_connect(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
mysqli_set_charset($db, "utf8");
session_start(); //Начала сессии
$tknumber = $_SESSION['userdata'][3];

//begindatedel =  implode(",", array_keys($_POST));
if (array_key_exists("exit", $_POST)) { //Проверка на нажатие кнопки выхода

    header("Location: LoginForm.php");
    session_abort();
}

/*if (in_array("exit", array_keys($_POST))) { //Проверка на нажатие кнопки выхода
    header("Location: LoginForm.php");
    session_abort();*/



if (preg_grep("/del-/", array_keys($_POST))) {
    $begindatedel =  implode(",", array_keys($_POST));
    $begindatedel = str_replace("del-", "", $begindatedel);
    $ftextquery = 'SELECT * FROM `Отпуск` WHERE `Номер трудового договора`="' . $tknumber . '" and `Дата начала отпуска` = "' . $begindatedel . '"';
    $fquery = mysqli_query($db, $ftextquery);
    $mass = mysqli_fetch_row($fquery);
    $bgdt = date_create($mass[1]);
    $endt = date_create($mass[2]);
    if (date_diff($bgdt, date_create(date("Y-m-d"))) -> days > 3) {
        $dniplus = date_diff($endt, $bgdt);
        $dniplus = $dniplus->days + 1;
        $dniplus = $dniplus + $_SESSION['userdata'][5];
        $_SESSION['userdata'][5] = $dniplus;
        $ftextquery = "UPDATE Сотрудники set Дни = " . $dniplus . " where `Номер трудового договора` = '" . $tknumber . "'";
        $fquery = mysqli_query($db, $ftextquery);
        $ftextquery = 'DELETE FROM `Отпуск` WHERE `Номер трудового договора`="' . $tknumber . '" and `Дата начала отпуска` = "' . $begindatedel . '"';
        $fquery = mysqli_query($db, $ftextquery);
        echo "<p class = 'goodmsg'>Запись успешна удалёна</p>";
    } else {
        echo "<p class = 'errormsg'>Отпуск можно отменить только за 3 дня или раньше</p>";
    }
} //проверка на нажатие кнопки удаления
if ((isset($_POST['begin-date'])) and (isset($_POST['end-date']))) { //проверка на создание заявки
        function add() //функция добавления записи
        {
            global $tknumber, $begindate, $enddate; //вызов переменных и запросы к БД
            global $db, $dni, $days;
            $ftextquery = 'INSERT INTO Отпуск VALUES("' . $tknumber . '","' . $begindate . '","' . $enddate . '");';
            $fquery = mysqli_query($db, $ftextquery);
            $dni = $dni - $days->days - 1;
            $_SESSION['userdata'][5] = $dni;
            $ftextquery = "UPDATE Сотрудники set Дни = " . $dni . " where `Номер трудового договора` = '" . $tknumber . "'";
            $fquery = mysqli_query($db, $ftextquery);

        }

        $begindate = $_POST['begin-date']; //Проверка на корректность отпуска
        $enddate = $_POST['end-date'];
        $ftextquery = 'SELECT * FROM `Отпуск` WHERE 
                      ((`Дата начала отпуска` <= "' . $begindate . '" AND `Дата окончания отпуска` >= "' . $begindate . '")
                       OR (`Дата начала отпуска` <= "' . $enddate . '" AND `Дата окончания отпуска` >= "' . $enddate . '") 
                       OR (`Дата начала отпуска` > "' . $begindate . '" AND `Дата окончания отпуска` < "' . $enddate . '")) 
                       and (`Номер трудового договора` = "' . $tknumber . '")';
        $fquery = mysqli_query($db, $ftextquery);
        if (mysqli_num_rows($fquery) > 0) { //Вывод сообщений об ошибке
            echo '<p class = \'errormsg\'>Желаемый промежуток времени полностью или частично совпадает с существующими отпусками, пожалуйста, проверьте данные в разделе "Мои отпуска"</p>';
        } elseif ($begindate <= date("Y-m-d")) {
            echo "<p class = 'errormsg'>Желаемая дата начала отпуска уже прошла, пожалуйста выберите дату позже сегоднешнего дня</p>";
        } elseif ($begindate > $enddate) {
            echo "<p class = 'errormsg'>Дата окончания отпуска меньше, чем дата начала</p>";
        } else {
            $enddate1 = date_create($enddate); 
            $begindate1 = date_create($begindate);
            $days = date_diff($enddate1, $begindate1);
            $dni = $_SESSION['userdata'][5];
            if (($days->days + 1 <= $dni)) {
                add(); //создание записи
                echo "<p class = 'goodmsg'>Запись успешна создана</p>";  //Сообщение об успещшном создании записи
            } else {
                echo "<p class = 'errormsg'>Недостаточно дней </p>"; //Сообщение об ошибке
            }
        }
}

?>

<div class="block1"> <!--Разметка для главной страницы-->

    <input type="radio" name = "tab" id = "tabid-1" checked> <!--Вкладки-->
    <input type="radio" name = "tab" id = "tabid-2">
    <?php
        if ($_SESSION['userdata'][6] === '1') { //Проверка на статус администратора
            echo '<input type="radio" name = "tab" id = "tabid-3">';

        }
    ?>
    <form action = "mainpage.php" method="POST"> <!--форма и кнопка для выхода-->
        <input class = "exit" type = "submit" value = "Завершить сеанс" name = "exit">
    </form>

    <div class = "tabbb"> <!--описание вкладок-->

        <label for = "tabid-1" class = "choose-1">Завление на отпуск</label>
        <label for = "tabid-2" class = "choose-2">Мои отпуска</label>
        <?php
        if ($_SESSION['userdata'][6] === '1') {  //Проверка на статус администратора
            echo '<label for = "tabid-3" class = "choose-3">Статистика</label>';
        }
        ?>
    </div>
    <div class = "content"> <!--Содержание вкладок-->
        <article class = "tab-1">
        <form action="mainpage.php" method="POST">
            <h1>Заявление на отпуск</h1>
            <p><?php
               // $strk = mysqli_fetch_row($_SESSION['secondname']);
                $secondname = $_SESSION['userdata'][0]; //Приветствие
                $firstname = $_SESSION['userdata'][1];
                $dni = $_SESSION['userdata'][5];
                echo "<p class = 'textfield'> Здравствуйте, " . $secondname. " " . $firstname . ", у вас есть " . $dni . " дней отпуска.</p>"; ?></p>
            </label>
            <label for="begin-date-field"> 
                <p class = "headtext">Дата начала отпуска:</p>
                <input class="textfield" type="date"
                       name="begin-date" id = "begin-date-field" value = "<?=@$begindate;?>"><br>
            </label>
            <label for="end-date-field">
                <p class = "headtext">Дата окончания отпуска:</p>
                <input class="textfield" type="date"
                       name="end-date" id = "end-date-field" value = "<?=@$enddate;?>"><br>
            </label>
            <input class = "buttn" type="submit" value="Принять">

        </form>
        </article>
        <article class = "tab-2"><!--Разметка для раздела "мои отпуска"-->
            <?php
            echo "<h1>Мои отпуска</h1>"; 

            $tknumber = $_SESSION['userdata'][3]; //Сбор данных об отпусках
            $query = "SELECT * FROM Отпуск WHERE `Номер трудового договора` = '" .$tknumber ."';";
            $resquery = mysqli_query($db, $query);
            $stroka = mysqli_fetch_all($resquery);
            if (count($stroka) === 0) {//Вывод информации от отпусках
                echo '<div class = "otpfield nope">Нет отпусков</div>';
            } else {

                for ($i = 0; $i < count($stroka); $i++) {
                    $d1 = date_create($stroka[$i][1])->format("d.m.Y");
                    $d2 = date_create($stroka[$i][2])->format("d.m.Y");
                    $stroka1 = "Отпуск с " . $d1 . " по " . $d2;
                    echo '<div class = "otpfield">' . $stroka1 . '</div>';
                    echo '<form action = "mainpage.php" method = "POST">
                    <input class = "delbut" type="submit" value="Удалить" name = "del-' . $stroka[$i][1] . '">
                    </form>';
                }
            }


            ?>
        </article>
        <?php

        if ($_SESSION['userdata'][6] === '1') //Проверка статуса администратора
        $daysstat = array(array()); //Создание массива для статистики
        for ($i = 2000; $i < 2100; $i++) {
            for ($j = 1; $j <= 13; $j++) {
                $daysstat[$i][$j] = 0;
            }
        }
        $query = "SELECT * FROM Отпуск"; //сбор статистики
        $resquery = mysqli_query($db, $query);
        $stroka = mysqli_fetch_all($resquery);
        if (count($stroka) === 0) {
            echo '<div class = "otpfield nope">Нет отпусков</div>';
        } else {
            for ($i = 0; $i < count($stroka); $i++) {
                $stroka1 = $stroka[$i][0] . " " . $stroka[$i][1] . " " . $stroka[$i][2];
                $d1 = date_create($stroka[$i][1]);
                $d2 = date_create($stroka[$i][2]);
                $diff = date_diff($d2, $d1) -> days;
                for ($j = 0; $j <= $diff; $j++) {
                    $daysstat[$d1->format('Y')][intval($d1->format('m'))] += 1;
                    date_add($d1, date_interval_create_from_date_string('1 days'));
                    $daysstat[$d1->format('Y')][13] += 1;

                }


            }

        }

        $mnthname  = [1=>"Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"];

        echo '<article class = "tab-3">'; //вывод статистики
        echo "<h1>Статистика</h1>";
        for ($i = 2000; $i < 2100; $i++) {
            if ($daysstat[$i][13] != 0) {
                echo "<p class = 'date1'>" . $i . " год: " . $daysstat[$i][13] . " дней" . "</p>";
                for ($j = 1; $j < 13; $j++) {
                    $m = $mnthname[$j];
                    echo "<p class = 'date2'>" . $m . " : " . $daysstat[$i][$j] . " дней" . "</p>";
                }
            }
        }
         echo '</article>';
        
        ?>


</div>
</body>