const password = document.getElementById("password");
const show = document.getElementById("showPass");
const hide = document.getElementById("hidePass");

hide.style.display = "none";

show.onclick = function () {

    password.type = "text";

    show.style.display = "none";
    hide.style.display = "block";

}

hide.onclick = function () {

    password.type = "password";

    hide.style.display = "none";
    show.style.display = "block";

}