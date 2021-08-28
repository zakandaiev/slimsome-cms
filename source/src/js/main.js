/* ==========  Third party (included with rigger)  ========== */
//= ../../node_modules/jquery/dist/jquery.min.js
//= ../../node_modules/toastr/build/toastr.min.js
//= ../../node_modules/trumbowyg/dist/trumbowyg.min.js
//= ../../node_modules/trumbowyg/dist/langs/ru.min.js
//= ../../node_modules/cyrillic-to-translit-js/dist/bundle.js
//= ../../node_modules/medium-zoom/dist/medium-zoom.min.js
//= ../../node_modules/slick-carousel/slick/slick.min.js
//= ../../node_modules/chart.js/dist/chart.min.js

$(document).ready(function() {
  // HEADER LOGIN BUTTON
  $(".header__login-btn").on("click", function(e) {
    e.preventDefault();
    $(this).toggleClass("active");
  });
  $("[data-open-login-form]").on("click", function(e) {
    e.preventDefault();
    $("html, body").animate({
        scrollTop: 0
      },
      300,
      function() {
        $(".header").addClass("header_mobile");
        $(".header__login-btn").addClass("active");
      }
    );
  });

  // HEADER MOBILE
  $(".header__burger").on("click", function() {
    $(".header").toggleClass("header_mobile");
  });

  // ACCODRION
  const accondionsContent = $('.accordion__content').hide();
  $('.accordion__title').click(function() {
    if ($(this).hasClass("active")) {
      $(this).next().slideUp();
    } else {
      $(this).next().slideDown();
    }
    $(this).toggleClass("active");
  });

  // WYSIWYG
  $("[data-editor='wysiwyg']").trumbowyg({
    lang: 'ru',
    svgPath: "../img/trumbowyg/icons.svg",
    removeformatPasted: true,
    tagsToRemove: ['script', 'link', 'meta', 'html', 'head', 'title', 'embed', 'iframe', 'input', 'select', 'textarea'],
    defaultLinkTarget: '_blank',
    tagClasses: {
      a: 'bordered'
    }
  });

  // LOAD SERVER INFO
  $("#server-info").load("../partials/widget_server_info.php");

  // COPY TO CLIPBOARD
  $("[data-copy]").on("click", function() {
    const $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(this).data("copy")).select();
    document.execCommand("copy");
    $temp.remove();
    if ($(this).data("copy-toast")) {
      toastr["info"]($(this).data("copy-toast"), null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["info"]("Скопировано", null, {"positionClass": "toast-bottom-right"});
    }
  });

  // TABLE SEARCH
  $(".table-search > button").on("click", function() {
    if (window.matchMedia("(max-width: 540px)").matches || ($(this).siblings("form").find("input").val() && $(this).parent(".table-search").hasClass("active"))) {
      $(this).siblings("form").submit();
    } else {
      $(this).parent(".table-search").toggleClass("active");
      $(this).siblings("form").find("input").focus();
    }
  });

  // SCROLL LINKS
  function smoothScroll(target) {
    $("html,body").animate(
      {"scrollTop":$(target).offset().top},
      300
    );
  }
  $("[data-scroll-to]").on("click", function(e) {
    e.preventDefault();
    smoothScroll("#" + $(this).data("scroll-to"));
  });

  // IMAGE ZOOM
  mediumZoom("[data-zoomable]", {
    margin: 14,
    background: '#000',
    scrollOffset: 0
  });

  // CAROUSEL
  $(".carousel").slick({
    arrows: true,
    dots: true,
    infinite: false,
    speed: 300,
    slidesToShow: 1,
    slidesToScroll: 1,
    adaptiveHeight: true,
    autoplay: false,
    autoplaySpeed: 3000,
    dotsClass: 'carousel__nav',
    prevArrow: '<div class="carousel__prev"><svg aria-hidden="true" focusable="false" viewBox="0 0 640 640"><path d="M428.36 25C445.03 8.33 472.12 8.33 488.79 25C505.45 41.67 505.45 68.76 488.79 85.42C472.32 101.89 389.96 184.25 241.71 332.5C389.96 480.75 472.32 563.11 488.79 579.58C505.45 596.25 505.45 623.34 488.79 640.01C472.12 656.67 445.03 656.67 428.36 640.01C400.65 612.3 178.92 390.64 151.2 362.93C143.13 354.9 138.63 343.97 138.72 332.59C138.72 321.48 142.82 310.54 151.2 302.16C206.63 246.73 400.65 52.72 428.36 25Z" fill="currentColor"></path></svg></div>',
    nextArrow: '<div class="carousel__next"><svg aria-hidden="true" focusable="false" viewBox="0 0 238 238"><path d="M181.776 107.719L78.705 4.648c-6.198-6.198-16.273-6.198-22.47 0s-6.198 16.273 0 22.47l91.883 91.883-91.883 91.883c-6.198 6.198-6.198 16.273 0 22.47s16.273 6.198 22.47 0l103.071-103.039a15.741 15.741 0 0 0 4.64-11.283c0-4.13-1.526-8.199-4.64-11.313z" fill="currentColor"></path></svg></div>'
  });

  // INCLUDE FORM PARTIALS
  //= forms/form_login.js
  //= forms/form_registration.js
  //= forms/form_restore.js
  //= forms/form_users.js
  //= forms/form_site.js
  //= forms/form_settings.js
  //= forms/form_services.js
  //= forms/form_payments.js
  //= forms/form_socials.js
  //= forms/form_pages.js
  //= forms/form_buy.js
  //= forms/form_prolong.js
  //= forms/form_news.js
});

