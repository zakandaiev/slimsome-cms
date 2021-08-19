<?php

require_once("db_connect.php");
require_once("db_settings.php");
require_once("core.php");
require_once("is_user_logged.php");
require_once("is_user_admin.php");
require_once("is_user_moder.php");

if (!$is_user_logged) {
  echo json_encode(array("success" => -1, "error" => "У вас нет прав"));
  exit();
}

if (!isset($_POST["user_id"]) || empty($_POST["user_id"]) ||
    !isset($_POST["pass_check"]) || empty($_POST["pass_check"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные пользователя"));
  exit();
}

if (!isset($_POST["news_id"]) || empty($_POST["news_id"])) {
  echo json_encode(array("success" => 0, "error" => "Неверные данные новости"));
  exit();
}

$user_id = filter_var(trim($_POST["user_id"]), FILTER_SANITIZE_NUMBER_INT);
$pass_check = filter_var(trim($_POST["pass_check"]), FILTER_SANITIZE_STRING);
$news_id = filter_var(trim($_POST["news_id"]), FILTER_SANITIZE_NUMBER_INT);

$comment = filter_var(trim($_POST["comment"]), FILTER_SANITIZE_STRING);

$validate_comment = validateComment($comment);

if($validate_comment !== "valid") {
  echo json_encode(array("success" => 0, "error" => $validate_comment));
  exit();
}

$user_validate_query = $pdo->prepare("SELECT * FROM ".$prefix."_users WHERE id=:user_id and password=:pass_check");
$user_validate_query->bindParam(":user_id", $user_id);
$user_validate_query->bindParam(":pass_check", $pass_check);

try {
  $user_validate_query->execute();
  if ($user_validate_query->rowCount() == 1) {
    $author = $user_validate_query->fetch();
    // SEND COMMENT
    $add_comment_query = $pdo->prepare("
      INSERT INTO ".$prefix."_comments
        (news_id, author, comment)
      VALUES
        (:news_id, :author, :comment)
    ");
    $add_comment_query->bindParam(":news_id", $news_id);
    $add_comment_query->bindParam(":author", $author["id"]);
    $add_comment_query->bindParam(":comment", $comment);
    try {
      $add_comment_query->execute();
      if ($add_comment_query->rowCount() >= 1) {
        $comment_id = $pdo->lastInsertId();
        $comment_position = getUserPosition($author["id"], null);
        $comment_avatar = getUserAvatar($author["id"], null);
        $comment_name = !empty($author["nick"]) ? $author["nick"] : $author["login"];
        $comment_del = '';
        if ($is_user_admin || $is_user_moder) {
          $comment_del = '<br><span data-del-comment="'.$comment_id.'">удалить</span>';
        }
        $comment_body = '<div class="comments__item"> <div class="comments__avatar"> <img class="avatar" src="'.$comment_avatar.'" title="'.$comment_position.'" alt="'.$comment_position.'"> </div> <div class="comments__message"> <div class="info"> <div class="author" title="'.$comment_position.'">'.$comment_name.'</div> <div class="date"> '.dateWhen(time()).$comment_del.' </div> </div> <div class="text">'.replaceSmiles($comment).'</div> </div> </div>';
        echo json_encode(array("success" => 1, "comment" => $comment_body));
      } else {
        echo json_encode(array("error" => 0, "error" => $add_comment_query->errorInfo()));
      }
    } catch(PDOException $error) { 
      echo json_encode(array("error" => 0, "error" => $error->getMessage()));
    }
    // END SEND COMMENT
  } else {
    echo json_encode(array("error" => 0, "error" => "Неверные данные пользователя"));
    exit();
  }
} catch(PDOException $error) { 
  echo json_encode(array("error" => 0, "error" => $error->getMessage()));
  exit();
}

?>