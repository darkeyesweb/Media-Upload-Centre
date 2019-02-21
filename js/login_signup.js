function login() {
  if ($('#username').val() !== "") {
    if ($('#password').val() !== "") {
      $.ajax({
        url: 'auth.php',
        type: 'get',
        data: {
          'username': $('#username').val(),
          'password': $('#password').val(),
          'function': 'login'
        },
        success: (xhr) => {
          if (xhr == "login") {
            window.location.href = "index.html";
          } else if (xhr == "badusername") {
            alert("Unknown Account");
          } else if (xhr == "badpass") {
            alert("Incorrect Password");
          } else {
          	alert(xhr);
            alert("Unknown Server Error Please Contact the Server Admin: admin@darkeyes.io");
          }
        }
      });
    }
  }
}

function loginClearError() {

    $('#login_error_password_box').text('');
    $('#login_error_username_box').text('');
    $('#login_error_box').text('');

    $('#login_div > input').css('background-color: default');
    $('#login_div > input').css('text: default');

}