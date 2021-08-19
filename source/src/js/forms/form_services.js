// ADD
$("#add_services").on("submit", function(e) {
  e.preventDefault();
  $this = $("#add_services");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  const formData = new FormData(this);
  const images = $this.find("input[name='images[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  const user_avatar = $this.find("input[name='user_avatar[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  formData.append("images_str", JSON.stringify(images));
  formData.append("user_avatar_str", user_avatar);
  $.ajax({
    method: "POST",
    url: "../core/add_services.php",
    data: formData,
    processData: false,
    contentType: false,
    cache: false
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      window.location.href = "profile.php?section=services";
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// EDIT
$("#edit_services").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_services");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  const formData = new FormData(this);
  const images = $this.find("input[name='images[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  const user_avatar = $this.find("input[name='user_avatar[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  formData.append("images_str", JSON.stringify(images));
  formData.append("user_avatar_str", user_avatar);
  $.ajax({
    method: "POST",
    url: "../core/edit_services.php",
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
      $this.find("[data-go-back]").fadeIn();
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// DELETE
$("[data-del-service]").on("click", function(e) {
  e.preventDefault();
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const service_id = $(this).data("del-service");
    $.ajax({
      method: "POST",
      url: "../core/delete_services.php",
      data: { service_id: service_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Удалено", null, {"positionClass": "toast-bottom-right"});
        $("#row_"+service_id).fadeOut(300, ()=>{$("#row_"+service_id).remove();});
        $("#services_count").text($("#services_count").text() - 1);
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});