function closeBarrel(){
    document.getElementById("openedItem").classList.add('hidden');
    document.getElementById("background").style.filter = "blur(0px)";
}

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

let list = document.querySelector('.inv-list');

let rarity = {
    1: 'common',
    2: 'rare',
    3: 'superrare',
    4: 'rarest'
};

let auth = false;
try {
    fetch("/api/profile/", {
        method: 'GET',
        headers: {
            'Authorization': localStorage.getItem("loginToken")
        }
    }).then(response => {
        if (response.status === 200) {
            auth = true;
        }
        return response.json();
    });
} catch (e) {

}

fetch("/api/market/", {
    method: 'GET'
}).then(response => {
    return response.json();
}).then(data => {
    data.forEach(lot => {
        let item = lot['item'];
        let block = document.createElement('div');
        block.classList.add('block');
        block.classList.add(rarity[item['quality']]);
        if (auth) {
            block.onclick = function () {
                let post = JSON.stringify({
                    "lot_id": lot['id']
                });
                fetch("/api/market/buy", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': localStorage.getItem("loginToken")
                    },
                    body: post
                }).then(response => {
                    return response.json();
                }).then(data => {
                    if ('error_status' in data) {
                        if (data['error_status'] === "NotEnoughMoney") {
                            document.querySelector(".openedItem").classList.remove('hidden');
                            document.querySelector(".background").style.filter = "blur(5px)";
                        }
                    } else {
                        location.reload();
                    }
                })
            }
            let buy = document.createElement('p');
            buy.classList.add("market-buy");
            buy.textContent = "купить";
            block.append(buy);
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
        price.textContent = lot['price'];
        price.classList.add('iprice');
        block.append(price);

        list.append(block);
    })
});
