function updateBal() {
    fetch("/api/profile", {
        method: 'GET'
    }).then(response => {
        return response.json();
    }).then(data => {
        document.querySelector('.balance').textContent = data['balance'];
    });
}

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
    });
}

function closeItem() {
    document.querySelector('.openedItem').classList.add('hidden');
    document.querySelector('.background').style.filter = "blur(0px)";
}

updateBal();


let list = document.querySelector('.inv-list');

let rarity = {
    3: 'common',
    4: 'rare'
};

let myProfile = false;
fetch("/api/profile/", {
    method: 'GET',
    headers: {
        'Authorization': localStorage.getItem("loginToken")
    }
}).then(response => {
    return response.json();
}).then(data => {
    myProfile = clientLogin == data['login'];
    console.log(clientLogin);
    console.log(data['login']);
});

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
        if (myProfile){
            block.onclick = function () {
                document.querySelector('.openedItem').classList.remove('hidden');
                document.querySelector('.openedItem').id = item['inv_id'];
                document.querySelector('.background').style.filter = "blur(5px)";

                document.querySelector('.actually-drop-size').src = item['picture'];
                document.querySelector('.actually-drop-name').textContent = item['name'];
            };
        }
        let img = document.createElement('img');
        img.src = item['picture'];
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
