/***** ADMINKA *****/
// CYR TO LAT
$("#news_title").on("change", function() {
  $("#news_url").prop("value", cyrillicToTranslit().transform($(this).val().toLowerCase().replace(/\s+/g, ' ').replace(/[^а-яё\w\s]/gi, ""),"-"));
});
// ADD
$("#add_news").on("submit", function(e) {
  e.preventDefault();
  $this = $("#add_news");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  const formData = new FormData(this);
  const image = $this.find("input[name='image[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  formData.append("image_str", image);
  $.ajax({
    method: "POST",
    url: "../core/add_news.php",
    data: formData,
    processData: false,
    contentType: false,
    cache: false
  }).done(function(response) {
    $this.removeClass("active");
    $this.find(".loader").remove();
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      window.location.href = "profile.php?section=news";
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// EDIT
$("#edit_news").on("submit", function(e) {
  e.preventDefault();
  $this = $("#edit_news");
  $this.addClass("active");
  $this.append('<div class="loader"><div></div><div></div><div></div><div></div></div>');
  const formData = new FormData(this);
  const image = $this.find("input[name='image[]']").prev(".form__preview").find("img").map(function() { return $(this).attr('src'); }).get();
  formData.append("image_str", image);
  $.ajax({
    method: "POST",
    url: "../core/edit_news.php",
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
$("[data-del-news]").on("click", function(e) {
  e.preventDefault();
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const news_id = $(this).data("del-news");
    $.ajax({
      method: "POST",
      url: "../core/delete_news.php",
      data: { news_id: news_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Удалено", null, {"positionClass": "toast-bottom-right"});
        $("#row_"+news_id).fadeOut(300, ()=>{$("#row_"+news_id).remove();});
        $("#news_count").text($("#news_count").text() - 1);
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});

/***** PUBLIC *****/
// SEARCH
$(".widget-search button").on("click", function() {
  $(this).siblings("form").submit();
});
// COMMENTS
$(document).on("click", ".comments__smiles img", function (e) {
  e.preventDefault();
  $("#add_comment input[name='comment']").val($("#add_comment input[name='comment']").val() + " :" + $(this).data("filename").split(".gif")[0] + ":");
});
$(document).on("mouseover", ".comments__smiles", function () {
  $(this).siblings("svg").addClass("active");
});
$(document).on("mouseleave", ".comments__smiles", function () {
  $(this).siblings("svg").removeClass("active");
});
// add
$(document).on("submit", "#add_comment", function(e) {
  e.preventDefault();
  $.ajax({
    method: "POST",
    url: "../core/add_comment.php",
    data: $("#add_comment").serialize()
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      $("#add_comment")[0].reset();
      toastr["success"]("Комментарий отправлен", null, {"positionClass": "toast-bottom-right"});
      $(".comments__messages").find("#no_comments_label").remove();
      $(".comments__messages").append(jsonData.comment);
      $(".comments__messages").children().last().addClass("highlight");
      setTimeout(function(){$(".comments__messages").children().last().removeClass("highlight");}, 600);
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
// del
$(document).on("click", "[data-del-comment]", function(e) {
  e.preventDefault();
  const $this = $(this);
  const confirmation = confirm("Вы подтверждаете удаление?");
  if (confirmation) {
    const comment_id = $(this).data("del-comment");
    $.ajax({
      method: "POST",
      url: "../core/delete_comment.php",
      data: { comment_id: comment_id }
    }).done(function(response) {
      const jsonData = JSON.parse(response);
      if (jsonData.success == "1") {
        toastr["success"]("Комментарий удален", null, {"positionClass": "toast-bottom-right"});
        $this.parents(".comments__item").fadeOut(300, ()=>{$this.parents(".comments__item").remove();});
      } else if (jsonData.success == "-1") {
        toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      } else {
        toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
      }
    });
  }
});
// LOAD MORE
let is_comment_load_more_enabled = true;
$(document).on("scroll", function() {
  let loader = $(".comments__messages").find(".loader");
  if(loader.length) {
    let news_id = $("#add_comment").find("input[name='news_id']").val();
    let messages_count = $("#comments").find(".comments__item").length;
    if (isScrolledIntoView(loader) && is_comment_load_more_enabled) {
      is_comment_load_more_enabled = false;
      $.ajax({
        method: "POST",
        url: "../core/db_comments_load_more.php",
        data: { news_id: news_id, from: messages_count }
      }).done(function(response) {
        const jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          is_comment_load_more_enabled = true;
          loader.before(jsonData.body);
        } else {
          loader.remove();
        }
      });
    }
  }
});
function isScrolledIntoView(elem) {
  let docViewTop = $(window).scrollTop();
  let docViewBottom = docViewTop + $(window).height();

  let elemTop = $(elem).offset().top;
  let elemBottom = elemTop + $(elem).height();

  return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}