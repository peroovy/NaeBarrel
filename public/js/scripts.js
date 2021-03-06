function updateBal() {
    fetch("/api/profile", {
        method: 'GET'
    }).then(response => {
        return response.json();
    }).then(data => {
        document.querySelector('.balance').textContent = data['balance'];
    });
}

updateBal();

let shopList = document.querySelector('.shop-list');

const nameMaxChars = 12;

fetch("/api/cases", {
    method: 'GET',
    headers: {
        'Authorization': localStorage.getItem("loginToken")
    }
}).then(response => {
    return response.json();
}).then(data => {
    data.forEach(elem => {
        let button = document.createElement('a');
        button.href = "/open?id=" + elem['id'];

        let e = document.createElement('div');
        e.classList.add('barrel');

        let tables = document.createElement('img');
        tables.classList.add('barrel-size');
        tables.src = '../pic/tables.png';
        tables.style.backgroundImage = "url('" + elem['picture'] + "')";
        e.append(tables);

        let price = document.createElement('p');
        price.classList.add('price');
        price.textContent = elem['price']
        e.append(price);

        let name = document.createElement('p');
        name.classList.add('name');
        name.textContent = elem['name'];
        if (name.textContent.length > nameMaxChars) {
            name.textContent = name.textContent.substr(0, nameMaxChars - 3) + "...";
        }
        e.append(name);

        button.append(e)
        shopList.append(button);
    })
});
