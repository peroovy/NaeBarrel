function login() {
    let email = document.querySelector(".login").value;
    let password = document.querySelector(".password").value;
    let post = {
        "email": email,
        "password": password
    }

    console.log(post)

    fetch("/api/auth/login", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(post)
    }).then(response => {
        return response.json();
    }).then(data => {
        localStorage.setItem("loginToken", "Bearer " + data['token']);
        console.log("done!")
        window.location.href = '/';
    });
}
