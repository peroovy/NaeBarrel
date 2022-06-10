let list = document.querySelector('.inv-list');

fetch("/api/clients/" + clientLogin + "/inventory", {
    method: 'GET',
    headers: {
        'Authorization': localStorage.getItem("loginToken")
    }
}).then(response => {
    return response.json();
}).then(data => {
    data.forEach(item => {
        let block = document.createElement('div');
        block.classList.add('block')

        let img = document.createElement('img');
        img.src = '../pic/fish.png';
        img.classList.add('fish-size');
        block.append(img);

        let name = document.createElement('p');
        name.textContent = item['name'];
        name.classList.add('iname');
        block.append(name);

        let sell = document.createElement('p');
        sell.textContent = "sell";
        sell.classList.add('sell');
        block.append(sell);

        let price = document.createElement('p');
        price.textContent = item['price'];
        price.classList.add('iprice');
        block.append(price);

        list.append(block);
    })
})
