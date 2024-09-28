$(document).ready(function () {
    let backendUrl = "https://fond-generally-stag.ngrok-free.app/Task%201/php";
    $("#registerForm").on("submit", function (event) {
      let formData = $(this).serialize();
      registerFormSubmit(event, formData, backendUrl);
    });
  });
  
  let registerFormSubmit = (event, formData, backendUrl) => {
    console.log(formData);
    event.preventDefault();
    $.ajax({
      type: "POST",
      url: `${backendUrl}/register.php`,
      data: formData,
      success: function (response) {
        console.log(response);
        var res = response;
        alert(res.message);
        if (res.status === "success") {
          window.location.href = "login.html";
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        console.error("Response Text:", xhr.responseText);
        alert(
          "An error occurred while processing your request. Please try again."
        );
      },
    });
  };