// ADD
$("#add_socials input").on("input", function() {
  $(this).prop("value", $(this).val().replace(/\s/g, ''));
});
$("#add_socials").on("submit", function(e) {
  e.preventDefault();
  $this = $("#add_socials");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/add_socials.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      window.location.href = "profile.php?section=socials";
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// EDIT
$("#edit_socials").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_socials");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/edit_socials.php",
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
// DELETE
$("[data-del-social]").on("click", function(e) {
  e.preventDefault();
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const social_id = $(this).data("del-social");
    $.ajax({
      method: "POST",
      url: "../core/delete_socials.php",
      data: { social_id: social_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        if (social_id==0) {
          document.location.reload();
        } else {
          toastr["success"]("Удалено", null, {"positionClass": "toast-bottom-right"});
          $("#row_"+social_id).fadeOut(300, ()=>{$("#row_"+social_id).remove();});
          $("#socials_count").text($("#socials_count").text() - 1);
        }
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});