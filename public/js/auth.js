let post = {
    "email": "nick.samkov@yandex.ru",
    "password": "123"
}

console.log(post)

fetch("/api/auth/login", {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: post
}).then(response => {
    return response.json();
});
