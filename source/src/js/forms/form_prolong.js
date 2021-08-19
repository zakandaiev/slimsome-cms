$("#service_prolong").on("submit", function(e) {
  e.preventDefault();
  $.ajax({
    method: "POST",
    url: "../core/prolong.php",
    data: $("#service_prolong").serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      $("#service_prolong").after(jsonData.payment_form);
      $("#payment_form").submit();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});