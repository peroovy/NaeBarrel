function updateBal() {
    fetch("/api/profile", {
        method: 'GET'
    }).then(response => {
        return response.json();
    }).then(data => {
        document.querySelector('.balance').textContent = data['balance'];
    });
}
