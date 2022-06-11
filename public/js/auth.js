function login() {
    let email = document.querySelector(".email").value;
    let password = document.querySelector(".password").value;
    let post = {
        "email": email,
        "password": password
    }

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

function register() {
    let email = document.querySelector(".email").value;
    let log = document.querySelector(".login").value;
    let password = document.querySelector(".password").value;
    let post = {
        "login": log,
        "email": email,
        "password": password,
        "permission": 3
    }

    fetch("/api/auth/register", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(post)
    }).then(response => {
        if (response.status === 200) {
            login();
        }
        return response.json();
    });
}

function logout() {
    console.log(localStorage.getItem("loginToken"));
    fetch("/api/auth/logout", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem("loginToken")
        }
    }).then(response => {
        localStorage.removeItem("loginToken");
        window.location.href = '/';
        return response.json();
    });
}

function deleteProfile() {
    fetch("/api/auth/deleteprofile", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem("loginToken")
        }
    }).then(response => {
        console.log("done!")
        window.location.href = '/';
        return response.json();
    })
}
