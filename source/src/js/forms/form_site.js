// FORM SUBMIT
$("#edit_site").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_site");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  const formData = new FormData(this);
  const site_logo = $this.find("input[name='site_logo[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  const site_background = $this.find("input[name='site_background[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  formData.append("site_logo_str", site_logo);
  formData.append("site_background_str", site_background);
  $.ajax({
    method: "POST",
    url: "../core/edit_site.php",
    data: formData,
    processData: false,
    contentType: false,
    cache: false
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Информация изменена", null, {"positionClass": "toast-bottom-right"});
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// COLORS LIVE CHANGE
$("input[name='site_color_accent']").on("input", function() {
  $(":root").css("--accent-color", $(this).val());
});
$("input[name='site_color_accent_2']").on("input", function() {
  $(":root").css("--accent-color-2", $(this).val());
});
$("input[name='site_color_body']").on("input", function() {
  $(":root").css("--body-color", $(this).val());
});
$("input[name='site_color_text']").on("input", function() {
  $(":root").css("--text-color", $(this).val());
});