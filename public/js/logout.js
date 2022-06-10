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
