let shopList = document.querySelector('.shop-list');

const nameMaxChars = 12;

fetch("/api/cases/", {
    method: 'GET'
}).then(response => {
    return response.json();
}).then(data => {
    data.forEach(elem => {
        let button = document.createElement('a');
        button.href = "/open?id=" + elem['id'];

        let e = document.createElement('div');
        e.classList.add('barrel');

        let img = document.createElement('img');
        img.classList.add('barrel-size');
        img.src = '../pic/barrels.png';
        e.append(img);

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
