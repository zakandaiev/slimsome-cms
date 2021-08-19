$("#restore_form").on("submit", function(e) {
  e.preventDefault();
  $this = $("#restore_form");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/restore.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Письмо отправлено", null, {"positionClass": "toast-bottom-right"});
      $this.after('<p>Новые данные для входа были отправлены на Ваш e-mail!</p><a href="/" class="btn btn_primary">Вернуться на главную</a>');
      $this.remove();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});