// UPLOAD IMAGES
$(document).on("change", "input[type='file']", function (event) {
  const input = $(this);
  let file_list = $(this).prop('files');
  let form_data = new FormData();
  $.each(file_list,function(id, elem) {
    form_data.append('images[]', elem);
  });
  form_data.append('upload_type', input.data("upload"));
  $.ajax({
    method: "POST",
    url: "../core/upload_form_images.php",
    data: form_data,
    contentType: false,
    cache: false,
    processData: false
  }).done(function(response) {
    const jsonData = JSON.parse(response);
    if (jsonData.success == "1") {
      let output = "";
      if (input.data("upload-multiple")) {
        output = input.prev(".form__preview").html();
      }
      $.each(jsonData.images, function(id, elem) {
        output += `<div class="form__image"><img src="${elem}"></div>`;
      });
      input.prev(".form__preview").html(output);
    } else if (jsonData.success == "-1") {
      toastr["error"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    } else {
      toastr["warning"](jsonData.error, null, {"positionClass": "toast-bottom-right"});
    }
  });
});
$(document).on("click", ".form__preview .form__image", function() {
  $(this).fadeOut(300, ()=>{$(this).remove();});
});

// CHAT
$(document).on("click", ".chat_for-logged .author, .chat_for-logged .avatar", function (e) {
  e.preventDefault();
  const msgId = $(this).parents(".chat__message").prop("id").split('message_id_')[1];
  const name = $(this).data("name");
  $("#reply_info").html('в ответ для <b>'+name+'</b> <span id="reply_remove" title="Удалить ответ">✗</span>');
  $("#add_chat input[name='refference']").prop("value", msgId);
});
$(document).on("click", "#reply_remove", function (e) {
  e.preventDefault();
  $(this).parent().html("");
});
$(document).on("click", ".chat .anchor", function (e) {
  //e.preventDefault();
  $(".chat__messages").css("scroll-behavior", "smooth");
  const msgId = $(this).prop("hash");

  setTimeout(function(){$(msgId).addClass("highlight");}, 300);
  setTimeout(function(){$(msgId).removeClass("highlight");}, 900);
  /*const msgPos = $(msgId).offset().top;
  $(".chat__messages").animate({
      scrollTop: msgPos
    },
    300,
    function() {
      $(msgId).addClass("highlight");
      setTimeout(function(){$(msgId).removeClass("highlight");}, 600);
    }
  );*/
});
$(document).on("click", ".chat__smiles img", function (e) {
  e.preventDefault();
  $("#add_chat input[name='message']").val($("#add_chat input[name='message']").val() + " :" + $(this).data("filename").split(".gif")[0] + ":");
});
$(document).on("mouseover", ".chat__smiles", function () {
  $(this).siblings("svg").addClass("active");
});
$(document).on("mouseleave", ".chat__smiles", function () {
  $(this).siblings("svg").removeClass("active");
});
$(document).ready(function() {
  // chat auto scroll to bottom on page load
  if ($(".chat__messages").length) {
    $(".chat__messages").scrollTop($(".chat__messages")[0].scrollHeight);
  }
  // chat load more
  let is_chat_load_more_enabled = true;
  $(".chat__messages").on("scroll", function() {
    const $this = $(this);
    let messages_count = $(this).find(".chat__message").length;
    if ($(this).scrollTop() < 300 && is_chat_load_more_enabled) {
      is_chat_load_more_enabled = false;
      $.ajax({
        method: "POST",
        url: "../core/db_chat_load_more.php",
        data: { from: messages_count }
      }).done(function(response) {
        const jsonData = JSON.parse(response);
        if (jsonData.success == "1") {
          is_chat_load_more_enabled = true;
          $this.prepend(jsonData.body);
        }
      });
    }
  });
});

// CHAT FORM
//= forms/form_chat.js