import "./public.scss";

jQuery(document).ready(function ($) {
  // set reaction cookie
  // function setReactionCookie(postId) {
  //   const cookieName = `postReaction_${postId}`;
  //   const expirationDate = new Date();
  //   expirationDate.setDate(expirationDate.getDate() + 365); // Cookie expires in 1 year
  //   document.cookie = `${cookieName}=true; expires=${expirationDate.toUTCString()}; path=/`;
  //   localStorage.setItem(cookieName, true);
  // }

  // check if visitor already reacted
  // function hasReacted(postId) {
  //   const cookieName = `postReaction_${postId}`;

  //   if (document.cookie.includes(cookieName) || localStorage.getItem(cookieName)) {
  //     return true;
  //   }
  //   return false;
  // }

  const alertElement = document.querySelector(".cprAlert span");

  $(".post-reactions-list li").on("click", function () {
    // console.log("clicked", alertElement);

    var post_id = $(this).data("post-id");
    var active_reaction = $(this).data("reaction-type");
    $(this).parent().addClass("disabled");
    // console.log(active_reaction);

    $.ajax({
      type: "POST",
      url: postReactScript.ajaxURL,
      data: {
        action: "update_post_reaction",
        post_id,
        reaction_type: active_reaction,
        nonce: postReactScript.nonce
      },
      success: (response) => {
        // console.log(response);
        // return;
        if (!response.success) {
          alertElement.innerText = response.data;
          alertElement.parentNode.style.display = "flex";
          // console.log("error", alertElement);
          setTimeout(() => {
            alertElement.parentNode.style.display = "none";
          }, 4000);
          $(this).parent().removeClass("disabled");
          return;
        }

        try {
          $(this)
            .parent()
            .find("li")
            .map((index, element) => {
              const reaction_type = $(element).data("reaction-type");
              $(this).parent().find(`li[data-reaction-type="${reaction_type}"] span:not(.prc_react_icon)`).html(response.data.count[reaction_type]);
              if (reaction_type === active_reaction && !$(this).hasClass("reacted_to")) {
                console.log(reaction_type, active_reaction);
                $(element).addClass("reacted_to");
              } else {
                console.log("remove class");
                $(element).removeClass("reacted_to");
              }
            });
        } catch (error) {
          console.error(error);
        }
        $(this).parent().removeClass("disabled");
      },
      error: (error) => {
        $(this).parent().removeClass("disabled");
        console.log(error.message);
      },
    });
    // }
  });
});
