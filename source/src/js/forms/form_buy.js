// DESCRIPTION
$("div[id*='service_desc_']").slice(1).hide();
// PRICES SELECT
$("select[name*='service_days_']").slice(1).hide();

$("select[name='service_id']").on("change", function() {
  // DESCRIPTION
  $("div[id*='service_desc_']").hide();
  $("div#service_desc_"+$(this).val()).show();
  // PRICES SELECT
  $("select[name*='service_days_']").hide();
  $("select[name*='service_days_"+$(this).val()+"']").show();
  // RESET SLIDER
  $(".carousel").slick("setPosition");
});

// BIND TYPE
$("select[name='service_bind_type']").on("change", function() {
  if ($(this).val() == "nick_pass") {
    $("div#nick_pass").show();
    $("div#steam_pass").hide();
  } else {
    $("div#steam_pass").show();
    $("div#nick_pass").hide();
  }
});

// SUBMIT
$("#buy_form").on("submit", function(e) {
  e.preventDefault();
  $.ajax({
    method: "POST",
    url: "../core/buy.php",
    data: $("#buy_form").serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      $("#buy_form").after(jsonData.payment_form);
      $("#payment_form").submit();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});