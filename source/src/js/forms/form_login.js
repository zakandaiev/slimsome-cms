$("#login-form [type=submit]").on("click", function(e) {
  e.preventDefault();
  $.ajax({
    method: "POST",
    url: "../core/login.php",
    data: $("#login-form").serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      document.location.reload();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});