function uploadAdmins() {
  $.ajax({
    method: "POST",
    url: "../core/upload_admins.php",
    data: { sure: true }
  }).done(function(response) {
    $("#edit_user_info").removeClass("active");
    $("#edit_user_info").find(".loader").remove();
    $("#edit_user_pass").removeClass("active");
    $("#edit_user_pass").find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["info"]("Дождитесь смены карты на сервере", null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["error"]("Не удалось загрузить вашу привилегию на сервер", null, {"positionClass": "toast-bottom-right"});
      setTimeout(()=>{
        toastr["info"]("Обратитесь к администратору сайта", null, {"positionClass": "toast-bottom-right"});
      }, 1000);
    }
  });
}

// PROFILE ACCOUNT
// edit user info
$("#edit_user_info").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_user_info");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/edit_user_info.php",
    data: $("#edit_user_info").serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Информация изменена", null, {"positionClass": "toast-bottom-right"});
      if (jsonData.upload == "1") {
        uploadAdmins();
      } else {
        $this.removeClass("active");
        $this.find(".loader").remove();
      }
    } else if (jsonData.success == "-1") {
      $this.removeClass("active");
      $this.find(".loader").remove();
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      $this.removeClass("active");
      $this.find(".loader").remove();
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// change user pass
$("#edit_user_pass").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_user_pass");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/edit_user_pass.php",
    data: $this.serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      $this[0].reset();
      toastr["success"]("Пароль изменен", null, {"positionClass": "toast-bottom-right"});
      if (jsonData.upload == "1") {
        uploadAdmins();
      } else {
        $this.removeClass("active");
        $this.find(".loader").remove();
      }
    } else if (jsonData.success == "-1") {
      $this.removeClass("active");
      $this.find(".loader").remove();
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      $this.removeClass("active");
      $this.find(".loader").remove();
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});

// PROFILE USERS LIST
// admin add new user
$("#add_user").on("submit", function(e) {
  e.preventDefault();
  $this = $("#add_user");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/add_user.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      window.location.href = "profile.php?section=users";
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// admin edit user
$("#edit_user").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_user");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/edit_user.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Информация изменена", null, {"positionClass": "toast-bottom-right"});
      $this.find("[data-go-back]").fadeIn();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// del
$("[data-del-user]").on("click", function(e) {
  e.preventDefault();
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const user_id = $(this).data("del-user");
    $.ajax({
      method: "POST",
      url: "../core/delete_user.php",
      data: { user_id: user_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Удалено", null, {"positionClass": "toast-bottom-right"});
        $("#row_"+user_id).fadeOut(300, ()=>{$("#row_"+user_id).remove();});
        $("#users_count").text($("#users_count").text() - 1);
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});
// upload new admins to gameserver
$("#upload-admins").on("click", function(e) {
  e.preventDefault();
  const confirmation = confirm("Вы подтверждаете действие?");
  $this = $("#upload-admins");
  if (confirmation) {
    $this.addClass("active");
    $.ajax({
      method: "POST",
      url: "../core/upload_admins.php",
      data: { sure: true }
    }).done(function(response) {
      $this.removeClass("active");
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Загружено", null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});