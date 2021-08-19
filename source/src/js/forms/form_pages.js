// CYR TO LAT
$("#page_name").on("change", function() {
  $("#page_url").prop("value", cyrillicToTranslit().transform($(this).val().toLowerCase().replace(/\s+/g, ' ').replace(/[^а-яё\w\s]/gi, ""),"-"));
});
// ADD
$("#add_page").on("submit", function(e) {
  e.preventDefault();
  $this = $("#add_page");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  $.ajax({
    method: "POST",
    url: "../core/add_page.php",
    data: $this.serialize()
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
     window.location.href = "profile.php?section=pages";
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// EDIT
$("form[data-form-edit-page]").on("submit", function(e) {
  e.preventDefault();
  $this = $("form[data-form-edit-page]");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  if ($(this).attr("id") == $(this).find("input[name='page_id']").val()) {
    const form_data = $(this).serialize();
    $.ajax({
      method: "POST",
      url: "../core/edit_page.php",
      data: form_data
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
  } else {
    toastr["warning"]("Неверный параметр страницы", null, {"positionClass": "toast-bottom-right"});
  }
});
// DELETE
$("[data-del-page]").on("click", function(e) {
  e.preventDefault();
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const page_id = $(this).data("del-page");
    $.ajax({
      method: "POST",
      url: "../core/delete_page.php",
      data: { page_id: page_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Удалено", null, {"positionClass": "toast-bottom-right"});
        $("#row_"+page_id).fadeOut(300, ()=>{$("#row_"+page_id).remove();});
        $("#pages_count").text($("#pages_count").text() - 1);
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});