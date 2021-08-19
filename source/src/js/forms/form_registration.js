$("#registration_form").on("submit", function(e) {
  e.preventDefault();
  $this = $("#registration_form");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/registration.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Регистрация успешна", null, {"positionClass": "toast-bottom-right"});
      $this.after('<h3>Вы зарегистрировались!</h3><p>Теперь вы можете авторизироваться на сайте.</p><p>Регистрационные данные были отправлены на ваш e-mail.</p><a href="/" class="btn btn_primary">Вернуться на главную</a>');
      $this.remove();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});