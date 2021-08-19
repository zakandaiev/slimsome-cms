$("#edit_payments").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_payments");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/edit_payments.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Информация изменена", null, {"positionClass": "toast-bottom-right"});
      $("#edit_payments [data-go-back]").fadeIn();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});