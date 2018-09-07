<?
    $host = 'localhost';
    $user = 'allhobybox';
    $pw = 'hobybox7410!!';
    $dbName = 'allhobybox';
    $mysqli = new mysqli($host, $user, $pw, $dbName);
 
    if($mysqli){
        echo "ok";
        $query = "INSERT into popup_kyobo (mbti, ip, user_agent) VALUES (?,?,?)";
        $stmt = mysqli_prepare($mysqli, $query);
        if($stmt === false) {
            echo('Statement 생성 실패 : ' . mysqli_error($mysqli));
            exit();
        }
        $mbti = $_POST['mbti'];
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        mysqli_stmt_bind_param($stmt, 'sss', $mbti, $ip, $user_agent);
        mysqli_stmt_execute($stmt);
    }else{
        echo "err";
    }

    mysqli_close($mysqli);
?>