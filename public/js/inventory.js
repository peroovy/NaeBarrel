function putOnMarket() {
    let price = document.querySelector('.market-field').value;
    if (price === '') {
        return;
    }

    let invId = document.querySelector('.openedItem').id;
    let post = JSON.stringify({
        "inventory_id": parseInt(invId),
        "price": parseInt(price)
    });

    fetch("/api/market/createlot", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem("loginToken")
        },
        body: post
    }).then(response => {
        return response.json();
    }).then(data => {
        location.reload();
    })
}

function sellItem() {
    let invId = document.querySelector('.openedItem').id;
    let post = JSON.stringify({
        "item_ids": [parseInt(invId)]
    });
    fetch("/api/items/sell", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem("loginToken")
        },
        body: post
    }).then(response => {
        return response.json();
    }).then(data => {
        location.reload();
    })
}

function closeItem() {
    document.querySelector('.openedItem').classList.add('hidden');
    document.querySelector('.background').style.filter = "blur(0px)";
}


let list = document.querySelector('.inv-list');

let rarity = {
    3: 'common',
    4: 'rare'
};

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
        block.classList.add('block');
        block.classList.add(rarity[item['quality']]);
        block.onclick = function () {
            document.querySelector('.openedItem').classList.remove('hidden');
            document.querySelector('.openedItem').id = item['inv_id'];
            document.querySelector('.background').style.filter = "blur(5px)";

            document.querySelector('.actually-drop-size').src = '../pic/fish.png';
            document.querySelector('.actually-drop-name').textContent = item['name'];
        };

        let img = document.createElement('img');
        img.src = '../pic/fish.png';
        img.classList.add('fish-size');
        block.append(img);

        let name = document.createElement('p');
        name.textContent = item['name'];
        name.classList.add('iname');
        block.append(name);

        let price = document.createElement('p');
        price.textContent = item['price'];
        price.classList.add('iprice');
        block.append(price);

        list.append(block);
    })
})
