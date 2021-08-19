// ADD
$(document).on("submit", "#add_chat", function(e) {
  e.preventDefault();
  $.ajax({
    method: "POST",
    url: "../core/add_chat.php",
    data: $("#add_chat").serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      toastr["success"]("Сообщение отправлено", null, {"positionClass": "toast-bottom-right"});
      $("#chat").load("../partials/chat.php", function() {
        $(".chat__messages").scrollTop($(".chat__messages").prop("scrollHeight"));
        $(".chat__messages").children().last().addClass("highlight");
        setTimeout(function(){$(".chat__messages").children().last().removeClass("highlight");}, 600);
      });
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// DELETE
$(document).on("click", "[data-del-chat]", function(e) {
  e.preventDefault();
  const $this = $(this);
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const chat_id = $(this).data("del-chat");
    $.ajax({
      method: "POST",
      url: "../core/delete_chat.php",
      data: { chat_id: chat_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Сообщение удалено", null, {"positionClass": "toast-bottom-right"});
        $this.parents(".chat__message").fadeOut(300, ()=>{$this.parents(".chat__message").remove();});
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});