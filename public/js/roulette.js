if (!barrelId) {
    window.location.href = "/";
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

function openBarrel(){
    document.getElementById("openedItem").classList.remove('hidden');
    document.getElementById("background").style.filter = "blur(5px)";

    let post = JSON.stringify({
        "case_id": barrelId
    });
    console.log(post);
    fetch("/api/cases/buy", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem("loginToken")
        },
        body: post
    }).then(response => {
        if (response.status === 401) {
            window.location.href = "/login/";
        }
        if (response.status === 422) {
            document.querySelector(".actually-drop-size").src = '../pic/fish.png';
            document.querySelector(".actually-drop-name").textContent = "Не хватает денег!!!";
        }
        return response.json();
    }).then(data => {
        document.querySelector(".actually-drop-size").src = data['picture'];
        document.querySelector(".actually-drop-name").textContent = data["name"];
        updateBal();
    });

}

function closeBarrel(){
    document.getElementById("openedItem").classList.add('hidden');
    document.getElementById("background").style.filter = "blur(0px)";
}

updateBal();

let rarity = {
    3: 'common',
    4: 'rare'
};

fetch("/api/cases/" + barrelId, {
    method: 'GET'
}).then(response => {
    return response.json();
}).then(data => {
    console.log(data);

    document.querySelector('.barrel-name').textContent = data['name'];
    document.querySelector('.simple-barrel').src = data['picture'];
    document.querySelector('.open').textContent = data['price'];

    let dropList = document.querySelector('.drop-list');
    data['items'].forEach(item => {
        let drop = document.createElement('div');
        drop.classList.add('drop');
        drop.classList.add(rarity[item['quality']]);

        let img = document.createElement('img');
        img.src = item['picture'];
        img.classList.add('fish-size');
        drop.append(img);

        let dropName = document.createElement('p');
        dropName.classList.add('drop-name');
        dropName.textContent = item['name'];
        drop.append(dropName);

        let dropChance = document.createElement('p');
        dropChance.classList.add('drop-chance');
        dropChance.textContent = parseFloat(item['chance']) * 100 + "%";
        drop.append(dropChance);

        dropList.append(drop);
    })


});

